window.SIMART_Transaksi = {
    laporan: (tanggalMulai, tanggalSelesai) => {
        const query = new URLSearchParams({ tanggal_mulai: tanggalMulai, tanggal_selesai: tanggalSelesai });
        return window.SIMART_API.get(`laporan/laporan_penjualan.php?${query}`);
    },
};

document.addEventListener('DOMContentLoaded', () => {
    // --- 1. TAB SWITCHING LOGIC ---
    const sidebarBtns = document.querySelectorAll('.sidebar-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    sidebarBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sidebarBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));

            btn.classList.add('active');
            const targetTab = btn.getAttribute('data-tab');
            document.getElementById(`tab-${targetTab}`).classList.add('active');
        });
    });

    const requestedTab = window.location.hash.replace('#tab-', '');
    if (requestedTab) {
        const requestedButton = document.querySelector(`.sidebar-btn[data-tab="${requestedTab}"]`);
        if (requestedButton) requestedButton.click();
    }
});

// --- 2. POS KASIR TRANSAKSI LOGIC ---
let cart = [];

function addToCart(id, title, price) {
    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cart.push({ id, title, price, qty: 1 });
    }
    renderCart();
}

function renderCart() {
    const cartTbody = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    cartTbody.innerHTML = '';

    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.qty;
        total += subtotal;

        const tr = document.createElement('tr');
        const titleCell = document.createElement('td');
        titleCell.textContent = item.title;
        const quantityCell = document.createElement('td');
        quantityCell.textContent = `${item.qty}x`;
        const subtotalCell = document.createElement('td');
        subtotalCell.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        tr.append(titleCell, quantityCell, subtotalCell);
        cartTbody.appendChild(tr);
    });

    cartTotal.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    const serializedItems = document.getElementById('sale-items-json');
    if (serializedItems) serializedItems.value = JSON.stringify(cart.map(({ id, qty }) => ({ id, qty })));
}

const formPosSale = document.getElementById('form-pos-sale');
if (formPosSale) {
    formPosSale.addEventListener('submit', (event) => {
        if (cart.length === 0) {
            event.preventDefault();
            alert('Keranjang kasir masih kosong.');
            return;
        }
        const submitButton = formPosSale.querySelector('button[type="submit"]');
        if (submitButton) submitButton.disabled = true;
    });
}

// --- 3. RESERVASI STATUS UPDATE LOGIC ---
function updateStatus(id, newStatus) {
    const statusElem = document.getElementById(`status-${id}`);
    if (statusElem) {
        statusElem.textContent = newStatus;
        if (newStatus === 'Disetujui') {
            statusElem.className = 'badge badge-success';
        } else {
            statusElem.className = 'badge badge-warning';
        }
    }
}

const formAddSchedule = document.getElementById('form-admin-add-schedule');
if (formAddSchedule) {
    formAddSchedule.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const ruangan = document.getElementById('admin-ruangan').value;
        const tanggal = document.getElementById('admin-tanggal').value;
        const jamMulai = document.getElementById('admin-jam-mulai').value;
        const jamSelesai = document.getElementById('admin-jam-selesai').value;
        const pemohon = document.getElementById('admin-pemohon').value;

        // Validasi sederhana: jam selesai tidak boleh lebih awal dari jam mulai
        if (jamSelesai <= jamMulai) {
            alert('Jam selesai harus lebih lambat dari jam mulai!');
            return;
        }

        // Format tampilan konsisten HH:MM - HH:MM WIB
        const formatWaktu = `${jamMulai} - ${jamSelesai} WIB`;

        const tbody = document.getElementById('admin-schedule-list');
        const tr = document.createElement('tr');

        const roomCell = document.createElement('td');
        const roomName = document.createElement('strong');
        roomName.textContent = ruangan;
        roomCell.appendChild(roomName);
        tr.appendChild(roomCell);

        const dateCell = document.createElement('td');
        dateCell.textContent = tanggal;
        tr.appendChild(dateCell);

        const timeCell = document.createElement('td');
        timeCell.textContent = formatWaktu;
        tr.appendChild(timeCell);

        const requesterCell = document.createElement('td');
        requesterCell.textContent = pemohon;
        tr.appendChild(requesterCell);

        const actionCell = document.createElement('td');
        const removeButton = document.createElement('button');
        removeButton.className = 'btn-sm btn-danger';
        removeButton.setAttribute('onclick', "this.closest('tr').remove()");
        removeButton.textContent = 'Hapus Jadwal';
        actionCell.appendChild(removeButton);
        tr.appendChild(actionCell);
        
        tbody.prepend(tr);
        formAddSchedule.reset();
        alert('Jadwal reservasi berhasil diplot ke web!');
    });
}

