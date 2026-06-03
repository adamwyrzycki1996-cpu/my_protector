# MyProtector Platform - WordPress Plugin Architecture
## Production-Ready Plugin Structure

**Plugin Name:** MyProtector Platform  
**Version:** 1.0.0  
**Author:** MyProtector Team  
**License:** Proprietary  
**WordPress Version:** 6.0+  
**PHP Version:** 8.0+

---

## 1. Plugin Overview

```
Plugin Slug: myprotector-platform
Text Domain: myprotector-platform
Domain Path: /languages
Main File: myprotector-platform.php
```

### Plugin Hierarchy

```
myprotector-platform/
├── Core/                          # Foundation & Bootstrap
├── Modules/                       # Feature Modules
│   ├── Reviews/
│   ├── BusinessProfiles/
│   ├── TrafficSignals/
│   ├── Dashboards/
│   ├── Resellers/
│   ├── Widgets/
│   ├── Emails/
│   ├── WooCommerce/
│   └── Admin/
├── Services/                      # Cross-cutting Services
├── Traits/                        # Reusable Traits
├── Database/                     # Schema & Migrations
├── API/                           # REST API Controllers
├── Shortcodes/                    # Shortcode Handlers
├── Gutenberg/                     # Block Editor Components
├── Admin/                         # Admin Interface
├── Public/                        # Public Interface
├── Assets/                        # Frontend Assets
│   ├── css/
│   ├── js/
│   └── images/
├── Templates/                     # Template Overrides
├── Languages/                     # i18n Files
├── config.php                     # Configuration
├── uninstall.php                  # Cleanup Script
└── README.md                      # Documentation
```

---

## 2. Folder Structure (Detailed)

