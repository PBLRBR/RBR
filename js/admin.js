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

    // popup edit reservasi (UI saja, tombol simpan belum berfungsi)
    const popup = document.getElementById('rsv-popup');
    const fields = {
        ruangan: document.getElementById('edit-ruangan'),
        tanggal: document.getElementById('edit-tanggal'),
        mulai: document.getElementById('edit-jam-mulai'),
        selesai: document.getElementById('edit-jam-selesai'),
        pemohon: document.getElementById('edit-pemohon'),
        agenda: document.getElementById('edit-agenda'),
        peserta: document.getElementById('edit-peserta')
    };

    const openPopup = (card) => {
        const d = card.dataset;
        fields.ruangan.value = d.ruangan;
        fields.tanggal.value = d.tanggal;
        fields.mulai.value = d.mulai;
        fields.selesai.value = d.selesai;
        fields.pemohon.value = d.pemohon;
        fields.agenda.value = d.agenda;
        fields.peserta.value = d.peserta;
        popup.hidden = false;
    };

    const closePopup = () => { popup.hidden = true; };

    document.querySelectorAll('.btn-edit-rsv').forEach(btn => {
        btn.addEventListener('click', () => openPopup(btn.closest('.rsv-card')));
    });

    document.getElementById('rsv-popup-close').addEventListener('click', closePopup);
    document.getElementById('rsv-popup-cancel').addEventListener('click', closePopup);
    popup.addEventListener('click', (e) => { if (e.target === popup) closePopup(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !popup.hidden) closePopup(); });
});