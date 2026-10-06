# Shopify Product QR Code Manager

A production-oriented Shopify app built with **plain PHP, MySQL, Shopify Admin GraphQL API, App Proxy, and Theme App Extension**.

The application allows merchants to create and manage QR codes associated with Shopify products and variants. QR codes can then be displayed dynamically on the storefront based on the currently selected product variant.

## Features

* Shopify embedded app
* Shopify Admin GraphQL API integration
* Product and variant selection
* QR code generation and management
* MySQL persistence
* Shopify App Proxy
* Theme App Extension
* Dynamic storefront QR code rendering
* Variant-aware QR code switching
* Product-level QR fallback
* App uninstall webhook handling
* Shopify App Bridge integration
* Offline Admin API access token handling
* Refresh-token rotation handling
* PHP 8.4+
* Composer dependency management

## Architecture

```text
Shopify Admin
     │
     ▼
Embedded Shopify App
     │
     ▼
Plain PHP Application
     │
     ├── Shopify Admin GraphQL API
     │
     └── MySQL
     
Shopify Product Page
     │
     ▼
Theme App Extension
     │
     ▼
Shopify App Proxy
     │
     ▼
PHP Storefront Endpoint
     │
     ▼
MySQL
     │
     ▼
QR Code Data
```

## Storefront Variant Flow

When a customer opens a product page:

```text
Product Page
     ↓
Theme App Extension
     ↓
Read selected variant
     ↓
JavaScript requests App Proxy
     ↓
PHP receives product + variant ID
     ↓
Check variant QR
     ↓
If unavailable, check product QR
     ↓
Return QR data
     ↓
Update QR displayed on storefront
```

The QR code is updated both when the product page initially loads and when the customer changes the selected variant.

## Technology Stack

### Backend

* PHP 8.4
* MySQL
* Composer

### Shopify

* Shopify Admin GraphQL API
* Shopify App Bridge
* Shopify App Proxy
* Shopify Theme App Extension
* Shopify CLI

### Frontend

* Liquid
* JavaScript
* CSS

## Database

QR codes are stored in the application's MySQL database.

Example QR record:

```text
shop_id
code
title
product_gid
product_title
variant_gid
variant_title
destination_type
scans
active
created_at
updated_at
```

Shopify product and variant GraphQL IDs are stored as the canonical identifiers.

## Project Structure

```text
controllers/       Application controllers
database/          Database schema
extensions/        Shopify Theme App Extension
lib/               Authentication, database and Shopify helpers
public/             Application entry point
views/              PHP views
webhooks/           Shopify webhook handlers
shopify.app.toml   Shopify application configuration
```

## Local Development

Requirements:

* PHP 8.4+
* MySQL
* Composer
* Node.js
* Shopify CLI
* Shopify development store

Install PHP dependencies:

```bash
composer install
```

Create your environment file:

```bash
copy .env.example .env
```

Configure the Shopify and MySQL credentials in `.env`.

Import:

```text
database/schema.sql
```

Start the Shopify development environment:

```bash
shopify app dev
```

## Production Deployment

The PHP application can be hosted separately on a PHP hosting environment such as cPanel.

The Shopify application configuration and Theme App Extension are deployed through Shopify CLI:

```bash
shopify app deploy
```

The PHP application itself is deployed to the hosting server separately.

## Security

Sensitive credentials are intentionally excluded from this repository.

Create a local `.env` file using `.env.example`.

Never commit:

* Shopify API secrets
* Access tokens
* Refresh tokens
* Database passwords
* Production credentials

## Portfolio

This project demonstrates practical Shopify app development using a framework-agnostic PHP backend rather than Laravel.

It focuses on integrating Shopify's platform with a traditional PHP/MySQL application architecture while providing a modern storefront integration through a Theme App Extension and App Proxy.