```
myprotector-platform/
│
├─── myprotector-platform.php              # Main Plugin File
├─── myprotector-platform-loader.php       # Autoloader
├─── config.php                            # Plugin Configuration
├─── uninstall.php                         # Uninstaller
├─── README.md                             # Readme
│
├─── Core/                                 # Core Foundation
│   ├── MyProtector.php                    # Main Plugin Class
│   ├── Bootstrap.php                      # Bootstrap & Init
│   ├── Plugin.php                         # Interface Contract
│   ├── Activator.php                      # Activation Handler
│   ├── Deactivator.php                    # Deactivation Handler
│   ├── Upgrader.php                       # Version Upgrades
│   │
│   ├── Bootstrap/
│   │   ├── Hooks.php                      # Hook Registration
│   │   ├── Services.php                   # Service Container
│   │   └── Shortcodes.php                 # Shortcode Registry
│   │
│   └── Base/
│       ├── Controller.php                 # Base Controller
│       ├── Model.php                      # Base Model
│       ├── Repository.php                 # Base Repository
│       ├── Service.php                    # Base Service
│       ├── Singleton.php                  # Singleton Pattern
│       └── WithInstance.php              # Instance Access
│
├─── Modules/                              # Feature Modules
│   │
│   ├── Reviews/
│   │   ├── Reviews.php                    # Module Entry Point
│   │   ├── ReviewsModule.php              # Module Config
│   │   │
│   │   ├── Admin/
│   │   │   ├── ReviewsAdminController.php
│   │   │   ├── ReviewsMetaBox.php
│   │   │   └── ReviewsListTable.php
│   │   │
│   │   ├── Public/
│   │   │   ├── ReviewsPublicController.php
│   │   │   ├── ReviewSubmissionForm.php
│   │   │   └── ReviewDisplay.php
│   │   │
│   │   ├── Api/
│   │   │   ├── ReviewsApiController.php
│   │   │   └── ReviewValidationApi.php
│   │   │
│   │   ├── Services/
│   │   │   ├── ReviewService.php
│   │   │   ├── ReviewModerationService.php
│   │   │   ├── ReviewAnalyticsService.php
│   │   │   └── ReviewImageService.php
│   │   │
│   │   ├── Models/
│   │   │   ├── Review.php
│   │   │   ├── ReviewResponse.php
│   │   │   ├── ReviewImage.php
│   │   │   └── ReviewHelpful.php
│   │   │
│   │   ├── Repositories/
│   │   │   ├── ReviewRepository.php
│   │   │   └── ReviewResponseRepository.php
│   │   │
│   │   ├── Validators/
│   │   │   └── ReviewValidator.php
│   │   │
│   │   └── assets/
│   │       ├── css/reviews-admin.css
│   │       ├── css/reviews-public.css
│   │       ├── js/reviews-admin.js
│   │       └── js/reviews-public.js
│   │
│   ├── BusinessProfiles/
│   │   ├── BusinessProfiles.php
│   │   ├── BusinessProfilesModule.php
│   │   │
│   │   ├── Admin/
│   │   │   ├── BusinessAdminController.php
│   │   │   ├── CompanyMetaBox.php
│   │   │   └── BusinessListTable.php
│   │   │
│   │   ├── Public/
│   │   │   ├── BusinessPublicController.php
│   │   │   ├── CompanyProfilePage.php
│   │   │   └── BusinessClaimForm.php
│   │   │
│   │   ├── Api/
│   │   │   ├── BusinessApiController.php
│   │   │   └── BusinessClaimApi.php
│   │   │
│   │   ├── Services/
│   │   │   ├── BusinessService.php
│   │   │   ├── BusinessVerificationService.php
│   │   │   ├── BusinessClaimService.php
│   │   │   └── BusinessAnalyticsService.php
│   │   │
│   │   ├── Models/
│   │   │   ├── Company.php
│   │   │   ├── CompanyCategory.php
│   │   │   └── CompanyDocument.php
│   │   │
│   │   ├── Repositories/
│   │   │   └── CompanyRepository.php
│   │   │
│   │   ├── Validators/
│   │   │   ├── BusinessValidator.php
│   │   │   └── ClaimValidator.php
│   │   │
│   │   └── assets/
│   │
│   ├── TrafficSignals/
│   │   ├── TrafficSignals.php
│   │   ├── TrafficSignalsModule.php
│   │   │
│   │   ├── Admin/
│   │   │   ├── TrafficLightAdminController.php
│   │   │   └── TrafficLightMetaBox.php
│   │   │
│   │   ├── Public/
│   │   │   ├── TrafficLightDisplay.php
│   │   │   └── TrafficLightBadge.php
│   │   │
│   │   ├── Services/
│   │   │   ├── TrafficLightService.php
│   │   │   ├── TrustScoreCalculator.php
│   │   │   └── TrafficLightAutoUpdater.php
│   │   │
│   │   ├── Models/
│   │   │   ├── TrafficLightStatus.php
│   │   │   ├── TrustRequirement.php
│   │   │   └── TrustHistory.php
│   │   │
│   │   └── assets/
│   │       ├── css/traffic-lights.css
│   │       └── images/
│   │           ├── walking.png
│   │           ├── shopping.png
│   │           └── bad.png
│   │
│   ├── Dashboards/
│   │   ├── Dashboards.php
│   │   ├── DashboardsModule.php
│   │   │
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── IndividualDashboardController.php
│   │   │   ├── BusinessDashboardController.php
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── SupportDashboardController.php
│   │   │   └── ResellerDashboardController.php
│   │   │
│   │   ├── Views/
│   │   │   ├── individual/
│   │   │   │   ├── dashboard.php
│   │   │   │   ├── my-reviews.php
│   │   │   │   ├── profile.php
│   │   │   │   └── settings.php
│   │   │   ├── business/
│   │   │   │   ├── dashboard.php
│   │   │   │   ├── reviews.php
│   │   │   │   ├── analytics.php
│   │   │   │   ├── widgets.php
│   │   │   │   └── settings.php
│   │   │   ├── admin/
│   │   │   │   ├── dashboard.php
│   │   │   │   ├── users.php
│   │   │   │   ├── reviews-moderation.php
│   │   │   │   ├── companies.php
│   │   │   │   ├── blacklist.php
│   │   │   │   └── settings.php
│   │   │   ├── support/
│   │   │   │   ├── dashboard.php
│   │   │   │   ├── tickets.php
│   │   │   │   └── quick-tools.php
│   │   │   └── reseller/
│   │   │       ├── dashboard.php
│   │   │       ├── referrals.php
│   │   │       ├── earnings.php
│   │   │       └── payout.php
│   │   │
│   │   ├── Services/
│   │   │   ├── DashboardService.php
│   │   │   └── DashboardWidgetService.php
│   │   │
│   │   └── assets/
│   │
│   ├── Resellers/
│   │   ├── Resellers.php
│   │   ├── ResellersModule.php
│   │   │
│   │   ├── Admin/
│   │   │   ├── ResellerAdminController.php
│   │   │   └── ResellerListTable.php
│   │   │
│   │   ├── Api/
│   │   │   └── ResellerApiController.php
│   │   │
│   │   ├── Services/
│   │   │   ├── ResellerService.php
│   │   │   ├── ReferralService.php
│   │   │   ├── CommissionService.php
│   │   │   └── PayoutService.php
│   │   │
│   │   ├── Models/
│   │   │   ├── Reseller.php
│   │   │   ├── Referral.php
│   │   │   └── Commission.php
│   │   │
│   │   ├── Repositories/
│   │   │   └── ResellerRepository.php
│   │   │
│   │   └── assets/
│   │
│   ├── Widgets/
│   │   ├── Widgets.php
│   │   ├── WidgetsModule.php
│   │   │
│   │   ├── Handlers/
│   │   │   ├── ClassicBadgeWidget.php
│   │   │   ├── MiniBadgeWidget.php
│   │   │   ├── ReviewsSliderWidget.php
│   │   │   └── PopupWidget.php
│   │   │
│   │   ├── Services/
│   │   │   ├── WidgetService.php
│   │   │   ├── WidgetGeneratorService.php
│   │   │   └── WidgetAnalyticsService.php
│   │   │
│   │   ├── Public/
│   │   │   ├── WidgetFrontend.php
│   │   │   └── WidgetScriptLoader.php
│   │   │
│   │   ├── assets/
│   │   │   ├── css/widgets.css
│   │   │   ├── js/widgets.js
│   │   │   └── images/
│   │
│   ├── Emails/
│   │   ├── Emails.php
│   │   ├── EmailsModule.php
│   │   │
│   │   ├── Services/
│   │   │   ├── EmailService.php
│   │   │   ├── EmailTemplateService.php
│   │   │   ├── EmailQueueService.php
│   │   │   └── EmailAnalyticsService.php
│   │   │
│   │   ├── Models/
│   │   │   ├── EmailTemplate.php
│   │   │   ├── EmailLog.php
│   │   │   └── EmailQueue.php
│   │   │
│   │   ├── Repositories/
│   │   │   └── EmailTemplateRepository.php
│   │   │
│   │   ├── Templates/
│   │   │   ├── user/
│   │   │   │   ├── welcome.php
│   │   │   │   ├── email-verification.php
│   │   │   │   └── password-reset.php
│   │   │   ├── review/
│   │   │   │   ├── invitation.php
│   │   │   │   ├── reminder.php
│   │   │   │   ├── confirmation.php
│   │   │   │   ├── published.php
│   │   │   │   └── response-notification.php
│   │   │   ├── business/
│   │   │   │   ├── claim-request.php
│   │   │   │   ├── claim-approved.php
│   │   │   │   └── trust-update.php
│   │   │   ├── reseller/
│   │   │   │   ├── application-received.php
│   │   │   │   ├── new-referral.php
│   │   │   │   └── commission-paid.php
│   │   │   └── support/
│   │   │       ├── ticket-received.php
│   │   │       └── ticket-resolved.php
│   │   │
│   │   └── assets/
│   │
│   ├── WooCommerce/
│   │   ├── WooCommerce.php
│   │   ├── WooCommerceModule.php
│   │   │
│   │   ├── Integrations/
│   │   │   ├── ProductIntegration.php
│   │   │   ├── OrderIntegration.php
│   │   │   ├── CartIntegration.php
│   │   │   └── CheckoutIntegration.php
│   │   │
│   │   ├── Services/
│   │   │   ├── WooCommerceService.php
│   │   │   ├── ReviewInvitationService.php
│   │   │   └── VerifiedPurchaseService.php
│   │   │
│   │   ├── Admin/
│   │   │   ├── WooCommerceAdminController.php
│   │   │   └── SettingsPage.php
│   │   │
│   │   └── assets/
│   │
│   └── Admin/
│       ├── Admin.php
│       ├── AdminModule.php
│       │
│       ├── Controllers/
│       │   ├── SettingsController.php
│       │   ├── UsersController.php
│       │   └── SystemController.php
│       │
│       ├── Pages/
│       │   ├── Dashboard.php
│       │   ├── Settings.php
│       │   ├── Tools.php
│       │   └── Help.php
│       │
│       ├── Services/
│       │   ├── AdminService.php
│       │   └── RoleManagerService.php
│       │
│       └── assets/
│
├─── Services/                          # Cross-Cutting Services
│   ├── Container/
│   │   ├── ServiceContainer.php
│   │   └── ContainerInterface.php
│   │
│   ├── Database/
│   │   ├── DatabaseService.php
│   │   └── QueryBuilder.php
│   │
│   ├── Cache/
│   │   ├── CacheService.php
│   │   └── CacheInvalidator.php
│   │
│   ├── Logger/
│   │   ├── LoggerService.php
│   │   └── LogWriter.php
│   │
│   ├── Security/
│   │   ├── SecurityService.php
│   │   ├── SanitizerService.php
│   │   └── NonceService.php
│   │
│   ├── Validator/
│   │   └── ValidatorService.php
│   │
│   ├── Config/
│   │   └── ConfigService.php
│   │
│   └── Audit/
│       └── AuditService.php
│
├─── Database/
│   ├── Schema.php                      # Database Schema
│   ├── Migrations/
│   │   ├── MigrationManager.php
│   │   ├── Version20260101_Initial.php
│   │   ├── Version20260102_Companies.php
│   │   ├── Version20260103_Reviews.php
│   │   └── Version20260104_TrafficLight.php
│   │
│   └── Seeds/
│       ├── DatabaseSeeder.php
│       ├── CompanyCategoriesSeeder.php
│       └── EmailTemplatesSeeder.php
│
├─── API/
│   ├── RestApi.php                    # REST API Bootstrap
│   ├── Authentication/
│   │   └── ApiAuthentication.php
│   │
│   ├── Controllers/
│   │   ├── ReviewsController.php
│   │   ├── CompaniesController.php
│   │   ├── UsersController.php
│   │   ├── ResellersController.php
│   │   ├── WidgetsController.php
│   │   └── AnalyticsController.php
│   │
│   └── Routes/
│       ├── RouteRegistrar.php
│       ├── ReviewsRoutes.php
│       ├── CompaniesRoutes.php
│       └── AuthRoutes.php
│
├─── Shortcodes/
│   ├── ShortcodeRegistry.php
│   ├── ReviewsShortcode.php
│   ├── BusinessSearchShortcode.php
│   ├── TrafficLightShortcode.php
│   └── TrustBadgeShortcode.php
│
├─── Gutenberg/
│   ├── blocks/
│   │   ├── reviews-list/
│   │   │   ├── index.js
│   │   │   ├── edit.js
│   │   │   ├── save.js
│   │   │   └── editor.css
│   │   │
│   │   ├── company-profile/
│   │   │   ├── index.js
│   │   │   ├── edit.js
│   │   │   └── save.js
│   │   │
│   │   └── trust-badge/
│   │       ├── index.js
│   │       ├── edit.js
│   │       └── save.js
│   │
│   └── init.php
│
├─── Admin/
│   ├── AdminLoader.php
│   ├── Menu/
│   │   ├── AdminMenu.php
│   │   ├── SubmenuFactory.php
│   │   └── MenuItem.php
│   │
│   ├── Pages/
│   │   ├── DashboardPage.php
│   │   ├── SettingsPage.php
│   │   ├── ToolsPage.php
│   │   └── HelpPage.php
│   │
│   ├── Notices/
│   │   ├── AdminNotice.php
│   │   ├── NoticeManager.php
│   │   └── Notices/
│   │       ├── UpdateNotice.php
│   │       └── WelcomeNotice.php
│   │
│   └── assets/
│       ├── css/admin.css
│       └── js/admin.js
│
├─── Public/
│   ├── PublicLoader.php
│   ├── Frontend/
│   │   ├── FrontendRenderer.php
│   │   └── ContentFilters.php
│   │
│   └── assets/
│       ├── css/public.css
│       └── js/public.js
│
├─── Templates/
│   ├── single-company.php
│   ├── single-review.php
│   ├── page-dashboard.php
│   └── partials/
│       ├── review-card.php
│       ├── traffic-light-badge.php
│       └── company-header.php
│
├─── Assets/
│   ├── css/
│   │   ├── global.css
│   │   ├── components.css
│   │   └── rtl.css
│   │
│   ├── js/
│   │   ├── global.js
│   │   ├── forms.js
│   │   └── notifications.js
│   │
│   └── images/
│       ├── logo.svg
│       ├── icons/
│       └── badges/
│
└─── Languages/
    ├── myprotector-platform.pot
    ├── en_US.po
    └── en_US.mo
```

