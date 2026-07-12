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

    public function getGalleryData(): array
    {
        $ttl = isset($this->accountConfig['cache_ttl']) ? (int)$this->accountConfig['cache_ttl'] : (int)Config::get('DEFAULT_CACHE_TTL', 3600);

        if ($this->isCacheExpired($ttl)) {
            $this->refreshCache();
        }

        if (file_exists($this->metaFile)) {
            $content = file_get_contents($this->metaFile);
            return json_decode($content, true) ?: [];
        }

        return [];
    }

    private function isCacheExpired(int $ttl): bool
    {
        if (!file_exists($this->metaFile)) {
            return true;
        }

        return (time() - filemtime($this->metaFile)) > $ttl;
    }

    private function refreshCache(): void
    {
        if (!is_dir($this->imageDir)) {
            if (!@mkdir($this->imageDir, 0755, true) && !is_dir($this->imageDir)) {
                error_log("PixelFetch Error: Verzeichnis konnte nicht erstellt werden: " . $this->imageDir);
                return;
            }
        }

        // Limit ermitteln: Account-Ebene -> ENV-Variable -> Standardwert
        $limitSetting = isset($this->accountConfig['max_pictures']) ? $this->accountConfig['max_pictures'] : Config::get('MAX_PICTURES', 24);
        $fetchLimit = ($limitSetting === 'all') ? 100 : (int)$limitSetting;

        $manager = new AccountManager($this->userKey, $this->accountConfig);
        $posts = $manager->fetchLatestPosts($fetchLimit);

        if ($posts === null || !is_array($posts)) {
            if (file_exists($this->metaFile)) {
                @touch($this->metaFile);
            }
            return;
        }

        $processedPosts = [];
        $activeImageFiles = [];

        // Avatar-URL aus dem ersten Post-Datensatz extrahieren (falls vorhanden)
        $avatarUrl = '';
        $profileUrl = '';
        $displayName = $this->accountConfig['username'];

        if (!empty($posts) && isset($posts[0]['account'])) {
            $avatarUrl = isset($posts[0]['account']['avatar']) ? $posts[0]['account']['avatar'] : '';
            $profileUrl = isset($posts[0]['account']['url']) ? $posts[0]['account']['url'] : '';
            $displayName = isset($posts[0]['account']['display_name']) && !empty($posts[0]['account']['display_name']) ? $posts[0]['account']['display_name'] : $posts[0]['account']['username'];
        }

        foreach ($posts as $post) {
            if (!is_array($post) || empty($post['media_attachments']) || !is_array($post['media_attachments'])) {
                continue;
            }

            $media = $post['media_attachments'][0];
            if (!isset($media['type']) || $media['type'] !== 'image' || empty($media['url'])) {
                continue;
            }

            $remoteUrl = $media['url'];
            $extension = pathinfo(parse_url($remoteUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $localFilename = hash('sha256', $remoteUrl) . '.' . $extension;
            $localPath = $this->imageDir . '/' . $localFilename;

            if (!file_exists($localPath)) {
                if (!HttpClient::downloadFile($remoteUrl, $localPath)) {
                    error_log("PixelFetch Error: Bild-Download fehlgeschlagen von: " . $remoteUrl);
                    continue;
                }
            }

            if (file_exists($localPath)) {
                $activeImageFiles[] = $localFilename;
                $description = isset($post['content']) ? strip_tags($post['content']) : '';

                $processedPosts[] = [
                    'id' => isset($post['id']) ? $post['id'] : uniqid(),
                    'url' => isset($post['url']) ? $post['url'] : '#',
                    'local_image' => $localFilename,
                    'description' => trim($description),
                    'likes' => (int)(isset($post['favourites_count']) ? $post['favourites_count'] : 0),
                    'comments' => (int)(isset($post['replies_count']) ? $post['replies_count'] : 0),
                    'created_at' => isset($post['created_at']) ? $post['created_at'] : date('c')
                ];
            }
        }

        // Profildaten oben in das JSON-Array injizieren, um sie im Frontend verfügbar zu machen
        $outputData = [
            'account_meta' => [
                'avatar_url' => $avatarUrl,
                'profile_url' => $profileUrl,
                'display_name' => $displayName
            ],
            'posts' => $processedPosts
        ];

        if (!@file_put_contents($this->metaFile, json_encode($outputData, JSON_PRETTY_PRINT))) {
            error_log("PixelFetch Error: Schreiben der Meta-Datei fehlgeschlagen: " . $this->metaFile);
        }

        $this->purgeOrphanedImages($activeImageFiles);
    }

    private function purgeOrphanedImages(array $activeFiles): void
    {
        if (!is_dir($this->imageDir)) {
            return;
        }

        $files = scandir($this->imageDir);
        if ($files === false) return;

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
