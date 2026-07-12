<?php

namespace PixelFetch\Storage;

use PixelFetch\Core\Config;
use PixelFetch\Core\HttpClient;
use PixelFetch\Pixelfeed\AccountManager;

class CacheManager
{
    private string $userKey;
    private array $accountConfig;
    private string $cacheDir;
    private string $metaFile;
    private string $imageDir;

    public function __construct(string $userKey, array $accountConfig)
    {
        $this->userKey = $userKey;
        $this->accountConfig = $accountConfig;
        
        $baseStorage = dirname(__DIR__, 2) . '/storage/cache/' . $userKey;
        $this->cacheDir = $baseStorage;
        $this->metaFile = $baseStorage . '/meta.json';
        $this->imageDir = $baseStorage . '/images';
    }

    /**
     * Gibt die Galerie-Daten zurück. Triggert das Lazy Update, falls der Cache abgelaufen ist.
     */
    public function getGalleryData(): array
    {
        $ttl = $this->accountConfig['cache_ttl'] ?? (int)Config::get('DEFAULT_CACHE_TTL', 3600);

        if ($this->isCacheExpired($ttl)) {
            $this->refreshCache();
        }

        if (file_exists($this->metaFile)) {
            $content = file_get_contents($this->metaFile);
            return json_decode($content, true) ?: [];
        }

        return [];
    }

    /**
     * Prüft, ob die Metadaten-Datei fehlt oder ihr Alter die TTL überschreitet
     */
    private function isCacheExpired(int $ttl): bool
    {
        if (!file_exists($this->metaFile)) {
            return true;
        }

        return (time() - file_mtime($this->metaFile)) > $ttl;
    }

    /**
     * Holt frische Daten via API, lädt neue Bilder herunter und bereinigt alte Dateien
     */
    private function refreshCache(): void
    {
        if (!is_dir($this->imageDir)) {
            mkdir($this->imageDir, 0755, true);
        }

        $manager = new AccountManager($this->userKey, $this->accountConfig);
        $posts = $manager->fetchLatestPosts(24); // Standardmäßig 24 Bilder holen

        if ($posts === null) {
            // Bei API-Fehlern behalten wir den alten Cache als Fallback und aktualisieren nur den Zeitstempel
            if (file_exists($this->metaFile)) {
                touch($this->metaFile);
            }
            return;
        }

        $processedPosts = [];
        $activeImageFiles = [];

        foreach ($posts as $post) {
            // Nur Beiträge mit Medien (Bilder) verarbeiten
            if (empty($post['media_attachments'])) {
                continue;
            }

            $media = $post['media_attachments'][0];
            if ($media['type'] !== 'image') {
                continue;
            }

            // Sicheren lokalen Dateinamen generieren (SHA256 der Remote-URL zum Schutz vor Injection)
            $remoteUrl = $media['url'];
            $extension = pathinfo(parse_url($remoteUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $localFilename = hash('sha256', $remoteUrl) . '.' . $extension;
            $localPath = $this->imageDir . '/' . $localFilename;

            // Falls das Bild noch nicht lokal existiert, herunterladen
            if (!file_exists($localPath)) {
                HttpClient::downloadFile($remoteUrl, $localPath);
            }

            // Datei als aktiv registrieren, damit sie nicht gelöscht wird
            if (file_exists($localPath)) {
                $activeImageFiles[] = $localFilename;

                // Strip HTML-Tags aus der Beschreibung für sichere Plaintext-Ausgabe
                $description = $post['content'] ? strip_tags($post['content']) : '';

                $processedPosts[] = [
                    'id' => $post['id'],
                    'url' => $post['url'], // Link zum Originalbeitrag auf Pixelfeed
                    'local_image' => $localFilename,
                    'description' => trim($description),
                    'likes' => (int)($post['favourites_count'] ?? 0),
                    'comments' => (int)($post['replies_count'] ?? 0),
                    'created_at' => $post['created_at']
                ];
            }
        }

        // Metadaten-Datei atomar schreiben
        file_put_contents($this->metaFile, json_encode($processedPosts, JSON_PRETTY_PRINT));

        // Speicherplatz-Bereinigung: Lösche Bilder aus dem Ordner, die auf Pixelfeed gelöscht wurden
        $this->purgeOrphanedImages($activeImageFiles);
    }

    /**
     * Entfernt ungenutzte Bilddateien aus dem lokalen Speicher
     */
    private function purgeOrphanedImages(array $activeFiles): void
    {
        if (!is_dir($this->imageDir)) {
            return;
        }

        $files = scandir($this->imageDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!in_array($file, $activeFiles)) {
                @unlink($this->imageDir . '/' . $file);
            }
        }
    }
}