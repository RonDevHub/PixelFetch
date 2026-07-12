<?php

namespace PixelFetch\Core;

class Config
{
    private static array $envCache = [];
    private static ?array $accountsCache = null;

    /**
     * Lädt die Umgebungsvariablen (.env oder System-Env)
     */
    public static function get(string $key, $default = null)
    {
        if (empty(self::$envCache)) {
            self::loadEnv();
        }

        if (isset(self::$envCache[$key])) {
            return self::$envCache[$key];
        }

        $systemEnv = getenv($key);
        if ($systemEnv !== false) {
            return $systemEnv;
        }

        return $default;
    }

    /**
     * Lädt die accounts.json und validiert die Struktur
     */
    public static function getAccount(string $user): ?array
    {
        if (self::$accountsCache === null) {
            $path = dirname(__DIR__, 2) . '/config/accounts.json';
            if (!file_exists($path)) {
                return null;
            }
            $content = file_get_contents($path);
            self::$accountsCache = json_decode($content, true) ?: [];
        }

        return self::$accountsCache[$user] ?? null;
    }

    /**
     * Interner Parser für die .env-Datei (schlank und schnell)
     */
    private static function loadEnv(): void
    {
        $path = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Bereinige Anführungszeichen
            $value = trim($value, '"\'');

            self::$envCache[$name] = $value;
        }
    }
}