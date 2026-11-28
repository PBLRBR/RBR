document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservation-request-form');
    if (!form) return;
    const message = document.getElementById('reservation-message');
    let redirectingAfterSubmit = false;
    const showMessage = (text, success = false, needsLogin = false) => {
        if (!message) return;
        message.querySelector('.reservation-alert-text').textContent = text;
        message.querySelector('.reservation-login-link').hidden = !needsLogin;
        message.querySelector('#reservation-message-title').textContent = success ? 'Berhasil' : 'Perhatian';
        message.classList.toggle('reservation-alert-success', success);
        message.hidden = !text;
    };
    const closeMessage = () => { if (message) message.hidden = true; };
    message?.querySelector('.reservation-alert-close').addEventListener('click', closeMessage);
    message?.addEventListener('click', event => { if (event.target === message) closeMessage(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && message && !message.hidden) closeMessage(); });
    form.addEventListener('input', () => showMessage(''));
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        showMessage('');
        const button = form.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const data = {
                id_tempat: Number(document.getElementById('reserve-place').value),
                tanggal_acara: document.getElementById('reserve-date').value,
                jam_mulai: document.getElementById('reserve-start').value,
                jam_selesai: document.getElementById('reserve-end').value,
                nama_acara: document.getElementById('reserve-name').value.trim(),
                penanggung_jawab: document.getElementById('reserve-pic').value.trim(),
                kontak_wa: document.getElementById('reserve-phone').value.trim(),
                deskripsi_acara: document.getElementById('reserve-desc').value.trim()
            };
            if (data.jam_selesai <= data.jam_mulai) throw new Error('Jam selesai harus setelah jam mulai.');
            await apiRequest('reservasi/tambah_reservasi.php', { method: 'POST', body: data });
            showMessage('Pengajuan reservasi tersimpan dan menunggu persetujuan admin.', true);
            redirectingAfterSubmit = true;
            window.setTimeout(() => location.assign('/riwayat.php'), 1600);
        } catch (error) {
            const needsLogin = error.message.toLowerCase().includes('login');
            showMessage(error.message, false, needsLogin);
        }
        finally { if (!redirectingAfterSubmit) button.disabled = false; }
    });
});
