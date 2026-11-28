// Gunakan entry point fisik: berfungsi di Wasmer tanpa .htaccess / URL rewrite.
// Endpoint asli masih bisa dipakai lewat router.php pada localhost.
function apiUrl(endpoint) {
    const [path, query = ''] = String(endpoint).split('?', 2);
    const params = new URLSearchParams(query);
    params.set('route', path);
    return `/api/index.php?${params.toString()}`;
}
let csrfTokenPromise = null;
let sessionExpiredShown = false;
let hadAuthenticatedPage = false;
let sessionProbeRunning = false;

function isPrivateSessionPage() {
    return ['/admin', '/admin/', '/admin/index.php', '/profil.php', '/riwayat.php'].includes(location.pathname);
}

function showSessionExpired() {
    if (sessionExpiredShown) return true;
    sessionExpiredShown = true;
    csrfTokenPromise = null;

    const allowed = ['/', '/profil.php', '/riwayat.php', '/reservasi.php', '/admin/'];
    const requested = new URLSearchParams(location.search).get('return_to');
    const current = location.pathname.startsWith('/admin') ? '/admin/' : location.pathname;
    const target = allowed.includes(requested) ? requested : allowed.includes(current) ? current : '/';
    const loginUrl = '/?' + new URLSearchParams({ auth: 'login', return_to: target });

    const dialog = document.createElement('dialog');
    dialog.id = 'simart-session-expired';
    dialog.setAttribute('aria-labelledby', 'simart-session-title');
    dialog.setAttribute('aria-describedby', 'simart-session-message');
    dialog.innerHTML = `<div aria-hidden="true" style="font-size:30px;color:#b64f30">&#9719;</div>
        <h2 id="simart-session-title">Sesi Telah Berakhir</h2>
        <p id="simart-session-message">Untuk menjaga keamanan akun Anda, silakan masuk kembali untuk melanjutkan aktivitas.</p>
        <p style="font-size:.85rem">Periksa kembali pekerjaan yang belum tersimpan sebelum mengulang pengiriman.</p>
        <button type="button" class="btn-primary">Masuk Kembali</button>`;
    const style = document.createElement('style');
    style.textContent = `#simart-session-expired{box-sizing:border-box;width:min(90vw,400px);border:0;border-radius:16px;padding:28px;background:#faf7ee;color:#302820;text-align:center;font:inherit;box-shadow:0 12px 45px #0003}#simart-session-expired::backdrop{background:#28231fee}#simart-session-expired h2{font-size:1.4rem;margin:12px 0}#simart-session-expired p{line-height:1.5;margin:12px 0}#simart-session-expired button{width:100%;padding:12px;margin-top:8px;cursor:pointer;border:0;border-radius:8px;background:#b64f30;color:white;font:inherit;font-weight:600} @media print {body:has(#simart-session-expired[open]) > :not(#simart-session-expired){display:none!important}}`;
    document.head.append(style);
    document.body.append(dialog);
    dialog.addEventListener('cancel', event => event.preventDefault());
    dialog.querySelector('button').addEventListener('click', () => location.replace(loginUrl));
    dialog.showModal();
    dialog.querySelector('button').focus();
    return true;
}

function handleSessionUnauthorized(status, endpoint) {
    if (status !== 401 || String(endpoint).split('?')[0] === 'auth/login.php') return false;
    if (!hadAuthenticatedPage) return false;
    return showSessionExpired();
}

async function checkSessionStatus() {
    if (!hadAuthenticatedPage || !isPrivateSessionPage() || sessionExpiredShown || sessionProbeRunning || document.hidden) return;
    sessionProbeRunning = true;
    try {
        // Probe pasif: tidak memperpanjang last_activity_at di server.
        const response = await fetch(apiUrl('auth/me.php'), {credentials:'same-origin', cache:'no-store'});
        if (response.status === 401) { showSessionExpired(); return; }
        if (!response.ok) return;
        const result = await response.json();
        if (result.success && result.data === null) showSessionExpired();
    } catch {
        // Gangguan jaringan bukan bukti sesi berakhir.
    } finally { sessionProbeRunning = false; }
}

