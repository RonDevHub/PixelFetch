<?php

require_once __DIR__ . '/autoloader.php';

use PixelFetch\Core\Config;
use PixelFetch\Storage\CacheManager;

$userKey = $_GET['user'] ?? '';
$accountConfig = Config::getAccount($userKey);

if (!$accountConfig) {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 - Galerie nicht gefunden</h1><p>Der angeforderte Benutzer existiert nicht oder ist nicht konfiguriert.</p>";
    exit;
}

$themeMode = Config::get('THEME_MODE', 'auto');

$cacheManager = new CacheManager($userKey, $accountConfig);
$posts = $cacheManager->getGalleryData();
?>
<!DOCTYPE html>
<html lang="de" data-theme="<?php echo htmlspecialchars($themeMode); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelFetch Galerie - <?php echo htmlspecialchars($userKey); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="gallery-grid">
    <?php if (empty($posts)): ?>
        <p>Keine Beiträge in der Galerie vorhanden oder Cache wird geladen.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php 
                $imageWebPath = 'storage/cache/' . urlencode($userKey) . '/images/' . htmlspecialchars($post['local_image']);
            ?>
            <div class="gallery-item" 
                 data-img="<?php echo $imageWebPath; ?>"
                 data-desc="<?php echo htmlspecialchars($post['description']); ?>"
                 data-likes="<?php echo $post['likes']; ?>"
                 data-comments="<?php echo $post['comments']; ?>">
                <img src="<?php echo $imageWebPath; ?>" alt="Pixelfeed Bild" loading="lazy">
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Nativer Lightbox-Container -->
<div id="lightbox" class="lightbox">
    <button id="close-btn" class="close-btn">&times;</button>
    <img id="lightbox-img" class="lightbox-content" src="" alt="Vergrößerte Ansicht">
    <div class="lightbox-info">
        <p id="lightbox-desc"></p>
        <div class="lightbox-meta">
            <span>❤️ <span id="lightbox-likes">0</span> Likes</span>
            <span>💬 <span id="lightbox-comments">0</span> Kommentare</span>
        </div>
    </div>
</div>

<!-- SVGs und Links am Ende der Galerie -->
<div class="footer-nav">
    <a class="footer-link" href="https://codeberg.org" target="_blank" rel="noopener" title="Codeberg Repository">
        <svg viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
        Codeberg
    </a>
    <a class="footer-link" href="https://liberapay.com" target="_blank" rel="noopener" title="Spenden">
        <svg viewBox="0 0 24 24"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10zm1-15h2v2h-2v6h2v2h-6v-2h2V9h-2V7h4z"/></svg>
        Spenden
    </a>
</div>

<script src="assets/js/gallery.js"></script>
</body>
</html>