---

## 3. Class Structure

### 3.1 Core Classes

```php
// Main Plugin Class
namespace MyProtector\Core;

class MyProtector {
    protected static $instance;
    protected $bootstrap;
    protected $container;
    
    public static function getInstance(): self;
    public function run(): void;
    public function get(string $id): mixed;
    public function getPath(): string;
    public function getUrl(): string;
    public function getVersion(): string;
}

// Bootstrap Class
namespace MyProtector\Core;

class Bootstrap {
    protected $loader;
    protected $services;
    
    public function registerHooks(): void;
    public function bootModules(): void;
    public function initServices(): void;
}

// Activator Class
namespace MyProtector\Core;

class Activator {
    public static function activate(): void;
    public function createTables(): void;
    public function createOptions(): void;
    public function createRoles(): void;
    public function createCapabilities(): void;
    public function runMigrations(): void;
}

// Deactivator Class
namespace MyProtector\Core;

class Deactivator {
    public static function deactivate(): void;
    public function clearCache(): void;
    public function scheduleCleanup(): void;
}
```

### 3.2 Module Base Classes

```php
// Module Interface
namespace MyProtector\Core;

interface ModuleInterface {
    public function getName(): string;
    public function getPath(): string;
    public function boot(): void;
    public function registerHooks(): void;
}

// Base Module Class
namespace MyProtector\Core;

abstract class Module implements ModuleInterface {
    protected $name;
    protected $path;
    protected $services = [];
    
    abstract public function boot(): void;
    abstract public function registerHooks(): void;
    
    protected function registerService(string $id, $service): void;
    protected function addAction(string $hook, callable $callback, int $priority = 10): void;
    protected function addFilter(string $hook, callable $callback, int $priority = 10): void;
    protected function addShortcode(string $tag, callable $callback): void;
    protected function registerApiRoute(string $namespace, string $route, array $args): void;
}
```

