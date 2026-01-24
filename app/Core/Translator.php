<?php
/**
 * Translator - Multi-language support
 */

namespace App\Core;

class Translator
{
    private static ?Translator $instance = null;
    private string $locale = 'en';
    private string $fallbackLocale = 'en';
    private array $translations = [];
    private array $loadedLocales = [];
    private string $langPath;

    public const SUPPORTED_LOCALES = ['en', 'ru', 'uk'];
    public const LOCALE_NAMES = [
        'en' => 'English',
        'ru' => 'Русский',
        'uk' => 'Українська'
    ];

    private function __construct(string $langPath)
    {
        $this->langPath = $langPath;
    }

    public static function getInstance(?string $langPath = null): Translator
    {
        if (self::$instance === null) {
            $path = $langPath ?? dirname(__DIR__, 2) . '/lang';
            self::$instance = new self($path);
        }
        return self::$instance;
    }

    /**
     * Set current locale
     */
    public function setLocale(string $locale): void
    {
        if (in_array($locale, self::SUPPORTED_LOCALES)) {
            $this->locale = $locale;
            $this->loadTranslations($locale);
        }
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        return self::SUPPORTED_LOCALES;
    }

    /**
     * Get locale display names
     */
    public function getLocaleNames(): array
    {
        return self::LOCALE_NAMES;
    }

    /**
     * Translate a key
     *
     * @param string $key Translation key (e.g., 'messages.welcome')
     * @param array $params Parameters for replacement (e.g., ['name' => 'John'])
     * @param string|null $locale Override locale
     * @return string
     */
    public function get(string $key, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        // Ensure translations are loaded
        $this->loadTranslations($locale);

        // Get translation
        $translation = $this->findTranslation($key, $locale);

        // Fallback to default locale
        if ($translation === null && $locale !== $this->fallbackLocale) {
            $this->loadTranslations($this->fallbackLocale);
            $translation = $this->findTranslation($key, $this->fallbackLocale);
        }

        // Fallback to key itself
        if ($translation === null) {
            return $key;
        }

        // Replace parameters
        return $this->replaceParams($translation, $params);
    }

    /**
     * Alias for get()
     */
    public function trans(string $key, array $params = [], ?string $locale = null): string
    {
        return $this->get($key, $params, $locale);
    }

    /**
     * Load translations for a locale
     */
    private function loadTranslations(string $locale): void
    {
        if (isset($this->loadedLocales[$locale])) {
            return;
        }

        $this->translations[$locale] = [];
        $localePath = $this->langPath . '/' . $locale;

        if (!is_dir($localePath)) {
            $this->loadedLocales[$locale] = true;
            return;
        }

        // Load all PHP files in the locale directory
        $files = glob($localePath . '/*.php');
        foreach ($files as $file) {
            $group = basename($file, '.php');
            $translations = require $file;
            if (is_array($translations)) {
                $this->translations[$locale][$group] = $translations;
            }
        }

        $this->loadedLocales[$locale] = true;
    }

    /**
     * Find translation by key
     */
    private function findTranslation(string $key, string $locale): ?string
    {
        // Support dot notation: "messages.welcome" -> translations[messages][welcome]
        $parts = explode('.', $key);

        if (count($parts) < 2) {
            // No group specified, search in 'general' group
            array_unshift($parts, 'general');
        }

        $group = array_shift($parts);
        $translations = $this->translations[$locale][$group] ?? [];

        foreach ($parts as $part) {
            if (!is_array($translations) || !isset($translations[$part])) {
                return null;
            }
            $translations = $translations[$part];
        }

        return is_string($translations) ? $translations : null;
    }

    /**
     * Replace parameters in translation string
     */
    private function replaceParams(string $translation, array $params): string
    {
        foreach ($params as $key => $value) {
            $translation = str_replace(':' . $key, (string)$value, $translation);
            $translation = str_replace('{' . $key . '}', (string)$value, $translation);
        }
        return $translation;
    }

    /**
     * Check if translation exists
     */
    public function has(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslations($locale);
        return $this->findTranslation($key, $locale) !== null;
    }

    /**
     * Get translations for a group
     */
    public function getGroup(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale;
        $this->loadTranslations($locale);
        return $this->translations[$locale][$group] ?? [];
    }
}

/**
 * Global translation helper function
 */
function __($key, array $params = [], ?string $locale = null): string
{
    return Translator::getInstance()->get($key, $params, $locale);
}
