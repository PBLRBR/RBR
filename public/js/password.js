document.addEventListener('DOMContentLoaded', () => {
    const forgot = document.getElementById('forgot-password-form');
    if (forgot) forgot.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            await apiRequest('auth/lupa_password.php',{method:'POST',body:{email: forgot.querySelector('input[type="email"]').value}});
            alert('Jika email terdaftar, tautan reset akan dikirim.');
        } catch(error) { alert(error.message); }
    });
    const reset = document.getElementById('reset-password-form');
    if (reset) reset.addEventListener('submit', async (event) => {
        event.preventDefault();
        const token = new URLSearchParams(location.search).get('token');
        if (!token) return alert('Tautan reset tidak valid.');
        const [password, konfirmasi_password] = [...reset.querySelectorAll('input[type="password"]')].map(x => x.value);
        if (password !== konfirmasi_password) return alert('Konfirmasi password tidak sesuai.');
        try {
            await apiRequest('auth/reset_password.php',{method:'POST',body:{token,password,konfirmasi_password}});
            alert('Password berhasil diganti. Silakan login.');
            location.assign('/?auth=login');
        } catch(error) { alert(error.message); }
    });
});
