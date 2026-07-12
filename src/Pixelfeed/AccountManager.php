<?php

namespace PixelFetch\Pixelfeed;

use PixelFetch\Core\Config;
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
            return null;
        }

        $accountData = json_decode($accountDataJson, true);
        if (!isset($accountData['id'])) {
            return null;
        }

        $accountId = $accountData['id'];

        // Schritt 2: Die neuesten Beiträge (Statuses) des Accounts abrufen
        $statusesUrl = $instance . '/api/v1/accounts/' . $accountId . '/statuses?limit=' . $limit;
        $statusesJson = HttpClient::get($statusesUrl, $token);

        if (!$statusesJson) {
            return null;
        }

        return json_decode($statusesJson, true);
    }
}