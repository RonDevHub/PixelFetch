<?php

require_once __DIR__ . '/autoloader.php';

use PixelFetch\Core\Config;

$userKey = isset($_GET['user']) ? trim((string)$_GET['user']) : '';
$file = isset($_GET['file']) ? trim((string)$_GET['file']) : '';

if (empty($userKey) || empty($file)) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

// Validierung des Accounts
$accountConfig = Config::getAccount($userKey);
if (!$accountConfig) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// Sicherheits-Check gegen Directory Traversal (nur erlaubte Zeichen im Dateinamen)
if (!preg_with_matches('/^[a-f0-9]{64}\.[a-z0-9]+$/i', $file)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

$baseStorage = dirname(__DIR__) . '/storage/cache/' . $userKey . '/images/';
$filePath = $baseStorage . $file;

if (!file_exists($filePath)) {
    header("HTTP/1.1 404 Not Found");
    exit;
}

// MIME-Type bestimmen und Bild ausgeben
$ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeTypes = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp'
];

$contentType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;

// Hilfsfunktion für den Regex-Match ohne globale Namespace-Probleme
function preg_with_matches(string $pattern, string $subject): bool {
    return preg_match($pattern, $subject) === 1;
}