### 3.3 Service Classes

```php
// Base Service
namespace MyProtector\Services;

abstract class Service {
    protected $container;
    
    public function __construct(ContainerInterface $container);
    protected function get(string $id): mixed;
}

// Review Service
namespace MyProtector\Modules\Reviews\Services;

class ReviewService extends Service {
    public function create(array $data): Review;
    public function update(int $id, array $data): Review;
    public function delete(int $id): bool;
    public function find(int $id): ?Review;
    public function findByCompany(int $companyId): Collection;
    public function approve(int $id): Review;
    public function reject(int $id, string $reason): Review;
    public function getPending(): Collection;
    public function search(array $criteria): Collection;
}

// Company Service
namespace MyProtector\Modules\BusinessProfiles\Services;

class BusinessService extends Service {
    public function create(array $data): Company;
    public function update(int $id, array $data): Company;
    public function claim(int $companyId, int $userId): Claim;
    public function verifyDomain(int $companyId, string $domain): bool;
    public function getTrustScore(int $companyId): float;
    public function updateTrustStatus(int $companyId): void;
}

// Traffic Light Service
namespace MyProtector\Modules\TrafficSignals\Services;

class TrafficLightService extends Service {
    public function calculateTrustScore(int $companyId): float;
    public function determineStatus(int $companyId): string;
    public function getStatusDisplay(int $companyId): array;
    public function autoUpdate(int $companyId): void;
    public function manualOverride(int $companyId, string $status, string $reason): void;
    public function checkRequirements(int $companyId): array;
}

// Email Service
namespace MyProtector\Modules\Emails\Services;

class EmailService extends Service {
    public function send(string $templateKey, array $to, array $data = []): bool;
    public function sendTemplate(EmailTemplate $template, array $to, array $data = []): bool;
    public function queue(string $templateKey, array $to, array $data = [], int $delay = 0): void;
    public function sendBatch(array $recipients, string $templateKey, array $data = []): void;
}
```

---

## 4. Namespaces

```
MyProtector\
├── Core\
│   ├── MyProtector
│   ├── Bootstrap
│   ├── Activator
│   ├── Deactivator
│   ├── Upgrader
│   ├── Bootstrap\
│   │   ├── Hooks
│   │   ├── Services
│   │   └── Shortcodes
│   └── Base\
│       ├── Controller
│       ├── Model
│       ├── Repository
│       ├── Service
│       ├── Singleton
│       └── WithInstance
│
├── Modules\
│   ├── Reviews\
│   │   ├── Reviews (Module)
│   │   ├── Admin\
│   │   ├── Public\
│   │   ├── Api\
│   │   ├── Services\
│   │   ├── Models\
│   │   ├── Repositories\
│   │   └── Validators\
│   │
│   ├── BusinessProfiles\
│   │   ├── BusinessProfiles (Module)
│   │   ├── Admin\
│   │   ├── Public\
│   │   ├── Api\
│   │   ├── Services\
│   │   ├── Models\
│   │   ├── Repositories\
│   │   └── Validators\
│   │
│   ├── TrafficSignals\
│   │   ├── TrafficSignals (Module)
│   │   ├── Admin\
│   │   ├── Public\
│   │   ├── Services\
│   │   └── Models\
│   │
│   ├── Dashboards\
│   │   ├── Dashboards (Module)
│   │   ├── Controllers\
│   │   └── Views\
│   │
│   ├── Resellers\
│   │   ├── Resellers (Module)
│   │   ├── Admin\
│   │   ├── Api\
│   │   ├── Services\
│   │   ├── Models\
│   │   └── Repositories\
│   │
│   ├── Widgets\
│   │   ├── Widgets (Module)
│   │   ├── Handlers\
│   │   ├── Services\
│   │   └── Public\
│   │
│   ├── Emails\
│   │   ├── Emails (Module)
│   │   ├── Services\
│   │   ├── Models\
│   │   ├── Repositories\
│   │   └── Templates\
│   │
│   ├── WooCommerce\
│   │   ├── WooCommerce (Module)
│   │   ├── Integrations\
│   │   ├── Services\
│   │   └── Admin\
│   │
│   └── Admin\
│       ├── Admin (Module)
│       ├── Controllers\
│       ├── Pages\
│       └── Services\
│
├── Services\
│   ├── Container\
│   ├── Database\
│   ├── Cache\
│   ├── Logger\
│   ├── Security\
│   ├── Validator\
│   ├── Config\
│   └── Audit\
│
├── Database\
│   ├── Schema
│   ├── Migrations\
│   └── Seeds\
│
├── API\
│   ├── Authentication\
│   ├── Controllers\
│   └── Routes\
│
├── Shortcodes\
├── Gutenberg\
├── Admin\
├── Public\
├── Templates\
└── Assets\
```

