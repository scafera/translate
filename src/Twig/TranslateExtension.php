<?php

declare(strict_types=1);

namespace Scafera\Translate\Twig;

use Scafera\Translate\LocaleManager;
use Scafera\Translate\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TranslateExtension extends AbstractExtension
{
    public function __construct(
        private readonly Translator $translator,
        private readonly LocaleManager $localeManager,
    ) {}

    /** @return list<TwigFunction> */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('t', $this->translator->get(...)),
            new TwigFunction('locale_direction', $this->localeManager->getDirection(...)),
        ];
    }
}
