document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const inputs = (form) => [...form.querySelectorAll('input')];
    if (loginForm) loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = loginForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const [email, password] = inputs(loginForm).map(input => input.value);
            const user = await apiRequest('auth/login.php', {method:'POST', body:{email,password}});
            location.assign(user.role === 'admin' ? '/admin/' : '/riwayat.php');
        } catch (error) { alert(error.message); }
        finally { button.disabled = false; }
    });
    if (registerForm) registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = registerForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const [nama,email,no_hp,password] = inputs(registerForm).map(input => input.value);
            await apiRequest('auth/register.php',{method:'POST',body:{nama,email,no_hp,password}});
            alert('Akun berhasil dibuat. Silakan masuk.');
            location.assign('/?auth=login');
        } catch(error) {alert(error.message);}
        finally {button.disabled = false;}
    });
});