---

## 5. Service Architecture

### 5.1 Service Container

```php
namespace MyProtector\Services\Container;

interface ContainerInterface {
    public function get(string $id): mixed;
    public function has(string $id): bool;
    public function set(string $id, callable $factory): void;
    public function singleton(string $id, callable $factory): void;
}

class ServiceContainer implements ContainerInterface {
    protected $services = [];
    protected $factories = [];
    protected $singletons = [];
    
    public function registerCoreServices(): void;
    public function registerModuleServices(): void;
    public function get(string $id): mixed;
    public function has(string $id): bool;
}
```

### 5.2 Service Registration Map

```php
// config/services.php
return [
    // Core Services
    'config' => \MyProtector\Services\Config\ConfigService::class,
    'logger' => \MyProtector\Services\Logger\LoggerService::class,
    'cache' => \MyProtector\Services\Cache\CacheService::class,
    'database' => \MyProtector\Services\Database\DatabaseService::class,
    'security' => \MyProtector\Services\Security\SecurityService::class,
    
    // Module Services
    'reviews.service' => \MyProtector\Modules\Reviews\Services\ReviewService::class,
    'reviews.moderation' => \MyProtector\Modules\Reviews\Services\ReviewModerationService::class,
    'reviews.analytics' => \MyProtector\Modules\Reviews\Services\ReviewAnalyticsService::class,
    
    'business.service' => \MyProtector\Modules\BusinessProfiles\Services\BusinessService::class,
    'business.claim' => \MyProtector\Modules\BusinessProfiles\Services\BusinessClaimService::class,
    
    'traffic.service' => \MyProtector\Modules\TrafficSignals\Services\TrafficLightService::class,
    'trust.calculator' => \MyProtector\Modules\TrafficSignals\Services\TrustScoreCalculator::class,
    
    'email.service' => \MyProtector\Modules\Emails\Services\EmailService::class,
    'email.template' => \MyProtector\Modules\Emails\Services\EmailTemplateService::class,
    
    'reseller.service' => \MyProtector\Modules\Resellers\Services\ResellerService::class,
    'reseller.commission' => \MyProtector\Modules\Resellers\Services\CommissionService::class,
    
    'widget.service' => \MyProtector\Modules\Widgets\Services\WidgetService::class,
    
    'woo.service' => \MyProtector\Modules\WooCommerce\Services\WooCommerceService::class,
];
```

### 5.3 Dependency Injection Example

```php
// ReviewService Constructor Injection
class ReviewService extends Service {
    private ReviewRepository $repository;
    private ReviewValidator $validator;
    private EmailService $email;
    private AuditService $audit;
    
    public function __construct(
        ContainerInterface $container,
        ReviewRepository $repository,
        ReviewValidator $validator,
        EmailService $email,
        AuditService $audit
    ) {
        parent::__construct($container);
        $this->repository = $repository;
        $this->validator = $validator;
        $this->email = $email;
        $this->audit = $audit;
    }
}
```

---

## 6. Hook Registration Plan

### 6.1 Hook Registry Structure

```php
namespace MyProtector\Core\Bootstrap;

class HookRegistry {
    private $actions = [];
    private $filters = [];
    private $shortcodes = [];
    private $apiRoutes = [];
    
    public function addAction(string $hook, callable $callback, int $priority = 10, int $args = 2): void;
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $args = 2): void;
    public function addShortcode(string $tag, callable $callback): void;
    public function register(): void;
}
```

### 6.2 Core Hooks (Init Priority Order)

```php
// Priority 1: Database & Setup (earliest)
add_action('init', [Activator::class, 'checkVersion'], 1);
add_action('init', [MigrationManager::class, 'runPending'], 2);

// Priority 5: Roles & Capabilities
add_action('init', [RoleManager::class, 'registerRoles'], 5);

// Priority 10: Modules Boot
add_action('init', [Bootstrap::class, 'bootModules'], 10);

// Priority 15: Shortcodes
add_action('init', [Bootstrap::class, 'registerShortcodes'], 15);

// Priority 20: REST API
add_action('rest_api_init', [RestApi::class, 'registerRoutes'], 20);

// Priority 25: Rewrite Rules
add_action('init', [RewriteRules::class, 'addRules'], 25);

// Priority 30: Gutenberg Blocks
add_action('init', [GutenbergInit::class, 'registerBlocks'], 30);

// Priority 50: Frontend Assets
add_action('wp_enqueue_scripts', [Assets::class, 'enqueueFrontend'], 50);

// Priority 100: Templates
add_filter('template_include', [TemplateRouter::class, 'loadTemplate'], 100);
```

### 6.3 Module Hook Registration

