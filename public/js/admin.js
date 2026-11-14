document.addEventListener('DOMContentLoaded', () => {
    // helper
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const esc = (s) => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    // switch page
    const sidebarBtns = $$('.sidebar-btn');
    const tabContents = $$('.tab-content');
    sidebarBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sidebarBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(`tab-${btn.dataset.tab}`).classList.add('active');
        });
    });

    const initialTab = location.hash.slice(1);
    if (['analytics','buku','pos','reservasi'].includes(initialTab)) {
        const button = $(`.sidebar-btn[data-tab="${initialTab}"]`);
        if (button) button.click();
    }
    sidebarBtns.forEach(btn => btn.addEventListener('click', () => { history.replaceState(null, '', '#' + btn.dataset.tab); }));
    const reloadTab = tab => location.assign('/admin/#' + tab);

    // popup: buka/tutup
    const openPopup = (popup) => { popup.hidden = false; };
    const closePopup = (popup) => { popup.hidden = true; };
    $$('.popup-overlay').forEach(popup => {
        $$('[data-popup-close]', popup).forEach(btn => btn.addEventListener('click', () => closePopup(popup)));
        popup.addEventListener('click', (e) => { if (e.target === popup) closePopup(popup); });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') $$('.popup-overlay:not([hidden])').forEach(closePopup);
    });

    // popup konfirmasi umum
    const confirmPopup = $('#confirm-popup');
    const confirmOk = $('#confirm-ok');
    let onConfirm = null;
    const askConfirm = ({ title, message, okText, danger }, callback) => {
        $('#confirm-title').textContent = title;
        $('#confirm-message').textContent = message;
        confirmOk.textContent = okText;
        confirmOk.classList.toggle('is-danger', !!danger);
        onConfirm = callback;
        openPopup(confirmPopup);
    };
    confirmOk.addEventListener('click', () => {
        closePopup(confirmPopup);
        if (onConfirm) onConfirm();
        onConfirm = null;
    });

    // pencarian di frontend: sembunyikan item yang tidak cocok & perbarui jumlah
    const bindSearch = (input, getItems, textOf, counter, label) => {
        const run = () => {
            const q = input.value.toLowerCase().trim();
            let n = 0;
            getItems().forEach(el => {
                const ok = textOf(el).toLowerCase().includes(q);
                el.hidden = !ok;
                if (ok) n++;
            });
            counter.textContent = label(n);
        };
        input.addEventListener('input', run);
        return run;
    };

    // master buku: cari, tambah, edit, hapus
    const bookList = $('#admin-book-list');
    const bookPopup = $('#book-popup');
    const bookForm = $('#form-add-book');
    const bookFields = ['judul', 'penulis', 'kategori', 'kondisi', 'harga', 'stok', 'deskripsi'];
    const bookInput = (f) => $(`#book-${f}`);
    let editingBook = null;

    const runBookSearch = bindSearch(
        $('#book-search'),
        () => $$('tr', bookList),
        (tr) => `${tr.dataset.judul} ${tr.dataset.penulis}`,
        $('#book-count'),
        (n) => `Menampilkan ${n} buku`
    );

    const fillBookRow = (tr, b) => {
        const stok = Number(b.stok);
        const stokCell = stok === 0 ? '<span class="stock-out">Habis</span>' : stok <= 2 ? `<span class="stock-low">${stok}</span>` : stok;
        Object.assign(tr.dataset, b);
        tr.innerHTML = `<td><strong>${esc(b.judul)}</strong></td><td>${esc(b.penulis)}</td><td>${esc(b.kategori)}</td>`
            + `<td>${rupiah(b.harga)}</td><td>${stokCell}</td><td><span class="badge">${esc(b.kondisi)}</span></td>`
            + '<td><button class="btn-sm btn-secondary btn-edit-book">Edit</button> <button class="btn-sm btn-danger btn-delete-book">Hapus</button></td>';
    };
    const afterBookChange = () => {
        runBookSearch();
    };

    $('#btn-add-book').addEventListener('click', () => {
        editingBook = null;
        bookForm.reset();
        saleCheck.checked = true;
        updateBookSale();
        $('#book-popup-title').textContent = 'Tambah Buku Baru';
        openPopup(bookPopup);
        bookInput('judul').focus();
    });

    const saleCheck = $('#book-sale');
    const updateBookSale = () => {
        const sale = saleCheck.checked;
        ['kondisi','harga','stok'].forEach(key => {
            const input = bookInput(key);
            input.disabled = !sale;
            input.required = sale;
        });
    };
    saleCheck.addEventListener('change', updateBookSale);

    bookList.addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        if (e.target.closest('.btn-edit-book')) {
            editingBook = tr;
            bookForm.reset();
            $('#book-popup-title').textContent = 'Edit Buku';
            bookFields.forEach(f => { bookInput(f).value = tr.dataset[f] || ''; });
            $('#book-penerbit').value = tr.dataset.penerbit || '';
            $('#book-tahun').value = tr.dataset.tahun || '';
            $('#book-cover').value = tr.dataset.foto || '';
            saleCheck.checked = tr.dataset.sale === '1';
            updateBookSale();
            openPopup(bookPopup);
        } else if (e.target.closest('.btn-delete-book')) {
            askConfirm({
                title:'Hapus Buku?',
                message:`Hapus buku "${tr.dataset.judul}" dari database?`,
                okText:'Ya, Hapus', danger:true
            }, async () => {
                try {
                    await apiRequest('buku/hapus_buku.php',{method:'DELETE',body:{id_buku:Number(tr.dataset.id)}});
                    reloadTab('buku');
                } catch(error) {alert(error.message);}
            });
        }
    });

    bookForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const button = bookForm.querySelector('[type="submit"]');
        button.disabled = true;
        try {
            const sale = saleCheck.checked;
            const body = {
                id_kategori: Number(bookInput('kategori').value),
                judul: bookInput('judul').value.trim(),
                penulis: bookInput('penulis').value.trim(),
                penerbit: $('#book-penerbit').value.trim(),
                tahun_terbit: $('#book-tahun').value || null,
                bisa_dijual: sale,
                kondisi: sale ? bookInput('kondisi').value : null,
                harga: sale ? Number(bookInput('harga').value) : null,
                stok: sale ? Number(bookInput('stok').value) : null,
                deskripsi: bookInput('deskripsi').value.trim(),
                foto_sampul: $('#book-cover').value.trim()
            };
            if (editingBook) body.id_buku = Number(editingBook.dataset.id);
            await apiRequest(editingBook ? 'buku/edit_buku.php':'buku/tambah_buku.php', {
                method: editingBook ? 'PUT':'POST', body
            });
            reloadTab('buku');
        } catch(error) { alert(error.message); }
        finally {button.disabled = false;}
    });

    // kasir: cari buku di katalog
    bindSearch(
        $('#pos-search'),
        () => $$('.pos-item-card'),
        (el) => `${$('h4', el).textContent} ${$('.pos-item-author', el).textContent}`,
        $('#pos-count'),
        (n) => `Menampilkan ${n} buku`
    );

    // kasir: keranjang (frontend saja, sampai sebelum checkout)
    const posCards = $$('.pos-item-card');
    const catalog = new Map(posCards.map(c => [c.dataset.id, {
        judul: c.dataset.judul, harga: Number(c.dataset.harga), stok: Number(c.dataset.stok),
        kondisi: c.dataset.kondisi, warna: c.dataset.warna
    }]));
    const cart = new Map(); // id buku -> jumlah
    const cartEl = $('#cart-items');
    const cashInput = $('#cash-received');
    const cartTotal = () => [...cart].reduce((sum, [id, qty]) => sum + catalog.get(id).harga * qty, 0);

    // kembalian / kekurangan uang
    const updateChange = () => {
        const diff = (Number(cashInput.value.replace(/\D/g, '')) || 0) - cartTotal();
        $('#cash-change-label').textContent = diff < 0 ? 'Kurang' : 'Kembalian';
        $('#cash-change').textContent = rupiah(Math.abs(diff));
        $('#cash-change').classList.toggle('is-short', diff < 0);
    };

    const cartItemHtml = ([id, qty]) => {
        const it = catalog.get(id);
        return `<div class="cart-item" data-id="${id}"><span class="book-icon book-icon-sm ${esc(it.warna)}"></span>`
            + `<div class="cart-item-info"><div class="cart-item-name">${esc(it.judul)}</div>`
            + `<div class="cart-item-meta">${rupiah(it.harga)} · ${esc(it.kondisi)}</div>`
            + '<div class="cart-item-row"><div class="qty-stepper">'
            + `<button type="button" data-cart="dec" aria-label="Kurangi">−</button><span>${qty}</span>`
            + `<button type="button" data-cart="inc" aria-label="Tambah"${qty >= it.stok ? ' disabled' : ''}>+</button></div>`
            + `<span class="cart-item-sub">${rupiah(it.harga * qty)}</span></div></div>`
            + '<button type="button" class="cart-item-remove" data-cart="remove" aria-label="Hapus item">✕</button></div>';
    };

    const renderCart = () => {
        cartEl.innerHTML = cart.size
            ? [...cart].map(cartItemHtml).join('')
            : '<p class="cart-empty">Keranjang masih kosong. Klik buku di katalog untuk menambahkan.</p>';
        $('#cart-qty').textContent = [...cart.values()].reduce((a, b) => a + b, 0);
        $('#cart-subtotal').textContent = $('#cart-total').textContent = rupiah(cartTotal());
        posCards.forEach(card => {
            const qty = cart.get(card.dataset.id) || 0;
            card.classList.toggle('in-cart', qty > 0);
            const tag = $('.in-cart-tag', card);
            if (tag) { tag.hidden = qty === 0; tag.textContent = `${qty} di keranjang`; }
        });
        updateChange();
    };

    // tambah / kurangi jumlah (tidak boleh melebihi stok)
    const changeQty = (id, delta) => {
        const next = (cart.get(id) || 0) + delta;
        if (next <= 0) cart.delete(id);
        else if (next <= catalog.get(id).stok) cart.set(id, next);
        renderCart();
    };

    posCards.forEach(card => card.addEventListener('click', () => {
        if (!card.classList.contains('out-of-stock')) changeQty(card.dataset.id, 1);
    }));
    cartEl.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-cart]');
        if (!btn) return;
        const id = btn.closest('.cart-item').dataset.id;
        if (btn.dataset.cart === 'inc') changeQty(id, 1);
        else if (btn.dataset.cart === 'dec') changeQty(id, -1);
        else { cart.delete(id); renderCart(); }
    });
    $('#cart-clear').addEventListener('click', () => { cart.clear(); renderCart(); });

    // metode pembayaran & uang diterima
    $$('.pay-method').forEach(btn => btn.addEventListener('click', () => {
        $$('.pay-method').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        $('.cash-box').hidden = btn.dataset.method !== 'tunai';
    }));
    cashInput.addEventListener('input', () => {
        const n = cashInput.value.replace(/\D/g, '');
        cashInput.value = n ? Number(n).toLocaleString('id-ID') : '';
        updateChange();
    });
    renderCart();
    // Pertahankan UUID jika request gagal di jaringan dan operator mencoba lagi
    // dengan keranjang serta metode pembayaran yang sama.
    let checkoutAttempt = null;
    $('#btn-checkout').addEventListener('click', async (e) => {
        if (!cart.size) return alert('Keranjang masih kosong.');
        const method = $('.pay-method.active').dataset.method === 'tunai' ? 'cash' : 'qris';
        if (method === 'cash' && (Number(cashInput.value.replace(/\D/g, '')) || 0) < cartTotal()) {
            return alert('Uang diterima masih kurang.');
        }
        if (!confirm(`Simpan transaksi sebesar ${rupiah(cartTotal())}?`)) return;
        const button = e.currentTarget;
        if (button.disabled) return;
        button.disabled = true;
        try {
            const payload = {
                metode_transaksi: method,
                items: [...cart].map(([id, qty]) => ({ id_buku: Number(id), jumlah_beli: qty }))
            };
            // Signature berubah bila barang/jumlah/metode berubah.
            const signature = JSON.stringify(payload);
            if (!checkoutAttempt || checkoutAttempt.signature !== signature) {
                checkoutAttempt = { signature, id: crypto.randomUUID() };
            }
            const result = await apiRequest('transaksi/tambah_transaksi.php', {
                method: 'POST',
                body: { ...payload, idempotency_key: checkoutAttempt.id }
            });
            checkoutAttempt = null; // transaksi sukses; checkout berikutnya wajib UUID baru
            alert(`Transaksi tersimpan. Invoice: ${result.transaksi.kode_invoice}`);
            reloadTab('pos');
        } catch (error) {
            alert(error.message + '\nJika koneksi terputus, periksa riwayat invoice. ' +
                'Untuk mencoba ulang checkout yang sama, jangan ubah keranjang atau metode pembayaran.');
        } finally { button.disabled = false; }
    });

    // reservasi: filter status, setujui, tolak, edit
    const rsvList = $('.rsv-list');
    const statusMeta = { pending: ['Menunggu Review', 'badge-warning'], approved: ['Disetujui', 'badge-success'], rejected: ['Ditolak', 'badge-danger'] };
    let rsvFilter = 'pending';

    const refreshRsv = () => {
        const cards = $$('.rsv-card');
        const count = { pending: 0, approved: 0, rejected: 0 };
        let shown = 0;
        cards.forEach(c => {
            count[c.dataset.status]++;
            c.hidden = rsvFilter !== 'all' && c.dataset.status !== rsvFilter;
            if (!c.hidden) shown++;
        });
        $$('[data-count]').forEach(el => { el.textContent = count[el.dataset.count]; });
        const nav = $('.nav-count');
        nav.textContent = count.pending;
        nav.hidden = count.pending === 0;
        $('#rsv-empty').hidden = shown > 0;
    };

    const setStatus = (card, status) => {
        card.dataset.status = status;
        card.classList.remove('rsv-pending', 'rsv-approved', 'rsv-rejected');
        card.classList.add(`rsv-${status}`);
        const badge = $('.rsv-badge', card);
        badge.className = `badge rsv-badge ${statusMeta[status][1]}`;
        badge.textContent = statusMeta[status][0];
        refreshRsv();
    };

    const updateStatus = async (card, status, alasan = null) => {
        await apiRequest('reservasi/ubah_status_reservasi.php', {
            method:'PATCH',
            body: {id_reservasi:Number(card.dataset.id),status_reservasi:
                ({pending:'menunggu',approved:'disetujui',rejected:'ditolak'})[status],
                ...(alasan !== null ? {alasan_penolakan:alasan} : {})}
        });
        setStatus(card,status);
    };
    const confirmStatus = (card, status) => {
        let reason = null;
        if (status === 'rejected') {
            reason = prompt('Alasan penolakan (wajib):');
            if (reason === null || !reason.trim()) return;
            reason = reason.trim();
        }
        const approve = status === 'approved';
        askConfirm({
            title: approve?'Setujui Reservasi?':'Tolak Reservasi?',
            message: `Ubah status reservasi "${card.dataset.agenda}" di database?`,
            okText: approve?'Ya, Setujui':'Ya, Tolak', danger:!approve
        }, async () => {
            try {await updateStatus(card,status,reason);}
            catch(error) {alert(error.message);}
        });
    };

    $$('.rsv-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            $$('.rsv-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            rsvFilter = btn.dataset.filter;
            refreshRsv();
        });
    });

    // popup edit reservasi
    const rsvPopup = $('#rsv-popup');
    const rsvForm = $('#form-edit-reservation');
    const fields = {
        ruangan: $('#edit-ruangan'), tanggal: $('#edit-tanggal'),
        mulai: $('#edit-jam-mulai'), selesai: $('#edit-jam-selesai'),
        pemohon: $('#edit-pemohon'), agenda: $('#edit-agenda')
    };
    let editingCard = null;
    const submitEdit = async (andApprove = false) => {
        if (!rsvForm.reportValidity() || !editingCard) return;
        const d = editingCard.dataset;
        const payload = {
            id_reservasi:Number(d.id), id_tempat:Number(fields.ruangan.value),
            tanggal_acara:fields.tanggal.value,
            jam_mulai:fields.mulai.value, jam_selesai:fields.selesai.value,
            nama_acara:fields.agenda.value.trim(),
            penanggung_jawab:fields.pemohon.value.trim(),
            kontak_wa:d.wa, deskripsi_acara:d.deskripsi || ''
        };
        try {
            await apiRequest('reservasi/edit_reservasi.php',{method:'PUT',body:payload});
            if (andApprove) await updateStatus(editingCard,'approved');
            reloadTab('reservasi');
        } catch(error) { alert(error.message); }
    };
    rsvList.addEventListener('click', e => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const card = btn.closest('.rsv-card');
        const act = btn.dataset.action;
        if (act === 'approve') confirmStatus(card,'approved');
        else if (act === 'reject') confirmStatus(card,'rejected');
        else if (act === 'reset') {
            askConfirm({
                title:'Kembalikan ke Menunggu?',
                message:`Status "${card.dataset.agenda}" akan kembali menjadi menunggu.`,
                okText:'Ya',danger:false
            }, async () => {
                try {await updateStatus(card,'pending');}
                catch(error) {alert(error.message);}
            });
        } else if (act === 'edit') {
            editingCard = card;
            fields.ruangan.value = card.dataset.idtempat;
            fields.tanggal.value = card.dataset.tanggal;
            fields.mulai.value = card.dataset.mulai;
            fields.selesai.value = card.dataset.selesai;
            fields.pemohon.value = card.dataset.pemohon;
            fields.agenda.value = card.dataset.agenda;
            $('#btn-save-approve').hidden = card.dataset.status === 'approved';
            openPopup(rsvPopup);
        }
    });
    rsvForm.addEventListener('submit', async e => {e.preventDefault();await submitEdit(false);});
    $('#btn-save-approve').addEventListener('click', async () => {await submitEdit(true);});

    refreshRsv(); // terapkan filter awal (Menunggu Review)
});