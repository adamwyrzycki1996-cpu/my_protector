# MyProtector Technical Architecture Document
## Trustpilot-Style Review Platform on WordPress

**Version:** 1.0  
**Date:** 2026-06-02  
**Status:** Stage 1 Planning  
**Author:** Senior WordPress SaaS Architect

---

## 1. Executive Summary

MyProtector is a comprehensive review platform designed to provide trust verification for businesses and consumers. Built on WordPress, it combines the proven Trustpilot-style review mechanics with advanced trust signaling, multi-role support, and e-commerce integration capabilities.

This architecture document outlines the complete technical implementation strategy, database design, and development roadmap for delivering a production-ready platform.

---

## 2. Feature Breakdown

### 2.1 Complete Feature Inventory

#### Core Review System
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Review Submission | P0 | High | Users can submit reviews with ratings, text, images |
| Review Moderation | P0 | High | Admin approval workflow for reviews |
| Review Responses | P0 | High | Businesses can respond to reviews |
| Review Display Widgets | P0 | High | 3 embeddable widget types for external sites |
| Review Schema/SEO | P0 | Medium | Structured data for search engines |
| Review Filtering | P1 | Medium | Filter by rating, date, category |
| Review Sorting | P1 | Low | Sort by date, rating, usefulness |

#### Trust Signal (Traffic Light) System
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Traffic Light Display | P0 | High | Visual trust indicators on profiles |
| Automatic Calculation | P0 | High | Algorithm based on review metrics |
| Manual Override | P1 | Medium | Admin can adjust trust status |
| Walking/Shopping/Bad Status | P1 | High | Three-tier trust categorization |
| Auto-Green Trigger | P2 | Medium | Auto-upgrade when all requirements met |

#### User Management & Roles
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Individual Registration | P0 | Medium | Consumer account creation |
| Business Registration | P0 | High | Business profile claiming |
| Admin Accounts | P0 | Low | Full platform administration |
| Customer Support | P0 | Medium | Support ticket management |
| Reseller Accounts | P1 | High | Commission-based referral system |
| Password Reset/Recovery | P0 | Low | Email-based recovery |
| Profile Editing | P0 | Low | User details management |

#### Business Features
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Business Profile Creation | P0 | High | Company information pages |
| Business Dashboard | P0 | High | Analytics and management console |
| Review Response System | P0 | High | Reply to customer reviews |
| API Download | P2 | High | Developer API access |
| Widget Options (4 types) | P1 | High | Multiple embeddable display options |
| Company Promise Page | P2 | Medium | Custom commitment page |
| Terms & Conditions Link | P0 | Low | Required legal page link |
| Insurance Company Fields | P2 | Medium | Specialized business data |

#### Admin & Moderation
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Review Approval Queue | P0 | High | Manual review moderation |
| Auto-Approval Toggle | P2 | High | AI-assisted approval system |
| Edit Reviews | P1 | Medium | Admin review editing |
| Manual Override Controls | P1 | Medium | Dual approval options |
| Blacklist Management | P2 | High | Block users/businesses |
| Evidence Upload (PDF) | P2 | High | Blacklist justification |
| Bug Reporting System | P2 | Medium | Issue tracking |

#### Reseller System
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Reseller Registration | P1 | High | Partner signup flow |
| Referral Tracking | P1 | High | Unique tracking links |
| Commission Dashboard | P1 | High | Payment tracking |
| Commission Payouts | P2 | High | Payment release system |
| Accounting Reports | P2 | High | Financial documentation |
| Payment Processing | P2 | High | Invoice generation |

#### E-commerce Integration
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| WooCommerce Plugin | P1 | High | Review collection integration |
| Spotify Integration | P2 | Medium | Music service trust display |
| Major Platform Plugins | P2 | High | Trustpilot-style integrations |
| Widget Performance | P1 | High | Optimized loading |

#### Communication System
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Email Invitations | P0 | High | Review request emails |
| 40+ Email Templates | P1 | Very High | Comprehensive email system |
| Email Campaigns | P2 | Very High | Bulk marketing emails |
| Live Chat (WhatsApp) | P2 | High | Real-time support |
| Social Sharing | P1 | Medium | Share buttons on reviews |

#### Content Management
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Custom Page SEO | P0 | Medium | Per-page meta customization |
| Page Content Editing | P0 | Medium | Visual page builder |
| Blog System | P1 | High | WordPress blog integration |
| Yoast SEO Activation | P1 | Medium | SEO plugin configuration |
| HTML Sitemap | P1 | Low | User-friendly navigation |
| XML Sitemap | P0 | Low | Auto-updating search sitemap |
| YouTube Videos | P2 | Medium | Customizable page videos |
| Authority Links | P2 | Medium | Customizable external links |

#### Social & Marketing
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| X (Twitter) Integration | P2 | Medium | Social feed/buttons |
| Instagram Connection | P2 | Medium | Visual content feed |
| LinkedIn Integration | P2 | Medium | Business verification |
| Share Buttons | P1 | Low | Review sharing |
| Authorship Validation | P2 | Medium | Reviewer verification |

#### Reporting & Analytics
| Feature | Priority | Complexity | Description |
|---------|----------|------------|-------------|
| Filter Reporting | P2 | High | Business analytics filters |
| Review History | P1 | Medium | Complete audit trail |
| Dashboard Analytics | P0 | High | Real-time statistics |
| Audit Date/Time Display | P0 | Medium | Page authority indicators |