```php
// Each Module Registers Hooks in registerHooks()

// Reviews Module Hooks
add_filter('the_content', [$this, 'filterReviewContent'], 20);
add_action('wp_ajax_submit_review', [$this->handler, 'submitReview']);
add_action('wp_ajax_nopriv_submit_review', [$this->handler, 'submitReview']);
add_action('wp_ajax_mark_helpful', [$this->handler, 'markHelpful']);
add_action('wp_ajax_report_review', [$this->handler, 'reportReview']);
add_filter('mp_get_reviews', [$this->repository, 'getReviews'], 10, 2);
add_action('transition_post_status', [$this->moderation, 'handleStatusChange'], 10, 3);

// Business Profiles Module Hooks
add_action('init', [$this->registerTaxonomy, 'registerCompanyCategories']);
add_filter('rewrite_rules_array', [$this->rewrite, 'addCompanyRules']);
add_action('template_redirect', [$this->claim, 'handleClaimRequest']);
add_filter('mp_get_company', [$this->repository, 'findBySlug'], 10, 2);
add_action('wp_ajax_claim_company', [$this->handler, 'processClaim']);

// Traffic Signals Module Hooks
add_filter('mp_calculate_trust_score', [$this->calculator, 'calculate'], 10, 2);
add_action('mp_review_published', [$this->updater, 'recalculateTrust']);
add_filter('mp_traffic_light_display', [$this->display, 'render'], 10, 2);
add_action('admin_notices', [$this->admin, 'showTrustWarnings']);

// Dashboards Module Hooks
add_filter('mp_dashboard_nav_items', [$this->nav, 'addItems'], 10, 2);
add_action('admin_menu', [$this->menu, 'addDashboardMenu']);
add_action('wp_ajax_save_dashboard_preference', [$this->handler, 'savePreference']);

// Resellers Module Hooks
add_action('mp_company_registered', [$this->referral, 'trackConversion']);
add_filter('mp_calculate_commission', [$this->commission, 'calculate'], 10, 3);
add_action('wp_ajax_request_payout', [$this->handler, 'requestPayout']);

// Widgets Module Hooks
add_shortcode('myprotector_reviews', [$this->shortcode, 'renderReviews']);
add_shortcode('myprotector_badge', [$this->shortcode, 'renderBadge']);
add_action('wp_head', [$this->analytics, 'trackWidgetViews']);

// Emails Module Hooks
add_action('mp_review_created', [$this->email, 'sendConfirmation']);
add_action('mp_review_approved', [$this->email, 'sendPublishedNotification']);
add_action('mp_company_claimed', [$this->email, 'sendClaimApproved']);
add_filter('cron_schedules', [$this->queue, 'addSchedule']);

// WooCommerce Module Hooks
add_action('woocommerce_order_status_completed', [$this->invite, 'sendReviewInvite']);
add_filter('woocommerce_product_tabs', [$this->tab, 'addReviewTab']);
add_action('woocommerce_after_single_product', [$this->widget, 'displayBadge']);

// Admin Module Hooks
add_action('admin_menu', [$this->menu, 'addPages']);
add_action('admin_init', [$this->settings, 'registerSettings']);
add_filter('plugin_action_links', [$this->actionLinks, 'addLinks'], 10, 2);
```

### 6.4 Hook Priority Guide

| Priority | Purpose | Examples |
|----------|---------|----------|
| 1-5 | Core setup, database | Version check, migrations |
| 5-10 | System configuration | Roles, capabilities, options |
| 10-20 | Module initialization | Boot modules, register CPTs |
| 20-30 | API & routing | REST routes, rewrite rules |
| 30-50 | UI setup | Assets, shortcodes, blocks |
| 50-100 | Template & display | Content filters, template loading |
| 100+ | Final processing | Output buffering, cache |

---

## 7. Activation/Deactivation Process

### 7.1 Activation Flow

