<?php
/**
 * MyProtector Core - Plugin Activator
 * 
 * Handles all plugin activation tasks
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

class Activator {
    /**
     * Run activation tasks
     * 
     * @return void
     */
    public static function activate(): void {
        $instance = new self();
        
        // Store activation flag for welcome notice
        set_transient('mp_activated', true, 60);
        
        // Create database tables
        $instance->createTables();
        
        // Set default options
        $instance->createOptions();
        
        // Register user roles
        $instance->createRoles();
        
        // Add capabilities to admin role
        $instance->createCapabilities();
        
        // Run migrations
        $instance->runMigrations();
        
        // Register custom post types (triggers flush)
        $instance->registerPostTypes();
        
        // Flush rewrite rules
        $instance->flushRewriteRules();
        
        // Schedule cron events
        $instance->scheduleEvents();
        
        // Seed initial data
        $instance->seedData();
        
        // Log activation
        $instance->logActivation();
        
        // Set activation time and version
        update_option('mp_activation_time', time());
        update_option('mp_activation_version', MYPROTECTOR_VERSION);
        update_option('mp_db_version', MYPROTECTOR_DB_VERSION);
        
        // Trigger activation hook
        do_action('mp_activated');
    }

    /**
     * Create database tables
     * 
     * @return void
     */
    protected function createTables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Companies table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_companies (
            company_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NOT NULL,
            company_name varchar(255) NOT NULL,
            company_slug varchar(255) NOT NULL,
            company_description text,
            company_website varchar(500),
            company_logo varchar(500),
            company_address text,
            company_phone varchar(50),
            company_email varchar(255),
            company_category bigint(20) unsigned,
            insurance_name varchar(255),
            insurance_url varchar(500),
            terms_url varchar(500),
            promise_page_url varchar(500),
            promise_page_title varchar(255),
            status enum('pending','approved','rejected','suspended') default 'pending',
            trust_score decimal(3,2) default 0.00,
            total_reviews int(10) unsigned default 0,
            avg_rating decimal(2,1) default 0.0,
            is_featured tinyint(1) default 0,
            rejection_reason text,
            approved_by bigint(20) unsigned default 0,
            approved_at datetime,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (company_id),
            UNIQUE KEY company_slug (company_slug),
            KEY idx_status (status),
            KEY idx_category (company_category),
            KEY idx_user (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Company documents table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_company_documents (
            document_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            document_type enum('insurance_certificate','business_license','incorporation','other') default 'other',
            document_name varchar(255) NOT NULL,
            document_url varchar(500) NOT NULL,
            document_path varchar(500),
            mime_type varchar(100),
            file_size int(10) unsigned default 0,
            is_verified tinyint(1) default 0,
            verified_by bigint(20) unsigned default 0,
            verified_at datetime,
            rejection_reason text,
            uploaded_by bigint(20) unsigned NOT NULL,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (document_id),
            KEY idx_company (company_id),
            KEY idx_type (document_type)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Reviews table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_reviews (
            review_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            review_title varchar(255) NOT NULL,
            review_content text NOT NULL,
            review_rating tinyint(3) unsigned NOT NULL,
            review_status enum('pending','approved','rejected','flagged') default 'pending',
            trust_level enum('unverified','verified','premium') default 'unverified',
            ip_address varchar(45),
            is_published tinyint(1) default 0,
            published_at datetime,
            helpful_count int(10) unsigned default 0,
            report_count int(10) unsigned default 0,
            is_featured tinyint(1) default 0,
            ai_analyzed tinyint(1) default 0,
            ai_sentiment varchar(20),
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (review_id),
            KEY idx_company (company_id),
            KEY idx_user (user_id),
            KEY idx_status (review_status),
            KEY idx_rating (review_rating),
            KEY idx_published (is_published, published_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Review responses table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_review_responses (
            response_id bigint(20) unsigned NOT NULL auto_increment,
            review_id bigint(20) unsigned NOT NULL,
            company_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            response_content text NOT NULL,
            is_official tinyint(1) default 1,
            status enum('pending','published','hidden') default 'pending',
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (response_id),
            KEY idx_review (review_id),
            KEY idx_company (company_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Review images table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_review_images (
            image_id bigint(20) unsigned NOT NULL auto_increment,
            review_id bigint(20) unsigned NOT NULL,
            image_url varchar(500) NOT NULL,
            image_path varchar(500),
            image_type enum('review','blacklist_evidence') default 'review',
            caption varchar(255),
            is_approved tinyint(1) default 0,
            uploaded_by bigint(20) unsigned NOT NULL,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (image_id),
            KEY idx_review (review_id),
            KEY idx_uploaded_by (uploaded_by)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Review helpful marks table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_review_helpful (
            id bigint(20) unsigned NOT NULL auto_increment,
            review_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (id),
            UNIQUE KEY unique_mark (review_id, user_id),
            KEY idx_review (review_id),
            KEY idx_user (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Review reports table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_review_reports (
            report_id bigint(20) unsigned NOT NULL auto_increment,
            review_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            reason text NOT NULL,
            ip_address varchar(45),
            status enum('pending','reviewed','dismissed') default 'pending',
            reviewed_by bigint(20) unsigned default 0,
            reviewed_at datetime,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (report_id),
            KEY idx_review (review_id),
            KEY idx_user (user_id),
            KEY idx_status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Review moderation log table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_review_moderation_log (
            log_id bigint(20) unsigned NOT NULL auto_increment,
            review_id bigint(20) unsigned NOT NULL,
            action varchar(50) NOT NULL,
            notes text,
            moderated_by bigint(20) unsigned NOT NULL,
            ip_address varchar(45),
            created_at datetime default current_timestamp,
            PRIMARY KEY  (log_id),
            KEY idx_review (review_id),
            KEY idx_moderated_by (moderated_by)
        ) $charset_collate;";
        dbDelta($sql);
        
        // User trust levels table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_user_trust_levels (
            trust_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NOT NULL,
            trust_level enum('unverified','verified','premium','trusted') default 'unverified',
            verified_at datetime,
            verification_method varchar(50),
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (trust_id),
            UNIQUE KEY idx_user (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Trust level history table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_trust_level_history (
            history_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NOT NULL,
            new_level enum('unverified','verified','premium','trusted') NOT NULL,
            old_level enum('unverified','verified','premium','trusted'),
            changed_by bigint(20) unsigned default 0,
            ip_address varchar(45),
            created_at datetime default current_timestamp,
            PRIMARY KEY  (history_id),
            KEY idx_user (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // IP ban list table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_ip_ban_list (
            ban_id bigint(20) unsigned NOT NULL auto_increment,
            ip_address varchar(45) NOT NULL,
            reason text,
            banned_by bigint(20) unsigned default 0,
            is_active tinyint(1) default 1,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (ban_id),
            UNIQUE KEY idx_ip (ip_address)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Trust signals table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_trust_signals (
            signal_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            status enum('green','amber','red') default 'red',
            calculated_status enum('green','amber','red') default 'red',
            requirements longtext,
            is_overridden tinyint(1) default 0,
            overridden_status enum('green','amber','red'),
            override_reason text,
            overridden_by bigint(20) unsigned default 0,
            overridden_at datetime,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (signal_id),
            UNIQUE KEY idx_company (company_id),
            KEY idx_status (status),
            KEY idx_overridden (is_overridden)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Trust signal history table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_trust_signal_history (
            history_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            old_status enum('green','amber','red'),
            new_status enum('green','amber','red') NOT NULL,
            change_reason text,
            changed_by bigint(20) unsigned default 0,
            is_override tinyint(1) default 0,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (history_id),
            KEY idx_company (company_id),
            KEY idx_created (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Claims table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_claims (
            claim_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            claim_type varchar(50) NOT NULL,
            claim_amount decimal(10,2) default 0.00,
            claim_description text,
            claim_status enum('pending','approved','rejected','resolved') default 'pending',
            claim_notes text,
            approved_by bigint(20) unsigned default 0,
            approved_at datetime,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (claim_id),
            KEY idx_company (company_id),
            KEY idx_status (claim_status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Refunds table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_refunds (
            refund_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            refund_amount decimal(10,2) default 0.00,
            refund_reason text,
            refunded_at datetime,
            status enum('pending','processed','failed') default 'pending',
            created_at datetime default current_timestamp,
            PRIMARY KEY  (refund_id),
            KEY idx_company (company_id),
            KEY idx_status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Subscriptions table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_subscriptions (
            subscription_id bigint(20) unsigned NOT NULL auto_increment,
            company_id bigint(20) unsigned NOT NULL,
            plan_name varchar(100) NOT NULL,
            plan_price decimal(10,2) default 0.00,
            billing_cycle enum('monthly','yearly') default 'monthly',
            status enum('active','cancelled','expired','paused') default 'active',
            start_date datetime,
            end_date datetime,
            next_billing_date datetime,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (subscription_id),
            KEY idx_company (company_id),
            KEY idx_status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Resellers table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_resellers (
            reseller_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NOT NULL,
            referral_code varchar(50) NOT NULL,
            commission_rate decimal(4,2) default 10.00,
            total_referrals int(10) unsigned default 0,
            total_earnings decimal(12,2) default 0.00,
            pending_commission decimal(12,2) default 0.00,
            paid_commission decimal(12,2) default 0.00,
            payment_threshold decimal(10,2) default 100.00,
            payout_method enum('bank','paypal','stripe') default 'bank',
            payout_details text,
            status enum('active','suspended','pending') default 'pending',
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (reseller_id),
            UNIQUE KEY referral_code (referral_code),
            KEY idx_user (user_id),
            KEY idx_status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Referrals table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_referrals (
            referral_id bigint(20) unsigned NOT NULL auto_increment,
            reseller_id bigint(20) unsigned NOT NULL,
            company_id bigint(20) unsigned,
            referred_email varchar(255),
            referral_status enum('pending','registered','upgraded','cancelled') default 'pending',
            commission_earned decimal(10,2) default 0.00,
            commission_paid tinyint(1) default 0,
            converted_at datetime,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (referral_id),
            KEY idx_reseller (reseller_id),
            KEY idx_company (company_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Support tickets table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_support_tickets (
            ticket_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NOT NULL,
            ticket_subject varchar(255) NOT NULL,
            ticket_content text NOT NULL,
            ticket_category enum('general','review','business','technical','billing') default 'general',
            ticket_priority enum('low','medium','high','urgent') default 'medium',
            ticket_status enum('open','in_progress','resolved','closed') default 'open',
            assigned_to bigint(20) unsigned,
            admin_response text,
            resolved_at datetime,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (ticket_id),
            KEY idx_user (user_id),
            KEY idx_status (ticket_status),
            KEY idx_assigned (assigned_to)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Blacklist table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_blacklist (
            blacklist_id bigint(20) unsigned NOT NULL auto_increment,
            entry_type enum('individual','business') NOT NULL,
            user_id bigint(20) unsigned,
            company_id bigint(20) unsigned,
            reason text NOT NULL,
            evidence_files text,
            reported_by bigint(20) unsigned,
            status enum('pending','approved','rejected') default 'pending',
            approved_by bigint(20) unsigned,
            approved_at datetime,
            expires_at datetime,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (blacklist_id),
            KEY idx_type_user (entry_type, user_id),
            KEY idx_type_company (entry_type, company_id),
            KEY idx_status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Email templates table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_email_templates (
            template_id bigint(20) unsigned NOT NULL auto_increment,
            template_key varchar(100) NOT NULL,
            template_name varchar(255) NOT NULL,
            template_subject varchar(255) NOT NULL,
            template_body text NOT NULL,
            template_type enum('transactional','marketing','notification','system') default 'transactional',
            is_active tinyint(1) default 1,
            variables text,
            created_at datetime default current_timestamp,
            updated_at datetime default current_timestamp on update current_timestamp,
            PRIMARY KEY  (template_id),
            UNIQUE KEY template_key (template_key)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Audit log table
        $sql = "CREATE TABLE {$wpdb->prefix}mp_audit_log (
            log_id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned,
            action_type varchar(100) NOT NULL,
            entity_type varchar(50),
            entity_id bigint(20) unsigned,
            old_value text,
            new_value text,
            ip_address varchar(45),
            user_agent text,
            created_at datetime default current_timestamp,
            PRIMARY KEY  (log_id),
            KEY idx_user (user_id),
            KEY idx_action (action_type),
            KEY idx_entity (entity_type, entity_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    /**
     * Create default options
     * 
     * @return void
     */
    protected function createOptions(): void {
        $defaults = [
            'mp_version' => MYPROTECTOR_VERSION,
            'mp_review_auto_approve' => false,
            'mp_email_from_name' => get_bloginfo('name'),
            'mp_email_from_email' => get_bloginfo('admin_email'),
            'mp_company_slug_base' => 'company',
            'mp_review_slug_base' => 'review',
            'mp_trust_min_reviews' => 50,
            'mp_trust_min_rating' => 4.5,
            'mp_woo_integration_enabled' => false,
            'mp_woo_invite_delay_days' => 7,
            'mp_maintenance_mode' => false,
            'mp_review_min_length' => 20,
            'mp_review_max_length' => 5000,
            'mp_review_image_limit' => 5,
            'mp_ai_moderation_enabled' => false,
            'mp_social_share_enabled' => true,
        ];

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Create user roles
     * 
     * @return void
     */
    protected function createRoles(): void {
        // Admin role
        add_role('mp_admin', 'MyProtector Admin', [
            'read' => true,
            'edit_posts' => true,
            'delete_posts' => true,
            'publish_posts' => true,
            'upload_files' => true,
        ]);

        // Customer support role
        add_role('mp_support', 'MyProtector Support', [
            'read' => true,
            'edit_posts' => false,
        ]);

        // Business role
        add_role('mp_business', 'MyProtector Business', [
            'read' => true,
        ]);

        // Reseller role
        add_role('mp_reseller', 'MyProtector Reseller', [
            'read' => true,
        ]);
    }

    /**
     * Create capabilities for admin role
     * 
     * @return void
     */
    protected function createCapabilities(): void {
        $admin = get_role('administrator');
        
        if (!$admin) {
            return;
        }

        $capabilities = [
            // Core
            'manage_myprotector',
            'edit_myprotector_settings',
            'view_myprotector_reports',
            
            // Reviews
            'mp_edit_reviews',
            'mp_delete_reviews',
            'mp_moderate_reviews',
            'mp_view_all_reviews',
            'mp_feature_reviews',
            
            // Companies
            'mp_edit_companies',
            'mp_delete_companies',
            'mp_verify_companies',
            'mp_approve_companies',
            'mp_override_trust_status',
            
            // Users
            'mp_manage_users',
            'mp_view_users',
            'mp_ban_users',
            
            // Resellers
            'mp_manage_resellers',
            'mp_view_resellers',
            'mp_release_commissions',
            
            // Blacklist
            'mp_manage_blacklist',
            'mp_approve_blacklist',
            
            // Support
            'mp_manage_tickets',
            'mp_view_tickets',
            
            // System
            'mp_export_data',
            'mp_view_audit_log',
            'mp_manage_settings',
        ];

        foreach ($capabilities as $cap) {
            $admin->add_cap($cap);
        }
    }

    /**
     * Run database migrations
     * 
     * @return void
     */
    protected function runMigrations(): void {
        // Store current db version
        $current_version = get_option('mp_db_version', '0.0.0');
        
        if (version_compare($current_version, MYPROTECTOR_DB_VERSION, '<')) {
            // Run any pending migrations
            do_action('mp_run_migrations', $current_version);
        }
    }

    /**
     * Register custom post types on activation
     * 
     * @return void
     */
    protected function registerPostTypes(): void {
        // Set flag to flush rewrite rules
        set_transient('mp_flush_rewrite_rules', true, 5);
        
        // Register post types (will be called again on init but that's fine)
        register_post_type('mp_review', [
            'public' => true,
            'rewrite' => ['slug' => 'reviews'],
        ]);
        
        register_post_type('mp_company', [
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'companies'],
        ]);
    }

    /**
     * Flush rewrite rules
     * 
     * @return void
     */
    protected function flushRewriteRules(): void {
        if (get_transient('mp_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_transient('mp_flush_rewrite_rules');
        }
    }

    /**
     * Schedule cron events
     * 
     * @return void
     */
    protected function scheduleEvents(): void {
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

        // Cleanup old data (weekly)
        if (!wp_next_scheduled('mp_cleanup_old_data')) {
            wp_schedule_event(time(), 'weekly', 'mp_cleanup_old_data');
        }
    }

    /**
     * Seed initial data
     * 
     * @return void
     */
    protected function seedData(): void {
        // Seed company categories
        $this->seedCompanyCategories();
        
        // Seed email templates
        $this->seedEmailTemplates();
    }

    /**
     * Seed company categories
     * 
     * @return void
     */
    protected function seedCompanyCategories(): void {
        $categories = [
            'Retail & E-commerce',
            'Restaurants & Food',
            'Hotels & Travel',
            'Health & Medical',
            'Professional Services',
            'Financial Services',
            'Technology & Software',
            'Home Services',
            'Education',
            'Entertainment',
        ];

        foreach ($categories as $category) {
            if (!term_exists($category, 'mp_company_category')) {
                wp_insert_term($category, 'mp_company_category');
            }
        }
    }

    /**
     * Seed email templates
     * 
     * @return void
     */
    protected function seedEmailTemplates(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'mp_email_templates';

        $templates = [
            [
                'template_key' => 'user_welcome',
                'template_name' => 'User Welcome',
                'template_subject' => 'Welcome to MyProtector!',
                'template_body' => 'Hello {{user_name}},

Welcome to MyProtector! We\'re excited to have you as a member of our community.

Get started by:
1. Complete your profile
2. Explore businesses
3. Leave honest reviews

Best regards,
The MyProtector Team',
                'template_type' => 'transactional',
            ],
            [
                'template_key' => 'review_invitation',
                'template_name' => 'Review Invitation',
                'template_subject' => 'Share your experience with {{company_name}}',
                'template_body' => 'Hello {{user_name}},

We noticed you recently interacted with {{company_name}}. We would love to hear about your experience!

Your review helps others make informed decisions and helps businesses improve.

[Write a Review]

Best regards,
The MyProtector Team',
                'template_type' => 'transactional',
            ],
            [
                'template_key' => 'review_confirmation',
                'template_name' => 'Review Confirmation',
                'template_subject' => 'Your review has been submitted!',
                'template_body' => 'Hello {{user_name}},

Thank you for submitting your review for {{company_name}}!

Your review: {{review_title}}
Rating: {{review_rating}}

Status: Pending Review

You\'ll receive an email once your review is published.

Best regards,
The MyProtector Team',
                'template_type' => 'transactional',
            ],
            [
                'template_key' => 'business_claim_approved',
                'template_name' => 'Business Claim Approved',
                'template_subject' => 'Your company profile is now live!',
                'template_body' => 'Hello {{user_name}},

Great news! Your claim on {{company_name}} has been approved.

You can now:
- Respond to reviews
- Update company information
- Access analytics
- Download widgets

[Go to Dashboard]

Best regards,
The MyProtector Team',
                'template_type' => 'transactional',
            ],
            [
                'template_key' => 'trust_status_update',
                'template_name' => 'Trust Status Update',
                'template_subject' => 'Your trust status has been updated',
                'template_body' => 'Hello {{user_name}},

The trust status for {{company_name}} has been updated.

New Status: {{trust_status}}
Trust Score: {{trust_score}}

[View Your Profile]

Best regards,
The MyProtector Team',
                'template_type' => 'notification',
            ],
        ];

        foreach ($templates as $template) {
            $wpdb->replace($table, array_merge($template, [
                'variables' => json_encode(['user_name', 'company_name']),
                'is_active' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ]));
        }
    }

    /**
     * Log activation
     * 
     * @return void
     */
    protected function logActivation(): void {
        global $wpdb;
        
        $wpdb->insert($wpdb->prefix . 'mp_audit_log', [
            'action_type' => 'plugin_activated',
            'entity_type' => 'plugin',
            'entity_id' => 0,
            'new_value' => json_encode([
                'version' => MYPROTECTOR_VERSION,
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
            ]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => current_time('mysql'),
        ]);
    }
}