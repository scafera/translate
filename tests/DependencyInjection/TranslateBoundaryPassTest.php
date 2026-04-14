<?php

declare(strict_types=1);

namespace Scafera\Translate\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Scafera\Translate\DependencyInjection\TranslateBoundaryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class TranslateBoundaryPassTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/scafera_translate_boundary_' . uniqid();
        mkdir($this->tmpDir . '/src', 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testBlocksSymfonyTranslationImport(): void
    {
        file_put_contents($this->tmpDir . '/src/Bad.php', <<<'PHP'
        <?php
        use Symfony\Component\Translation\TranslatorInterface;
        class Bad {}
        PHP);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Symfony Translation/');

        $this->runPass();
    }

    public function testBlocksNewFqcnInstantiation(): void
    {
        file_put_contents($this->tmpDir . '/src/Bad.php', <<<'PHP'
        <?php
        class Bad {
            public function run() {
                $t = new \Symfony\Component\Translation\Translator('en');
            }
        }
        PHP);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessageMatches('/Symfony Translation/');

        $this->runPass();
    }

    public function testAllowsScaferaTranslateImports(): void
    {
        file_put_contents($this->tmpDir . '/src/Good.php', <<<'PHP'
        <?php
        use Scafera\Translate\Translator;
        use Scafera\Translate\LocaleManager;
        class Good {}
        PHP);

        $this->runPass();
        $this->assertTrue(true);
    }

    public function testNoSrcDirectoryDoesNotThrow(): void
    {
        // Remove src/ — simulates project with no userland code
        rmdir($this->tmpDir . '/src');

        $this->runPass();
        $this->assertTrue(true);
    }

    private function runPass(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', $this->tmpDir);

        $pass = new TranslateBoundaryPass();
        $pass->process($container);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        ) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
