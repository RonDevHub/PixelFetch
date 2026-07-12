<?php

spl_autoload_register(function ($class) {
    // Projekt-spezifischer Namespace-Präfix
    $prefix = 'PixelFetch\\';

    // Basisverzeichnis für das Namespace-Präfix (src-Ordner liegt eine Ebene über public)
    $baseDir = dirname(__DIR__) . '/src/';

    // Prüfen, ob die Klasse den Präfix nutzt
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Relativen Klassennamen abrufen
    $relativeClass = substr($class, $len);

    // Ersetze Namespace-Separatoren durch Verzeichnistrenner und hänge .php an
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // Wenn die Datei existiert, lade sie
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("PixelFetch Autoloader: Datei nicht gefunden -> " . $file);
    }
});