<?php
require_once __DIR__ . '/autoloader.php';
use PixelFetch\Core\Config;

if (Config::get('DISABLE_DOCS') === 'true') {
    header("HTTP/1.1 403 Forbidden");
    echo "<h1>403 - Forbidden</h1><p>Die Dokumentationsseite wurde vom Administrator deaktiviert.</p>";
    exit;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$baseUri = $protocol . $host . dirname($_SERVER['SCRIPT_NAME']);
$baseUri = rtrim($baseUri, '/\\');
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelFetch - Dokumentation</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; line-height: 1.6; color: #333; }
        pre { background: #eee; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { font-family: monospace; background: #eee; padding: 2px 5px; border-radius: 3px; }
        .preview-box { border: 2px dashed #ccc; padding: 10px; margin-top: 20px; border-radius: 8px; }
        .icon-link { display: inline-flex; align-items: center; gap: 5px; text-decoration: none; color: #000; font-weight: bold; }
        .icon-link svg { width: 20px; height: 20px; }
    </style>
</head>
<body>

<h1>📷 PixelFetch</h1>
<p>PixelFetch ist ein datenschutzfreundlicher, ressourcensparender Multi-Account-Cache für Pixelfeed-Galerien.</p>

<hr>

<h2>Integration</h2>
<p>Binde deine Galerie einfach über ein <code>&lt;iframe&gt;</code> in jede beliebige HTML-Seite ein. Ersetze <code>DEIN_USER_SCHLÜSSEL</code> durch den in der Konfiguration hinterlegten Namen.</p>

<pre><code>&lt;iframe src="<?php echo $baseUri; ?>/embed.php?user=DEIN_USER_SCHLÜSSEL" 
        style="width:100%; height:600px; border:none;" 
        loading="lazy"&gt;&lt;/iframe&gt;</code></pre>

<hr>

<h2>Entwickler-Ressourcen</h2>
<p>
    <a class="icon-link" href="https://codeberg.org" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
        Codeberg Repository
    </a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a class="icon-link" href="https://liberapay.com" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm1-15h2v2h-2v6h2v2h-6v-2h2V9h-2V7h4z"/></svg>
        Spenden supporten
    </a>
</p>

</body>
</html>