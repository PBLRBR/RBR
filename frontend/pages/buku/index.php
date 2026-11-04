<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/pages/auth/auth.php';
require_once __DIR__ . '/index.controller.php';
simart_start_session();
CatalogController::index();

CatalogController::index();
