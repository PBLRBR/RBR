    <!-- popup masuk / daftar (dibuka lewat ?auth=login atau ?auth=register) -->
    <div class="modal-overlay" id="modal-auth">
        <div class="modal-box">
            <button type="button" class="modal-close" id="close-modal">&times;</button>
            <div class="modal-tabs">
                <button type="button" class="tab-btn active" id="tab-login-btn">Masuk</button>
                <button type="button" class="tab-btn" id="tab-register-btn">Daftar</button>
            </div>

            <!-- form masuk -->
            <form id="form-login" class="auth-form active">
                <div class="form-group">
                    <label>Email atau Nomor Telepon</label>
                    <input type="text" placeholder="contoh: budi@ratna.org">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" placeholder="Masukkan password Anda">
                </div>
                <button type="submit" class="btn-primary full-width">Masuk</button>
                <p><a href="/lupa-password.php">Lupa password?</a></p>
            </form>

            <!-- form daftar -->
            <form id="form-register" class="auth-form">
                <p class="auth-warning" id="register-warning" role="alert" aria-live="polite" hidden></p>
                <div class="form-group"><label>Nama Lengkap</label><input type="text" placeholder="Nama lengkap"></div>
                <div class="form-group"><label>Email</label><input type="email" placeholder="budi@ratna.org"></div>
                <div class="form-group"><label>Nomor WhatsApp</label><input type="tel" data-simart-phone inputmode="tel" placeholder="0812XXXXXXXX" maxlength="16" required></div>
                <div class="form-group"><label>Password</label><input type="password" placeholder="Minimal 8 karakter"></div>
                <button type="submit" class="btn-primary full-width">Daftar Akun</button>
            </form>
        </div>
    </div>
