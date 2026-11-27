document.addEventListener('DOMContentLoaded', () => {
    const homeNavbar = document.querySelector('.home-hero')?.closest('body')?.querySelector('.navbar');
    if (homeNavbar) {
        const updateHomeNavbar = () => homeNavbar.classList.toggle('home-navbar-scrolled', window.scrollY > 24);
        updateHomeNavbar();
        window.addEventListener('scroll', updateHomeNavbar, { passive: true });
    }

    // Navigasi ringkas untuk layar kecil, dipasang otomatis di semua header.
    document.querySelectorAll('.navbar .nav-container').forEach(container => {
        const nav = container.querySelector('.nav-menu');
        if (!nav || container.querySelector('.nav-toggle')) return;
        const toggle = document.createElement('button');
        toggle.className = 'nav-toggle';
        toggle.type = 'button';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka menu navigasi');
        toggle.innerHTML = '<span></span><span></span><span></span>';
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') !== 'true';
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            container.classList.toggle('nav-open', open);
        });
        container.insertBefore(toggle, nav);

        const auth = container.querySelector('.nav-auth');
        if (auth) {
            const originalParent = auth.parentElement;
            const originalNext = auth.nextSibling;
            const syncAuthPlacement = () => {
                if (window.matchMedia('(max-width: 760px)').matches) {
                    if (auth.parentElement !== nav) nav.append(auth);
                } else if (auth.parentElement !== originalParent) {
                    originalParent.insertBefore(auth, originalNext);
                }
            };
            syncAuthPlacement();
            window.addEventListener('resize', syncAuthPlacement);
        }

        const drawerHeader = document.createElement('div');
        drawerHeader.className = 'mobile-nav-header';
        drawerHeader.innerHTML = '<a class="mobile-nav-brand" href="/"><img src="/images/rbr.png" alt=""><span>Rumah Budaya Ratna</span></a><button type="button" class="mobile-nav-close" aria-label="Tutup menu">&times;</button>';
        nav.prepend(drawerHeader);
        drawerHeader.querySelector('.mobile-nav-close').addEventListener('click', () => {
            container.classList.remove('nav-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Buka menu navigasi');
        });
    });

    document.querySelectorAll('.story-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const detail = document.getElementById(button.getAttribute('aria-controls'));
            const open = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', String(open));
            detail.hidden = !open;
            button.innerHTML = `${open ? 'Tutup cerpen' : 'Baca cerpen lengkap'} <span aria-hidden="true">${open ? '↑' : '↓'}</span>`;
        });
    });
    const profileForm = document.getElementById('profile-form');

    profileForm?.addEventListener('submit', async event => {
        event.preventDefault();

        const button = profileForm.querySelector('button[type="submit"]');
        if (button.disabled) return;

        const message = document.getElementById('profile-message');
        const nameInput = profileForm.elements.namedItem('nama');
        const phoneInput = profileForm.elements.namedItem('no_hp');

        button.disabled = true;
        message.hidden = true;

        try {
            const profile = await apiRequest('auth/profil.php', {
                method: 'PATCH',
                body: {
                    nama: nameInput.value.trim(),
                    no_hp: phoneInput.value.trim(),
                },
            });

            nameInput.value = profile.nama;
            phoneInput.value = profile.no_hp ?? '';

            const displayName = document.getElementById('profile-display-name');
            if (displayName) displayName.textContent = profile.nama;

            document.querySelectorAll('.nav-auth a[href="/profil.php"] .nav-account-name')
                .forEach(label => {
                    label.textContent = profile.nama;
                });

            message.textContent = 'Profil berhasil disimpan.';
        } catch (error) {
            message.textContent = error.message;
        } finally {
            message.hidden = false;
            button.disabled = false;
        }
    });

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
