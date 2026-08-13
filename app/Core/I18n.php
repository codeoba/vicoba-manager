<?php namespace Core;

class I18n {
    private static array $translations = [];
    private static string $currentLang = 'sw';

    public static function init(): void {
        self::$currentLang = $_SESSION['lang'] ?? 'sw';
        $langFile = ROOT_PATH . '/lang/' . self::$currentLang . '.php';
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        }
    }

    public static function t(string $key, string $default = ''): string {
        return self::$translations[$key] ?? ($default ?: $key);
    }

    public static function setLang(string $lang): void {
        if (in_array($lang, ['sw', 'en'], true)) {
            $_SESSION['lang'] = $lang;
            self::$currentLang = $lang;
        }
    }

    public static function getLang(): string {
        return self::$currentLang;
    }
}
