# Build Info Bundle documentation

## Configuration

The complete configuration is:

```yaml
build_info:
    path: '%kernel.project_dir%/var/build-info.json'
    twig:
        enabled: false
```

`path` identifies a file owned by the host application. The bundle never writes inside its own installation directory. `twig.enabled` defaults to `false`.

## Reading metadata

`SmartGroup\BuildInfoBundle\Service\BuildInfoReader` reads the configured file and returns a `SmartGroup\BuildInfoBundle\ValueObject\BuildInfo` value. Its raw fields are:

| Field | Requirement |
| --- | --- |
| `version` | Non-empty string. |
| `revision` | Non-empty string. |
| `built_at` | ISO 8601 timestamp including a timezone. |

The serialized form uses `built_at`; the PHP value object exposes the same value as `builtAt`.

The reader treats the metadata file as untrusted input. Missing files, unreadable files, invalid JSON, missing fields, invalid field types, and invalid timestamps all return this predictable fallback:

```php
[
    'version' => 'unknown',
    'revision' => 'unknown',
    'built_at' => 'unknown',
]
```

## Generation

Run the command while building an artifact, before deploying it:

```bash
php bin/console build-info:generate
```

The command writes the configured `path`. It creates the parent directory when necessary.

For each value, the command uses this precedence:

1. The command-line option: `--build-version`, `--revision`, or `--built-at`.
2. The matching environment variable: `BUILD_INFO_VERSION`, `BUILD_INFO_REVISION`, or `BUILD_INFO_BUILT_AT`.
3. Generator fallback.

Git is only a fallback source. When it is usable, the generator derives the version from an exact `v<version>` tag or the nearest matching tag with a `-dev` suffix. It reads the abbreviated `HEAD` revision. When Git, repository data, or tags are unavailable, it writes `0.0.0-dev` and `unknown`. The timestamp fallback is the generation time in UTC.

This design supports artifacts without `.git`, hosts without Git, shallow clones, and CI environments that provide their own build data. The bundle does not create Git hooks or modify Git configuration.

## Optional local Git hook

For local development, a host application can regenerate metadata after a successful merge. Create the following executable file in the host application's `.git/hooks/post-merge` directory:

```sh
#!/bin/sh

php bin/console build-info:generate
```

Git runs `post-merge` after `git merge` and after a `git pull` that performs a merge. It does not run after `git pull --rebase`; run the command manually in that workflow.

Git does not version files inside `.git/hooks`. To share the hook with a team, store a template such as `.githooks/post-merge` in the host application's repository and have each developer copy it to `.git/hooks/post-merge` and mark it executable. The bundle neither installs nor enables hooks automatically.

## CI/CD example

Supply immutable values from the build environment instead of relying on a repository checkout:

```bash
BUILD_INFO_VERSION=1.2.0 \
BUILD_INFO_REVISION=a1b2c3d \
BUILD_INFO_BUILT_AT=2026-10-20T12:34:56+00:00 \
php bin/console build-info:generate
```

Do not place secrets, remote URLs, credentials, or other deployment data in the metadata file.

## Twig

The Twig service is loaded only when both conditions are met:

1. `build_info.twig.enabled` is `true`;
2. the host application has `symfony/twig-bundle` installed.

It exposes a single function:

```twig
{{ build_info().version }}
```

The returned value also contains `revision` and `built_at`. The host application owns labels, markup, and date formatting.

## Installation modes

### Symfony Flex

After the community recipe is available, enable Symfony contrib recipes and install the package:

```bash
composer config extra.symfony.allow-contrib true
composer require smartgroup/build-info-bundle
```

Flex registers the bundle and creates `config/packages/build_info.yaml`:

```yaml
build_info:
    # Path to the generated build metadata JSON file.
    # path: '%kernel.project_dir%/var/build-info.json'
    twig:
        enabled: true
```

Twig integration remains inactive when `symfony/twig-bundle` is not installed.

### Manual installation

For applications without Symfony Flex, or that do not allow contrib recipes, install the package with Composer and add this to `config/bundles.php`:

```bash
composer require smartgroup/build-info-bundle
```

```php
SmartGroup\BuildInfoBundle\SmartGroupBuildInfoBundle::class => ['all' => true],
```

Then create `config/packages/build_info.yaml` with the configuration shown above.
