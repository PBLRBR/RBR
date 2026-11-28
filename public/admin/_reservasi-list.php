<div id="rsv-results">
    <div class="rsv-list">
        <?php foreach ($reservations as [$pemohon, $agenda, $ruangan, $tanggal, $mulai, $selesai, $peserta, $wa, $diajukan, $status, $bentrok, $idReservasi, $idTempat, $deskripsi]): ?>
            <article class="rsv-card rsv-<?= $status ?>" data-id="<?= $idReservasi ?>" data-idtempat="<?= $idTempat ?>"
                data-status="<?= $status ?>" data-pemohon="<?= $h($pemohon) ?>" data-agenda="<?= $h($agenda) ?>"
                data-ruangan="<?= $h($ruangan) ?>" data-wa="<?= $h($wa) ?>" data-deskripsi="<?= $h($deskripsi) ?>"
                data-tanggal="<?= $h($tanggal) ?>" data-mulai="<?= $h($mulai) ?>" data-selesai="<?= $h($selesai) ?>">
                <div class="rsv-card-head">
                    <div>
                        <h3 data-f="agenda"><?= $h($agenda) ?></h3>
                        <p class="rsv-by">Oleh <strong data-f="pemohon"><?= $h($pemohon) ?></strong> </p>
                    </div>
                    <span class="badge rsv-badge <?= $statusMeta[$status][1] ?>"><?= $statusMeta[$status][0] ?></span>
                </div>
                <dl class="rsv-details">
                    <div>
                        <dt>Ruangan</dt>
                        <dd data-f="ruangan"><?= $h($ruangan) ?></dd>
                    </div>
                    <div>
                        <dt>Tanggal</dt>
                        <dd data-f="tanggal"><?= $tglLabel($tanggal) ?></dd>
                    </div>
                    <div>
                        <dt>Waktu</dt>
                        <dd data-f="waktu"><?= $mulai ?> - <?= $selesai ?> WIB</dd>
                    </div>

                    <div>
                        <dt>Kontak WA</dt>
                        <dd><?= $h($wa) ?></dd>
                    </div>
                </dl>
                <?php if ($bentrok): ?>
                    <div class="rsv-warning">⚠️ <strong>Bentrok jadwal:</strong> Workshop Puisi (10:00 - 14:00) sudah disetujui
                        di
                        ruangan &amp; tanggal yang sama. Edit jadwalnya sebelum menyetujui.</div>
                <?php endif; ?>
                <!-- tombol tampil sesuai status kartu (diatur CSS) -->
                <div class="rsv-actions">
                    <a class="btn-sm btn-secondary" href="https://wa.me/<?= preg_replace('/\D/', '', $wa) ?>"
                        target="_blank" rel="noopener">💬 Chat WA</a>
                    <button class="btn-sm btn-secondary hide-rejected" data-action="edit">✏️ Edit</button>
                    <button class="btn-sm btn-danger only-pending" data-action="reject">Tolak</button>
                    <button class="btn-sm btn-approve only-pending" data-action="approve">✔ Setujui</button>
                    <button class="btn-sm btn-secondary only-approved" data-action="reset">Batalkan Persetujuan</button>
                    <button class="btn-sm btn-secondary only-rejected" data-action="reset">Kembalikan ke Review</button>
                </div>
            </article>
        <?php endforeach; ?>
        <p class="subtitle" id="rsv-empty" hidden>Tidak ada permintaan reservasi pada filter ini.</p>
    </div>
    <?php if ($rsvTotalPages > 1): ?>
        <?php
        $pageNumbers = array_unique([
            1,
            ...range(max(1, $rsvPage - 2), min($rsvTotalPages, $rsvPage + 2)),
            $rsvTotalPages,
        ]);
        sort($pageNumbers, SORT_NUMERIC);
        $previousPageNumber = 0;
        ?>

        <nav class="rsv-pagination" aria-label="Halaman reservasi">
            <?php if ($rsvPage > 1): ?>
                <a class="rsv-page" href="<?= $h($rsvUrl($rsvFilter, $rsvPage - 1)) ?>" aria-label="Halaman sebelumnya">‹</a>
            <?php else: ?>
                <span class="rsv-page is-disabled" aria-hidden="true">‹</span>
            <?php endif; ?>

            <?php foreach ($pageNumbers as $page): ?>
                <?php if ($page - $previousPageNumber > 1): ?>
                    <span class="rsv-page-gap" aria-hidden="true">…</span>
                <?php endif; ?>

                <?php if ($page === $rsvPage): ?>
                    <span class="rsv-page is-current" aria-current="page" aria-label="Halaman <?= $page ?>"><?= $page ?></span>
                <?php else: ?>
                    <a class="rsv-page" href="<?= $h($rsvUrl($rsvFilter, $page)) ?>"
                        aria-label="Halaman <?= $page ?>"><?= $page ?></a>
                <?php endif; ?>

                <?php $previousPageNumber = $page; ?>
            <?php endforeach; ?>

            <?php if ($rsvHasNext): ?>
                <a class="rsv-page" href="<?= $h($rsvUrl($rsvFilter, $rsvPage + 1)) ?>" aria-label="Halaman berikutnya">›</a>
            <?php else: ?>
                <span class="rsv-page is-disabled" aria-hidden="true">›</span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</div>