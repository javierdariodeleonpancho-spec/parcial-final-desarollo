<?php

declare(strict_types=1);

require_once __DIR__ . '/nusoap.php';

$mimeCandidates = [
    __DIR__ . '/vendor/nusoapmime.php',
    __DIR__ . '/vendor/nusoap/nusoapmime.php',
    __DIR__ . '/nusoap/nusoapmime.php',
];

foreach ($mimeCandidates as $candidate) {
    if (is_file($candidate)) {
        require_once $candidate;
        break;
    }
}

if (!class_exists('nusoap_mime_client')) {
    class nusoap_mime_client extends nusoap_client
    {
    }
}

if (!class_exists('nusoap_mime_server')) {
    class nusoap_mime_server extends nusoap_server
    {
    }
}
