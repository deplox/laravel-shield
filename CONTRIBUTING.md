# Contributing

## Requirements

- PHP 8.4+
- Composer

## Setup

```bash
git clone https://github.com/deplox/laravel-shield.git
cd laravel-shield
composer install
```

## Running Tests

```bash
composer test
```

The suite uses [Pest](https://pestphp.com/) with an in-memory SQLite database via [Orchestra Testbench](https://github.com/orchestral/testbench). No external services needed.

## Pull Requests

- One logical change per PR. Bug fixes, features, and refactors should be separate.
- All new behaviour must be covered by tests.
- Keep commit messages in the imperative: `feat: add X`, `fix: correct Y`, `refactor: simplify Z`.
- Run the test suite before opening a PR — CI will also run on PHP 8.4 and 8.5.

## Reporting Issues

Use [GitHub Issues](https://github.com/deplox/laravel-shield/issues). For security vulnerabilities, see [SECURITY.md](SECURITY.md).
