<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

$autoloaderPath = __DIR__ . '/autoloader.php';
if (!file_exists($autoloaderPath)) {
    die("Fataler Fehler: autoloader.php wurde nicht gefunden.");
}
require_once $autoloaderPath;

use PixelFetch\Core\Config;
use PixelFetch\Storage\CacheManager;

$userKey = isset($_GET['user']) ? trim((string)$_GET['user']) : '';

if (empty($userKey)) {
    header("HTTP/1.1 400 Bad Request");
    echo "<h1>400 - Fehlender Parameter</h1><p>Bitte übergib einen Benutzer via ?user=benutzername</p>";
    exit;
}

$accountConfig = Config::getAccount($userKey);

if (!$accountConfig || !is_array($accountConfig)) {
    header("HTTP/1.1 404 Not Found");
    echo "<h1>404 - Galerie nicht gefunden</h1><p>Der angeforderte Benutzer existiert nicht oder ist nicht konfiguriert.</p>";
    exit;
}

$themeMode = isset($_GET['theme']) ? trim((string)$_GET['theme']) : (isset($accountConfig['theme_mode']) ? $accountConfig['theme_mode'] : Config::get('THEME_MODE', 'auto'));

if (!in_array($themeMode, ['light', 'dark', 'auto'])) {
    $themeMode = 'auto';
}

$galleryData = [];
$posts = [];
$accountMeta = [];

