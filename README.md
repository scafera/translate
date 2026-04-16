# scafera/translate

Translation for the Scafera framework. UI string translation with locale management, RTL support, and Twig integration.

Internally adopts `symfony/translation`. Userland code never imports Symfony Translation types — boundary enforcement blocks it at compile time.

> **Provides:** UI string translation for Scafera — `Translator` for lookup, `LocaleManager` for locale switching (with RTL detection). Flat JSON translation files with `{name}` parameter syntax (not Symfony's `%name%`). When `scafera/frontend` is installed, a companion bundle registers `{{ t() }}` and `{{ locale_direction() }}` Twig functions automatically.
>
> **Depends on:** A Scafera host project whose architecture package defines a translations directory via `getTranslationsDir()` (e.g. `support/translations/` in `scafera/layered`). Twig integration activates only when `scafera/frontend` is installed.
>
> **Extension points:** None of its own — `Translator` and `LocaleManager` are consumed as services. New locales are picked up by adding `{locale}.json` files to the translations directory. Locale behavior is tuned via two parameters in `config/config.yaml`: `scafera.translate.default_locale` and `scafera.translate.rtl_locales`.
>
> **Not responsible for:** Non-JSON translation formats (no YAML, no XLIFF — deliberate) · multi-level fallback chains (single fallback to default locale only) · choosing the translations directory (architecture package owns `getTranslationsDir()`) · direct use of `Symfony\Component\Translation` types in userland (blocked by `TranslateBoundaryPass` and `TranslateBoundaryValidator`).

This is a **capability package**. It adds optional translation to a Scafera project. It does not define folder structure or architectural rules — those belong to architecture packages.

## What it provides

- `Translator` — get translated strings with `{name}` parameter replacement
- `LocaleManager` — locale switching, available locales, RTL detection
- `JsonFileLoader` — loads flat JSON translation files (internal)
- Twig integration — `{{ t() }}` and `{{ locale_direction() }}` (when `scafera/frontend` is installed)
- Companion bundle auto-discovery via `extra.scafera-bundles` in composer.json

## Design decisions

- **`{name}` parameter syntax, not `%name%`** — translation files use `{name}` for parameter placeholders. This is engine-independent (Symfony uses `%name%`) and human-readable. Replacement is done after Symfony's `trans()` call via simple `strtr()`.
- **Translations directory is architecture-owned** — defined by `getTranslationsDir()` on the architecture package, not hardcoded. For `scafera/layered`, this is `support/translations/`.
- **JSON only** — no YAML, no XLIFF. Flat key-value format. Keeps translation files simple and parseable by any tooling.
- **Companion bundle for Twig** — the `{{ t() }}` function is registered via `extra.scafera-bundles` companion discovery (ADR-056), not by requiring `scafera/frontend` as a dependency. The translate package works without Twig.
- **Missing keys return the raw key** — `get('MISSING')` returns `'MISSING'`, not an error. Fallback to default locale is automatic for keys that exist in another locale.

## Installation

```bash
composer require scafera/translate
```

## Requirements

- PHP >= 8.4
- scafera/kernel

## Translation files

JSON files in the architecture-defined translations directory (e.g., `support/translations/{locale}.json`):

```json
{
    "WELCOME": "Welcome to the app!",
    "GREETING": "Hello, {name}!"
}
```

Flat key-value format. No nesting, no YAML, no XLIFF. Parameters use `{name}` syntax — not Symfony's `%name%` format. This keeps translation files engine-independent and human-readable.

## Usage

```php
use Scafera\Translate\Translator;

$translator->get('WELCOME');                        // 'Welcome to the app!'
$translator->get('GREETING', ['name' => 'Alice']);  // 'Hello, Alice!'
$translator->has('WELCOME');                        // true
$translator->getLocale();                           // 'en'
$translator->getDirection();                        // 'ltr'
```

Missing keys return the raw key string. Fallback to the default locale is automatic.

## Locale management

```php
use Scafera\Translate\LocaleManager;

$localeManager->setLocale('ar');
$localeManager->getLocale();              // 'ar'
$localeManager->getDirection();           // 'rtl'
$localeManager->getDirection('en');       // 'ltr'
$localeManager->getAvailableLocales();    // ['ar', 'en']
```

Available locales are discovered from `*.json` files in the translations directory.

## Twig integration

When `scafera/frontend` is installed, a companion bundle registers automatically:

```twig
<html dir="{{ locale_direction() }}">
    <h1>{{ t('WELCOME') }}</h1>
    <p>{{ t('GREETING', {name: user.name}) }}</p>
</html>
```

## Configuration

All configuration is optional. The package works out of the box with sensible defaults.

| Parameter | Default | Description |
|-----------|---------|-------------|
| `scafera.translate.default_locale` | `en` | Default locale used when no locale is set. Also used as the fallback locale when a key is missing in the current locale. |
| `scafera.translate.rtl_locales` | `[ar, fa, ur]` | Locale codes that use right-to-left text direction. `LocaleManager::getDirection()` returns `'rtl'` for these. |

**Not configurable via parameters:**
- Translation files directory — defined by the architecture package via `getTranslationsDir()`. For `scafera/layered`, this is `support/translations/`.
- File format — JSON only. No YAML, no XLIFF.
- Fallback chain — always falls back to the default locale. No multi-level fallback.

To override defaults, add to `config/config.yaml`:

```yaml
# config/config.yaml
parameters:
    scafera.translate.default_locale: fr
    scafera.translate.rtl_locales: [ar, fa, ur]
```

If you are using `en` as your default locale and the standard RTL set, no configuration is needed.

## Boundary enforcement

| Blocked | Use instead |
|---------|-------------|
| `Symfony\Component\Translation\*` | `Scafera\Translate\Translator` |

Enforced via compiler pass (build time) and validator (`scafera validate`). Detects `use`, `new`, and `extends` patterns.

## License

MIT
