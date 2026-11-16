document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('visit-form');
    if (!form) return;
    const msg = document.getElementById('visit-message');
    const button = document.getElementById('visit-submit');
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        button.disabled = true;
        msg.hidden = true;
        try {
            const payload = {
                nama_pengunjung: document.getElementById('visit-name').value.trim(),
                no_hp: document.getElementById('visit-phone').value.trim(),
                keperluan: document.getElementById('visit-purpose').value,
            };
            // Header CSRF dikirim otomatis oleh apiRequest(), bukan dari atribut form terpisah.
            const result = await apiRequest('kunjungan/daftar_kunjungan.php', {method:'POST',body:payload});
            msg.classList.remove('visit-error');
            msg.textContent = `Kunjungan dicatat pada ${result.tanggal_kunjungan} pukul ${result.jam_masuk}.`;
            form.reset();
        } catch(error) {
            msg.classList.add('visit-error');
            msg.textContent = error.message;
        } finally {
            msg.hidden = false;
            button.disabled = false;
        }
    });
});
