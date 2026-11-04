window.SIMART_Kategori = {
    list: () => window.SIMART_API.get('kategori/kategori.php'),
    create: (payload) => window.SIMART_API.post('kategori/tambah_kategori.php', payload),
    update: (payload) => window.SIMART_API.post('kategori/edit_kategori.php', payload),
    remove: (payload) => window.SIMART_API.post('kategori/hapus_kategori.php', payload),
};
