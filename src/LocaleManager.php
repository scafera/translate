<?php

declare(strict_types=1);

namespace Scafera\Translate;

final class LocaleManager
{
    private string $currentLocale;

    /** @var list<string>|null */
    private ?array $cachedLocales = null;

    /**
     * @param list<string> $rtlLocales
     */
    public function __construct(
        private readonly string $defaultLocale,
        private readonly string $translationsDir,
        private readonly array $rtlLocales = ['ar', 'fa', 'ur'],
    ) {
        $this->currentLocale = $defaultLocale;
    }

    public function setLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /** @return list<string> */
    public function getAvailableLocales(): array
    {
        if ($this->cachedLocales !== null) {
            return $this->cachedLocales;
        }

        $locales = [];

        if (is_dir($this->translationsDir)) {
            foreach (glob($this->translationsDir . '/*.json') as $file) {
                $locales[] = basename($file, '.json');
            }
        }

        sort($locales);
        $this->cachedLocales = $locales;

        return $locales;
    }

    public function getDirection(?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;

        // Extract base language from locale (e.g., 'ar_SA' -> 'ar')
        $base = explode('_', str_replace('-', '_', $locale))[0];

        return \in_array($base, $this->rtlLocales, true) ? 'rtl' : 'ltr';
    }
}
