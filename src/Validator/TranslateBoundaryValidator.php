<?php

declare(strict_types=1);

namespace Scafera\Translate\Validator;

use Scafera\Kernel\Contract\ValidatorInterface;
use Scafera\Kernel\Tool\FileFinder;

final class TranslateBoundaryValidator implements ValidatorInterface
{
    public function getName(): string
    {
        return 'Translation Boundary';
    }

    public function validate(string $projectDir): array
    {
        $srcDir = $projectDir . '/src';

        if (!is_dir($srcDir)) {
            return [];
        }

        $violations = [];

        $pattern = 'Symfony\\\\Component\\\\Translation\\\\';
        $message = 'Symfony Translation — use Scafera\\Translate\\Translator instead';

        foreach (FileFinder::findPhpFiles($srcDir) as $file) {
            $relative = str_replace($projectDir . '/', '', $file);
            $contents = file_get_contents($file);

            if (preg_match('/^use\s+' . $pattern . '/m', $contents)
                || preg_match('/new\s+\\\\?' . $pattern . '/m', $contents)
                || preg_match('/extends\s+\\\\?' . $pattern . '/m', $contents)
            ) {
                $violations[] = "{$relative}: uses {$message}";
            }
        }

        return $violations;
    }
}
