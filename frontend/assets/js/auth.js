document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.api-form[data-endpoint]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('[type="submit"]');
            const message = form.querySelector('.form-message');
            button.disabled = true;
            message.textContent = 'Memproses…';
            try {
                const data = Object.fromEntries(new FormData(form).entries());
                const result = await window.SIMART_API.post(form.dataset.endpoint, data);
                message.textContent = result.message || 'Berhasil.';
                if (result.success && form.dataset.endpoint.includes('reset_password')) {
                    form.reset();
                }
            } catch (error) {
                message.textContent = error.message;
            } finally {
                button.disabled = false;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. MODAL AUTH (MASUK / DAFTAR) ---
    const modalAuth = document.getElementById('modal-auth');
    const openLoginBtn = document.getElementById('open-login');
    const openRegisterBtn = document.getElementById('open-register');
    const closeModalBtn = document.getElementById('close-modal');
    const tabLoginBtn = document.getElementById('tab-login-btn');
    const tabRegisterBtn = document.getElementById('tab-register-btn');
    const formLogin = document.getElementById('form-login');
    const formRegister = document.getElementById('form-register');

    if (modalAuth) {  
        function openModal(tab) {
            modalAuth.style.display = 'flex';
            if (tab === 'login') {
                switchTab(tabLoginBtn, formLogin, tabRegisterBtn, formRegister);
            } else {
                switchTab(tabRegisterBtn, formRegister, tabLoginBtn, formLogin);
            }
        }

        function switchTab(activeBtn, activeForm, inactiveBtn, inactiveForm) {
            activeBtn.classList.add('active');
            inactiveBtn.classList.remove('active');
            activeForm.classList.add('active');
            inactiveForm.classList.remove('active');
        }

        if (openLoginBtn) openLoginBtn.addEventListener('click', () => openModal('login'));
        if (openRegisterBtn) openRegisterBtn.addEventListener('click', () => openModal('register'));
        if (window.simartAuthTab) openModal(window.simartAuthTab);
        if (closeModalBtn) closeModalBtn.addEventListener('click', () => modalAuth.style.display = 'none');
        if (tabLoginBtn) tabLoginBtn.addEventListener('click', () => switchTab(tabLoginBtn, formLogin, tabRegisterBtn, formRegister));
        if (tabRegisterBtn) tabRegisterBtn.addEventListener('click', () => switchTab(tabRegisterBtn, formRegister, tabLoginBtn, formLogin));

        window.addEventListener('click', (e) => {
            if (e.target === modalAuth) modalAuth.style.display = 'none';
        });
    }

    // --- 2. PENCARIAN & FILTERING KATALOG BUKU ---
    const searchInput = document.getElementById('search-input');
    const filterTags = document.getElementById('filter-tags');
    const bookCards = document.querySelectorAll('.book-card');
    const noResultText = document.getElementById('no-result');

    if (searchInput && bookCards.length > 0) {
        let currentCategory = 'semua';

        function filterBooks() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            bookCards.forEach(card => {
                const title = card.querySelector('.book-title').textContent.toLowerCase();
                const author = card.querySelector('.author').textContent.toLowerCase();
                const category = card.getAttribute('data-category');

                const matchesSearch = title.includes(query) || author.includes(query);
                const matchesCategory = (currentCategory === 'semua') || (category === currentCategory);

                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noResultText) {
                noResultText.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        searchInput.addEventListener('input', filterBooks);

        if (filterTags) {
            filterTags.addEventListener('click', (e) => {
                if (e.target.tagName === 'BUTTON') {
                    filterTags.querySelectorAll('.tag').forEach(tag => tag.classList.remove('active'));
                    e.target.classList.add('active');
                    currentCategory = e.target.getAttribute('data-category');
                    filterBooks();
                }
            });
        }
    }

    // --- 3. RESERVATION CALENDAR ---
    const calendarDays = document.getElementById('calendar-days');
    if (calendarDays) {
        const title = document.getElementById('calendar-month-year');
        const bookedDates = new Set(JSON.parse(calendarDays.dataset.bookedDates || '[]'));
        const monthDate = new Date();
        monthDate.setDate(1);

        function renderCalendar() {
            calendarDays.replaceChildren();
            title.textContent = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(monthDate);
            const firstWeekday = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1).getDay();
            const daysInMonth = new Date(monthDate.getFullYear(), monthDate.getMonth() + 1, 0).getDate();
            for (let blank = 0; blank < firstWeekday; blank += 1) {
                const spacer = document.createElement('span');
                spacer.setAttribute('aria-hidden', 'true');
                calendarDays.appendChild(spacer);
            }
            for (let day = 1; day <= daysInMonth; day += 1) {
                const dateKey = `${monthDate.getFullYear()}-${String(monthDate.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const dayElem = document.createElement('span');
                dayElem.textContent = String(day);
                dayElem.setAttribute('role', 'button');
                dayElem.tabIndex = 0;
                if (bookedDates.has(dateKey)) dayElem.classList.add('has-event');
                dayElem.addEventListener('click', () => {
                    calendarDays.querySelectorAll('span').forEach(el => el.classList.remove('selected'));
                    dayElem.classList.add('selected');
                });
                calendarDays.appendChild(dayElem);
            }
        }

        document.getElementById('prev-month')?.addEventListener('click', () => {
            monthDate.setMonth(monthDate.getMonth() - 1);
            renderCalendar();
        });
        document.getElementById('next-month')?.addEventListener('click', () => {
            monthDate.setMonth(monthDate.getMonth() + 1);
            renderCalendar();
        });
        renderCalendar();
    }

    // Profile updates are submitted to the authenticated PHP handler.
});

