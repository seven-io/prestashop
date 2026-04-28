<p align="center">
  <img src="https://www.seven.io/wp-content/uploads/Logo.svg" width="250" alt="seven logo" />
</p>

<h1 align="center">seven SMS for PrestaShop</h1>

<p align="center">
  Official module for <a href="https://www.prestashop.com/">PrestaShop</a> 1.6, 1.7 and 8.x - send SMS to your shop customers via the seven gateway.
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-teal.svg" alt="MIT License" /></a>
  <img src="https://img.shields.io/badge/PrestaShop-1.6%20|%201.7%20|%208.x-blue" alt="PrestaShop 1.6 | 1.7 | 8.x" />
  <img src="https://img.shields.io/badge/PHP-7.2%2B-purple" alt="PHP 7.2+" />
  <a href="https://packagist.org/packages/seven.io/prestashop"><img src="https://img.shields.io/packagist/v/seven.io/prestashop" alt="Packagist" /></a>
</p>

---

## Features

- **Single & Bulk SMS** - Send messages to one or many customers
- **Custom Sender ID** - Up to 11 alphanumeric or 16 numeric characters
- **Configurable from the Admin** - Manage everything from **Modules > Module Manager > seven**

## Prerequisites

- PrestaShop 1.6, 1.7 or 8.x
- PHP 7.2+
- A [seven account](https://www.seven.io/) with API key ([How to get your API key](https://help.seven.io/en/developer/where-do-i-find-my-api-key))

## Installation

### Composer (recommended)

```bash
cd /path/to/prestashop
composer require seven.io/prestashop
```

Then activate the module: **Modules > Module Manager > seven > Install**.

### Manual

1. Download the [latest release](https://github.com/seven-io/prestashop/releases/latest) ZIP.
2. Extract it into the modules folder:

   ```bash
   unzip -d /path/to/prestashop/modules <archive_name>.zip
   ```

3. **Modules > Module Manager**, search for *seven* and click **Install**.

## Configuration

Open **Modules > Module Manager**, click **Configure** on the *seven* module and paste your API key. Optional fields like sender ID can be set on the same screen.

## Support

Need help? Feel free to [contact us](https://www.seven.io/en/company/contact/) or [open an issue](https://github.com/seven-io/prestashop/issues).

## License

[MIT](LICENSE)
