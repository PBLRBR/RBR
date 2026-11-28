document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('visit-form');
    if (!form) return;
    const msg = document.getElementById('visit-message');
    const button = document.getElementById('visit-submit');
    const visitStorageKey = 'simart:guestbook:pending';
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (button.disabled || !form.reportValidity()) return;
        button.disabled = true;
        msg.hidden = true;
        try {
            if (
                typeof globalThis.crypto?.randomUUID !== 'function' ||
                !globalThis.crypto?.subtle
            ) {
                throw new Error('Buka SIMART melalui HTTPS atau localhost untuk mencatat kunjungan.');
            }

            const payload = {
                nama_pengunjung: document.getElementById('visit-name').value.trim(),
                no_hp: document.getElementById('visit-phone').value.trim(),
                keperluan: document.getElementById('visit-purpose').value,
            };

            const hash = await crypto.subtle.digest(
                'SHA-256',
                new TextEncoder().encode(JSON.stringify(payload))
            );
            const signature = Array.from(new Uint8Array(hash), byte =>
                byte.toString(16).padStart(2, '0')
            ).join('');

            const pending = JSON.parse(sessionStorage.getItem(visitStorageKey) ?? 'null');

            if (pending && pending.signature !== signature && !confirm(
                'Ada kiriman sebelumnya yang belum terkonfirmasi. Mulai catatan kunjungan baru dengan isian ini?'
            )) {
                return;
            }

            const requestId = pending?.signature === signature
                ? pending.id
                : crypto.randomUUID();

            sessionStorage.setItem(visitStorageKey, JSON.stringify({ id: requestId, signature }));

            const result = await apiRequest('kunjungan/daftar_kunjungan.php', {
                method: 'POST',
                body: { ...payload, idempotency_key: requestId },
            });

            sessionStorage.removeItem(visitStorageKey);
            // Header CSRF dikirim otomatis oleh apiRequest(), bukan dari atribut form terpisah.
            msg.classList.remove('visit-error');
            msg.textContent = `Kunjungan dicatat pada ${result.tanggal_kunjungan} pukul ${result.jam_masuk}.`;
            form.reset();
        } catch (error) {
            if (error.status === 422) {
                sessionStorage.removeItem(visitStorageKey);
            }
            msg.classList.add('visit-error');
            msg.textContent = error.message;
        } finally {
            msg.hidden = false;
            button.disabled = false;
        }
    });
});
