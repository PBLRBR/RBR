document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const inputs = (form) => [...form.querySelectorAll('input')];
    const registerWarning = document.getElementById('register-warning');
    const loginWarning = document.getElementById('login-warning');
    const showRegisterWarning = (message, success = false) => {
        if (!registerWarning) return;
        registerWarning.textContent = message;
        registerWarning.hidden = !message;
        registerWarning.classList.toggle('auth-warning-success', success);
    };
    const showLoginNotice = (message) => {
        if (!loginWarning) return;
        loginWarning.textContent = message;
        loginWarning.hidden = !message;
    };
    if (registerForm) registerForm.addEventListener('input', () => showRegisterWarning(''));
    if (loginForm) loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = loginForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const [email, password] = inputs(loginForm).map(input => input.value);
            const user = await apiRequest('auth/login.php', {method:'POST', body:{email,password}});
            if (user.role === 'admin') {
                location.assign('/admin/');
                return;
            }
            const next = new URLSearchParams(location.search).get('next');
            let destination = '/';
            if (next && next.startsWith('/') && !next.startsWith('//') && !next.includes('\\')) {
                const target = new URL(next, location.origin);
                if (target.origin === location.origin && !/^\/admin(?:\/|$)/.test(target.pathname)) {
                    destination = target.pathname + target.search + target.hash;
                }
            }
            location.assign(destination);
        } catch (error) { alert(error.message); }
        finally { button.disabled = false; }
    });
    if (registerForm) registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const fields = inputs(registerForm);
        const nama = fields[0].value.trim();
        const email = fields[1].value.trim();
        const no_hp = fields[2].value.trim();
        const password = fields[3].value;
        const normalizedPhone = window.simartNormalizePhone ? window.simartNormalizePhone(no_hp) : null;
        if (!nama || !email || !normalizedPhone || password.length < 8) {
            showRegisterWarning('Nama, email, dan nomor seluler Indonesia yang valid, serta password minimal 8 karakter wajib diisi. Nomor dapat memakai awalan 08, 62, atau +62.');
            return;
        }
        showRegisterWarning('');
        const button = registerForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            await apiRequest('auth/register.php',{method:'POST',body:{nama,email,no_hp:normalizedPhone,password}});
            registerForm.reset();
            showRegisterWarning('');
            document.getElementById('tab-login-btn')?.click();
            showLoginNotice('Akun berhasil dibuat. Silakan masuk dengan email dan password Anda.');
        } catch(error) {showRegisterWarning(error.message);}
        finally {button.disabled = false;}
    });
});
