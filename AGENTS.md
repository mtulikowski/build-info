# Build Info Bundle — Agent Development Instructions

## 1. Project overview

Build Info Bundle is a reusable Symfony bundle that provides application build metadata.

The bundle supports:
- Reading build metadata from a JSON file.
- Generating metadata from Git during development or CI/CD.
- Supplying version, revision and build timestamp to the host application.
- Optional Twig integration.
- Use in multiple independent Symfony projects.

The bundle must remain independent of any specific application, business domain, UI framework, deployment provider or CI platform.

Prioritize a small public API, predictable behavior, portability and backward compatibility.

## 2. Supported platforms

The bundle supports:

- PHP 8.1 and newer.
- Symfony 6.4 LTS.
- Symfony 7.x.
- Symfony 8.x.

Use Composer constraints consistent with this support policy:

```json
{
  "php": ">=8.1",
  "symfony/config": "^6.4 || ^7.0 || ^8.0",
  "symfony/console": "^6.4 || ^7.0 || ^8.0",
  "symfony/dependency-injection": "^6.4 || ^7.0 || ^8.0",
  "symfony/http-kernel": "^6.4 || ^7.0 || ^8.0",
  "symfony/process": "^6.4 || ^7.0 || ^8.0"
}
```

Do not use Symfony APIs introduced after Symfony 6.4 unless an alternative compatible implementation is provided.

Do not use PHP features unavailable in PHP 8.1.

## 3. Architecture

Use Symfony bundle conventions.

Recommended structure:

```text
src/
├── Command/
├── DependencyInjection/
├── Exception/
├── Service/
├── Twig/
├── ValueObject/
└── VendorBuildInfoBundle.php
config/
├── services.php
tests/
├── Unit/
├── Integration/
└── Functional/
docs/
└── index.md
```

Keep responsibilities separate:

- `ValueObject`: immutable build metadata.
- `Service`: reading, validation and generation of metadata.
- `Command`: console interaction only.
- `DependencyInjection`: bundle configuration and service registration.
- `Twig`: optional presentation integration only.
- `Exception`: explicit domain exceptions when needed.

Do not place application-specific deployment, layout, CSS, JavaScript, database or business logic in the bundle.

Do not add Doctrine ORM, Twig, AssetMapper, Stimulus, Turbo or Bootstrap as mandatory dependencies.

## 4. Build metadata contract

The canonical metadata fields are:

```json
{
  "version": "1.2.0",
  "revision": "a1b2c3d",
  "built_at": "2026-10-20T12:34:56+00:00"
}
```

Rules:

- `version` is a non-empty string.
- `revision` is a non-empty string.
- `built_at` uses ISO 8601 with a timezone.
- Invalid, missing or unreadable metadata must result in a predictable fallback value or a documented exception, according to bundle configuration.
- Store raw, machine-readable values only. Formatting dates or labels belongs to the host application.
- The metadata file belongs to the host application, normally under `%kernel.project_dir%/var/`.
- The bundle directory must be treated as read-only at runtime.

Do not expose Git commands, filesystem paths or exception details in Twig output by default.

## 5. Git and CI/CD behavior

Git is an optional metadata source.

The bundle must support deployments where:

- The application artifact does not contain `.git`.
- Git is not installed on the production host.
- The build runs in a shallow clone.
- Tags are unavailable.
- Version data is supplied by CI/CD environment variables or command options.

Generation should normally run in CI/CD before the deployment artifact is created.

Do not require Git hooks.

Do not change `core.hooksPath`, repository configuration, user configuration or global Git configuration.

Optional developer scripts may be documented separately, but must not be installed or activated automatically.

## 6. Configuration and public API

The bundle configuration must be minimal, explicit and documented.

Example intended configuration:

```yaml
build_info:
    path: '%kernel.project_dir%/var/build-info.json'
    twig:
        enabled: true
```

Public services and configuration keys are part of the compatibility contract.

Before changing a public class, interface, command name, configuration option, metadata field or Twig API:

1. Determine whether it is public.
2. Preserve backward compatibility within the same major bundle version.
3. Mark obsolete behavior as deprecated before removal when practical.
4. Document migration steps.

Follow Semantic Versioning:

