<?php

declare(strict_types=1);

// Attempt to load a bundled NuSOAP distribution if available.
$libraryCandidates = [
    __DIR__ . '/vendor/nusoap.php',
    __DIR__ . '/vendor/nusoap/nusoap.php',
    __DIR__ . '/nusoap/nusoap.php',
];

foreach ($libraryCandidates as $candidate) {
    if (is_file($candidate)) {
        require_once $candidate;
        break;
    }
}

if (!class_exists('nusoap_client')) {
    require_once __DIR__ . '/nusoap_adapter.php';
}
