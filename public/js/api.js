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

    return send();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-logout]').forEach(button => button.addEventListener('click', async () => {
        try { await apiRequest('auth/logout.php', {method:'POST'}); location.assign('/'); }
        catch(error) { alert(error.message); }
    }));
});
