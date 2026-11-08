document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservation-request-form');
    if (!form) return;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
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
            alert('Pengajuan reservasi tersimpan dan menunggu persetujuan admin.');
            location.assign('/riwayat.php');
        } catch (error) { alert(error.message + (error.message.includes('login') ? ' Buka beranda untuk login.' : '')); }
        finally { button.disabled = false; }
    });
});
