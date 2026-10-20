# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Read and validate application build metadata from a configurable JSON file.
- Generate canonical `version`, `revision`, and `built_at` metadata through `build-info:generate`.
- Support command options and CI environment variables as Git-independent metadata sources.
- Provide predictable fallback metadata when a file is missing or invalid.
- Add optional `build_info()` Twig integration.
- Add PHPUnit, PHPStan, and PHP-CS-Fixer configuration.
