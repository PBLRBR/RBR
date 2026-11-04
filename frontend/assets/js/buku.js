window.SIMART_Buku = {
    list: () => window.SIMART_API.get('buku/buku.php'),
    detail: (id) => window.SIMART_API.get(`buku/detail_buku.php?id_buku=${encodeURIComponent(id)}`),
    create: (payload) => window.SIMART_API.post('buku/tambah_buku.php', payload),
    update: (payload) => window.SIMART_API.post('buku/edit_buku.php', payload),
    remove: (id) => window.SIMART_API.post('buku/hapus_buku.php', { id_buku: id }),
};
