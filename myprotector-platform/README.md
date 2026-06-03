# MyProtector Platform - WordPress Plugin

**Version:** 1.0.0  
**WordPress:** 6.0+  
**PHP:** 8.0+  
**License:** Proprietary

## Overview

MyProtector Platform is a comprehensive WordPress plugin that provides a Trustpilot-style review platform. It enables businesses to build trust through verified reviews, traffic light trust signals, and comprehensive review management.

## Architecture

### Module Structure

The plugin follows a modular architecture with the following modules:

| Module | Purpose |
|--------|---------|
| **Core** | Bootstrap, plugin lifecycle, base classes |
| **Reviews** | Review submission, moderation, display |
| **BusinessProfiles** | Company profiles, claiming, verification |
| **TrafficSignals** | Trust score, traffic light display |
| **Dashboards** | Individual, Business, Admin, Support dashboards |
| **Resellers** | Partner referral system, commissions |
| **Widgets** | Embeddable review widgets |
| **Emails** | Email templates, sending, queue |
| **WooCommerce** | E-commerce integration |
| **Admin** | Admin settings, tools |

### Directory Structure

```
myprotector-platform/
├── Core/                    # Foundation classes
│   ├── Bootstrap/          # Hook registration, services
│   └── Base/               # Base classes (Module, Controller)
├── Modules/                # Feature modules
│   ├── Reviews/
│   ├── BusinessProfiles/
│   ├── TrafficSignals/
│   ├── Dashboards/
│   ├── Resellers/
│   ├── Widgets/
│   ├── Emails/
│   ├── WooCommerce/
│   └── Admin/
├── Services/               # Cross-cutting services
├── Database/               # Schema, migrations
├── API/                   # REST API controllers
├── Admin/                  # Admin interface
├── Public/                 # Public interface
├── Templates/             # Template overrides
└── Assets/                 # CSS, JS, images
```

### Namespaces

```
MyProtector\
├── Core\
│   ├── MyProtector
│   ├── Bootstrap
│   ├── Activator
│   └── Base\
├── Modules\
│   ├── Reviews\           # Services, Models, Repositories
│   ├── BusinessProfiles\
│   ├── TrafficSignals\
│   └── ...
├── Services\
│   ├── Container\
│   ├── Database\
│   ├── Cache\
│   └── Logger\
└── API\
```

### Service Architecture

Services are registered in a central container and accessed via dependency injection:

```php
// Access service
$reviewsService = $this->service('reviews.service');

// Create service
class ReviewService extends Service {
    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
    }
}
```

### Hook Registration

Modules register hooks via the base Module class:

```php
// Actions
$this->addAction('wp_ajax_submit_review', [$this, 'handleSubmission']);

// Filters
$this->addFilter('the_content', [$this, 'filterContent']);

// Shortcodes
$this->addShortcode('mp_reviews', [$this, 'renderReviews']);

// REST API
$this->registerApiRoute('myprotector/v1', '/reviews', $args);
```

### Activation Process

1. Create database tables (10 custom tables)
2. Set default options
3. Register user roles (Admin, Support, Business, Reseller)
4. Add capabilities to admin role
5. Register custom post types
6. Flush rewrite rules
7. Schedule cron events
8. Seed initial data (categories, email templates)

### Deactivation Process

1. Clear all transients
2. Clear object cache
3. Unschedule cron events
4. Clean temporary data
5. Flush rewrite rules

## Database Tables

| Table | Purpose |
|-------|---------|
| `mp_companies` | Business profiles |
| `mp_reviews` | Review submissions |
| `mp_review_responses` | Business responses |
| `mp_review_images` | Image attachments |
| `mp_traffic_light_status` | Trust status |
| `mp_resellers` | Partner accounts |
| `mp_referrals` | Referral tracking |
| `mp_support_tickets` | Helpdesk tickets |
| `mp_blacklist` | Blocked entities |
| `mp_email_templates` | Email templates |
| `mp_audit_log` | Activity tracking |

## User Roles

| Role | Capabilities |
|------|-------------|
| **Administrator** | Full platform access |
| **Customer Support** | Ticket management, limited moderation |
| **Business** | Company profile, review responses |
| **Reseller** | Referral tracking, commission viewing |
| **Individual** | Submit reviews, manage profile |

## API Endpoints

Base URL: `/wp-json/myprotector/v1/`

- `GET /reviews` - List reviews
- `POST /reviews` - Create review
- `GET /companies` - List companies
- `GET /companies/{id}` - Get company
- `GET /widgets` - Get widget code

## Installation

1. Upload plugin to `/wp-content/plugins/myprotector-platform/`
2. Activate plugin
3. Go to Settings > MyProtector to configure

## Requirements

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+
- SSL recommended for API

## License

Proprietary - All rights reserved