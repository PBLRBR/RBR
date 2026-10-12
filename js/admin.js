document.addEventListener('DOMContentLoaded', () => {
    // switch page
    const sidebarBtns = document.querySelectorAll('.sidebar-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    sidebarBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sidebarBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const targetTab = btn.getAttribute('data-tab');
            document.getElementById(`tab-${targetTab}`).classList.add('active');
        });
    });

    // popup: buka/tutup (UI saja, tombol simpan belum berfungsi)
    const openPopup = (popup) => { popup.hidden = false; };
    const closePopup = (popup) => { popup.hidden = true; };

    document.querySelectorAll('.popup-overlay').forEach(popup => {
        popup.querySelectorAll('[data-popup-close]').forEach(btn => {
            btn.addEventListener('click', () => closePopup(popup));
        });
        popup.addEventListener('click', (e) => { if (e.target === popup) closePopup(popup); });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.popup-overlay:not([hidden])').forEach(closePopup);
    });

    // popup tambah buku
    const bookPopup = document.getElementById('book-popup');
    document.getElementById('btn-add-book').addEventListener('click', () => {
        document.getElementById('form-add-book').reset();
        openPopup(bookPopup);
        document.getElementById('book-judul').focus();
    });

    // popup edit reservasi
    const rsvPopup = document.getElementById('rsv-popup');
    const fields = {
        ruangan: document.getElementById('edit-ruangan'),
        tanggal: document.getElementById('edit-tanggal'),
        mulai: document.getElementById('edit-jam-mulai'),
        selesai: document.getElementById('edit-jam-selesai'),
        pemohon: document.getElementById('edit-pemohon'),
        agenda: document.getElementById('edit-agenda'),
        peserta: document.getElementById('edit-peserta')
    };

    document.querySelectorAll('.btn-edit-rsv').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.closest('.rsv-card').dataset;
            fields.ruangan.value = d.ruangan;
            fields.tanggal.value = d.tanggal;
            fields.mulai.value = d.mulai;
            fields.selesai.value = d.selesai;
            fields.pemohon.value = d.pemohon;
            fields.agenda.value = d.agenda;
            fields.peserta.value = d.peserta;
            openPopup(rsvPopup);
        });
    });
});