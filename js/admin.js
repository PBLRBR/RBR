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

    // sub-tab master data (buku / ruangan)
    const masterTabs = document.querySelectorAll('.master-tab');
    const masterPanels = document.querySelectorAll('.master-panel');

    masterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            masterTabs.forEach(t => t.classList.remove('active'));
            masterPanels.forEach(p => p.classList.remove('active'));

            tab.classList.add('active');
            document.getElementById(`master-${tab.dataset.master}`).classList.add('active');
        });
    });

    // popup tambah / edit ruangan
    const roomPopup = document.getElementById('room-popup');
    const roomTitle = document.getElementById('room-popup-title');
    const roomForm = document.getElementById('form-room');

    document.getElementById('btn-add-room').addEventListener('click', () => {
        roomForm.reset();
        roomTitle.textContent = 'Tambah Ruangan Baru';
        openPopup(roomPopup);
        document.getElementById('room-nama').focus();
    });

    document.querySelectorAll('.btn-edit-room').forEach(btn => {
        btn.addEventListener('click', () => {
            const d = btn.closest('tr').dataset;
            roomForm.reset();
            roomTitle.textContent = 'Edit Ruangan';
            document.getElementById('room-nama').value = d.nama;
            document.getElementById('room-kapasitas').value = d.kapasitas;
            document.getElementById('room-status').value = d.status;
            document.getElementById('room-fasilitas').value = d.fasilitas;
            document.getElementById('room-deskripsi').value = d.deskripsi;
            openPopup(roomPopup);
        });
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