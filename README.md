# SmartGroup Build Info Bundle

SmartGroup Build Info Bundle provides application build metadata for Symfony applications. It reads a JSON file created during development or CI/CD and can optionally expose the raw metadata to Twig.

## Requirements

- PHP 8.1 or newer;
- Symfony 6.4, 7.x, or 8.x.

## Installation

Install the package with Composer:

```bash
composer require smartgroup/build-info-bundle
```

In a Symfony Flex application, the `symfony-bundle` package type registers the bundle automatically. Add the configuration file yourself:

```yaml
# config/packages/build_info.yaml
build_info:
    path: '%kernel.project_dir%/var/build-info.json'
    twig:
        enabled: false
```

Without Symfony Flex, register the bundle in `config/bundles.php`:

```php
SmartGroup\BuildInfoBundle\SmartGroupBuildInfoBundle::class => ['all' => true],
```

Then add the configuration shown above.

## Metadata file

The metadata file contains raw, machine-readable values:

```json
{
    "version": "1.2.0",
    "revision": "a1b2c3d",
    "built_at": "2026-10-20T12:34:56+00:00"
}
```

`version` and `revision` must be non-empty strings. `built_at` must be an ISO 8601 timestamp with a timezone.

If the file is absent, unreadable, or invalid, the reader returns `unknown` for all three values. It does not expose file paths or parsing details to Twig.

## Generating metadata

Generate metadata before creating the deployment artifact:

```bash
php bin/console build-info:generate
```

The generator uses Git as an optional fallback. It works without Git or a `.git` directory: the fallback version is `0.0.0-dev` and the fallback revision is `unknown`.

CI/CD can provide all values explicitly:

```bash
php bin/console build-info:generate \
    --build-version=1.2.0 \
    --revision=a1b2c3d \
    --built-at=2026-10-20T12:34:56+00:00
```

When an option is omitted, the command reads its corresponding environment variable:

- `BUILD_INFO_VERSION`;
- `BUILD_INFO_REVISION`;
- `BUILD_INFO_BUILT_AT`.

Command options take precedence over environment variables. The bundle does not install or configure Git hooks.

## Twig

Twig is optional. Enable it only when the host application has `symfony/twig-bundle` installed:

```yaml
build_info:
    twig:
        enabled: true
```

Use the `build_info()` function where the host application chooses to render the metadata:

```twig
{{ build_info().version }}
{{ build_info().revision }}
{{ build_info().built_at }}
```

The bundle does not provide markup, labels, or date formatting.
