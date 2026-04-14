<?php

declare(strict_types=1);

namespace Scafera\Translate\Tests;

use PHPUnit\Framework\TestCase;
use Scafera\Translate\LocaleManager;

final class LocaleManagerTest extends TestCase
{
    public function testDefaultLocale(): void
    {
        $manager = new LocaleManager('en', '/tmp/nonexistent');

        $this->assertSame('en', $manager->getLocale());
    }

    public function testSetLocale(): void
    {
        $manager = new LocaleManager('en', '/tmp/nonexistent');
        $manager->setLocale('fr');

        $this->assertSame('fr', $manager->getLocale());
    }

    public function testLtrDirection(): void
    {
        $manager = new LocaleManager('en', '/tmp/nonexistent');

        $this->assertSame('ltr', $manager->getDirection());
        $this->assertSame('ltr', $manager->getDirection('fr'));
    }

    public function testRtlDirection(): void
    {
        $manager = new LocaleManager('ar', '/tmp/nonexistent');

        $this->assertSame('rtl', $manager->getDirection());
        $this->assertSame('rtl', $manager->getDirection('fa'));
        $this->assertSame('rtl', $manager->getDirection('ur'));
    }

    public function testAvailableLocalesFromDirectory(): void
    {
        $tmpDir = sys_get_temp_dir() . '/scafera_translate_test_' . uniqid();
        mkdir($tmpDir);
        file_put_contents($tmpDir . '/en.json', '{}');
        file_put_contents($tmpDir . '/ar.json', '{}');
        file_put_contents($tmpDir . '/fr.json', '{}');

        $manager = new LocaleManager('en', $tmpDir);
        $locales = $manager->getAvailableLocales();

        $this->assertSame(['ar', 'en', 'fr'], $locales);

        // Cleanup
        array_map('unlink', glob($tmpDir . '/*.json'));
        rmdir($tmpDir);
    }
}
