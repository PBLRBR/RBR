<?php

$query = isset($_GET['return_to']) ? '?auth=login&return_to=' . rawurlencode((string) $_GET['return_to']) : '?auth=login';
header('Location: ../../index.php' . $query);
exit;
