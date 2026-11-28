/* Pagination Master Buku & POS, serta tabel dashboard yang bisa diperluas. Dimuat setelah admin.js. */
(() => {
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const rupiah = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

    function pageList(page, pages) {
        const keep = [...new Set([1, pages, page - 1, page, page + 1])].filter(p => p >= 1 && p <= pages).sort((a, b) => a - b);
        const out = [];
        let prev = 0;
        keep.forEach(p => { if (p - prev > 1) out.push('…'); out.push(p); prev = p; });
        return out;
    }

    function mountPager(box, page, pages, go) {
        box.replaceChildren();
        if (pages <= 1) return;
        const nav = document.createElement('nav');
        nav.className = 'simart-pager';
        nav.setAttribute('aria-label', 'Navigasi halaman');
        const btn = (label, p, opts = {}) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = label;
            if (opts.label) b.setAttribute('aria-label', opts.label);
            if (opts.active) { b.classList.add('active'); b.setAttribute('aria-current', 'page'); }
            b.disabled = !!opts.disabled;
            b.addEventListener('click', () => go(p));
            return b;
        };
        const info = document.createElement('span');
        info.className = 'simart-pager-info';
        info.textContent = `Halaman ${page} dari ${pages}`;
        nav.append(info, btn('‹', page - 1, { disabled: page <= 1, label: 'Sebelumnya' }));
        pageList(page, pages).forEach(p => {
            if (p === '…') {
                const gap = document.createElement('span');
                gap.className = 'simart-pager-gap';
                gap.textContent = '…';
                nav.append(gap);
            } else nav.append(btn(String(p), p, { active: p === page }));
        });
        nav.append(btn('›', page + 1, { disabled: page >= pages, label: 'Berikutnya' }));
        box.append(nav);
    }

    // Pagination sisi klien untuk daftar elemen (menggabung dengan pencarian)
    function paginate({ getItems, match, perPage, anchor, counter, label, search }) {
        const box = document.createElement('div');
        box.className = 'simart-pager-wrap';
        anchor.after(box);
        let page = 1;
        const render = () => {
            const q = search ? search.value.toLowerCase().trim() : '';
            const all = getItems();
            const hit = all.filter(el => match(el, q));
            const pages = Math.max(1, Math.ceil(hit.length / perPage));
            page = Math.min(page, pages);
            const start = (page - 1) * perPage;
            const shown = new Set(hit.slice(start, start + perPage));
            all.forEach(el => { el.hidden = !shown.has(el); });
            if (counter) counter.textContent = label(hit.length, hit.length ? start + 1 : 0, Math.min(start + perPage, hit.length));
            mountPager(box, page, pages, p => { page = p; render(); });
        };
        search?.addEventListener('input', () => { page = 1; render(); });
        render();
        return render;
    }

    // Tabel dashboard: 5 teratas <-> data lengkap 10 per halaman
    function expandable(o) {
        const body = $(o.body), btn = $(o.btn), box = $(o.pager), title = $(o.title);
        if (!body || !btn || !box || !title) return;
        let saved = '', data = [], page = 1, open = false;
        const draw = () => {
            const pages = Math.max(1, Math.ceil(data.length / 10));
            page = Math.min(page, pages);
            body.innerHTML = data.length
                ? data.slice((page - 1) * 10, page * 10).map(o.row).join('')
                : `<tr><td colspan="${o.cols}">${o.empty}</td></tr>`;
            mountPager(box, page, pages, p => { page = p; draw(); });
        };
        btn.addEventListener('click', async () => {
            if (open) {
                body.innerHTML = saved;
                box.replaceChildren();
                btn.textContent = o.openText;
                title.textContent = o.titleClosed;
                open = false;
                return;
            }
            btn.disabled = true;
            try {
                data = await apiRequest(o.endpoint);
                saved = body.innerHTML;
                page = 1;
                open = true;
                draw();
                btn.textContent = o.closeText;
                title.textContent = o.titleOpen;
            } catch (error) { alert(error.message); }
            finally { btn.disabled = false; }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Master Buku: 10 per halaman
        const bookList = $('#admin-book-list');
        if (bookList) {
            paginate({
                getItems: () => $$('tr', bookList),
                match: (tr, q) => `${tr.dataset.judul} ${tr.dataset.penulis}`.toLowerCase().includes(q),
                perPage: 10,
                anchor: bookList.closest('table'),
                counter: $('#book-count'),
                label: (n, a, b) => n ? `Menampilkan ${a}–${b} dari ${n} buku` : 'Tidak ada buku',
                search: $('#book-search'),
            });
        }

        // POS: 16 per halaman
        const grid = $('.pos-items-grid');
        if (grid) {
            paginate({
                getItems: () => $$('.pos-item-card', grid),
                match: (el, q) => `${$('h4', el).textContent} ${$('.pos-item-author', el).textContent}`.toLowerCase().includes(q),
                perPage: 16,
                anchor: grid,
                counter: $('#pos-count'),
                label: (n, a, b) => n ? `Menampilkan ${a}–${b} dari ${n} buku` : 'Tidak ada buku',
                search: $('#pos-search'),
            });
        }

        expandable({
            body: '#recent-transactions', btn: '#btn-tx-toggle', pager: '#tx-pager', title: '#tx-title',
            endpoint: 'transaksi/transaksi.php', cols: 5, empty: 'Belum ada transaksi.',
            openText: 'Lihat Data Lengkap', closeText: 'Kembali ke 5 Aktivitas Terbaru',
            titleClosed: 'Aktivitas Transaksi Terbaru', titleOpen: 'Semua Transaksi',
            row: t => `<tr data-transaksi-id="${Number(t.id_transaksi)}" data-transaksi-waktu="${esc(t.tanggal_transaksi)}">`
                + `<td><button type="button" class="btn-sm btn-secondary btn-view-transaction" data-id="${Number(t.id_transaksi)}">${esc(t.kode_invoice)}</button></td>`
                + `<td>${esc(String(t.tanggal_transaksi).slice(0, 19))}</td><td>${Number(t.jumlah_item)} buku</td>`
                + `<td>${rupiah(t.total_bayar)}</td><td>${esc(t.metode_transaksi)}</td></tr>`,
        });

        expandable({
            body: '#best-sellers', btn: '#btn-best-toggle', pager: '#best-pager', title: '#best-title',
            endpoint: 'laporan/buku_terlaris.php', cols: 3, empty: 'Belum ada penjualan.',
            openText: 'Lihat Data Lengkap', closeText: 'Kembali ke 5 Buku Terlaris',
            titleClosed: '5 Buku Terlaris (Semua Periode)', titleOpen: 'Semua Buku Terlaris (Semua Periode)',
            row: b => `<tr><td>${esc(b.judul)}</td><td>${Number(b.total_terjual)}</td><td>${rupiah(b.pendapatan)}</td></tr>`,
        });
    });
})();
