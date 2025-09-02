<?php
declare(strict_types=1);
// Minimal bootstrap for pure unit tests (no WordPress).
$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
    require $vendor;
}
