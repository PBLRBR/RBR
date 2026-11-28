document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const inputs = (form) => [...form.querySelectorAll('input')];
    const registerWarning = document.getElementById('register-warning');
    const showRegisterWarning = (message, success = false) => {
        if (!registerWarning) return;
        registerWarning.textContent = message;
        registerWarning.hidden = !message;
        registerWarning.classList.toggle('auth-warning-success', success);
    };
    if (registerForm) registerForm.addEventListener('input', () => showRegisterWarning(''));
    if (loginForm) loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = loginForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const [email, password] = inputs(loginForm).map(input => input.value);
            const user = await apiRequest('auth/login.php', {method:'POST', body:{email,password}});
            const returnTo = new URLSearchParams(location.search).get('return_to');
            const memberPages = ['/', '/profil.php', '/riwayat.php', '/reservasi.php'];
            const destination = user.role === 'admin'
                ? '/admin/'
                : memberPages.includes(returnTo) ? returnTo : '/';
            location.assign(destination);
        } catch (error) { alert(error.message); }
        finally { button.disabled = false; }
    });
    if (registerForm) registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const [nama, email, no_hp, password] = inputs(registerForm).map(input => input.value.trim());
        const phoneDigits = no_hp.replace(/\D/g, '');
        if (!nama || !email || !/^08\d{8,11}$/.test(phoneDigits) || password.length < 8) {
            showRegisterWarning('Nama, email, dan nomor HP seluler valid (10–13 digit awalan 08), serta password minimal 8 karakter wajib diisi.');
            return;
        }
        showRegisterWarning('');
        const button = registerForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await apiRequest('auth/register.php',{method:'POST',body:{nama,email,no_hp,password}});
            showRegisterWarning('Akun berhasil dibuat. Silakan masuk.', true);
        } catch(error) {showRegisterWarning(error.message);}
        finally {button.disabled = false;}
    });
});
