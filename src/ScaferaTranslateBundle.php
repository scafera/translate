<?php

declare(strict_types=1);

namespace Scafera\Translate;

use Scafera\Kernel\InstalledPackages;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ScaferaTranslateBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $projectDir = $builder->getParameter('kernel.project_dir');
        $translationsDir = $this->resolveTranslationsDir($projectDir);

        if ($translationsDir === null) {
            return;
        }

        $defaultLocale = 'en';
        if ($builder->hasParameter('scafera.translate.default_locale')) {
            $defaultLocale = $builder->getParameter('scafera.translate.default_locale');
        }

        $builder->prependExtensionConfig('framework', [
            'default_locale' => $defaultLocale,
            'translator' => [
                'default_path' => $translationsDir,
                'fallbacks' => [$defaultLocale],
            ],
        ]);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $projectDir = $builder->getParameter('kernel.project_dir');
        $translationsDir = $this->resolveTranslationsDir($projectDir);

        if ($translationsDir === null) {
            return;
        }

        $defaultLocale = 'en';
        if ($builder->hasParameter('scafera.translate.default_locale')) {
            $defaultLocale = $builder->getParameter('scafera.translate.default_locale');
        }

        $rtlLocales = ['ar', 'fa', 'ur'];
        if ($builder->hasParameter('scafera.translate.rtl_locales')) {
            $rtlLocales = $builder->getParameter('scafera.translate.rtl_locales');
        }

        $container->services()
            // JSON loader for Symfony's translation component
            ->set(Loader\JsonFileLoader::class)
                ->tag('translation.loader', ['alias' => 'json'])

            // Locale management
            ->set(LocaleManager::class)
                ->args([$defaultLocale, $translationsDir, $rtlLocales])
                ->public()

            // Translator (wraps Symfony translator)
            ->set(Translator::class)
                ->args([
                    service('translator'),
                    service(LocaleManager::class),
                ])
                ->public()

            // Validators
            ->set(Validator\TranslateBoundaryValidator::class)
                ->tag('scafera.validator');
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DependencyInjection\TranslationResourcePass());
        $container->addCompilerPass(new DependencyInjection\TranslateBoundaryPass());
    }

    private function resolveTranslationsDir(string $projectDir): ?string
    {
        $architecture = InstalledPackages::resolveArchitecture($projectDir);
        $relativeDir = $architecture?->getTranslationsDir();

        if ($relativeDir === null) {
            return null;
        }

        return $projectDir . '/' . $relativeDir;
    }
}
