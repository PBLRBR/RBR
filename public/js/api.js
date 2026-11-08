const API_BASE = '/api';
async function apiRequest(endpoint, options = {}) {
    const { body, headers, ...rest } = options;
    const response = await fetch(`${API_BASE}/${endpoint}`, {
        credentials: 'same-origin', ...rest,
        headers: { ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}), ...headers },
        ...(body !== undefined ? { body: JSON.stringify(body) } : {})
    });
    const raw = await response.text();
    let result;
    try { result = JSON.parse(raw); }
    catch { throw new Error(`Server bukan mengirim JSON (HTTP ${response.status}). Periksa konfigurasi PHP/router.`); }
    if (!response.ok || !result.success) throw new Error(result.message || 'Terjadi kesalahan.');
    return result.data;
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-logout]').forEach(button => button.addEventListener('click', async () => {
        try { await apiRequest('auth/logout.php', {method:'POST'}); location.assign('/'); }
        catch(error) {alert(error.message);}
    }));
});
