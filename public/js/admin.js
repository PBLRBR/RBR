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
    if (['analytics', 'buku', 'pos', 'reservasi'].includes(initialTab)) {
        const button = $(`.sidebar-btn[data-tab="${initialTab}"]`);
        if (button) button.click();
    }
    sidebarBtns.forEach(btn => btn.addEventListener('click', () => { history.replaceState(null, '', '#' + btn.dataset.tab); }));
    const reloadTab = tab => {
        history.replaceState(null, '', '#' + tab);
        pageReloading = true;
        setLoading(true, 'Menyegarkan halaman…');
        location.reload();
    };

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
    $('#book-tahun').max = new Date().getFullYear();
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
        const stokCell = stok === 0 ? '<span class="stock-out">Habis</span>' : stok <= 3 ? `<span class="stock-low">${stok}</span>` : stok;
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
        ['kondisi', 'harga', 'stok'].forEach(key => {
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
        } else if (e.target.closest('.btn-detail-book')) {
            const info = [
                ['Judul', tr.dataset.judul], ['Penulis', tr.dataset.penulis],
                ['Penerbit', tr.dataset.penerbit], ['Tahun terbit', tr.dataset.tahun],
                ['Kategori', tr.dataset.namakategori], ['Jenis', tr.dataset.sale === '1' ? 'Dijual' : 'Koleksi baca'],
                ['Kondisi', tr.dataset.kondisi], ['Harga', tr.dataset.sale === '1' ? rupiah(tr.dataset.harga) : '-'],
                ['Stok', tr.dataset.sale === '1' ? tr.dataset.stok : '-'],
                ['Deskripsi', tr.dataset.deskripsi || '-']
            ];
            $('#book-detail-content').innerHTML = '<dl class="admin-details">'
                + info.map(([name, value]) => `<div><dt>${esc(name)}</dt><dd>${esc(value || '-')}</dd></div>`).join('')
                + '</dl>';
            openPopup($('#book-detail-popup'));
        } else if (e.target.closest('.btn-delete-book')) {
            askConfirm({
                title: 'Hapus Buku?',
                message: `Nonaktifkan buku "${tr.dataset.judul}"? Buku hilang dari katalog/kasir, tetapi riwayat transaksi tetap tersimpan.`,
                okText: 'Ya, Hapus', danger: true
            }, async () => {
                try {
                    await apiRequest('buku/hapus_buku.php', { method: 'DELETE', body: { id_buku: Number(tr.dataset.id) } });
                    reloadTab('buku');
                } catch (error) { alert(error.message); }
            });
        }
    });

    bookForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const save = async () => {
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
                if (editingBook) {
                    body.id_buku = Number(editingBook.dataset.id);
                    body.expected_stok = editingBook.dataset.sale === '1'
                        ? Number(editingBook.dataset.stok)
                        : null;
                }
                await apiRequest(editingBook ? 'buku/edit_buku.php' : 'buku/tambah_buku.php', {
                    method: editingBook ? 'PUT' : 'POST', body
                });
                reloadTab('buku');
            } catch (error) { alert(error.message); }
            finally { button.disabled = false; }
        };
        if (editingBook) return save(); // edit tetap langsung simpan
        askConfirm({
            title: 'Simpan Buku?',
            message: `Simpan buku "${bookInput('judul').value.trim()}" ke database?`,
            okText: 'Ya, Simpan', danger: false
        }, save);
    });


    // Detail transaksi memakai id_transaksi, bukan kode invoice.
    const transactionPopup = $('#transaction-popup');
    const showTransactionDetail = async (id) => {
        const transactionId = Number(id);
        if (!Number.isInteger(transactionId) || transactionId < 1) return;
        setLoading(true, 'Memuat detail transaksi…');
        let result;
        try { result = await apiRequest(`transaksi/detail_transaksi.php?id_transaksi=${transactionId}`); }
        finally { setLoading(false); }
        const t = result.transaksi;
        const rows = result.detail;
        $('#transaction-title').textContent = `Invoice ${t.kode_invoice}`;
        const fields = [['Kasir', t.nama_kasir], ['Tanggal', String(t.tanggal_transaksi ?? '').replace(/(\d{2}:\d{2}:\d{2})\.\d+$/, '$1')],
        ['Pembeli', t.nama_pembeli || 'Pengunjung'],
        ['Metode', t.metode_transaksi === 'cash' ? 'Tunai' : 'QRIS']];
        $('#transaction-details').innerHTML = '<dl class="admin-details">'
            + fields.map(([name, value]) => `<div><dt>${esc(name)}</dt><dd>${esc(value)}</dd></div>`).join('') + '</dl>'
            + '<div style="overflow-x:auto"><table class="data-table"><thead><tr><th>Buku</th><th>Qty</th><th>Harga/unit</th><th>Subtotal</th></tr></thead><tbody>'
            + rows.map(r => `<tr><td>${esc(r.judul)}</td><td>${Number(r.jumlah_beli)}</td><td>${rupiah(Number(r.subtotal) / Number(r.jumlah_beli))}</td><td>${rupiah(r.subtotal)}</td></tr>`).join('')
            + `</tbody><tfoot><tr><th colspan="3">Total</th><th>${rupiah(t.total_bayar)}</th></tr></tfoot></table></div>`;
        openPopup(transactionPopup);
    };
    $('#recent-transactions').addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-view-transaction');
        if (!btn) return;
        btn.disabled = true;
        try { await showTransactionDetail(btn.dataset.id); }
        catch (error) { alert(error.message); }
        finally { btn.disabled = false; }
    });
    $('#btn-print-transaction').addEventListener('click', () => window.print());

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
    // Pertahankan UUID yang sama jika checkout mengalami kegagalan jaringan.
    // UUID baru hanya dipakai ketika isi keranjang, pembeli atau metode berubah.
    const checkoutButton = $('#btn-checkout');
    const checkoutStorageKey = `simart:pos:pending:${checkoutButton.dataset.kasirId}`;
    const updateCheckoutButton = () => {
        const hasPendingCheckout = sessionStorage.getItem(checkoutStorageKey) !== null;
        checkoutButton.textContent = hasPendingCheckout
            ? 'Lanjutkan Checkout Tertunda'
            : 'Proses Transaksi';
    };

    try {
        updateCheckoutButton();
    } catch {
        // Handler checkout akan menampilkan pesan jika penyimpanan tidak bisa diakses.
    }
    checkoutButton.addEventListener('click', async (e) => {
        const button = e.currentTarget;
        if (button.disabled) return;

        let pending;
        try {
            pending = JSON.parse(sessionStorage.getItem(checkoutStorageKey) ?? 'null');
        } catch {
            return alert('Checkout tertunda tidak bisa dibaca. Periksa riwayat invoice sebelum melanjutkan.');
        }

        let payload;
        let checkoutId;

        if (pending) {
            const jumlahBuku = pending.payload.items.reduce(
                (total, item) => total + item.jumlah_beli, 0
            );
            const metode = pending.payload.metode_transaksi === 'cash' ? 'Tunai' : 'QRIS';
            const pembeli = pending.payload.nama_pembeli || 'Pengunjung';
            const totalTersimpan = pending.payload.expected_total_minor;
            const nominal = Number.isSafeInteger(totalTersimpan)
                ? `\nTotal awal: ${rupiah(totalTersimpan / 100)}.`
                : '\nNominal awal tidak tersedia. Periksa riwayat invoice sebelum melanjutkan.';

            if (!confirm(
                `Lanjutkan checkout tertunda untuk ${pembeli} (${jumlahBuku} buku, ${metode})?` +
                `${nominal}\nPastikan pembayaran sudah diterima. ` +
                'Jika transaksi sudah tercatat, invoice yang sama akan dibuka.'
            )) return;

            payload = pending.payload;
            checkoutId = pending.id;
        } else {
            if (!cart.size) return alert('Keranjang masih kosong.');

            const method = $('.pay-method.active').dataset.method === 'tunai' ? 'cash' : 'qris';
            const cashAmount = Number(cashInput.value.replace(/\D/g, '')) || 0;

            if (method === 'cash' && cashAmount < cartTotal()) {
                return alert('Uang diterima masih kurang.');
            }

            if (!globalThis.crypto?.randomUUID) {
                return alert('Gunakan localhost atau HTTPS agar checkout bisa membuat UUID.');
            }

            const pesan = method === 'qris'
                ? `Pastikan pembayaran QRIS sudah diterima. Simpan transaksi ${rupiah(cartTotal())}?`
                : `Simpan transaksi tunai sebesar ${rupiah(cartTotal())}?`;

            if (!confirm(pesan)) return;

            payload = {
                metode_transaksi: method,
                nama_pembeli: $('#buyer-name').value.trim(),
                expected_total_minor: Math.round(cartTotal() * 100),
                items: [...cart]
                    .map(([id, qty]) => ({ id_buku: Number(id), jumlah_beli: qty }))
                    .sort((a, b) => a.id_buku - b.id_buku),
            };
            checkoutId = crypto.randomUUID();

            try {
                sessionStorage.setItem(
                    checkoutStorageKey,
                    JSON.stringify({ id: checkoutId, payload })
                );
            } catch {
                return alert('Data checkout tidak bisa disimpan sementara. Transaksi belum dikirim.');
            }
        }

        const method = payload.metode_transaksi;
        button.disabled = true;

        try {
            const result = await apiRequest('transaksi/tambah_transaksi.php', {
                method: 'POST',
                body: { ...payload, idempotency_key: checkoutId },
            });
            // Hanya setelah respons sukses, UUID ini dianggap selesai.
            sessionStorage.removeItem(checkoutStorageKey);
            // Data sudah di-commit pada backend. Perbarui stok DOM dan keranjang langsung.
            result.items.forEach(({ id_buku, stok_tersisa }) => {
                const id = String(id_buku);
                const b = catalog.get(id);
                if (!b) return;

                if (stok_tersisa == null || !Number.isFinite(Number(stok_tersisa))) return;
                b.stok = Number(stok_tersisa);
                const card = posCards.find(el => el.dataset.id === id);
                if (card) {
                    card.dataset.stok = b.stok;
                    card.classList.toggle('out-of-stock', b.stok === 0);
                    const label = $('.pos-stock', card);
                    if (label) {
                        label.textContent = `Stok ${b.stok}`;
                        label.classList.toggle('low', b.stok <= 3);
                    }
                    if (b.stok === 0) {
                        const foot = $('.pos-item-foot', card);
                        const tag = $('.in-cart-tag', card);
                        if (tag) tag.remove();
                        if (foot && !$('.stock-pill.empty', foot)) {
                            const span = document.createElement('span');
                            span.className = 'stock-pill empty'; span.textContent = 'Habis';
                            foot.appendChild(span);
                        }
                    }
                }
                const row = [...$$('tr', bookList)].find(el => el.dataset.id === id);
                if (row) {
                    row.dataset.stok = String(b.stok);
                    const cell = row.children[4];
                    if (cell) cell.innerHTML = b.stok === 0 ? '<span class="stock-out">Habis</span>'
                        : b.stok <= 3 ? `<span class="stock-low">${b.stok}</span>` : String(b.stok);
                }
            });
            cart.clear(); cashInput.value = ''; $('#buyer-name').value = ''; renderCart();
            const tbody = $('#recent-transactions');
            tbody.querySelectorAll('tr').forEach(tr => {
                if (tr.querySelector('td[colspan="5"]')) tr.remove();
            });
            const tr = document.createElement('tr');
            tr.innerHTML = `<td><button type="button" class="btn-sm btn-secondary btn-view-transaction" data-id="${Number(result.transaksi.id_transaksi)}">${esc(result.transaksi.kode_invoice)}</button></td>`
                + `<td>${esc(String(result.transaksi.tanggal_transaksi ?? '').replace(/(\d{2}:\d{2}:\d{2})\.\d+$/, '$1'))}</td><td>${result.items.reduce((n, i) => n + Number(i.jumlah_beli), 0)} buku</td>`
                + `<td>${rupiah(result.transaksi.total_bayar)}</td><td>${esc(method)}</td>`;
            const invoiceId = Number(result.transaksi.id_transaksi);
            const invoiceTime = String(result.transaksi.tanggal_transaksi ?? '');

            tbody.querySelector(`.btn-view-transaction[data-id="${invoiceId}"]`)
                ?.closest('tr')?.remove();

            tr.dataset.transaksiId = String(invoiceId);
            tr.dataset.transaksiWaktu = invoiceTime;

            const normalizeTime = value => {
                const [seconds, fraction = ''] = value.split('.');
                return `${seconds}.${fraction.padEnd(6, '0')}`;
            };

            const invoiceOrderTime = normalizeTime(invoiceTime);
            const olderRow = Array.from(tbody.rows).find(row => {
                const rowTime = normalizeTime(row.dataset.transaksiWaktu ?? '');
                const rowId = Number(row.dataset.transaksiId);

                return rowTime < invoiceOrderTime ||
                    (rowTime === invoiceOrderTime && rowId < invoiceId);
            });

            tbody.insertBefore(tr, olderRow ?? null);
            while (tbody.rows.length > 5) {
                tbody.deleteRow(-1);
            }
            // Jika detail gagal dimuat, transaksi tetap sukses. Hindari checkout ulang.
            try { await showTransactionDetail(result.transaksi.id_transaksi); }
            catch (detailError) { alert(`Transaksi BERHASIL disimpan (${result.transaksi.kode_invoice}), tetapi detail belum dapat dibuka: ${detailError.message}`); }
        } catch (error) {
            if (error.status === 422) {
                sessionStorage.removeItem(checkoutStorageKey);
                alert(`${error.message}\nPerbaiki data checkout, lalu coba lagi.`);
                return;
            }

            alert(
                `${error.message}\n` +
                'Periksa riwayat invoice. Jika perlu, tekan Proses Transaksi lagi ' +
                'untuk melanjutkan checkout dengan kunci yang sama.'
            );
        }
        finally {
            button.disabled = false;

            try {
                updateCheckoutButton();
            } catch {
                // Pertahankan label tombol jika penyimpanan tidak bisa diakses.
            }
        }
    });

    // reservasi: filter status, setujui, tolak, edit
    const rsvList = $('#rsv-results');
    const statusMeta = { pending: ['Menunggu Review', 'badge-warning'], approved: ['Disetujui', 'badge-success'], rejected: ['Ditolak', 'badge-danger'] };
    let rsvFilter = $('.rsv-filter.active')?.dataset.filter ?? 'pending';

    const refreshRsv = () => {
        let shown = 0;

        $$('.rsv-card').forEach(card => {
            card.hidden = rsvFilter !== 'all' && card.dataset.status !== rsvFilter;
            if (!card.hidden) shown++;
        });

        $('#rsv-empty').hidden = shown > 0;
    };

    let rsvRequest;
    const rsvSearchForm = $('#rsv-search-form');

    async function loadRsvPage(pageUrl) {
        rsvRequest?.abort();

        const request = new AbortController();
        rsvRequest = request;

        const partialUrl = new URL(pageUrl);
        partialUrl.searchParams.set('rsv_partial', '1');
        rsvList.setAttribute('aria-busy', 'true');
        const loadingMessage = $('#rsv-loading');
        const requestedFilter = pageUrl.searchParams.get('rsv_status') ?? 'pending';
        const filterLabels = {
            pending: 'Menunggu Review',
            approved: 'Disetujui',
            rejected: 'Ditolak',
            all: 'Semua',
        };

        loadingMessage.textContent =
            `Memuat reservasi: ${filterLabels[requestedFilter] ?? 'Menunggu Review'}…`;

        try {
            const response = await fetch(partialUrl, {
                signal: request.signal,
                credentials: 'same-origin',
            });

            if (!response.ok || response.redirected) {
                throw new Error('Gagal memuat daftar reservasi.');
            }

            const html = await response.text();
            if (request.signal.aborted) return;

            const doc = new DOMParser().parseFromString(html, 'text/html');
            const results = doc.getElementById('rsv-results');

            if (!results) {
                throw new Error('Respons daftar reservasi tidak valid.');
            }

            rsvFilter = pageUrl.searchParams.get('rsv_status') ?? 'pending';
            const search = pageUrl.searchParams.get('rsv_q') ?? '';

            rsvList.replaceChildren(...results.childNodes);
            for (const status of ['pending', 'approved', 'rejected']) {
                $(`[data-count="${status}"]`).textContent = results.dataset[status];
            }

            const pendingCount = Number(results.dataset.pending);
            const navCount = $('.nav-count');
            navCount.textContent = pendingCount;
            navCount.hidden = pendingCount === 0;
            rsvSearchForm.elements.namedItem('rsv_status').value = rsvFilter;

            $$('.rsv-filter[data-filter]').forEach(filterLink => {
                filterLink.classList.toggle('active', filterLink.dataset.filter === rsvFilter);

                const filterUrl = new URL(filterLink.href);
                filterUrl.searchParams.set('rsv_q', search);
                filterLink.href = filterUrl.href;
            });

            pageUrl.searchParams.set('rsv_page', results.dataset.page);
            history.replaceState(null, '', pageUrl);
            refreshRsv();
        } catch {
            if (!request.signal.aborted) {
                location.assign(pageUrl.href);
            }
        } finally {
            if (rsvRequest === request) {
                rsvList.removeAttribute('aria-busy');
                loadingMessage.textContent = '';
            }
        }
    }

    $('#tab-reservasi').addEventListener('click', event => {
        const link = event.target.closest(
            'a.rsv-filter, nav[aria-label="Halaman reservasi"] a'
        );

        if (!link || event.button !== 0 ||
            event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        clearTimeout(rsvSearchTimer);

        const pageUrl = new URL(link.href);
        const search = rsvSearchInput.value.trim();

        if ((pageUrl.searchParams.get('rsv_q') ?? '') !== search) {
            pageUrl.searchParams.set('rsv_q', search);
            pageUrl.searchParams.set('rsv_page', '1');
        }

        loadRsvPage(pageUrl);
    });

    const rsvSearchInput = rsvSearchForm.elements.namedItem('rsv_q');
    let rsvSearchTimer;
    let rsvBeforeSearch = null;

    const searchRsv = () => {
        clearTimeout(rsvSearchTimer);

        const pageUrl = new URL(rsvSearchForm.action);
        pageUrl.search = new URLSearchParams(new FormData(rsvSearchForm)).toString();
        pageUrl.searchParams.set('rsv_q', rsvSearchInput.value.trim());
        pageUrl.searchParams.set('rsv_page', '1');

        loadRsvPage(pageUrl);
    };

    rsvSearchInput.addEventListener('input', () => {
        clearTimeout(rsvSearchTimer);
        rsvRequest?.abort();

        const keyword = rsvSearchInput.value.trim().toLowerCase();
        const currentUrl = new URL(location.href);
        const isFirstPage = (currentUrl.searchParams.get('rsv_page') ?? '1') === '1';
        const isUnfiltered = !currentUrl.searchParams.get('rsv_q');

        if (keyword && isFirstPage && isUnfiltered &&
            rsvBeforeSearch?.status !== rsvFilter) {
            rsvBeforeSearch = {
                status: rsvFilter,
                results: rsvList.cloneNode(true),
            };
        }

        if (!keyword) {
            if (rsvBeforeSearch?.status === rsvFilter) {
                rsvList.replaceChildren(...rsvBeforeSearch.results.childNodes);
            }

            rsvBeforeSearch = null;
            rsvList.querySelector('.rsv-pagination')?.style.removeProperty('display');
            refreshRsv();
            searchRsv();
            return;
        }

        rsvList.querySelector('.rsv-pagination')?.style.setProperty('display', 'none');
        rsvList.querySelectorAll('.rsv-card').forEach(card => {
            const matchesStatus = rsvFilter === 'all' || card.dataset.status === rsvFilter;
            const matchesKeyword = [
                card.dataset.agenda,
                card.dataset.pemohon,
                card.dataset.ruangan,
            ].some(value => {
                if (keyword === '') return true;

                const text = (value ?? '').toLowerCase();
                return Array.from(text.matchAll(/[\p{L}\p{N}_]+/gu))
                    .some(word => text.startsWith(keyword, word.index));
            });

            card.hidden = !matchesStatus || !matchesKeyword;
        });

        $('#rsv-empty').hidden = true;
        rsvSearchTimer = setTimeout(searchRsv, 150);
    });
    rsvSearchForm.addEventListener('submit', event => {
        event.preventDefault();
        searchRsv();
    });
    const setStatus = (card, status) => {
        const previousStatus = card.dataset.status;

        if (previousStatus !== status) {
            const previousCount = $(`[data-count="${previousStatus}"]`);
            const nextCount = $(`[data-count="${status}"]`);

            previousCount.textContent = Math.max(0, Number(previousCount.textContent) - 1);
            nextCount.textContent = Number(nextCount.textContent) + 1;
        }

        const pendingCount = Number($('[data-count="pending"]').textContent);
        const nav = $('.nav-count');
        nav.textContent = pendingCount;
        nav.hidden = pendingCount === 0;
        card.dataset.status = status;
        card.classList.remove('rsv-pending', 'rsv-approved', 'rsv-rejected');
        card.classList.add(`rsv-${status}`);
        const badge = $('.rsv-badge', card);
        badge.className = `badge rsv-badge ${statusMeta[status][1]}`;
        badge.textContent = statusMeta[status][0];
        refreshRsv();
    };

    const reloadRsvAfterSave = () => {
        rsvBeforeSearch = null;
        clearTimeout(rsvSearchTimer);

        const pageUrl = new URL(location.href);
        const search = rsvSearchInput.value.trim();

        if ((pageUrl.searchParams.get('rsv_q') ?? '') !== search) {
            pageUrl.searchParams.set('rsv_page', '1');
        }

        pageUrl.searchParams.set('rsv_status', rsvFilter);
        pageUrl.searchParams.set('rsv_q', search);

        return loadRsvPage(pageUrl);
    };

    const updateStatus = async (card, status, alasan = null) => {
        await apiRequest('reservasi/ubah_status_reservasi.php', {
            method: 'PATCH',
            body: {
                id_reservasi: Number(card.dataset.id), status_reservasi:
                    ({ pending: 'menunggu', approved: 'disetujui', rejected: 'ditolak' })[status],
                ...(alasan !== null ? { alasan_penolakan: alasan } : {})
            }
        });
        setStatus(card, status);
    };
    const waRejectLink = (card, reason) => {
        let n = (card.dataset.wa || '').replace(/\D/g, '');
        if (n.startsWith('0')) n = '62' + n.slice(1);
        const text = `Halo ${card.dataset.pemohon}, mohon maaf pengajuan reservasi "${card.dataset.agenda}" di Rumah Budaya Ratna belum dapat kami setujui.\nAlasan: ${reason}`;
        return `https://wa.me/${n}?text=${encodeURIComponent(text)}`;
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
            title: approve ? 'Setujui Reservasi?' : 'Tolak Reservasi?',
            message: `Ubah status reservasi "${card.dataset.agenda}" di database?`,
            okText: approve ? 'Ya, Setujui' : 'Ya, Tolak', danger: !approve
        }, async () => {
            const wa = status === 'rejected' ? window.open('', '_blank') : null;
            if (wa) wa.opener = null;

            try {
                await updateStatus(card, status, reason);

                if (status === 'rejected') {
                    const url = waRejectLink(card, reason);
                    if (wa) {
                        wa.location.href = url;
                    } else {
                        location.assign(url);
                    }
                } else {
                    await reloadRsvAfterSave();
                }
            } catch (error) {
                if (wa) wa.close();
                alert(error.message);
            }
        });
    };

    // popup edit reservasi
    const rsvPopup = $('#rsv-popup');
    const rsvForm = $('#form-edit-reservation');
    const editSaveButton = rsvForm.querySelector('button[type="submit"]');
    const editApproveButton = $('#btn-save-approve');
    const fields = {
        ruangan: $('#edit-ruangan'), tanggal: $('#edit-tanggal'),
        mulai: $('#edit-jam-mulai'), selesai: $('#edit-jam-selesai'),
        pemohon: $('#edit-pemohon'), agenda: $('#edit-agenda')
    };
    let editingCard = null;
    const submitEdit = async (andApprove = false) => {
        if (editSaveButton.disabled || !editingCard || !rsvForm.reportValidity()) return;
        const d = editingCard.dataset;
        const payload = {
            id_reservasi: Number(d.id), id_tempat: Number(fields.ruangan.value),
            tanggal_acara: fields.tanggal.value,
            jam_mulai: fields.mulai.value, jam_selesai: fields.selesai.value,
            nama_acara: fields.agenda.value.trim(),
            penanggung_jawab: fields.pemohon.value.trim(),
            kontak_wa: d.wa, deskripsi_acara: d.deskripsi || ''
        };
        editSaveButton.disabled = true;
        editApproveButton.disabled = true;

        try {
            await apiRequest('reservasi/edit_reservasi.php', {
                method: 'PUT',
                body: { ...payload, setujui: andApprove },
            });
        } catch (error) {
            alert(error.message);
            return;
        } finally {
            editSaveButton.disabled = false;
            editApproveButton.disabled = false;
        }

        closePopup(rsvPopup);
        await reloadRsvAfterSave();
    };
    rsvList.addEventListener('click', e => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const card = btn.closest('.rsv-card');
        const act = btn.dataset.action;
        if (act === 'approve') confirmStatus(card, 'approved');
        else if (act === 'reject') confirmStatus(card, 'rejected');
        else if (act === 'reset') {
            askConfirm({
                title: 'Kembalikan ke Menunggu?',
                message: `Status "${card.dataset.agenda}" akan kembali menjadi menunggu.`,
                okText: 'Ya', danger: false
            }, async () => {
                try {
                    await updateStatus(card, 'pending');
                    await reloadRsvAfterSave();
                } catch (error) {
                    alert(error.message);
                }
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
    rsvForm.addEventListener('submit', async e => { e.preventDefault(); await submitEdit(false); });
    $('#btn-save-approve').addEventListener('click', async () => { await submitEdit(true); });

    refreshRsv(); // terapkan filter awal (Menunggu Review)
});
