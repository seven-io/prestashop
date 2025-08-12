# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a PrestaShop module for seven.io SMS integration, supporting PrestaShop versions 1.7 and 8.x. The module enables automated SMS notifications for order events and bulk SMS functionality.

## Installation & Setup

**Via Composer:**
```bash
composer require seven.io/prestashop
```

**Via GitHub:**
1. Download latest release from GitHub
2. Extract to PrestaShop modules directory: `unzip -d /path/to/prestashop/modules <archive_name>.zip`
3. Activate through PrestaShop admin panel

## Architecture

### Core Components

- **seven.php**: Main module class extending PrestaShop's Module class
- **classes/**: Utility and business logic classes
  - `Constants.php`: Configuration constants and default values
  - `Util.php`: Helper utilities for order processing and configuration
  - `SmsUtil.php`: SMS sending functionality
  - `Form.php`/`FormUtil.php`: Admin form generation
  - `Personalizer.php`: Message personalization with placeholders
  - `TableWrapper.php`: Database table management
- **controllers/admin/**: Admin controller for bulk SMS functionality
- **models/**: Database models (SevenMessage for message history)
- **translations/**: Multi-language support (en, de, dk)

### Key Functionality

The module hooks into PrestaShop events to send automated SMS notifications:

- **Order Status Changes**: Delivery, shipping, refund notifications via `hookActionOrderStatusPostUpdate`
- **Payment Confirmation**: Payment success notifications via `hookActionPaymentConfirmation`
- **Invoice Creation**: Invoice generation notifications via `hookActionSetInvoice`

### Message Personalization

Supports placeholder system for dynamic content:
- `{address.<property>}`: Customer address fields (firstname, lastname, etc.)
- `{order.<property>}`: Order data (id, reference, etc.)
- `{invoice.<property>}`: Invoice data (number, total_paid_tax_incl, etc.)

### Configuration Management

All configuration is stored in PrestaShop's Configuration system using constants defined in `Constants.php`. The module automatically manages hook registration/unregistration based on enabled events.

## Development Commands

This is a PHP-based PrestaShop module with no build system. Development involves:

**Dependency Management:**
```bash
composer install          # Install dependencies
composer update           # Update dependencies
```

**Code Quality:**
- No specific linting/testing commands configured
- Follow PSR standards and PrestaShop coding conventions
- Use PHP 8.0+ type hints and features
- Requires PHP 8.0+ for compatibility with PrestaShop 8.x

## Database

The module creates its own database table via `TableWrapper::create()` during installation for storing message history. Table is automatically dropped on uninstall.

## Key Files to Understand

1. `seven.php:20-166`: Main module class with hook implementations
2. `classes/Constants.php:24-40`: Default configuration and message templates
3. `classes/Util.php:40-46`: Order state to action mapping logic
4. `classes/SmsUtil.php`: SMS sending implementation (referenced but not shown)

## PrestaShop Integration

- Compatible with PrestaShop 1.7 and 8.x (upgraded for v8)
- Uses PrestaShop's Configuration API for settings storage
- Integrates with PrestaShop's tab system for admin interface
- Follows PrestaShop module development patterns and lifecycle methods
- Updated with PHP 8.0+ type declarations for v8 compatibility
- Modern method signatures and return types for improved type safety