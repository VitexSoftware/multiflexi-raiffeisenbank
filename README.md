# multiflexi-raiffeisenbank

RaiffeisenBank Premium API support for [MultiFlexi](https://multiflexi.eu).

## Description

This package provides RaiffeisenBank credential management for MultiFlexi, split into two Debian packages:

| Package | Enhances | Provides |
|---------|----------|----------|
| `multiflexi-raiffeisenbank` | `php-vitexsoftware-multiflexi-core` | Credential prototype with account, certificate and API fields |
| `multiflexi-raiffeisenbank-ui` | `multiflexi-web` | Certificate info panel, validity progress bar, rate limit display |

## Credential Fields

- **ACCOUNT_NUMBER** — Raiffeisen Bank account number
- **ACCOUNT_CURRENCY** — Account currency (CZK, EUR, USD…)
- **CERT_FILE** — Path to PKCS12/PEM certificate file
- **CERT_PASS** — Certificate password
- **XIBMCLIENTID** — X-IBM-Client-Id for API access

## UI Features

The web interface component displays:
- Certificate holder and issuer details
- Certificate validity with a color-coded progress bar
- Serial number (HEX/DEC)
- API rate limit status per certificate
- Signature algorithm and version

## Installation

### From Debian packages

```bash
apt install multiflexi-raiffeisenbank multiflexi-raiffeisenbank-ui
```

### From source (development)

```bash
composer install
make phpunit
make cs
```

## Building Debian Packages

```bash
make deb
```

This produces `multiflexi-raiffeisenbank_*.deb` and `multiflexi-raiffeisenbank-ui_*.deb` in the parent directory.

## License

MIT — see [debian/copyright](debian/copyright) for details.

## MultiFlexi

[![MultiFlexi](https://github.com/VitexSoftware/MultiFlexi/blob/main/doc/multiflexi-app.svg)](https://www.multiflexi.eu/)
