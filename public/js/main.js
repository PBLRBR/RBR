document.addEventListener('DOMContentLoaded', () => {
    // form dummy: tampilan saja, belum terhubung ke backend
    document.querySelectorAll('form[data-dummy]').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            alert('Tampilan saja, belum terhubung ke backend.');
        });
    });

    // popup masuk / daftar (dibuka lewat ?auth=login atau ?auth=register)
    const modal = document.getElementById('modal-auth');
    if (modal) {
        const show = (tab) => {
            ['login', 'register'].forEach(name => {
                const on = name === tab;
                document.getElementById(`tab-${name}-btn`).classList.toggle('active', on);
                document.getElementById(`form-${name}`).classList.toggle('active', on);
            });
        };
        const auth = new URLSearchParams(location.search).get('auth');
        if (auth) {
            modal.style.display = 'flex';
            show(auth === 'register' ? 'register' : 'login');
        }
        document.getElementById('tab-login-btn').addEventListener('click', () => show('login'));
        document.getElementById('tab-register-btn').addEventListener('click', () => show('register'));
        document.getElementById('close-modal').addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
    }

    // pencarian & filter kategori katalog buku
    const searchInput = document.getElementById('search-input');
    const filterTags = document.getElementById('filter-tags');
    const bookCards = document.querySelectorAll('.book-card');
    if (searchInput && bookCards.length) {
        let category = 'semua';
        const filterBooks = () => {
            const q = searchInput.value.toLowerCase().trim();
            let visible = 0;
            bookCards.forEach(card => {
                const text = card.querySelector('.book-title').textContent.toLowerCase()
                    + ' ' + card.querySelector('.author').textContent.toLowerCase();
                const ok = text.includes(q) && (category === 'semua' || card.dataset.category === category);
                card.style.display = ok ? 'block' : 'none';
                if (ok) visible++;
            });
            document.getElementById('no-result').style.display = visible === 0 ? 'block' : 'none';
        };
        searchInput.addEventListener('input', filterBooks);
        filterTags.addEventListener('click', e => {
            if (e.target.tagName !== 'BUTTON') return;
            filterTags.querySelectorAll('.tag').forEach(t => t.classList.remove('active'));
            e.target.classList.add('active');
            category = e.target.dataset.category;
            filterBooks();
        });
    }

    // kalender reservasi (tanggal terisi dari data-booked-dates)
    const days = document.getElementById('calendar-days');
    if (days) {
        const title = document.getElementById('calendar-month-year');
        const booked = new Set(JSON.parse(days.dataset.bookedDates || '[]'));
        const month = new Date();
        month.setDate(1);

        const render = () => {
            days.replaceChildren();
            title.textContent = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(month);
            const first = new Date(month.getFullYear(), month.getMonth(), 1).getDay();
            const total = new Date(month.getFullYear(), month.getMonth() + 1, 0).getDate();
            for (let i = 0; i < first; i++) days.appendChild(document.createElement('span'));
            for (let d = 1; d <= total; d++) {
                const key = `${month.getFullYear()}-${String(month.getMonth() + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                const el = document.createElement('span');
                el.textContent = d;
                if (booked.has(key)) el.classList.add('has-event');
                el.addEventListener('click', () => {
                    days.querySelectorAll('span').forEach(s => s.classList.remove('selected'));
                    el.classList.add('selected');
                });
                days.appendChild(el);
            }
        };
        document.getElementById('prev-month').addEventListener('click', () => { month.setMonth(month.getMonth() - 1); render(); });
        document.getElementById('next-month').addEventListener('click', () => { month.setMonth(month.getMonth() + 1); render(); });
        render();
    }
});
