<?php

declare(strict_types=1);

namespace Scafera\Translate\DependencyInjection;

use Scafera\Kernel\Tool\FileFinder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal Enforces that Symfony Translation types do not leak into userland code.
 */
final class TranslateBoundaryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        $srcDir = $projectDir . '/src';

        if (!is_dir($srcDir)) {
            return;
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
                $violations[] = "  - {$relative}: uses {$message}";
            }
        }

        if (!empty($violations)) {
            throw new \LogicException(
                "Scafera\\Translate boundary violation:\n\n"
                . implode("\n", $violations)
                . "\n\nUse Scafera\\Translate\\Translator instead.",
            );
        }
    }
}
