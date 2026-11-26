/* SIMART: modul grafik F7/F8 mandiri. Tidak mengubah admin.js atau api.js. */
(() => {
    'use strict';

    const periodOptions = [
        { key: 'hari_ini', label: 'Hari Ini' },
        { key: '7_hari', label: '7 Hari Terakhir' },
        { key: 'bulan_ini', label: 'Bulan Ini' },
        { key: '6_bulan', label: '6 Bulan' },
    ];
    const money = n => 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Number(n) || 0);
    const amount = n => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(Number(n) || 0);
    const compactMoney = n => 'Rp ' + new Intl.NumberFormat('id-ID', {
        notation: 'compact', maximumFractionDigits: 1,
    }).format(Number(n) || 0);
    const svgNS = 'http://www.w3.org/2000/svg';

    const element = (tag, className, text) => {
        const el = document.createElement(tag);
        if (className) el.className = className;
        if (text !== undefined) el.textContent = text;
        return el;
    };
    const svgElement = (tag, attrs = {}, text) => {
        const el = document.createElementNS(svgNS, tag);
        Object.entries(attrs).forEach(([key, value]) => el.setAttribute(key, String(value)));
        if (text !== undefined) el.textContent = text;
        return el;
    };

    // Endpoint F7/F8 mengembalikan ringkasan pada level teratas JSON.
    // apiRequest() lama hanya mengembalikan result.data, sehingga modul ini memakai fetch langsung.
    const getReport = async (endpoint, period, signal) => {
        const url = apiUrl(`laporan/${endpoint}.php?` + new URLSearchParams({ periode: period }));
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            signal,
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error(`Respons API bukan JSON (HTTP ${response.status}).`);
        }
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || `Permintaan gagal (HTTP ${response.status}).`);
        }
        if (!Array.isArray(result.data) || !result.ringkasan) {
            throw new Error('Format respons API tidak sesuai.');
        }
        return result;
    };

    function dateLabel(iso) {
        const pieces = String(iso).split('-').map(Number);
        if (pieces.length < 2 || !Number.isInteger(pieces[0]) || !Number.isInteger(pieces[1])) {
            return String(iso);
        }
        const date = new Date(pieces[0], pieces[1] - 1, pieces[2] || 1, 12);
        return new Intl.DateTimeFormat('id-ID', {
            day: pieces.length === 3 ? 'numeric' : undefined,
            month: 'short',
            year: pieces.length === 2 ? 'numeric' : undefined,
        }).format(date);
    }

    function showEmpty(container, message) {
        container.replaceChildren(element('p', 'simart-dash-empty', message));
    }

    function renderSalesChart(container, data) {
        const rows = data.map(row => ({
            name: String(row.periode ?? ''),
            value: Math.max(0, Number(row.omzet) || 0),
            count: Number(row.jumlah_transaksi) || 0,
        }));
        if (!rows.length || rows.every(row => row.value === 0 && row.count === 0)) {
            showEmpty(container, 'Belum ada transaksi pada periode ini.');
            return;
        }
        const W = 850; const H = 290;
        const left = 82; const right = 23; const top = 24; const bottom = 60;
        const graphW = W - left - right; const graphH = H - top - bottom;
        const highest = Math.max(1, ...rows.map(row => row.value));
        const ceiling = highest * 1.15;
        const pointX = i => left + (rows.length === 1 ? graphW / 2 : i * graphW / (rows.length - 1));
        const pointY = value => top + graphH * (1 - value / ceiling);
        const svg = svgElement('svg', {
            viewBox: `0 0 ${W} ${H}`, role: 'img',
            'aria-label': 'Grafik tren omzet penjualan berdasarkan tanggal atau bulan',
            preserveAspectRatio: 'xMidYMid meet',
        });
        svg.append(svgElement('title', {}, 'Tren omzet penjualan SIMART'));
        for (let i = 0; i <= 4; i++) {
            const value = ceiling * (4 - i) / 4;
            const y = pointY(value);
            svg.append(svgElement('line', {
                x1: left, y1: y, x2: W - right, y2: y,
                stroke: '#e9e2da', 'stroke-width': 1,
            }));
            svg.append(svgElement('text', {
                x: left - 12, y: y + 4, 'text-anchor': 'end',
                fill: '#6b655f', 'font-size': 11,
            }, compactMoney(value)));
        }
        const points = rows.map((row, i) => [pointX(i), pointY(row.value)]);
        const area = [`M ${points[0][0]} ${top + graphH}`]
            .concat(points.map(([x, y]) => `L ${x} ${y}`))
            .concat([`L ${points[points.length - 1][0]} ${top + graphH}`, 'Z']).join(' ');
        svg.append(svgElement('path', { d: area, fill: '#b85434', 'fill-opacity': 0.10 }));
        svg.append(svgElement('polyline', {
            points: points.map(([x, y]) => `${x},${y}`).join(' '),
            fill: 'none', stroke: '#b85434', 'stroke-width': 3,
            'stroke-linecap': 'round', 'stroke-linejoin': 'round',
        }));
        const labelStep = Math.max(1, Math.ceil(rows.length / 7));
        rows.forEach((row, i) => {
            const [x, y] = points[i];
            const point = svgElement('circle', { cx: x, cy: y, r: 4, fill: '#b85434' });
            point.append(svgElement('title', {}, `${dateLabel(row.name)}: ${money(row.value)} (${row.count} transaksi)`));
            svg.append(point);
            if (i % labelStep === 0 || i === rows.length - 1) {
                svg.append(svgElement('text', {
                    x, y: H - 30, 'text-anchor': 'middle',
                    fill: '#6b655f', 'font-size': 11,
                }, dateLabel(row.name)));
            }
        });
        container.replaceChildren(svg);
    }

    function renderRoomsChart(container, data) {
        const rows = data.map(row => ({
            name: String(row.nama_tempat ?? 'Tanpa Nama'),
            count: Math.max(0, Number(row.jumlah_reservasi) || 0),
            hours: Math.max(0, Number(row.total_jam_terpakai) || 0),
        }));
        if (!rows.length || rows.every(row => row.count === 0)) {
            showEmpty(container, 'Belum ada reservasi disetujui pada periode ini.');
            return;
        }
        const maxCount = Math.max(1, ...rows.map(row => row.count));
        const list = element('div', 'simart-room-list');
        rows.forEach(row => {
            const item = element('div', 'simart-room-row');
            const meta = element('div', 'simart-room-meta');
            meta.append(
                element('strong', '', row.name),
                element('span', '', `${row.count} reservasi · ${amount(row.hours)} jam`),
            );
            const track = element('div', 'simart-room-track');
            const fill = element('div', 'simart-room-fill');
            fill.style.width = `${Math.max(0, Math.min(100, row.count / maxCount * 100))}%`;
            track.append(fill);
            item.append(meta, track);
            list.append(item);
        });
        container.replaceChildren(list);
    }

    function initDashboard() {
        const root = document.querySelector('#tab-analytics');
        const stats = root?.querySelector('.stats-grid');
        const filters = root?.querySelector('.period-filters');
        if (!root || !stats || !filters) return;

        // Tambahkan grafik tanpa mengubah section dan tabel admin yang sudah ada.
        const wrap = element('section', 'simart-dashboard-addon');
        wrap.setAttribute('aria-label', 'Laporan analitik interaktif');
        wrap.innerHTML = `
            <div class="simart-dashboard-toolbar">
                <div>
                    <h3>Grafik Analitik</h3>
                    <p>Data langsung dari transaksi dan reservasi yang tersimpan di SIMART.</p>
                </div>
                <span id="dash-period-label" class="simart-period-label">Bulan Ini</span>
            </div>
            <p id="dash-report-status" class="simart-report-status" role="status" aria-live="polite"></p>
            <div class="simart-analytics-grid">
                <article class="simart-analytics-card">
                    <h4>F7 · Tren Omzet Penjualan</h4>
                    <p>Omzet berdasarkan tanggal transaksi (Rp).</p>
                    <div id="dash-sales-chart" class="simart-chart" aria-live="polite"></div>
                    <div id="dash-sales-summary" class="simart-analytics-foot"></div>
                </article>
                <article class="simart-analytics-card">
                    <h4>F8 · Pemanfaatan Ruangan</h4>
                    <p>Frekuensi reservasi yang disetujui per ruang.</p>
                    <div id="dash-rooms-chart" class="simart-chart" aria-live="polite"></div>
                    <div id="dash-rooms-summary" class="simart-analytics-foot"></div>
                </article>
            </div>`;
        stats.insertAdjacentElement('afterend', wrap);

        const salesChart = wrap.querySelector('#dash-sales-chart');
        const roomsChart = wrap.querySelector('#dash-rooms-chart');
        const status = wrap.querySelector('#dash-report-status');
        const salesFoot = wrap.querySelector('#dash-sales-summary');
        const roomsFoot = wrap.querySelector('#dash-rooms-summary');
        const cards = [...stats.querySelectorAll('.stat-card')];
        const setCard = (index, title, value, foot) => {
            const card = cards[index];
            if (!card) return;
            const heading = card.querySelector('h4');
            const figure = card.querySelector('.stat-number');
            const caption = card.querySelector('.stat-trend');
            if (heading) heading.textContent = title;
            if (figure) figure.textContent = value;
            if (caption) caption.textContent = foot;
        };
        if (cards[3]) {
            const heading = cards[3].querySelector('h4');
            const footer = cards[3].querySelector('.stat-trend');
            if (heading) heading.textContent = 'Total Kunjungan';
            if (footer) footer.textContent = 'Sepanjang waktu, tidak mengikuti filter';
        }

        const buttons = [...filters.querySelectorAll('.period-filter')];
        let activeRequest = null;
        let requestCounter = 0;
        async function load(period, label) {
            if (activeRequest) activeRequest.abort();
            activeRequest = new AbortController();
            const requestId = ++requestCounter;
            buttons.forEach(button => {
                const selected = button.dataset.periode === period;
                button.classList.toggle('active', selected);
                button.setAttribute('aria-pressed', String(selected));
            });
            wrap.querySelector('#dash-period-label').textContent = label;
            status.textContent = 'Memuat data laporan…';
            showEmpty(salesChart, 'Memuat grafik omzet…');
            showEmpty(roomsChart, 'Memuat grafik ruangan…');
            salesFoot.textContent = '';
            roomsFoot.textContent = '';
            // Saat memuat, jangan tampilkan nilai all-time seolah-olah hasil filter.
            setCard(0, 'Omzet Periode', '…', 'Memuat laporan');
            setCard(1, 'Buku Terjual', '…', 'Memuat laporan');
            setCard(2, 'Reservasi Disetujui', '…', 'Memuat laporan');

            const [sales, rooms] = await Promise.allSettled([
                getReport('tren_penjualan', period, activeRequest.signal),
                getReport('utilisasi_ruang', period, activeRequest.signal),
            ]);
            if (requestId !== requestCounter) return;
            const errors = [];
            if (sales.status === 'fulfilled') {
                const result = sales.value;
                const sum = result.ringkasan;
                setCard(0, 'Omzet Periode', money(sum.total_pendapatan), `${Number(sum.jumlah_transaksi) || 0} transaksi`);
                setCard(1, 'Buku Terjual', amount(sum.jumlah_buku_terjual), 'Pada periode terpilih');
                renderSalesChart(salesChart, result.data);
                salesFoot.textContent = `Total omzet: ${money(sum.total_pendapatan)} · ${Number(sum.jumlah_transaksi) || 0} transaksi`;
            } else {
                setCard(0, 'Omzet Periode', '—', 'Data belum tersedia');
                setCard(1, 'Buku Terjual', '—', 'Data belum tersedia');
                showEmpty(salesChart, 'Grafik penjualan tidak dapat dimuat.');
                errors.push(`Penjualan: ${sales.reason?.message || 'gagal memuat'}`);
            }
            if (rooms.status === 'fulfilled') {
                const result = rooms.value;
                const sum = result.ringkasan;
                setCard(2, 'Reservasi Disetujui', amount(sum.total_reservasi_disetujui),
                    `${amount(sum.total_jam_terpakai)} jam pemakaian ruangan`);
                renderRoomsChart(roomsChart, result.data);
                roomsFoot.textContent = `${Number(sum.total_reservasi_disetujui) || 0} reservasi · ${amount(sum.total_jam_terpakai)} jam terpakai`;
            } else {
                setCard(2, 'Reservasi Disetujui', '—', 'Data belum tersedia');
                showEmpty(roomsChart, 'Grafik ruangan tidak dapat dimuat.');
                errors.push(`Ruangan: ${rooms.reason?.message || 'gagal memuat'}`);
            }
            status.textContent = errors.length
                ? `Sebagian laporan gagal dimuat. ${errors.join(' | ')}. Pilih periode untuk mencoba lagi.`
                : `Laporan periode ${label.toLowerCase()} berhasil dimuat.`;
        }

        buttons.slice(0, periodOptions.length).forEach((button, index) => {
            const option = periodOptions[index];
            button.type = 'button';
            button.dataset.periode = option.key;
            button.textContent = option.label;
            button.setAttribute('aria-pressed', 'false');
            button.addEventListener('click', () => load(option.key, option.label));
        });
        // Filter "Tahun Ini" lama diganti "6 Bulan" agar cocok dengan API yang tersedia.
        // Bila struktur tombol sudah diubah FE, tombol ekstra dibiarkan tidak tersentuh.
        load('bulan_ini', 'Bulan Ini');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
})();
