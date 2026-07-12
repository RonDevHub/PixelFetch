<?php
require_once __DIR__ . '/autoloader.php';

use PixelFetch\Core\Config;

if (Config::get('DISABLE_DOCS') === 'true') {
    header("HTTP/1.1 403 Forbidden");
    echo "<h1>403 - Forbidden</h1><p>Die Dokumentationsseite wurde vom Administrator deaktiviert.</p>";
    exit;
}

// Basis-URL für die Iframes dynamisch ermitteln
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$baseUri = $protocol . $host . dirname($_SERVER['SCRIPT_NAME']);
$baseUri = rtrim($baseUri, '/\\');

// Accounts für die Vorschau-Generierung laden
$accountsPath = dirname(__DIR__) . '/config/accounts.json';
$accounts = [];
$configError = null;

if (!file_exists($accountsPath)) {
    $configError = "Die Konfigurationsdatei <code>config/accounts.json</code> fehlt. Bitte erstelle sie basierend auf der Beispieldatei.";
} else {
    $content = file_get_contents($accountsPath);
    $accounts = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $configError = "Die <code>config/accounts.json</code> enthält ungültiges JSON: " . json_last_error_msg();
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelFetch - Setup & Galerie-Vorschau</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            line-height: 1.6;
            color: #1f2937;
            background-color: #f9fafb;
        }

        h1,
        h2,
        h3 {
            color: #111827;
        }

        pre {
            background: #1e293b;
            color: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 14px;
        }

        code {
            font-family: monospace;
            background: #e2e8f0;
            padding: 2px 5px;
            border-radius: 4px;
            color: #0f172a;
            font-size: 14px;
        }

        pre code {
            background: none;
            color: inherit;
            padding: 0;
            font-size: inherit;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .account-error {
            background: #fff7ed;
            border: 1px solid #ffedd5;
            color: #9a3412;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .preview-iframe {
            width: 100%;
            height: 450px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            margin-top: 10px;
        }

        .icon-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: #4b5563;
            font-weight: 600;
        }

        .icon-link:hover {
            color: #2563eb;
        }

        .icon-link svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .footer-nav {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 20px;
        }
    </style>
</head>

<body>

    <div class="card">
        <h1>📷 PixelFetch Dashboard</h1>
        <p>Datenschutzfreundlicher, ressourcensparender Multi-Account-Cache für deine Pixelfeed-Galerien.</p>
    </div>

    <h2>🛠️ Konfigurierte Account-Vorschau</h2>

    <?php if ($configError): ?>
        <div class="error-box">
            <strong>Konfigurationsfehler:</strong> <?php echo $configError; ?>
        </div>
    <?php elseif (empty($accounts)): ?>
        <div class="card">
            <p>Es sind noch keine Accounts in der <code>config/accounts.json</code> definiert.</p>
        </div>
    <?php else: ?>
        <?php foreach ($accounts as $userKey => $config): ?>
            <div class="card">
                <h3>Account: <code><?php echo htmlspecialchars($userKey); ?></code></h3>

                <?php
                // Validierung der Account-Parameter vor der Iframe-Ausgabe
                $hasError = false;
                $errors = [];

                if (empty($config['instance']) || !filter_var($config['instance'], FILTER_VALIDATE_URL)) {
                    $errors[] = "Ungültige oder fehlende Instanz-URL (<code>instance</code>).";
                    $hasError = true;
                }
                if (empty($config['username'])) {
                    $errors[] = "Fehlender Pixelfeed-Benutzername (<code>username</code>).";
                    $hasError = true;
                }
                if (empty($config['token'])) {
                    $errors[] = "Fehlendes Personal Access Token (<code>token</code>).";
                    $hasError = true;
                }

                if ($hasError): ?>
                    <div class="account-error">
                        <strong>Konfigurationsfehler für diesen Account:</strong>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p><strong>Instanz:</strong> <?php echo htmlspecialchars($config['instance']); ?> | <strong>Nutzer:</strong> @<?php echo htmlspecialchars($config['username']); ?></p>

                    <h4>Einbettungscode für diesen Account:</h4>
                    <pre><code>&lt;iframe src="<?php echo $baseUri; ?>/embed.php?user=<?php echo urlencode($userKey); ?>" style="width:100%; height:600px; border:none;" loading="lazy"&gt;&lt;/iframe&gt;</code></pre>

                    <h4>Live-Vorschau:</h4>
                    <iframe class="preview-iframe" src="embed.php?user=<?php echo urlencode($userKey); ?>" loading="lazy"></iframe>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="card">
        <h2>Ressourcen & Entwicklung</h2>
        <div class="footer-nav">
            <a class="icon-link" href="https://codeberg.org/RonDevHub/PixelFetch" target="_blank" rel="noopener" title="Codeberg Repository">
                <svg viewBox="0 0 640 640">
                    <path d="M64 320C64 368.1 77.5 415.3 103.1 456L316.5 180.1C318 178.1 321.9 178.1 323.4 180.1L412.5 295.3L348.7 295.3L350.1 300.4L416.5 300.4L435.3 324.7L356.9 324.7L359.1 332.7L441.5 332.7L458.1 354.1L365.1 354.1L368 364.4L466 364.4L480.8 383.5L373.3 383.5L376.8 396.1L490.4 396.1L503.4 412.9L381.4 412.9L385.3 426.8L514.1 426.8L526.1 442.3L389.6 442.3L393.5 456.2L536.8 456.2C562.5 415.2 576 368 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320zM397.9 471.5L401.8 485.4L515.4 485.4C519.1 481 522.9 476.2 526.4 471.5L397.9 471.5zM406.1 500.9L409.9 514.8L485.9 514.8C490.9 510.6 496.3 505.7 501.1 500.9L406.1 500.9zM414.3 530.3L418.2 544.1L443.9 544.1C451.5 539.7 458.5 535.3 466.1 530.3L414.3 530.3z" />
                </svg>
                Codeberg
            </a>
            <a class="icon-link" href="https://rondev.de/donate" target="_blank" rel="noopener" title="Spenden">
                <svg class="heart" viewBox="0 0 540 540">
                    <path fill="currentColor" d="M308.2 488.2L494.4 302c29.2-29.2 45.6-68.9 45.6-110.2 0-86.1-69.8-155.8-155.8-155.8-41.3 0-81 16.4-110.2 45.6-2.2 2.2-5.8 2.2-8 0-29.2-29.2-68.9-45.6-110.2-45.6-86.1 0-155.8 69.8-155.8 155.8 0 41.3 16.4 81 45.6 110.2L231.8 488.2c21.1 21.1 55.3 21.1 76.4 0zM54 191.8c0 7.5-6 13.5-13.5 13.5S27 199.3 27 191.8c0-71.1 57.7-128.8 128.8-128.8 7.5 0 13.5 6 13.5 13.5S163.3 90 155.8 90C99.6 90 54 135.6 54 191.8zm258.2-72c-11.6 11.6-26.9 17.5-42.2 17.5-7.5 0-13.5-6-13.5-13.5s6-13.5 13.5-13.5c8.4 0 16.7-3.2 23.1-9.6 24.2-24.2 56.9-37.7 91.1-37.7 7.5 0 13.5 6 13.5 13.5S391.6 90 384.2 90c-27 0-52.9 10.7-72 29.8z" />
                </svg>
                Spenden
            </a>
        </div>
    </div>

</body>

</html>