<?php
require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
simart_require_role('member');
$memberHeaderOptions = [
    'variant' => 'purchase',
];

require __DIR__ . '/riwayat.view.php';
