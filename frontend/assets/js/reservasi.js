window.SIMART_Reservasi = {
    list: () => window.SIMART_API.get('reservasi/reservasi.php'),
    detail: (id) => window.SIMART_API.get(`reservasi/detail_reservasi.php?id_reservasi=${encodeURIComponent(id)}`),
    create: (payload) => window.SIMART_API.post('reservasi/tambah_reservasi.php', payload),
    update: (payload) => window.SIMART_API.post('reservasi/edit_reservasi.php', payload),
    updateStatus: (payload) => window.SIMART_API.post('reservasi/ubah_status_reservasi.php', payload),
    remove: (payload) => window.SIMART_API.post('reservasi/hapus_reservasi.php', payload),
};
