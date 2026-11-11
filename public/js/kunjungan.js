document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('visit-form');
    const msg = document.getElementById('visit-message');
    const button = document.getElementById('visit-submit');
    if (!form) return;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        button.disabled = true;
        msg.hidden = true;
        try {
            const payload = {
                nama_pengunjung: document.getElementById('visit-name').value.trim(),
                no_hp: document.getElementById('visit-phone').value.trim(),
                keperluan: document.getElementById('visit-purpose').value
            };
            const result = await apiRequest('kunjungan/daftar_kunjungan.php', {method:'POST', headers:{'X-CSRF-Token':form.dataset.csrf}, body:payload});
            msg.classList.remove('visit-error');
            msg.textContent = `Berhasil tercatat untuk ${payload.nama_pengunjung}, tanggal ${result.tanggal_kunjungan} pukul ${result.jam_masuk}.`;
            form.reset();
        } catch (error) {
            msg.classList.add('visit-error');
            msg.textContent = error.message;
        } finally { msg.hidden = false; button.disabled = false; }
    });
});