```php
// myprotector-platform.php
register_activation_hook(__FILE__, ['MyProtector\\Core\\Activator', 'activate']);

// Activator.php
namespace MyProtector\Core;

class Activator {
    
    public static function activate(): void {
        $instance = new self();
        
        // 1. Set activation flag for notice
        set_transient('mp_activated', true, 60);
        
        // 2. Create database tables
        $instance->createTables();
        
        // 3. Create options with defaults
        $instance->createOptions();
        
        // 4. Register user roles
        $instance->createRoles();
        
        // 5. Add capabilities
        $instance->createCapabilities();
        
        // 6. Run database migrations
        $instance->runMigrations();
        
        // 7. Create custom post types
        $instance->registerPostTypes();
        
        // 8. Flush rewrite rules
        $instance->flushRewriteRules();
        
        // 9. Set up scheduled events
        $instance->scheduleEvents();
        
        // 10. Seed initial data
        $instance->seedInitialData();
        
        // 11. Log activation
        $instance->logActivation();
    }
    
    private function createTables(): void {
        require_once(MYPROTECTOR_PATH . 'Database/Schema.php');
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Execute each table creation
        Schema::createCompaniesTable($wpdb, $charset_collate);
        Schema::createReviewsTable($wpdb, $charset_collate);
        Schema::createReviewResponsesTable($wpdb, $charset_collate);
        Schema::createReviewImagesTable($wpdb, $charset_collate);
        Schema::createTrafficLightStatusTable($wpdb, $charset_collate);
        Schema::createResellersTable($wpdb, $charset_collate);
        Schema::createReferralsTable($wpdb, $charset_collate);
        Schema::createSupportTicketsTable($wpdb, $charset_collate);
        Schema::createBlacklistTable($wpdb, $charset_collate);
        Schema::createEmailTemplatesTable($wpdb, $charset_collate);
        Schema::createAuditLogTable($wpdb, $charset_collate);
        
        update_option('mp_db_version', MYPROTECTOR_DB_VERSION);
    }
    
    private function createOptions(): void {
        $defaults = [
            'mp_version' => MYPROTECTOR_VERSION,
            'mp_review_auto_approve' => false,
            'mp_email_from_name' => get_bloginfo('name'),
            'mp_email_from_email' => get_bloginfo('admin_email'),
            'mp_company_slug_base' => 'company',
            'mp_review_slug_base' => 'review',
            'mp_TrustScore_min_reviews' => 50,
            'mp_trust_score_min_rating' => 4.5,
            'mp_woo_integration_enabled' => false,
            'mp_woo_invite_delay_days' => 7,
            'mp_maintenance_mode' => false,
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
    
    private function createRoles(): void {
        // Administrator
        add_role('mp_admin', 'MyProtector Admin', [
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'manage_myprotector' => true,
        ]);
        
        // Customer Support
        add_role('mp_support', 'MyProtector Support', [
            'read' => true,
            'edit_posts' => false,
            'mp_support_access' => true,
        ]);
        
        // Business
        add_role('mp_business', 'MyProtector Business', [
            'read' => true,
            'mp_business_access' => true,
        ]);
        
        // Reseller
        add_role('mp_reseller', 'MyProtector Reseller', [
            'read' => true,
            'mp_reseller_access' => true,
        ]);
    }
    
    private function createCapabilities(): void {
        $admin = get_role('administrator');
        
        $capabilities = [
            // Core capabilities
            'manage_myprotector',
            'edit_myprotector_settings',
            'view_myprotector_reports',
            
            // Reviews
            'mp_edit_reviews',
            'mp_delete_reviews',
            'mp_moderate_reviews',
            'mp_view_all_reviews',
            
            // Business
            'mp_edit_companies',
            'mp_delete_companies',
            'mp_verify_companies',
            'mp_override_trust_status',
            
            // Users
            'mp_manage_users',
            'mp_ban_users',
            
            // Blacklist
            'mp_manage_blacklist',
            'mp_approve_blacklist',
            
            // Resellers
            'mp_manage_resellers',
            'mp_release_commissions',
            
            // System
            'mp_export_data',
            'mp_view_audit_log',
        ];
        
        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }
    }
    
    private function registerPostTypes(): void {
        set_transient('mp_flush_rewrite_rules', true, 5);
    }
    
    private function flushRewriteRules(): void {
        if (get_transient('mp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_transient('mp_flush_rewrite_rules');
        }
    }
    
    private function scheduleEvents(): void {
        // Daily trust score recalculation
        if (!wp_next_scheduled('mp_daily_trust_update')) {
            wp_schedule_event(time(), 'daily', 'mp_daily_trust_update');
        }
        
        // Hourly email queue processing
        if (!wp_next_scheduled('mp_process_email_queue')) {
            wp_schedule_event(time(), 'hourly', 'mp_process_email_queue');
        }
        
        // Weekly analytics aggregation
        if (!wp_next_scheduled('mp_weekly_analytics')) {
            wp_schedule_event(time(), 'weekly', 'mp_weekly_analytics');
        }
    }
    
    private function seedInitialData(): void {
        // Seed company categories
        DatabaseSeeder::seedCompanyCategories();
        
        // Seed email templates
        DatabaseSeeder::seedEmailTemplates();
        
        // Seed pages
        DatabaseSeeder::seedPages();
    }
    
    private function logActivation(): void {
        update_option('mp_activation_time', time());
        update_option('mp_activation_version', MYPROTECTOR_VERSION);
        
        do_action('mp_activated');
    }
}
```

### 7.2 Deactivation Flow

```php
// myprotector-platform.php
register_deactivation_hook(__FILE__, ['MyProtector\\Core\\Deactivator', 'deactivate']);

// Deactivator.php
namespace MyProtector\Core;

class Deactivator {
    
    public static function deactivate(): void {
        $instance = new self();
        
        // 1. Clear all transients
        $instance->clearTransients();
        
        // 2. Clear object cache
        $instance->clearCache();
        
        // 3. Cancel scheduled events
        $instance->unscheduleEvents();
        
        // 4. Clear scheduled events
        $instance->clearScheduledEvents();
        
        // 5. Remove temporary data
        $instance->cleanTempData();
        
        // 6. Do NOT remove database (preserve data)
        // Only remove on uninstall
        
        // 7. Flush rewrite rules
        $instance->flushRewriteRules();
        
        // 8. Log deactivation
        $instance->logDeactivation();
        
        // 9. Clear any cron locks
        $instance->clearCronLocks();
    }
    
    private function clearTransients(): void {
        global $wpdb;
        
        // Clear all MP transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_mp_%' 
             OR option_name LIKE '_transient_timeout_mp_%'"
        );
        
        // Clear transients from main site and network
        if (is_multisite()) {
            $wpdb->query(
                "DELETE FROM {$wpdb->sitemeta} 
                 WHERE meta_key LIKE '_transient_mp_%' 
                 OR meta_key LIKE '_transient_timeout_mp_%'"
            );
        }
    }
    
    private function clearCache(): void {
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear any object cache
        wp_cache_flush();
    }
    
    private function unscheduleEvents(): void {
        $events = [
            'mp_daily_trust_update',
            'mp_process_email_queue',
            'mp_weekly_analytics',
            'mp_cleanup_old_data',
            'mp_generate_sitemap',
        ];
        
        foreach ($events as $event) {
            $timestamp = wp_next_scheduled($event);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $event);
            }
        }
    }
    
    private function clearScheduledEvents(): void {
        // Clear all MP cron events
        wp_clear_scheduled_hook('mp_daily_trust_update');
        wp_clear_scheduled_hook('mp_process_email_queue');
        wp_clear_scheduled_hook('mp_weekly_analytics');
    }
    
    private function cleanTempData(): void {
        // Remove any temporary uploads
        $upload_dir = wp_upload_dir();
        $mp_temp_dir = $upload_dir['basedir'] . '/myprotector-temp';
        
        if (is_dir($mp_temp_dir)) {
            // Only remove if older than 1 hour (safety)
            if (filemtime($mp_temp_dir) < time() - 3600) {
                // Use WP_Filesystem for reliability
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->delete($mp_temp_dir, true);
            }
        }
    }
    
    private function flushRewriteRules(): void {
        flush_rewrite_rules();
    }
    
    private function logDeactivation(): void {
        update_option('mp_deactivation_time', time());
        
        do_action('mp_deactivated');
    }
    
    private function clearCronLocks(): void {
        delete_option('mp_cron_lock');
        delete_transient('mp_doing_cron');
    }
}
```

