(() => {
    const apiRoot = new URL('../../../backend/api/', document.currentScript.src);

    async function request(path, { method = 'GET', body, ...options } = {}) {
        const response = await fetch(new URL(path, apiRoot), {
            method,
            headers: body ? { 'Content-Type': 'application/json' } : {},
            credentials: 'same-origin',
            body: body ? JSON.stringify(body) : undefined,
            ...options,
        });
        const result = await response.json();
        if (!response.ok || result.success === false) {
            throw new Error(result.message || `Permintaan gagal (${response.status}).`);
        }
        return result;
    }

    window.SIMART_API = {
        get: (path, options) => request(path, options),
        post: (path, body, options = {}) => request(path, { ...options, method: 'POST', body }),
        put: (path, body, options = {}) => request(path, { ...options, method: 'PUT', body }),
        delete: (path, body, options = {}) => request(path, { ...options, method: 'DELETE', body }),
    };
})();
