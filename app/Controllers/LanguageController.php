<?php
/**
 * Language Controller - Handle language switching
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Translator;

class LanguageController extends Controller
{
    /**
     * Switch language
     */
    public function switch(string $locale): void
    {
        // Validate locale
        if (!in_array($locale, Translator::SUPPORTED_LOCALES)) {
            $locale = 'en';
        }

        // Set locale in session
        $this->app->setLocale($locale);

        // Get redirect URL from referer or default to home
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';

        // Ensure we redirect to same domain
        $parsedReferer = parse_url($referer);
        $redirect = $parsedReferer['path'] ?? '/';
        if (isset($parsedReferer['query'])) {
            $redirect .= '?' . $parsedReferer['query'];
        }

        $this->redirect($redirect);
    }
}
