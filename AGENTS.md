# AGENTS.md

## Project Overview

This project provides RaiffeisenBank Premium API support for MultiFlexi as a separate Debian-packaged addon. It produces two binary packages from one source:

- **multiflexi-raiffeisenbank** — credential prototype for `php-vitexsoftware-multiflexi-core` (`MultiFlexi\CredentialProtoType\RaiffeisenBank`)
- **multiflexi-raiffeisenbank-ui** — UI form helper for `multiflexi-web` (`MultiFlexi\Ui\CredentialType\RaiffeisenBank`)

## Directory Structure

- `src/MultiFlexi/CredentialProtoType/RaiffeisenBank.php` — core credential prototype class
- `src/MultiFlexi/Ui/CredentialType/RaiffeisenBank.php` — web UI credential form helper
- `src/images/RaiffeisenBank.svg` — logo asset
- `debian/` — Debian packaging
- `tests/` — PHPUnit tests

## Build & Test

```bash
make vendor    # install composer dependencies
make phpunit   # run tests
make cs        # fix coding standards
make deb       # build Debian packages
```

## Coding Standards

- PHP 8.1+ with strict types
- PSR-12 via ergebnis/php-cs-fixer-config
- Run `make cs` before committing

## Debian Packaging

The `debian/control` defines two binary packages with proper dependency chains:
- `multiflexi-raiffeisenbank` depends on `php-vitexsoftware-multiflexi-core` and `multiflexi-cli (>= 2.2.0)`
- `multiflexi-raiffeisenbank-ui` depends on `multiflexi-raiffeisenbank` and `multiflexi-web`

The `postinst` for `multiflexi-raiffeisenbank` runs `multiflexi-cli crprototype sync` to register the credential prototype.

## Key Classes

### MultiFlexi\CredentialProtoType\RaiffeisenBank
Extends `\MultiFlexi\CredentialProtoType` and implements `\MultiFlexi\credentialTypeInterface`.
Defines fields: ACCOUNT_NUMBER, ACCOUNT_CURRENCY, CERT_FILE, CERT_PASS, XIBMCLIENTID, RBAPI_RATE_LIMIT_JSON_FILE.

### MultiFlexi\Ui\CredentialType\RaiffeisenBank
Extends `\MultiFlexi\Ui\CredentialFormHelperPrototype`.
Displays certificate metadata (validity, issuer, serial number) and API rate limit information in the web UI.
