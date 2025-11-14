<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/nusoapmime.php';
require_once __DIR__ . '/src/ClientesService.php';

$wsdlTemplatePath = __DIR__ . '/wsdl/wsCliente.wsdl';

if (!file_exists($wsdlTemplatePath)) {
    http_response_code(500);
    echo 'No se encontró el archivo de definición WSDL.';
    exit;
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
$serviceUrl = $baseUrl . '/wsCliente.php';
$namespace = $serviceUrl;

$wsdlContent = str_replace(
    ['{{BASE_NAMESPACE}}', '{{BASE_URL}}'],
    [$namespace, $serviceUrl],
    file_get_contents($wsdlTemplatePath)
);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['wsdl'])) {
    header('Content-Type: text/xml; charset=utf-8');
    echo $wsdlContent;
    exit;
}

$tempWsdl = tempnam(sys_get_temp_dir(), 'wsdl_');
file_put_contents($tempWsdl, $wsdlContent);

try {
    $server = new nusoap_server($tempWsdl);
    $server->setClass(ClientesService::class);
    $server->service(file_get_contents('php://input'));
} finally {
    if (is_file($tempWsdl)) {
        unlink($tempWsdl);
    }
}
