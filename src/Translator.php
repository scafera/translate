<?php

declare(strict_types=1);

namespace Scafera\Translate;

use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslator;

final class Translator
{
    public function __construct(
        private readonly SymfonyTranslator $inner,
        private readonly LocaleManager $localeManager,
    ) {}

    /**
     * @param array<string, string> $params
     */
    public function get(string $key, array $params = []): string
    {
        $translated = $this->inner->trans($key, [], null, $this->localeManager->getLocale());

        // Replace {name} style parameters
        if ($params !== []) {
            $replacements = [];
            foreach ($params as $name => $value) {
                $replacements['{' . $name . '}'] = $value;
            }
            $translated = strtr($translated, $replacements);
        }

        return $translated;
    }

    public function has(string $key): bool
    {
        $translated = $this->inner->trans($key, [], null, $this->localeManager->getLocale());

        return $translated !== $key;
    }

    public function getLocale(): string
    {
        return $this->localeManager->getLocale();
    }

    public function getDirection(): string
    {
        return $this->localeManager->getDirection();
    }
}
