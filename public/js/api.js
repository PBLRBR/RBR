const API_BASE = '/api';
let csrfTokenPromise = null;

async function fetchCsrfToken(forceNew = false) {
    if (forceNew) csrfTokenPromise = null;

    if (!csrfTokenPromise) {
        csrfTokenPromise = (async () => {
            const response = await fetch(`${API_BASE}/auth/csrf.php`, {
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

        const response = await fetch(`${API_BASE}/${endpoint}`, {
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
            throw new Error(result.message || 'Terjadi kesalahan.');
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
    document.querySelectorAll('[data-logout]').forEach(button => button.addEventListener('click', async () => {
        try { await apiRequest('auth/logout.php', {method:'POST'}); location.assign('/'); }
        catch(error) { alert(error.message); }
    }));
});
