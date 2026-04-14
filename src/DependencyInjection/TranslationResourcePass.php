<?php

declare(strict_types=1);

namespace Scafera\Translate\DependencyInjection;

use Scafera\Kernel\InstalledPackages;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal Registers JSON translation files as Symfony translator resources.
 *
 * Scafera uses {locale}.json naming (e.g., en.json, ar.json).
 * Symfony expects {domain}.{locale}.{format} naming for auto-discovery.
 * This pass bridges the gap by manually registering each JSON file.
 *
 * The translations directory is resolved from the installed architecture package.
 */
final class TranslationResourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('translator.default')) {
            return;
        }

        $projectDir = $container->getParameter('kernel.project_dir');
        $architecture = InstalledPackages::resolveArchitecture($projectDir);
        $relativeDir = $architecture?->getTranslationsDir();

        if ($relativeDir === null) {
            return;
        }

        $translationsDir = $projectDir . '/' . $relativeDir;

        if (!is_dir($translationsDir)) {
            return;
        }

        $translator = $container->findDefinition('translator.default');

        foreach (glob($translationsDir . '/*.json') as $file) {
            $locale = basename($file, '.json');
            $translator->addMethodCall('addResource', ['json', $file, $locale, 'messages']);
        }
    }
}
