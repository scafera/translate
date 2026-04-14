<?php

declare(strict_types=1);

namespace Scafera\Translate\Tests;

use PHPUnit\Framework\TestCase;
use Scafera\Translate\LocaleManager;
use Scafera\Translate\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslatorTest extends TestCase
{
    private function createTranslator(array $translations = []): Translator
    {
        $inner = $this->createStub(TranslatorInterface::class);
        $inner->method('trans')->willReturnCallback(
            fn(string $key) => $translations[$key] ?? $key,
        );

        $localeManager = new LocaleManager('en', '/tmp/nonexistent');

        return new Translator($inner, $localeManager);
    }

    public function testGetReturnsTranslatedString(): void
    {
        $translator = $this->createTranslator(['HELLO' => 'Hello!']);

        $this->assertSame('Hello!', $translator->get('HELLO'));
    }

    public function testGetReplacesParameters(): void
    {
        $translator = $this->createTranslator(['WELCOME' => 'Welcome, {name}!']);

        $this->assertSame('Welcome, Alice!', $translator->get('WELCOME', ['name' => 'Alice']));
    }

    public function testMissingKeyReturnsRawKey(): void
    {
        $translator = $this->createTranslator();

        // No translation registered — returns the key string unchanged
        $this->assertSame('MISSING_KEY', $translator->get('MISSING_KEY'));
    }

    public function testHasReturnsTrueForExistingKey(): void
    {
        $translator = $this->createTranslator(['EXISTS' => 'Yes']);

        $this->assertTrue($translator->has('EXISTS'));
    }

    public function testHasReturnsFalseForMissingKey(): void
    {
        $translator = $this->createTranslator();

        $this->assertFalse($translator->has('NOPE'));
    }

    public function testGetDirectionDelegatesToLocaleManager(): void
    {
        $inner = $this->createStub(TranslatorInterface::class);
        $inner->method('trans')->willReturnArgument(0);
        $localeManager = new LocaleManager('ar', '/tmp/nonexistent');
        $translator = new Translator($inner, $localeManager);

        $this->assertSame('rtl', $translator->getDirection());
    }
}