try {
    if (class_exists('PixelFetch\Storage\CacheManager')) {
        $cacheManager = new CacheManager($userKey, $accountConfig);
        $galleryData = $cacheManager->getGalleryData();
        
        $posts = isset($galleryData['posts']) ? $galleryData['posts'] : [];
        $accountMeta = isset($galleryData['account_meta']) ? $galleryData['account_meta'] : [];
    } else {
        echo "<h1>Fataler Fehler</h1><p>Klasse CacheManager konnte nicht geladen werden.</p>";
        exit;
    }
} catch (\Throwable $e) {
    error_log("PixelFetch Exception in embed.php: " . $e->getMessage());
    echo "<h1>Interner Fehler beim Laden der Daten</h1>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="de" data-theme="<?php echo htmlspecialchars((string)$themeMode, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PixelFetch Galerie - <?php echo htmlspecialchars($userKey, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="gallery-wrapper">
    
    <header class="gallery-header">
        <img src="assets/logo/Pixelfetch.png" alt="PixelFetch Logo" class="gallery-logo">
        
        <?php if (!empty($accountMeta['profile_url'])): ?>
            <a href="<?php echo htmlspecialchars($accountMeta['profile_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="user-profile-badge">
                <span class="user-name"><?php echo htmlspecialchars($accountMeta['display_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if (!empty($accountMeta['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($accountMeta['avatar_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Avatar" class="user-avatar">
                <?php endif; ?>
            </a>
        <?php endif; ?>
    </header>

    <div class="gallery-grid">
        <?php if (empty($posts)): ?>
            <p>Keine Beiträge in der Galerie vorhanden.</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php 
                    if (!is_array($post) || empty($post['local_image'])) {
                        continue;
                    }
                    $imageWebPath = 'image.php?user=' . urlencode($userKey) . '&file=' . urlencode((string)$post['local_image']);
                    $description = isset($post['description']) ? (string)$post['description'] : '';
                    $likes = isset($post['likes']) ? (int)$post['likes'] : 0;
                    $comments = isset($post['comments']) ? (int)$post['comments'] : 0;
                    $permalink = isset($post['url']) ? (string)$post['url'] : '#';
                ?>
                <div class="gallery-item" 
                     data-img="<?php echo $imageWebPath; ?>"
                     data-desc="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
                     data-likes="<?php echo $likes; ?>"
                     data-comments="<?php echo $comments; ?>"
                     data-permalink="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>">
                    <img src="<?php echo $imageWebPath; ?>" alt="Pixelfeed Bild" loading="lazy">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div id="lightbox" class="lightbox">
    <button id="close-btn" class="close-btn" aria-label="Schließen">&times;</button>
    <img id="lightbox-img" class="lightbox-content" src="" alt="Vergrößerte Ansicht">
    <div class="lightbox-info">
        <p id="lightbox-desc"></p>
        <div class="lightbox-meta">
            <span class="lightbox-icon"><svg viewBox="0 0 540 540"><path opacity=".4" fill="#ff4d6d" d="M27 191.8c0 7.5 6 13.5 13.5 13.5s13.5-6 13.5-13.5c0-56.2 45.6-101.8 101.8-101.8 7.5 0 13.5-6 13.5-13.5S163.3 63 155.8 63C84.7 63 27 120.7 27 191.8zm229.5-68c0 7.5 6 13.5 13.5 13.5 15.2 0 30.5-5.8 42.2-17.5 19.1-19.1 45-29.8 72-29.8 7.5 0 13.5-6 13.5-13.5S391.6 63 384.2 63c-34.2 0-66.9 13.6-91.1 37.7-6.4 6.4-14.7 9.6-23.1 9.6-7.5 0-13.5 6-13.5 13.5z"/><path fill="#ff4d6d" d="M308.2 488.2L494.4 302c29.2-29.2 45.6-68.9 45.6-110.2 0-86.1-69.8-155.8-155.8-155.8-41.3 0-81 16.4-110.2 45.6-2.2 2.2-5.8 2.2-8 0-29.2-29.2-68.9-45.6-110.2-45.6-86.1 0-155.8 69.8-155.8 155.8 0 41.3 16.4 81 45.6 110.2L231.8 488.2c21.1 21.1 55.3 21.1 76.4 0zM54 191.8c0 7.5-6 13.5-13.5 13.5S27 199.3 27 191.8c0-71.1 57.7-128.8 128.8-128.8 7.5 0 13.5 6 13.5 13.5S163.3 90 155.8 90C99.6 90 54 135.6 54 191.8zm258.2-72c-11.6 11.6-26.9 17.5-42.2 17.5-7.5 0-13.5-6-13.5-13.5s6-13.5 13.5-13.5c8.4 0 16.7-3.2 23.1-9.6 24.2-24.2 56.9-37.7 91.1-37.7 7.5 0 13.5 6 13.5 13.5S391.6 90 384.2 90c-27 0-52.9 10.7-72 29.8z"/></svg> <span id="lightbox-likes">0</span> Likes</span>
            <span class="lightbox-icon"><svg viewBox="0 0 540 540"><path opacity=".4" fill="currentColor" d="M27 243c0 7.5 6 13.5 13.5 13.5S54 250.5 54 243c0-99.2 91.3-189 216-189 7.5 0 13.5-6 13.5-13.5S277.5 27 270 27C133 27 27 126.4 27 243zM53 484.7c4.9 5.6 13.4 6.2 19.1 1.3 13-11.4 24.3-24.6 34.8-38.2 10.3-13.3 14.4-28.7 13.6-43.5-.4-7.4-6.8-13.1-14.2-12.7s-13.1 6.8-12.7 14.2c.5 8.7-1.9 17.6-8 25.5-9.5 12.2-19.6 24.2-31.2 34.4-5.6 4.9-6.2 13.4-1.3 19z"/><path fill="currentColor" d="M540 243c0 134.2-120.9 243-270 243-15.3 0-30.3-1.1-44.9-3.3-4.6-.7-9.4 .3-13.3 2.9-21.7 14.2-43.2 24.7-61.8 32.3-21.9 9.1-45 16.9-68.4 21.2-24 4.4-47.9-7.9-58.4-29.9-10.4-21.9-4.9-48.2 13.4-64 10.2-9.1 19.1-19.6 27.5-30.4 3.6-4.7 3-11.3-1-15.6-39.4-42.2-63.1-96.7-63.1-156.2 0-134.2 120.9-243 270-243S540 108.8 540 243zM54 243c0-99.2 91.3-189 216-189 7.5 0 13.5-6 13.5-13.5S277.5 27 270 27c-137 0-243 99.4-243 216 0 7.5 6 13.5 13.5 13.5S54 250.5 54 243zm66.4 161.2c-.4-7.4-6.8-13.1-14.2-12.7s-13.1 6.8-12.7 14.2c.5 8.7-1.9 17.6-8 25.5-9.5 12.2-19.6 24.2-31.2 34.4-5.6 4.9-6.2 13.4-1.3 19s13.4 6.2 19.1 1.3c13-11.4 24.3-24.6 34.8-38.2 10.3-13.3 14.4-28.7 13.6-43.5z"/></svg> <span id="lightbox-comments">0</span> Kommentare</span>
            <span class="lightbox-icon"><a class="footer-link" id="lightbox-permalink" href="#" target="_blank" rel="noopener" title="Originaler Beitrag"><svg viewBox="0 0 540 540"><path opacity=".4" fill="currentColor" d="M27 326.9c0 7.5 6 13.5 13.5 13.5s13.5-6 13.5-13.5c0-42.2 16.8-82.7 46.6-112.5 5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0C46.6 230.2 27 277.5 27 326.9zM193.2 472.5c0 7.5 6 13.5 13.5 13.5 40.5 0 79.4-16.1 108-44.7 5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0c-23.6 23.6-55.6 36.8-88.9 36.8-7.5 0-13.5 6-13.5 13.5zm2.1-391c-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0c29.8-29.8 70.3-46.6 112.5-46.6 7.5 0 13.5-6 13.5-13.5S334.4 27 326.9 27c-49.4 0-96.7 19.6-131.6 54.5zm1.6 223.4c-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0L324 216c5.3 5.3 13.8 5.3 19.1 0s5.3-13.8 0-19.1c-10.5-10.5-27.6-10.5-38.2 0l-108 108zm225.3-9.3c-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0c28.6-28.6 44.7-67.5 44.7-108 0-7.5-6-13.5-13.5-13.5s-13.5 6-13.5 13.5c0 33.3-13.2 65.3-36.8 88.9z"/><path fill="currentColor" d="M252.6 138.8c-21.1 21.1-55.3 21.1-76.4 0s-21.1-55.3 0-76.4c42-42 98.2-62.4 157-62.4 114.2 0 206.7 92.6 206.7 206.7 0 54.8-21.8 107.4-60.6 146.2-21.1 21.1-55.3 21.1-76.4 0s-21.1-55.3 0-76.4c18.5-18.5 28.9-43.6 28.9-69.8 0-54.5-44.2-98.7-98.7-98.7-30.3 0-58.8 8.9-80.7 30.8zm-38.2-38.2c29.8-29.8 70.3-46.6 112.5-46.6 7.5 0 13.5-6 13.5-13.5S334.4 27 326.9 27c-49.4 0-96.7 19.6-131.6 54.5-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0zM486 206.7c0-7.5-6-13.5-13.5-13.5s-13.5 6-13.5 13.5c0 33.3-13.2 65.3-36.8 88.9-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0c28.6-28.6 44.7-67.5 44.7-108zM138.8 176.2c21.1 21.1 21.1 55.3 0 76.4-21.9 21.9-30.8 50.4-30.8 80.7 0 54.5 44.2 98.7 98.7 98.7 26.2 0 51.3-10.4 69.8-28.9 21.1-21.1 55.3-21.1 76.4 0s21.1 55.3 0 76.4c-38.8 38.8-91.4 60.6-146.2 60.6-114.2 0-206.7-92.6-206.7-206.7 0-58.8 20.4-115 62.4-157 21.1-21.1 55.3-21.1 76.4 0zm-38.2 38.2c5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0c-34.9 34.9-54.5 82.2-54.5 131.6 0 7.5 6 13.5 13.5 13.5s13.5-6 13.5-13.5c0-42.2 16.8-82.7 46.6-112.5zM314.7 441.3c5.3-5.3 5.3-13.8 0-19.1s-13.8-5.3-19.1 0c-23.6 23.6-55.6 36.8-88.9 36.8-7.5 0-13.5 6-13.5 13.5s6 13.5 13.5 13.5c40.5 0 79.4-16.1 108-44.7zM285.8 177.8c21.1-21.1 55.3-21.1 76.4 0s21.1 55.3 0 76.4l-108 108c-21.1 21.1-55.3 21.1-76.4 0s-21.1-55.3 0-76.4l108-108zm57.3 19.1c-10.5-10.5-27.6-10.5-38.2 0l-108 108c-5.3 5.3-5.3 13.8 0 19.1s13.8 5.3 19.1 0L324 216c5.3 5.3 13.8 5.3 19.1 0s5.3-13.8 0-19.1z"/></svg> Beitrag öffnen</a></span>
        </div>
    </div>
</div>

<div class="footer-nav">
    <a class="footer-link" href="https://codeberg.org/RonDevHub/PixelFetch" target="_blank" rel="noopener" title="Codeberg Repository">
        <svg viewBox="0 0 640 640"><path d="M64 320C64 368.1 77.5 415.3 103.1 456L316.5 180.1C318 178.1 321.9 178.1 323.4 180.1L412.5 295.3L348.7 295.3L350.1 300.4L416.5 300.4L435.3 324.7L356.9 324.7L359.1 332.7L441.5 332.7L458.1 354.1L365.1 354.1L368 364.4L466 364.4L480.8 383.5L373.3 383.5L376.8 396.1L490.4 396.1L503.4 412.9L381.4 412.9L385.3 426.8L514.1 426.8L526.1 442.3L389.6 442.3L393.5 456.2L536.8 456.2C562.5 415.2 576 368 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320zM397.9 471.5L401.8 485.4L515.4 485.4C519.1 481 522.9 476.2 526.4 471.5L397.9 471.5zM406.1 500.9L409.9 514.8L485.9 514.8C490.9 510.6 496.3 505.7 501.1 500.9L406.1 500.9zM414.3 530.3L418.2 544.1L443.9 544.1C451.5 539.7 458.5 535.3 466.1 530.3L414.3 530.3z"/></svg>
        Codeberg
    </a>
    <a class="footer-link" href="https://rondev.de/donate" target="_blank" rel="noopener" title="Spenden">
        <svg class="heart" viewBox="0 0 540 540"><path fill="currentColor" d="M308.2 488.2L494.4 302c29.2-29.2 45.6-68.9 45.6-110.2 0-86.1-69.8-155.8-155.8-155.8-41.3 0-81 16.4-110.2 45.6-2.2 2.2-5.8 2.2-8 0-29.2-29.2-68.9-45.6-110.2-45.6-86.1 0-155.8 69.8-155.8 155.8 0 41.3 16.4 81 45.6 110.2L231.8 488.2c21.1 21.1 55.3 21.1 76.4 0zM54 191.8c0 7.5-6 13.5-13.5 13.5S27 199.3 27 191.8c0-71.1 57.7-128.8 128.8-128.8 7.5 0 13.5 6 13.5 13.5S163.3 90 155.8 90C99.6 90 54 135.6 54 191.8zm258.2-72c-11.6 11.6-26.9 17.5-42.2 17.5-7.5 0-13.5-6-13.5-13.5s6-13.5 13.5-13.5c8.4 0 16.7-3.2 23.1-9.6 24.2-24.2 56.9-37.7 91.1-37.7 7.5 0 13.5 6 13.5 13.5S391.6 90 384.2 90c-27 0-52.9 10.7-72 29.8z"/></svg>
        Spenden
    </a>
</div>

<script src="assets/js/gallery.js"></script>
</body>
</html>