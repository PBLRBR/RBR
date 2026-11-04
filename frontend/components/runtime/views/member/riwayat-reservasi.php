<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            Riwayat Reservasi | SIMART
        </title>
        <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/responsive.css">
    </head>
    <body>
        <?php require_once dirname(__DIR__, 3) . '/navbar.php'; ?>
        <main class="container page-content">
            <h1>
                Riwayat Reservasi
            </h1>
            <p class="subtitle">
                Pantau status pengajuan penggunaan ruang budaya Anda.
            </p>
            <div class="admin-card" style="overflow-x:auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>
                                Tanggal pengajuan
                            </th>
                            <th>
                                Ruang
                            </th>
                            <th>
                                Waktu kegiatan
                            </th>
                            <th>
                                Agenda
                            </th>
                            <th>
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <?php
                            $status = (string) $reservation['status_reservasi'];
                            $badgeClass = $status === 'disetujui' ? 'badge-success' : ($status === 'ditolak' ? 'badge-danger' : 'badge-warning');
                            ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($reservation['dibuat_pada'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($reservation['nama_tempat'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($reservation['tanggal_acara'])) . ' · ' . substr((string) $reservation['jam_mulai'], 0, 5) . '–' . substr((string) $reservation['jam_selesai'], 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($reservation['nama_acara'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($reservations === []): ?>
                            <tr><td colspan="5" class="text-center">Belum ada reservasi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php
        require_once dirname(__DIR__, 3) . '/runtime/support/functions.php';
        simart_public_footer('compact');
        ?>
    </body>
</html>