- Patch: fixes without public API changes.
- Minor: backward-compatible functionality.
- Major: incompatible public API or configuration changes.

## 7. Twig integration

Twig support must be optional.

- Load Twig-specific services only when `symfony/twig-bundle` is installed and Twig support is enabled.
- Do not require Twig for console-only or API-only Symfony applications.
- Prefer a Twig function such as `build_info()` over forcing a global variable.
- Do not provide a base layout or opinionated HTML markup.
- The host application chooses where and how metadata is displayed.
- Text such as “Version”, “Build” and date formatting belongs to the host application.

## 8. PHP coding standards

- Use `declare(strict_types=1)` in every PHP file.
- Use typed properties, parameters and return types.
- Prefer `final` classes unless extension is an explicit design goal.
- Use readonly properties where supported by PHP 8.1 and appropriate.
- Keep methods focused and small.
- Use clear English names in source code.
- Handle expected errors explicitly.
- Do not suppress errors with `@`.
- Do not silently convert invalid metadata into valid-looking data.
- Do not introduce abstractions without a concrete use case.
- Follow Symfony and PSR standards.
- Add PHPDoc only where native type declarations cannot express useful information.

## 9. Security

Build metadata is informational, but must be handled safely.

- Never include secrets, deployment credentials, environment variables or full Git remotes in build metadata.
- Treat the metadata file as untrusted input.
- Validate JSON structure and field types before use.
- Escape all Twig output through Twig defaults.
- Do not execute shell commands built from untrusted input.
- Invoke processes using argument arrays, never concatenated shell command strings.
- Do not expose internal command failures to end users.

## 10. Testing and quality

Use PHPUnit for:

- Unit tests of metadata validation and fallback behavior.
- Unit tests of version derivation and Git-independent generation.
- Integration tests of dependency injection configuration.
- Functional tests of the optional console command and Twig integration.

Test at least:

- Valid metadata file.
- Missing metadata file.
- Invalid JSON.
- Missing required fields.
- Invalid field types.
- Git tag present.
- No Git tag available.
- Git unavailable.
- CI-provided values.
- Twig disabled.
- Twig package unavailable.

The CI matrix must cover:

| PHP | Symfony |
|---|---|
| 8.1 | 6.4 |
| 8.2 | 7.4 |
| 8.4 | 8.x |

Run dependency tests with `--prefer-lowest` for the oldest supported dependency set.

Run PHPStan and PHP CS Fixer before merging changes.

Do not claim checks passed unless they were executed.

## 11. Dependencies

Keep runtime dependencies minimal.

- Require only Symfony components that the bundle directly uses.
- Keep Twig optional.
- Do not require a full Symfony skeleton.
- Do not add frontend packages.
- Do not add database dependencies.
- Do not embed third-party libraries in the repository.
- Evaluate the maintenance and compatibility cost before adding each dependency.

## 12. Documentation

Maintain:

- `README.md` with installation and quick-start instructions.
- `docs/index.md` with full configuration and CI/CD examples.
- A changelog describing user-visible changes.
- Upgrade notes for each major version.

Document both installation modes:

- Symfony Flex.
- Manual registration in `config/bundles.php`.

All examples must avoid assuming a specific CI provider or a particular host operating system.

## 13. Git and change management

Before editing:

1. Read this `AGENTS.md`.
2. Inspect the relevant implementation and tests.
3. Identify the smallest compatible change.
4. Check whether the change affects the public API.

During implementation:

1. Keep each change focused.
2. Preserve supported Symfony and PHP compatibility.
3. Update tests with behavior changes.
4. Update documentation when installation, configuration or public behavior changes.
5. Do not modify unrelated files.
6. Do not commit, push, tag, publish or alter repository settings without explicit instruction.

Do not discard uncommitted changes created by another developer.

## 14. Definition of done

A task is complete when:

- The requested behavior is implemented.
- The code works on every declared PHP and Symfony version.
- Public API compatibility has been evaluated.
- Relevant PHPUnit tests pass.
- PHPStan and PHP CS Fixer report no new issues.
- Documentation reflects the resulting behavior.
- The bundle remains independent of a particular host application.
- Verification performed and any limitations are reported clearly.
