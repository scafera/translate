<?php

declare(strict_types=1);

namespace Scafera\Translate\Loader;

use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @internal Loads flat JSON translation files into Symfony's translation catalogue.
 */
final class JsonFileLoader implements LoaderInterface
{
    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $catalogue = new MessageCatalogue($locale);

        if (!is_file($resource)) {
            return $catalogue;
        }

        $content = file_get_contents($resource);
        $messages = json_decode($content, true);

        if (!\is_array($messages)) {
            throw new \RuntimeException(sprintf('Translation file "%s" contains invalid JSON.', $resource));
        }

        $catalogue->add($messages, $domain);

        return $catalogue;
    }
}
