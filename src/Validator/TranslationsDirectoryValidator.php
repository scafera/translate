<?php

declare(strict_types=1);

namespace Scafera\Translate\Validator;

use Scafera\Kernel\Contract\ValidatorInterface;
use Scafera\Kernel\InstalledPackages;

final class TranslationsDirectoryValidator implements ValidatorInterface
{
    public function getId(): string
    {
        return 'translate.translations-directory';
    }

    public function getName(): string
    {
        return 'Translations directory';
    }

    public function validate(string $projectDir): array
    {
        $architecture = InstalledPackages::resolveArchitecture($projectDir);
        $translationsDir = $architecture?->getTranslationsDir();

        if ($translationsDir === null) {
            return [];
        }

        if (is_dir($projectDir . '/' . $translationsDir)) {
            return [];
        }

        return ['scafera/translate is installed but ' . $translationsDir . '/ directory does not exist. Create it or remove the package.'];
    }
}
