<?php

declare(strict_types=1);

namespace Scafera\Translate\Twig;

use Scafera\Kernel\InstalledPackages;
use Scafera\Translate\LocaleManager;
use Scafera\Translate\Translator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @internal Companion bundle — auto-registered when scafera/frontend is installed.
 */
final class ScaferaTranslateTwigBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $projectDir = $builder->getParameter('kernel.project_dir');
        $architecture = InstalledPackages::resolveArchitecture($projectDir);

        if ($architecture?->getTranslationsDir() === null) {
            return;
        }

        $container->services()
            ->set(TranslateExtension::class)
                ->args([
                    service(Translator::class),
                    service(LocaleManager::class),
                ])
                ->tag('twig.extension');
    }
}