async function fetchCsrfToken(forceNew = false) {
    if (forceNew) csrfTokenPromise = null;

    if (!csrfTokenPromise) {
        csrfTokenPromise = (async () => {
            const response = await fetch(apiUrl('auth/csrf.php'), {
                method: 'GET', credentials: 'same-origin', cache: 'no-store'
            });
            const result = await response.json();
            const token = result?.data?.csrf_token;
            if (!response.ok || !result.success || typeof token !== 'string') {
                throw new Error(result.message || 'Gagal mendapatkan token keamanan.');
            }
            return token;
        })().catch(error => {
            csrfTokenPromise = null;
            throw error;
        });
    }

    return csrfTokenPromise;
}

let loadingCount = 0;
let pageReloading = false;
function setLoading(on, text = 'Memproses data…') {
    let el = document.getElementById('simart-loading');
    if (!el) {
        if (!on) return;
        const style = document.createElement('style');
        style.textContent = '#simart-loading{position:fixed;inset:0;z-index:2000;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;background:rgba(43,38,35,.45);color:#fff;font:600 .95rem sans-serif}#simart-loading[hidden]{display:none}#simart-loading i{width:42px;height:42px;border:4px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:simart-spin .8s linear infinite}@keyframes simart-spin{to{transform:rotate(360deg)}}';
        document.head.append(style);
        el = document.createElement('div');
        el.id = 'simart-loading';
        el.setAttribute('role', 'status');
        el.innerHTML = '<i></i><span>Memproses data…</span>';
        document.body.append(el);
    }
    el.querySelector('span').textContent = text;
    el.hidden = !on;
}

async function apiRequest(endpoint, options = {}) {
    if (sessionExpiredShown) {
        const error = new Error('Sesi telah berakhir. Silakan login kembali.');
        error.status = 401;
        error.sessionExpired = true;
        throw error;
    }
    const { body, headers, ...rest } = options;
    const method = String(rest.method || 'GET').toUpperCase();
    const mutatesData = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);

    async function send(retryToken = false) {
        const requestHeaders = {
            ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
            ...headers
        };
        if (mutatesData) {
            requestHeaders['X-CSRF-Token'] = await fetchCsrfToken(retryToken);
        }

        const response = await fetch(apiUrl(endpoint), {
            credentials: 'same-origin', ...rest, headers: requestHeaders,
            ...(body !== undefined ? { body: JSON.stringify(body) } : {})
        });

        // Server menolak token SEBELUM menjalankan aksi. Aman mencoba ulang satu kali.
        if (mutatesData && response.status === 419 && !retryToken) {
            return send(true);
        }

        const raw = await response.text();
        let result;
        try { result = JSON.parse(raw); }
        catch { throw new Error(`Server bukan mengirim JSON (HTTP ${response.status}). Periksa konfigurasi PHP/router.`); }

        if (!response.ok || !result.success) {
            const error = new Error(result.message || 'Terjadi kesalahan.');
            error.status = response.status;
            error.code = result.code;
            error.sessionExpired = handleSessionUnauthorized(response.status, endpoint);
            throw error;
        }

        // Logout menghancurkan session, jadi token lama tidak boleh digunakan lagi.
        if (endpoint === 'auth/logout.php') csrfTokenPromise = null;
        return result.data;
    }

    if (!mutatesData) return send();
    loadingCount++; setLoading(true);
    try { return await send(); }
    finally { if (--loadingCount === 0 && !pageReloading) setLoading(false); }
}

document.addEventListener('DOMContentLoaded', () => {
    hadAuthenticatedPage = Boolean(document.querySelector('[data-logout]')) || isPrivateSessionPage();
    if (new URLSearchParams(location.search).get('session') === 'expired' && !hadAuthenticatedPage) {
        showSessionExpired();
    }
    if (hadAuthenticatedPage && isPrivateSessionPage()) {
        checkSessionStatus();
        window.setInterval(checkSessionStatus, 60000);
        document.addEventListener('visibilitychange', checkSessionStatus);
        window.addEventListener('pageshow', checkSessionStatus);
    }
    document.querySelectorAll('[data-logout]').forEach(button => button.addEventListener('click', async () => {
        try { await apiRequest('auth/logout.php', {method:'POST'}); location.assign('/'); }
        catch(error) { if (!error.sessionExpired) alert(error.message); }
    }));
});
