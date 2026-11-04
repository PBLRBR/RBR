window.SIMART_Karya = {
    list: () => window.SIMART_API.get('karya/karya.php'),
    detail: (id) => window.SIMART_API.get(`karya/detail_karya.php?id_karya=${encodeURIComponent(id)}`),
    create: (payload) => window.SIMART_API.post('karya/tambah_karya.php', payload),
    update: (payload) => window.SIMART_API.post('karya/edit_karya.php', payload),
    remove: (payload) => window.SIMART_API.post('karya/hapus_karya.php', payload),
};
