<?php
require_once dirname(__DIR__, 3) . '/runtime/support/auth.php';
simart_require_role('member');
$memberHeaderOptions = [
    'variant' => 'purchase',
];

require dirname(__DIR__, 2) . '/views/member/pembelian.php';