### 7.3 Uninstallation (Cleanup)

```php
// uninstall.php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once __DIR__ . '/config.php';

// Remove all plugin data
$instance = new \MyProtector\Core\Uninstaller();
$instance->uninstall();

// Class Uninstaller
namespace MyProtector\Core;

class Uninstaller {
    
    public function uninstall(): void {
        // Check if user wants to keep data
        if (get_option('mp_keep_data_on_uninstall')) {
            // Only remove plugin options, keep posts/tables
            $this->removeOptions();
            return;
        }
        
        // Full cleanup
        $this->dropTables();
        $this->removeOptions();
        $this->removeRoles();
        $this->removeCapabilities();
        $this->removeFiles();
        $this->clearTransients();
        $this->removePosts();
    }
    
    private function dropTables(): void {
        global $wpdb;
        
        $tables = [
            'mp_companies',
            'mp_reviews',
            'mp_review_responses',
            'mp_review_images',
            'mp_traffic_light_status',
            'mp_resellers',
            'mp_referrals',
            'mp_support_tickets',
            'mp_blacklist',
            'mp_email_templates',
            'mp_email_logs',
            'mp_audit_log',
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
    }
    
    private function removeOptions(): void {
        global $wpdb;
        
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE 'mp_%'"
        );
    }
    
    private function removeRoles(): void {
        remove_role('mp_admin');
        remove_role('mp_support');
        remove_role('mp_business');
        remove_role('mp_reseller');
    }
    
    private function removeCapabilities(): void {
        $admin = get_role('administrator');
        if ($admin) {
            $caps = [
                'manage_myprotector',
                'edit_myprotector_settings',
                // ... all custom capabilities
            ];
            foreach ($caps as $cap) {
                $admin->remove_cap($cap);
            }
        }
    }
}
```

---

## 8. Plugin Configuration

```php
// config.php
namespace MyProtector;

define('MYPROTECTOR_VERSION', '1.0.0');
define('MYPROTECTOR_DB_VERSION', '1.0.0');
define('MYPROTECTOR_PATH', plugin_dir_path(__FILE__));
define('MYPROTECTOR_URL', plugin_dir_url(__FILE__));
define('MYPROTECTOR_BASENAME', plugin_basename(__FILE__));
define('MYPROTECTOR_SLUG', 'myprotector-platform');

// Environment
define('MYPROTECTOR_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
define('MYPROTECTOR_LOG_LEVEL', 'debug'); // debug, info, warning, error

// Database
define('MYPROTECTOR_TABLE_PREFIX', 'mp_');

// Cache
define('MYPROTECTOR_CACHE_ENABLED', true);
define('MYPROTECTOR_CACHE_TTL', 3600); // 1 hour

// Email
define('MYPROTECTOR_EMAIL_ENABLED', true);
define('MYPROTECTOR_EMAIL_BATCH_SIZE', 100);

// API
define('MYPROTECTOR_API_NAMESPACE', 'myprotector/v1');
define('MYPROTECTOR_API_TIMEOUT', 30);

// Performance
define('MYPROTECTOR_ASSETS_VERSION', MYPROTECTOR_VERSION);
define('MYPROTECTOR_MINIFY_ASSETS', !MYPROTECTOR_DEBUG);
```

---

## 9. Main Plugin File Structure

```php
<?php
/**
 * Plugin Name: MyProtector Platform
 * Plugin URI: https://myprotector.example.com
 * Description: Trustpilot-style review platform for WordPress
 * Version: 1.0.0
 * Author: MyProtector Team
 * Author URI: https://myprotector.example.com
 * License: Proprietary
 * Text Domain: myprotector-platform
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

namespace MyProtector;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Autoloader
require_once __DIR__ . '/myprotector-platform-loader.php';

// Bootstrap the plugin
use MyProtector\Core\MyProtector;

function myprotector(): MyProtector {
    return MyProtector::getInstance();
}

// Initialize
myprotector()->run();
```

---

## 10. Autoloader

```php
// myprotector-platform-loader.php
namespace MyProtector;

spl_autoload_register(function (string $class): void {
    // Project namespace
    $prefix = 'MyProtector\\';
    
    // Base directory
    $base_dir = __DIR__ . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
```

---

## 11. Module Registration

```php
// modules.php - Module registry
return [
    \MyProtector\Modules\Reviews\Reviews::class,
    \MyProtector\Modules\BusinessProfiles\BusinessProfiles::class,
    \MyProtector\Modules\TrafficSignals\TrafficSignals::class,
    \MyProtector\Modules\Dashboards\Dashboards::class,
    \MyProtector\Modules\Resellers\Resellers::class,
    \MyProtector\Modules\Widgets\Widgets::class,
    \MyProtector\Modules\Emails\Emails::class,
    \MyProtector\Modules\WooCommerce\WooCommerce::class,
    \MyProtector\Modules\Admin\Admin::class,
];
```

---

*Document Version: 1.0*
*Generated: 2026-06-02*