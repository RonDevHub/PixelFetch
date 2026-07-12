<?php

namespace PixelFetch\Pixelfeed;

use PixelFetch\Core\HttpClient;

class AccountManager
{
    private array $accountConfig;
    private string $userKey;

    public function __construct(string $userKey, array $accountConfig)
    {
        $this->userKey = $userKey;
        $this->accountConfig = $accountConfig;
    }

    /**
     * Holt die Rohdaten der neuesten Beiträge direkt von der Pixelfeed-Instanz
     */
    public function fetchLatestPosts(int $limit = 20): ?array
    {
        $instance = rtrim($this->accountConfig['instance'], '/');
        $token = $this->accountConfig['token'];
        $username = $this->accountConfig['username'];

        // Schritt 1: Account-ID über das Webfinger/Lookup-System der API ermitteln
        $lookupUrl = $instance . '/api/v1/accounts/lookup?acct=' . urlencode($username);
        $accountDataJson = HttpClient::get($lookupUrl, $token);

        if (!$accountDataJson) {
            error_log("PixelFetch Error: Lookup fehlgeschlagen für URL: " . $lookupUrl);
            return null;
        }

        $accountData = json_decode($accountDataJson, true);
        if (!is_array($accountData) || !isset($accountData['id'])) {
            error_log("PixelFetch Error: Ungültige Lookup-Antwort für User " . $username);
            return null;
        }

        $accountId = $accountData['id'];

        // Schritt 2: Die neuesten Beiträge (Statuses) des Accounts abrufen
        $statusesUrl = $instance . '/api/v1/accounts/' . $accountId . '/statuses?limit=' . $limit;
        $statusesJson = HttpClient::get($statusesUrl, $token);

        if (!$statusesJson) {
            error_log("PixelFetch Error: Status-Abruf fehlgeschlagen für URL: " . $statusesUrl);
            return null;
        }

        $statuses = json_decode($statusesJson, true);
        if (!is_array($statuses)) {
            error_log("PixelFetch Error: Statuses JSON konnte nicht dekodiert werden.");
            return null;
        }

        return $statuses;
    }
}
