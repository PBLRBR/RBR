<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/components/runtime/support/auth.php';
simart_start_session();
require_once dirname(__DIR__, 2) . '/components/runtime/controllers/public/CatalogController.php';

CatalogController::index();