---

## 3. Stage 1 Scope (Milestone: $750)

### 3.1 Critical Path Features (Must Complete)

#### Content & Branding Changes
- [ ] Replace all Trustpilot references with MyProtector branding
- [ ] Update all headings, tags, meta descriptions
- [ ] Replace all images with MyProtector assets
- [ ] Update button text and labels
- [ ] Change URL slugs from trustpilot to myprotector
- [ ] Update footer, header, navigation elements
- [ ] Replace favicon, logo, and brand imagery

#### User Account Systems
- [ ] **Individual Dashboard** - Full functionality
  - Profile viewing and editing
  - Submitted reviews management
  - Password change capability
  - Account deletion option
  - Review reminders settings
  
- [ ] **Business Dashboard** - Full functionality
  - Profile claiming workflow
  - Company information editing
  - Review response capability
  - Basic analytics view
  - Widget code access
  
- [ ] **Admin Dashboard** - Full functionality
  - User management (all roles)
  - Review moderation queue
  - Content editing capabilities
  - Site-wide settings
  - Blacklist management access
  
- [ ] **Customer Support Dashboard** - Full functionality
  - Ticket viewing and management
  - User communication
  - Escalation procedures
  - Basic reporting

#### Email System
- [ ] Review invitation email setup
- [ ] Email template configuration
- [ ] SMTP integration for sending
- [ ] Review submission confirmation emails
- [ ] Account-related notifications

#### Review System
- [ ] Review submission form
- [ ] Review display on business pages
- [ ] Basic rating calculation
- [ ] Traffic light system (basic)
- [ ] Review approval workflow

#### Business Features
- [ ] New company registration
- [ ] Company profile pages
- [ ] Basic review responses
- [ ] Traffic light display

#### Widget System
- [ ] Widget 1: Classic Badge (Star ratings)
- [ ] Widget 2: Mini Badge (Compact)
- [ ] Widget 3: Reviews Slider
- [ ] WooCommerce Plugin integration

#### Content & SEO
- [ ] Page-by-page SEO customization
- [ ] Content editor for all pages
- [ ] HTML sitemap
- [ ] XML sitemap with auto-update
- [ ] Yoast SEO configuration
- [ ] Blog section setup

#### Developer Profile (Co-Founder)
- [ ] Author page with bio
- [ ] LinkedIn profile link
- [ ] Social media connections
- [ ] About Us page inclusion

#### Site Configuration
- [ ] Domain transfer/setup
- [ ] SSL certificate
- [ ] Real company name/contact info
- [ ] Social media links (X, Instagram, LinkedIn)
- [ ] Share buttons on reviews

#### Authority & Compliance
- [ ] Audit date/time on each page
- [ ] Reviewer sign-off capability
- [ ] Content validation display

### 3.2 Stage 1 Exclusions (Defer to Stage 2+)
- Advanced AI review moderation
- Reseller dashboard and commission system
- Full accounting/invoice system
- 40+ email templates (basic only in Stage 1)
- API download system
- Subdomain/database separation
- Blacklist public reporting system
- Email campaign system
- Advanced video integrations
- Authority link customization

---

## 4. Database Design

### 4.1 WordPress Core Tables (Extended)

```
┌─────────────────────────────────────────────────────────────────┐
│                    WORDPRESS CORE TABLES                        │
├─────────────────────────────────────────────────────────────────┤
│ wp_users           - Core user accounts                         │
│ wp_usermeta        - User metadata                              │
│ wp_posts           - Posts, Pages, Reviews (post_type based)    │
│ wp_postmeta        - Post/Review metadata                       │
│ wp_comments        - Standard WordPress comments                │
│ wp_terms           - Categories, Tags, Business categories      │
│ wp_term_taxonomy   - Term taxonomy definitions                  │
│ wp_term_relationships - Post-term relationships                │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Custom Database Tables

#### Table: mp_companies
```sql
CREATE TABLE mp_companies (
    company_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    company_name        VARCHAR(255) NOT NULL,
    company_slug        VARCHAR(255) UNIQUE NOT NULL,
    company_description TEXT,
    company_website     VARCHAR(500),
    company_logo        VARCHAR(500),
    company_address     TEXT,
    company_phone       VARCHAR(50),
    company_email       VARCHAR(255),
    company_category    BIGINT UNSIGNED,
    insurance_name      VARCHAR(255),
    insurance_url       VARCHAR(500),
    terms_url           VARCHAR(500),
    promise_page_url    VARCHAR(500),
    promise_page_title  VARCHAR(255),
    status              ENUM('pending', 'claimed', 'verified', 'suspended') DEFAULT 'pending',
    trust_score         DECIMAL(3,2) DEFAULT 0.00,
    total_reviews       INT UNSIGNED DEFAULT 0,
    avg_rating          DECIMAL(2,1) DEFAULT 0.0,
    is_featured         BOOLEAN DEFAULT FALSE,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (company_category),
    INDEX idx_slug (company_slug),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (company_category) REFERENCES wp_terms(term_id)
);
```

#### Table: mp_reviews
```sql
CREATE TABLE mp_reviews (
    review_id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id          BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    review_title        VARCHAR(255) NOT NULL,
    review_content      TEXT NOT NULL,
    review_rating       TINYINT UNSIGNED NOT NULL CHECK (review_rating BETWEEN 1 AND 5),
    review_status       ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    trust_level         ENUM('unverified', 'verified', 'premium') DEFAULT 'unverified',
    ip_address          VARCHAR(45),
    is_published        BOOLEAN DEFAULT FALSE,
    published_at        DATETIME,
    helpful_count       INT UNSIGNED DEFAULT 0,
    report_count        INT UNSIGNED DEFAULT 0,
    is_featured         BOOLEAN DEFAULT FALSE,
    ai_analyzed         BOOLEAN DEFAULT FALSE,
    ai_sentiment        VARCHAR(20),
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company_id),
    INDEX idx_user (user_id),
    INDEX idx_status (review_status),
    INDEX idx_rating (review_rating),
    INDEX idx_published (is_published, published_at),
    FOREIGN KEY (company_id) REFERENCES mp_companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);
```

#### Table: mp_review_responses
```sql
CREATE TABLE mp_review_responses (
    response_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id           BIGINT UNSIGNED NOT NULL,
    company_id          BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NOT NULL,
    response_content    TEXT NOT NULL,
    is_official         BOOLEAN DEFAULT TRUE,
    status              ENUM('pending', 'published', 'hidden') DEFAULT 'pending',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_review (review_id),
    INDEX idx_company (company_id),
    FOREIGN KEY (review_id) REFERENCES mp_reviews(review_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES mp_companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);
```

#### Table: mp_review_images
```sql
CREATE TABLE mp_review_images (
    image_id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_id           BIGINT UNSIGNED NOT NULL,
    image_url           VARCHAR(500) NOT NULL,
    image_type          ENUM('review', 'blacklist_evidence') DEFAULT 'review',
    caption             VARCHAR(255),
    is_approved         BOOLEAN DEFAULT FALSE,
    uploaded_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_review (review_id),
    FOREIGN KEY (review_id) REFERENCES mp_reviews(review_id) ON DELETE CASCADE
);
```

#### Table: mp_traffic_light_status
```sql
CREATE TABLE mp_traffic_light_status (
    status_id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id          BIGINT UNSIGNED NOT NULL UNIQUE,
    trust_status        ENUM('walking', 'shopping', 'bad') DEFAULT 'bad',
    trust_score         DECIMAL(5,2) DEFAULT 0.00,
    traffic_light_color ENUM('green', 'yellow', 'red') DEFAULT 'red',
    requirements_met     JSON,
    requirements_total  INT DEFAULT 5,
    insurance_added     BOOLEAN DEFAULT FALSE,
    terms_added         BOOLEAN DEFAULT FALSE,
    promise_page_added  BOOLEAN DEFAULT FALSE,
    admin_verified      BOOLEAN DEFAULT FALSE,
    manual_override     BOOLEAN DEFAULT FALSE,
    override_reason     TEXT,
    override_by         BIGINT UNSIGNED,
    last_calculated     DATETIME,
    calculated_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES mp_companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (override_by) REFERENCES wp_users(ID)
);
```

#### Table: mp_resellers
```sql
CREATE TABLE mp_resellers (
    reseller_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    referral_code       VARCHAR(50) UNIQUE NOT NULL,
    commission_rate     DECIMAL(4,2) DEFAULT 10.00,
    total_referrals     INT UNSIGNED DEFAULT 0,
    total_earnings      DECIMAL(12,2) DEFAULT 0.00,
    pending_commission  DECIMAL(12,2) DEFAULT 0.00,
    paid_commission     DECIMAL(12,2) DEFAULT 0.00,
    payment_threshold   DECIMAL(10,2) DEFAULT 100.00,
    payout_method       ENUM('bank', 'paypal', 'stripe') DEFAULT 'bank',
    payout_details      JSON,
    status              ENUM('active', 'suspended', 'pending') DEFAULT 'pending',
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
);
```

#### Table: mp_referrals
```sql
CREATE TABLE mp_referrals (
    referral_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reseller_id         BIGINT UNSIGNED NOT NULL,
    company_id          BIGINT UNSIGNED,
    referred_email      VARCHAR(255),
    referral_status     ENUM('pending', 'registered', 'upgraded', 'cancelled') DEFAULT 'pending',
    commission_earned   DECIMAL(10,2) DEFAULT 0.00,
    commission_paid     BOOLEAN DEFAULT FALSE,
    converted_at        DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reseller (reseller_id),
    INDEX idx_company (company_id),
    FOREIGN KEY (reseller_id) REFERENCES mp_resellers(reseller_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES mp_companies(company_id) ON DELETE SET NULL
);
```

#### Table: mp_support_tickets
```sql
CREATE TABLE mp_support_tickets (
    ticket_id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED NOT NULL,
    ticket_subject      VARCHAR(255) NOT NULL,
    ticket_content      TEXT NOT NULL,
    ticket_category     ENUM('general', 'review', 'business', 'technical', 'billing') DEFAULT 'general',
    ticket_priority     ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    ticket_status       ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    assigned_to         BIGINT UNSIGNED,
    admin_response      TEXT,
    resolved_at         DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (ticket_status),
    INDEX idx_assigned (assigned_to),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES wp_users(ID) ON DELETE SET NULL
);
```

#### Table: mp_blacklist
```sql
CREATE TABLE mp_blacklist (
    blacklist_id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entry_type         ENUM('individual', 'business') NOT NULL,
    user_id             BIGINT UNSIGNED,
    company_id          BIGINT UNSIGNED,
    reason              TEXT NOT NULL,
    evidence_files      JSON,
    reported_by         BIGINT UNSIGNED,
    status              ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by         BIGINT UNSIGNED,
    approved_at         DATETIME,
    expires_at          DATETIME,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_user (entry_type, user_id),
    INDEX idx_type_company (entry_type, company_id),
    INDEX idx_status (status),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES mp_companies(company_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES wp_users(ID),
    FOREIGN KEY (approved_by) REFERENCES wp_users(ID)
);
```

#### Table: mp_email_templates
```sql
CREATE TABLE mp_email_templates (
    template_id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_key        VARCHAR(100) UNIQUE NOT NULL,
    template_name       VARCHAR(255) NOT NULL,
    template_subject    VARCHAR(255) NOT NULL,
    template_body       TEXT NOT NULL,
    template_type       ENUM('transactional', 'marketing', 'notification', 'system') DEFAULT 'transactional',
    is_active           BOOLEAN DEFAULT TRUE,
    variables           JSON,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Table: mp_audit_log
```sql
CREATE TABLE mp_audit_log (
    log_id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             BIGINT UNSIGNED,
    action_type         VARCHAR(100) NOT NULL,
    entity_type         VARCHAR(50),
    entity_id           BIGINT UNSIGNED,
    old_value           JSON,
    new_value           JSON,
    ip_address          VARCHAR(45),
    user_agent          TEXT,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE SET NULL
);
```

#### Table: mp_page_settings
```sql
CREATE TABLE mp_page_settings (
    setting_id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_id             BIGINT UNSIGNED NOT NULL,
    seo_title           VARCHAR(255),
    seo_description     TEXT,
    seo_keywords        VARCHAR(500),
    og_image            VARCHAR(500),
    canonical_url       VARCHAR(500),
    schema_markup       JSON,
    custom_css          TEXT,
    custom_js           TEXT,
    authority_video_url VARCHAR(500),
    authority_links     JSON,
    last_edited_by      BIGINT UNSIGNED,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES wp_posts(ID) ON DELETE CASCADE,
    FOREIGN KEY (last_edited_by) REFERENCES wp_users(ID)
);
```

---

## 5. WordPress Architecture

### 5.1 Directory Structure
```
my-protector/
├── wp-content/
│   ├── plugins/
│   │   ├── myprotector-core/                 # Main Plugin
│   │   │   ├── myprotector-core.php
│   │   │   ├── includes/
│   │   │   │   ├── class-loader.php
│   │   │   │   ├── class-database.php
│   │   │   │   ├── class-shortcodes.php
│   │   │   │   ├── class-widgets.php
│   │   │   │   ├── class-rest-api.php
│   │   │   │   ├── class-webhooks.php
│   │   │   │   ├── admin/
│   │   │   │   │   ├── class-admin.php
│   │   │   │   │   ├── class-menu-pages.php
│   │   │   │   │   ├── class-meta-boxes.php
│   │   │   │   │   └── partials/
│   │   │   │   ├── public/
│   │   │   │   │   ├── class-public.php
│   │   │   │   │   ├── templates/
│   │   │   │   │   └── partials/
│   │   │   │   ├── reviews/
│   │   │   │   │   ├── class-reviews-controller.php
│   │   │   │   │   ├── class-review-validator.php
│   │   │   │   │   └── class-review-analytics.php
│   │   │   │   ├── companies/
│   │   │   │   │   ├── class-companies-controller.php
│   │   │   │   │   └── class-company-verification.php
│   │   │   │   ├── trust/
│   │   │   │   │   ├── class-traffic-light.php
│   │   │   │   │   └── class-trust-calculator.php
│   │   │   │   ├── emails/
│   │   │   │   │   ├── class-email-templates.php
│   │   │   │   │   └── class-email-sender.php
│   │   │   │   └── utilities/
│   │   │   │       ├── class-helpers.php
│   │   │   │       └── class-security.php
│   │   │   ├── assets/
│   │   │   │   ├── css/
│   │   │   │   ├── js/
│   │   │   │   └── images/
│   │   │   ├── languages/
│   │   │   └── templates/
│   │   │
│   │   ├── myprotector-woocommerce/          # WooCommerce Integration
│   │   │   ├── myprotector-woocommerce.php
│   │   │   └── includes/
│   │   │
│   │   ├── myprotector-reseller/              # Reseller System
│   │   │   └── includes/
│   │   │
│   │   ├── myprotector-admin-dashboard/       # Admin Dashboard
│   │   │   └── includes/
│   │   │
│   │   └── myprotector-widget/                # Embeddable Widget
│   │       └── dist/
│   │
│   ├── themes/
│   │   └── myprotector-theme/
│   │       ├── style.css
│   │       ├── functions.php
│   │       ├── template-parts/
│   │       │   ├── header/
│   │       │   ├── footer/
│   │       │   ├── review/
│   │       │   ├── company/
│   │       │   └── dashboard/
│   │       ├── page-templates/
│   │       │   ├── dashboard-individual.php
│   │       │   ├── dashboard-business.php
│   │       │   ├── dashboard-admin.php
│   │       │   ├── dashboard-support.php
│   │       │   ├── dashboard-reseller.php
│   │       │   ├── company-profile.php
│   │       │   └── write-review.php
│   │       ├── assets/
│   │       ├── js/
│   │       └── languages/
│   │
│   └── mu-plugins/
│       └── myprotector-mu/
```

### 5.2 Theme Structure
```
myprotector-theme/
├── style.css (Theme styles with custom properties)
├── functions.php
├── header.php
├── footer.php
├── front-page.php (Landing page)
├── single-company.php
├── single-review.php
├── archive-company.php
├── page-dashboard.php
├── page-about.php
├── page-contact.php
├── page-privacy-policy.php
├── page-terms.php
└── template-parts/
    ├── content-hero.php
    ├── content-traffic-light.php
    ├── content-review-card.php
    ├── content-company-card.php
    └── content-dashboard-nav.php
```

### 5.3 Plugin Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    PLUGIN DEPENDENCY GRAPH                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │              MyProtector Core (Required)                │    │
│  │  • Reviews • Companies • Traffic Light • Base REST API   │    │
│  └──────────────────────────┬──────────────────────────────┘    │
│                             │                                    │
│         ┌───────────────────┼───────────────────┐                │
│         │                   │                   │                │
│         ▼                   ▼                   ▼                │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐        │
│  │ WooCommerce  │   │   Reseller   │   │Admin Dashboard│        │
│  │ Integration  │   │    System    │   │    Panel      │        │
│  └──────────────┘   └──────────────┘   └──────────────┘        │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │              Shared: REST API Endpoints                   │   │
│  │              Shared: Shortcodes                           │   │
│  │              Shared: Email System                         │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. User Roles & Capabilities

### 6.1 Role Hierarchy

```
                    ┌──────────────┐
                    │   Super Admin│ (WordPress Network Admin)
                    └──────┬───────┘
                           │
                           ▼
┌──────────────────────────────────────────────────────────────────┐
│                                                                  │
│   ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│   │    Admin    │    │  Customer   │    │   Reseller  │         │
│   │  (Full)     │    │  Support    │    │  (Partner)  │         │
│   └──────┬──────┘    └──────┬──────┘    └──────┬──────┘         │
│          │                  │                  │                  │
│          │    ┌─────────────┼─────────────┐   │                  │
│          │    │             │             │   │                  │
│          ▼    │             ▼             │   ▼                  │
│   ┌─────────────┐         ┌─────────────┐      ┌─────────────┐  │
│   │   Business  │         │  Individual │      │   Business  │  │
│   │  (Premium)   │         │  (Consumer) │      │  (Referred) │  │
│   └─────────────┘         └─────────────┘      └─────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### 6.2 Detailed Role Definitions

#### Administrator
```php
// Role Key: 'administrator' (WordPress default extended)
$admin_capabilities = [
    // Core Platform
    'manage_myprotector_options',     // Site-wide settings
    'view_all_reviews',               // See all reviews
    'moderate_all_reviews',           // Approve/reject any review
    'edit_any_review',                // Modify any review
    
    // User Management
    'manage_myprotector_users',       // Full user management
    'promote_users',                  // Change user roles
    'ban_users',                      // Suspend accounts
    
    // Business Management
    'manage_all_companies',           // Full company control
    'verify_companies',               // Verify business claims
    'override_trust_status',          // Manual traffic light override
    
    // Content
    'edit_pages',                     // Page editing
    'edit_others_pages',              // Edit any page
    'publish_pages',                  // Publish pages
    'manage_categories',              // Category management
    
    // Blacklist & Compliance
    'manage_blacklist',               // Full blacklist control
    'approve_blacklist_entries',      // Approve reported items
    
    // Reporting
    'view_analytics',                 // Full analytics access
    'export_reports',                 // Export any report
    
    // System
    'manage_myprotector_settings',    // Plugin settings
    'manage_myprotector_tools',       // Developer tools
];
```

#### Customer Support
```php
// Role Key: 'mp_customer_support'
$support_capabilities = [
    // Reviews
    'view_all_reviews',
    'respond_to_reviews',
    'flag_reviews',
    
    // Users
    'view_users',
    'edit_user_details',
    'reset_user_passwords',
    
    // Tickets
    'manage_support_tickets',
    'respond_to_tickets',
    'escalate_tickets',
    
    // Limited Business
    'view_company_profiles',
    
    // Reporting
    'view_basic_analytics',
    'view_ticket_reports',
];
```

#### Business (Premium)
```php
// Role Key: 'mp_business'
$business_capabilities = [
    // Company Profile
    'edit_own_company',
    'upload_company_logo',
    'add_company_images',
    'update_company_info',
    
    // Reviews
    'view_company_reviews',
    'respond_to_own_reviews',
    'request_review_removal',
    
    // Responses
    'post_official_responses',
    'edit_own_responses',
    
    // Widgets
    'access_review_widgets',
    'generate_widget_code',
    
    // Analytics
    'view_own_analytics',
    'export_own_reports',
    
    // Settings
    'manage_notification_settings',
    'manage_team_members',
];
```

#### Individual (Consumer)
```php
// Role Key: 'subscriber' (WordPress default)
$individual_capabilities = [
    // Profile
    'read',
    'edit_profile',
    'upload_avatar',
    
    // Reviews
    'create_reviews',
    'edit_own_reviews',
    'delete_own_reviews',
    'upload_review_images',
    
    // Engagement
    'mark_reviews_helpful',
    'report_reviews',
    
    // Communication
    'submit_support_tickets',
    'view_own_tickets',
];
```

#### Reseller (Partner)
```php
// Role Key: 'mp_reseller'
$reseller_capabilities = [
    // Referrals
    'create_referral_links',
    'track_own_referrals',
    'view_referral_analytics',
    
    // Earnings
    'view_own_earnings',
    'request_payouts',
    'view_payment_history',
    
    // Company
    'create_linked_companies',
    'view_linked_company_stats',
    
    // Resources
    'access_marketing_materials',
    'access_api_documentation',
    
    // Profile
    'edit_own_profile',
    'update_payment_details',
];
```

---

## 7. Dashboard Structure

### 7.1 Admin Dashboard

```
┌────────────────────────────────────────────────────────────────────┐
│  MyProtector Admin Dashboard                                       │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐  │
│  │   Reviews   │ │  Companies  │ │    Users    │ │   Support   │  │
│  │    1,234    │ │     456     │ │    5,678    │ │     89      │  │
│  │  Pending: 23│ │   Active:   │ │  Online: 45 │ │   Open: 12  │  │
│  │             │ │     234     │ │             │ │             │  │
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘  │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │  Recent Activity Feed                                       │   │
│  │  • New review on TechCorp (★★★★☆) - 2 min ago              │   │
│  │  • Company "ShopEasy" claimed - 15 min ago                 │   │
│  │  • User john@example.com registered - 1 hour ago            │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌─────────────────────────────┐ ┌────────────────────────────┐  │
│  │  Moderation Queue            │ │  Traffic Light Summary     │  │
│  │  ┌─────────────────────────┐ │ │  🟢 Walking:    156       │  │
│  │  │ [Flagged] Review #1234  │ │ │  🟡 Shopping:   67        │  │
│  │  │ from user@example.com   │ │ │  🔴 Bad:        23        │  │
│  │  │ [Approve] [Reject]      │ │ │                            │  │
│  │  └─────────────────────────┘ │ │  [View All Companies]     │  │
│  └─────────────────────────────┘ └────────────────────────────┘  │
│                                                                     │
│  Navigation:                                                        │
│  • Dashboard  • Reviews  • Companies  • Users  • Support Tickets  │
│  • Resellers  • Blacklist  • Reports  • Settings  • SEO Tools      │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### 7.2 Admin Dashboard Features

#### Reviews Management
- Approval queue with filters
- Bulk approve/reject actions
- AI auto-moderation toggle
- Review editor with audit trail
- Manual status override
- Featured review selection

#### Companies Management
- Company listing with status
- Verification workflow
- Traffic light configuration
- Domain verification tools
- Insurance/Terms/Promise tracking
- Auto-green trigger settings

#### Users Management
- User table with roles
- Account suspension
- Password reset
- Activity history
- Trust score view

#### Reports & Analytics
- Review trends graph
- Company performance
- User registration stats
- Traffic sources
- Export to CSV/PDF

#### Settings
- Email template editor
- Notification settings
- Widget configuration
- SEO defaults
- API keys

### 7.3 Business Dashboard

```
┌────────────────────────────────────────────────────────────────────┐
│  MyProtector Business Dashboard                     [Company: ABC]│
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │  ⭐ Trust Score: 4.5  │  🟢 Walking  │  Reviews: 234         │  │
│  └──────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌─────────────────────┐ ┌─────────────────────┐ ┌──────────────┐ │
│  │ This Month          │ │ Average Rating      │ │ Responses    │ │
│  │ ★★★★☆ 4.2          │ │ 4.5/5.0             │ │ 45/234 (19%)  │ │
│  │ 12 new reviews      │ │ Trend: ↑ +0.2       │ │ Avg: 2.4 hrs  │ │
│  └─────────────────────┘ └─────────────────────┘ └──────────────┘ │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │  Recent Reviews                                             │   │
│  │  ┌──────────────────────────────────────────────────────┐ │   │
│  │  │ ⭐⭐⭐⭐⭐ "Excellent service!" - John D.              │ │   │
│  │  │ Great product quality and fast delivery...            │ │   │
│  │  │ [Respond]  [Mark Helpful]  [Report]                   │ │   │
│  │  └──────────────────────────────────────────────────────┘ │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  Quick Actions:                                                     │
│  [📝 Invite Reviews] [📊 View Analytics] [🔧 Settings]            │
│  [📋 Get Widget Code] [👥 Team Members]                             │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### 7.4 Individual Dashboard

```
┌────────────────────────────────────────────────────────────────────┐
│  MyProtector My Account                               [👤 John D.] │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Profile Completion: ████████░░ 80%                                │
│                                                                     │
│  ┌─────────────────────┐ ┌─────────────────────┐                  │
│  │ My Reviews          │ │ Helpful Marks       │                  │
│  │ 5 reviews written   │ │ 23 marked helpful   │                  │
│  │ 2 pending           │ │                     │                  │
│  └─────────────────────┘ └─────────────────────┘                  │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │  My Recent Activity                                         │   │
│  │  • Review on TechCorp - ★★★★★ - 2 days ago                  │   │
│  │  • Review on ShopEasy - ★★★☆☆ - 1 week ago                 │   │
│  │  • Marked "Great service" helpful - 3 days ago               │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  My Reviews: [View All] [Write New Review]                         │
│                                                                     │
│  Account: [Edit Profile] [Change Password] [Notification Settings] │
│  [Submit Ticket] [Download My Data] [Delete Account]              │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### 7.5 Customer Support Dashboard

```
┌────────────────────────────────────────────────────────────────────┐
│  Support Dashboard                                    [Online: 3] │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Queue Summary:                                                    │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐              │
│  │  Open    │ │In Progress│ │ Pending  │ │ Resolved │              │
│  │   12     │ │    8      │ │   5      │ │   156    │              │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘              │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Active Tickets                                             │   │
│  │  ┌─────────────────────────────────────────────────────┐   │   │
│  │  │ [#2345] Review dispute - User: john@... - URGENT     │   │   │
│  │  │ [Take Ticket] [Escalate to Admin]                     │   │   │
│  │  └─────────────────────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  Quick Links:                                                       │
│  [🔍 User Lookup] [📋 Common Solutions] [📊 Weekly Report]          │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### 7.6 Reseller Dashboard

```
┌────────────────────────────────────────────────────────────────────┐
│  Partner Dashboard                              [Commission: 10%]   │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Earnings Overview:                                                │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐                │
│  │ Total Earned │ │   Pending   │ │   Paid Out   │                │
│  │   $2,450.00  │ │   $350.00   │ │  $2,100.00   │                │
│  └──────────────┘ └──────────────┘ └──────────────┘                │
│                                                                     │
│  Your Referral Code: MYPROTECTOR-JOHN123                            │
│  [Copy Link] [Share on LinkedIn] [Email Template]                  │
│                                                                     │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │  Referral Performance                                       │   │
│  │  Total Referrals: 45  │  Converted: 23  │  Rate: 51%       │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  Recent Conversions:                                                │
│  • TechCorp - Registered - $50 commission - 3 days ago           │
│  • ShopEasy - Upgraded to Premium - $100 commission - 1 week ago │
│                                                                     │
│  [Request Payout] [View Full Report] [Marketing Materials]         │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

---

## 8. Custom Post Types

### 8.1 CPT: Companies (mp_company)
```php
register_post_type('mp_company', [
    'labels' => [
        'name' => 'Companies',
        'singular_name' => 'Company',
        'add_new' => 'Add Company',
        'edit_item' => 'Edit Company',
        'view_item' => 'View Company',
    ],
    'public' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'companies'],
    'show_in_rest' => true,
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
    'menu_icon' => 'dashicons-building',
    'taxonomies' => ['mp_company_category'],
]);
```

### 8.2 CPT: Reviews (mp_review)
```php
register_post_type('mp_review', [
    'labels' => [
        'name' => 'Reviews',
        'singular_name' => 'Review',
        'add_new' => 'Add Review',
        'edit_item' => 'Edit Review',
    ],
    'public' => true,
    'has_archive' => false,
    'rewrite' => ['slug' => 'reviews'],
    'show_in_rest' => true,
    'supports' => ['title', 'editor', 'author', 'custom-fields', 'comments'],
    'menu_icon' => 'dashicons-star-filled',
    'capability_type' => 'post',
    'capabilities' => [
        'edit_post' => 'edit_review',
        'read_post' => 'read_review',
        'delete_post' => 'delete_review',
    ],
]);
```

### 8.3 CPT: Blog Posts (post - WordPress Default)
- Standard WordPress posts
- Yoast SEO integration
- Author pages with Co-Founder profiles
- Categories and tags

### 8.4 Custom Taxonomies

```php
// Company Categories
register_taxonomy('mp_company_category', 'mp_company', [
    'labels' => [
        'name' => 'Company Categories',
        'singular_name' => 'Category',
    ],
    'hierarchical' => true,
    'show_in_rest' => true,
    'rewrite' => ['slug' => 'company-category'],
]);

// Review Tags
register_taxonomy('mp_review_tag', 'mp_review', [
    'labels' => [
        'name' => 'Review Tags',
        'singular_name' => 'Tag',
    ],
    'hierarchical' => false,
    'show_in_rest' => true,
    'rewrite' => ['slug' => 'review-tag'],
]);

// Ticket Categories
register_taxonomy('mp_ticket_category', 'mp_ticket', [
    'labels' => [
        'name' => 'Ticket Categories',
        'singular_name' => 'Category',
    ],
    'hierarchical' => true,
]);
```

---

## 9. WooCommerce Integration Plan

### 9.1 Plugin Components

```
┌─────────────────────────────────────────────────────────────────┐
│            MyProtector WooCommerce Integration                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  Order Trigger  │  │  Review Widget  │  │  Trust Display  │  │
│  │     Module      │  │     Module      │  │     Module      │  │
│  └────────┬────────┘  └────────┬────────┘  └────────┬────────┘  │
│           │                    │                    │            │
│           └────────────────────┼────────────────────┘            │
│                                │                                  │
│                    ┌───────────┴───────────┐                      │
│                    │   WooCommerce API     │                      │
│                    │   Integration Layer   │                      │
│                    └───────────────────────┘                      │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 9.2 Integration Features

#### Order-to-Review Flow
1. Order completion triggers review invitation
2. Customer receives email with direct review link
3. Review is linked to specific order/product
4. Verified purchase badge displayed

#### Trust Widget Placement
- Product page: Below add-to-cart button
- Cart page: Order summary section
- Checkout confirmation: Thank you page
- Account pages: Order history integration

#### Data Synchronization
```php
// Hooks used:
add_action('woocommerce_order_status_completed', 'mp_send_review_invitation');
add_action('woocommerce_new_order', 'mp_create_order_reference');
add_filter('woocommerce_product_get_rating', 'mp_override_product_rating');
add_action('woocommerce_customer_created', 'mp_link_customer_to_platform');
```

### 9.3 Widget Display Options

| Widget Type | Location | Size | Features |
|-------------|----------|------|----------|
| Classic Badge | Product Page | 200x100 | Stars + Count |
| Mini Badge | Cart | 100x50 | Compact rating |
| Slider | Footer | 300x200 | Rotating reviews |
| Popup | All Pages | 400x300 | Click to expand |

---

## 10. Development Roadmap

### Phase 1: Foundation (Week 1-2) - $750 Milestone
```
Deliverables:
✓ Custom database tables created
✓ User roles and capabilities configured
✓ Basic theme structure established
✓ Core plugin architecture
✓ Page templates for all dashboards
✓ All Trustpilot references replaced with MyProtector branding
✓ Custom CSS/JS for theme styling
✓ Basic review submission form
✓ Individual registration and dashboard
✓ Business registration and claim flow
✓ Admin dashboard basics
✓ Customer support dashboard basics
✓ Basic email setup (SMTP)
✓ Page SEO custom fields
✓ XML sitemap configuration
✓ Yoast SEO activation
✓ Blog section setup
✓ Social sharing buttons
✓ 3 Review widgets (basic)
✓ WooCommerce plugin (basic)
✓ Developer profile creation
✓ Traffic light basics (visual only)
```

### Phase 2: Core Functionality (Week 3-4)
```
Deliverables:
□ Review moderation workflow
□ Review approval/rejection system
□ Company dashboard full features
□ Review response system
□ Trust score calculation algorithm
□ Traffic light status engine
□ Advanced search and filtering
□ Review analytics
□ Full email template system
□ Notification system
□ User activity tracking
```

### Phase 3: Advanced Features (Week 5-6)
```
Deliverables:
□ AI review moderation toggle
□ Reseller registration system
□ Referral tracking
□ Commission dashboard
□ API documentation
□ Advanced widget options (4th widget)
□ Blacklist management
□ Bug reporting system
□ Advanced analytics
□ Export functionality
```

### Phase 4: Integration & Polish (Week 7-8)
```
Deliverables:
□ Full payment processing
□ Invoice generation
□ Email campaign system
□ Live chat (WhatsApp) integration
□ Social media connections
□ Subdomain architecture planning
□ Performance optimization
□ Security audit
□ Documentation
□ User acceptance testing
□ Go-live preparation
```

---

## 11. Remaining Tasks for 100% Completion

### Items NOT completed in claimed "80%":
Based on the requirements list, the following items require development:

1. **Email System** - 40+ email templates, campaign system
2. **AI Review Moderation** - Auto-approval toggle with AI
3. **Reseller System** - Dashboard, commission tracking, payouts
4. **Accounting/Invoicing** - Invoice generation, payment processing
5. **Blacklist System** - Public reporting, evidence uploads, approvals
6. **Advanced Widgets** - 4th widget option, API download
7. **Subdomain Architecture** - Separate database for high-volume businesses
8. **Social Media Integration** - X, Instagram, LinkedIn connections
9. **Advanced Dashboard Features** - Full feature parity with Trustpilot
10. **Video Integration** - YouTube customization per page
11. **Authority Links** - Customizable links per page
12. **Bug Reporting System** - User-facing issue submission

### Honest Assessment:
The current state likely represents approximately **30-40% completion** when considering:
- Core infrastructure still needs building
- Email system is minimal
- Reseller system not started
- Payment/accounting not started
- AI features not implemented
- Advanced integrations pending
- Dashboard features incomplete

---

## 12. Appendix

### A. Recommended Plugins
- Yoast SEO (Premium)
- WooCommerce
- WPForms (Contact forms)
- WP Mail SMTP (Email delivery)
- Wordfence (Security)
- WP Rocket (Performance)
- User Role Editor
- WPML (Future multilingual)

### B. External Services
- SendGrid/Mailgun (Email sending)
- AWS S3 (File storage)
- CloudFlare (CDN/DDoS protection)
- Stripe/PayPal (Payments)

### C. Performance Targets
- Page load: < 2 seconds
- Time to Interactive: < 3 seconds
- Lighthouse Score: > 90
- Uptime: 99.9%

---

**Document Version:** 1.0  
**Last Updated:** 2026-06-02  
**Next Review:** Upon Stage 1 completion
