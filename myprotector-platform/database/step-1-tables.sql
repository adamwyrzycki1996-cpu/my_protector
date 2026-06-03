-- =====================================================
-- MyProtector Platform - MySQL Database Schema
-- Step 1: Create Tables (No Foreign Keys)
-- Run this file first, then run step-2-foreign-keys.sql
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS wp_mp_review_images;
DROP TABLE IF EXISTS wp_mp_reviews;
DROP TABLE IF EXISTS wp_mp_traffic_signals;
DROP TABLE IF EXISTS wp_mp_commissions;
DROP TABLE IF EXISTS wp_mp_notifications;
DROP TABLE IF EXISTS wp_mp_email_logs;
DROP TABLE IF EXISTS wp_mp_businesses;
DROP TABLE IF EXISTS wp_mp_resellers;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. wp_mp_resellers
-- =====================================================
DROP TABLE IF EXISTS wp_mp_resellers;
CREATE TABLE wp_mp_resellers (
    reseller_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    company_name VARCHAR(255) NULL,
    company_url VARCHAR(500) NULL,
    referral_code VARCHAR(50) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    commission_tier ENUM('standard', 'silver', 'gold', 'platinum') NOT NULL DEFAULT 'standard',
    custom_commission_rates JSON NULL,
    total_referrals INT UNSIGNED NOT NULL DEFAULT 0,
    total_earnings DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    pending_earnings DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    paid_earnings DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payout_method ENUM('bank_transfer', 'paypal', 'stripe', 'wire') DEFAULT 'bank_transfer',
    payout_details JSON NULL,
    payout_threshold DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    payout_schedule ENUM('weekly', 'biweekly', 'monthly') NOT NULL DEFAULT 'monthly',
    minimum_payout DECIMAL(10,2) NOT NULL DEFAULT 50.00,
    reseller_status ENUM('pending', 'active', 'suspended', 'terminated') NOT NULL DEFAULT 'pending',
    approved_at DATETIME NULL,
    approved_by BIGINT UNSIGNED NULL,
    marketing_materials_access TINYINT(1) NOT NULL DEFAULT 1,
    api_access TINYINT(1) NOT NULL DEFAULT 0,
    api_key VARCHAR(255) NULL,
    tracking_domain VARCHAR(255) NULL,
    utm_parameters JSON NULL,
    total_clicks INT UNSIGNED NOT NULL DEFAULT 0,
    avg_order_value DECIMAL(10,2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_payout_at DATETIME NULL,
    last_activity_at DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (reseller_id),
    UNIQUE KEY uk_referral_code (referral_code),
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_status (reseller_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. wp_mp_businesses
-- =====================================================
DROP TABLE IF EXISTS wp_mp_businesses;
CREATE TABLE wp_mp_businesses (
    business_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    reseller_id BIGINT UNSIGNED NULL,
    category_id BIGINT UNSIGNED NULL,
    business_name VARCHAR(255) NOT NULL,
    business_slug VARCHAR(255) NOT NULL,
    business_description LONGTEXT NULL,
    business_tagline VARCHAR(255) NULL,
    business_email VARCHAR(255) NULL,
    business_phone VARCHAR(50) NULL,
    business_website VARCHAR(500) NULL,
    address_line1 VARCHAR(255) NULL,
    address_line2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(2) NOT NULL DEFAULT 'US',
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    verified_at DATETIME NULL,
    verified_by BIGINT UNSIGNED NULL,
    business_status ENUM('pending', 'active', 'suspended', 'closed', 'archived') DEFAULT 'pending',
    claim_status ENUM('unclaimed', 'claimed', 'verified') DEFAULT 'unclaimed',
    total_reviews INT UNSIGNED NOT NULL DEFAULT 0,
    approved_reviews INT UNSIGNED NOT NULL DEFAULT 0,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    total_rating_sum INT UNSIGNED NOT NULL DEFAULT 0,
    response_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    avg_response_time INT UNSIGNED NULL,
    logo_url VARCHAR(500) NULL,
    cover_image_url VARCHAR(500) NULL,
    brand_color VARCHAR(7) NULL,
    insurance_name VARCHAR(255) NULL,
    insurance_url VARCHAR(500) NULL,
    terms_url VARCHAR(500) NULL,
    promise_page_url VARCHAR(500) NULL,
    promise_page_title VARCHAR(255) NULL,
    facebook_url VARCHAR(500) NULL,
    twitter_url VARCHAR(500) NULL,
    instagram_url VARCHAR(500) NULL,
    linkedin_url VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    first_review_at DATETIME NULL,
    last_review_at DATETIME NULL,
    woocommerce_id BIGINT UNSIGNED NULL,
    woocommerce_shop_name VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    featured_until DATETIME NULL,
    deleted_at DATETIME NULL,
    PRIMARY KEY (business_id),
    UNIQUE KEY uk_business_slug (business_slug),
    UNIQUE KEY uk_business_email (business_email),
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_category_id (category_id),
    INDEX idx_business_status (business_status),
    INDEX idx_avg_rating (avg_rating),
    INDEX idx_total_reviews (total_reviews)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. wp_mp_traffic_signals
-- =====================================================
DROP TABLE IF EXISTS wp_mp_traffic_signals;
CREATE TABLE wp_mp_traffic_signals (
    signal_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id BIGINT UNSIGNED NOT NULL,
    trust_status ENUM('walking', 'shopping', 'bad') NOT NULL DEFAULT 'bad',
    traffic_light_color ENUM('green', 'yellow', 'red') NOT NULL DEFAULT 'red',
    trust_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    trust_score_breakdown JSON NULL,
    requirements_met JSON NULL,
    requirements_total INT UNSIGNED NOT NULL DEFAULT 5,
    requirements_fulfilled INT UNSIGNED NOT NULL DEFAULT 0,
    has_min_reviews TINYINT(1) NOT NULL DEFAULT 0,
    has_min_rating TINYINT(1) NOT NULL DEFAULT 0,
    has_verified_domain TINYINT(1) NOT NULL DEFAULT 0,
    has_insurance TINYINT(1) NOT NULL DEFAULT 0,
    has_terms TINYINT(1) NOT NULL DEFAULT 0,
    has_promise_page TINYINT(1) NOT NULL DEFAULT 0,
    has_active_subscription TINYINT(1) NOT NULL DEFAULT 0,
    is_auto_calculated TINYINT(1) NOT NULL DEFAULT 1,
    manual_override TINYINT(1) NOT NULL DEFAULT 0,
    override_reason TEXT NULL,
    override_by BIGINT UNSIGNED NULL,
    override_at DATETIME NULL,
    last_calculated_at DATETIME NULL,
    calculation_data JSON NULL,
    status_reasons JSON NULL,
    improvement_tips JSON NULL,
    show_traffic_light TINYINT(1) NOT NULL DEFAULT 1,
    badge_style ENUM('standard', 'compact', 'badge_only') NOT NULL DEFAULT 'standard',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (signal_id),
    UNIQUE KEY uk_business_id (business_id),
    INDEX idx_trust_status (trust_status),
    INDEX idx_trust_score (trust_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. wp_mp_reviews
-- =====================================================
DROP TABLE IF EXISTS wp_mp_reviews;
CREATE TABLE wp_mp_reviews (
    review_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    review_title VARCHAR(255) NOT NULL,
    review_content LONGTEXT NOT NULL,
    review_rating TINYINT UNSIGNED NOT NULL,
    review_status ENUM('pending', 'approved', 'rejected', 'flagged', 'spam') DEFAULT 'pending',
    review_verified ENUM('unverified', 'verified', 'premium') DEFAULT 'unverified',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME NULL,
    helpful_count INT UNSIGNED NOT NULL DEFAULT 0,
    report_count INT UNSIGNED NOT NULL DEFAULT 0,
    view_count INT UNSIGNED NOT NULL DEFAULT 0,
    ai_analyzed TINYINT(1) NOT NULL DEFAULT 0,
    ai_sentiment VARCHAR(20) NULL,
    ai_spam_score DECIMAL(5,4) NULL,
    user_agent VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    order_id BIGINT UNSIGNED NULL,
    product_id BIGINT UNSIGNED NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    priority INT UNSIGNED NOT NULL DEFAULT 0,
    deleted_at DATETIME NULL,
    PRIMARY KEY (review_id),
    INDEX idx_business_id (business_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (review_status),
    INDEX idx_rating (review_rating),
    INDEX idx_published_at (published_at),
    INDEX idx_created_at (created_at),
    INDEX idx_business_status (business_id, review_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. wp_mp_review_images
-- =====================================================
DROP TABLE IF EXISTS wp_mp_review_images;
CREATE TABLE wp_mp_review_images (
    image_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    review_id BIGINT UNSIGNED NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    image_filename VARCHAR(255) NOT NULL,
    image_type ENUM('review', 'blacklist_evidence', 'business_logo', 'business_gallery') DEFAULT 'review',
    mime_type VARCHAR(50) NOT NULL DEFAULT 'image/jpeg',
    file_size INT UNSIGNED NOT NULL,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    caption VARCHAR(255) NULL,
    alt_text VARCHAR(255) NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    uploaded_by BIGINT UNSIGNED NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cdn_url VARCHAR(500) NULL,
    thumbnail_url VARCHAR(500) NULL,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    deleted_at DATETIME NULL,
    PRIMARY KEY (image_id),
    INDEX idx_review_id (review_id),
    INDEX idx_image_type (image_type),
    INDEX idx_uploaded_at (uploaded_at),
    INDEX idx_is_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. wp_mp_commissions
-- =====================================================
DROP TABLE IF EXISTS wp_mp_commissions;
CREATE TABLE wp_mp_commissions (
    commission_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reseller_id BIGINT UNSIGNED NOT NULL,
    business_id BIGINT UNSIGNED NULL,
    referral_id BIGINT UNSIGNED NULL,
    commission_type ENUM('signup', 'subscription', 'upgrade', 'review', 'custom') NOT NULL,
    commission_amount DECIMAL(12,2) NOT NULL,
    commission_rate DECIMAL(5,2) NOT NULL,
    reference_type VARCHAR(50) NULL,
    reference_id VARCHAR(255) NULL,
    reference_amount DECIMAL(12,2) NULL,
    commission_status ENUM('pending', 'approved', 'paid', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    approved_at DATETIME NULL,
    approved_by BIGINT UNSIGNED NULL,
    payout_id BIGINT UNSIGNED NULL,
    paid_at DATETIME NULL,
    paid_amount DECIMAL(12,2) NULL,
    is_validated TINYINT(1) NOT NULL DEFAULT 0,
    validated_at DATETIME NULL,
    validation_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    PRIMARY KEY (commission_id),
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_business_id (business_id),
    INDEX idx_commission_status (commission_status),
    INDEX idx_commission_type (commission_type),
    INDEX idx_created_at (created_at),
    INDEX idx_reseller_status (reseller_id, commission_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. wp_mp_notifications
-- =====================================================
DROP TABLE IF EXISTS wp_mp_notifications;
CREATE TABLE wp_mp_notifications (
    notification_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('review_received', 'review_approved', 'review_rejected', 'review_response', 'trust_update', 'commission_earned', 'commission_paid', 'referral_signup', 'system', 'reminder', 'alert') NOT NULL,
    notification_title VARCHAR(255) NOT NULL,
    notification_message TEXT NOT NULL,
    notification_data JSON NULL,
    related_type VARCHAR(50) NULL,
    related_id BIGINT UNSIGNED NULL,
    reference_user_id BIGINT UNSIGNED NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    delivery_method ENUM('in_app', 'email', 'push', 'sms') NOT NULL DEFAULT 'in_app',
    delivery_status ENUM('pending', 'sent', 'delivered', 'failed') NOT NULL DEFAULT 'pending',
    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    action_url VARCHAR(500) NULL,
    action_label VARCHAR(100) NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    PRIMARY KEY (notification_id),
    INDEX idx_user_id (user_id),
    INDEX idx_notification_type (notification_type),
    INDEX idx_is_read (is_read),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_user_read (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. wp_mp_email_logs
-- =====================================================
DROP TABLE IF EXISTS wp_mp_email_logs;
CREATE TABLE wp_mp_email_logs (
    log_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email_id VARCHAR(50) NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) NULL,
    recipient_id BIGINT UNSIGNED NULL,
    recipient_type ENUM('user', 'business', 'reseller', 'admin', 'guest') NOT NULL,
    email_subject VARCHAR(255) NOT NULL,
    email_template VARCHAR(100) NOT NULL,
    email_body_text LONGTEXT NULL,
    email_body_html LONGTEXT NULL,
    email_type ENUM('transactional', 'marketing', 'notification', 'system', 'alert') NOT NULL,
    email_category VARCHAR(50) NOT NULL,
    related_type VARCHAR(50) NULL,
    related_id BIGINT UNSIGNED NULL,
    send_status ENUM('queued', 'sending', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed', 'unsubscribed') NOT NULL DEFAULT 'queued',
    queued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sending_at DATETIME NULL,
    sent_at DATETIME NULL,
    delivered_at DATETIME NULL,
    opened_at DATETIME NULL,
    clicked_at DATETIME NULL,
    opens_count INT UNSIGNED NOT NULL DEFAULT 0,
    clicks_count INT UNSIGNED NOT NULL DEFAULT 0,
    last_click_url VARCHAR(500) NULL,
    bounce_reason VARCHAR(255) NULL,
    bounce_type ENUM('hard', 'soft', 'block', 'spam') NULL,
    failure_reason TEXT NULL,
    smtp_response VARCHAR(255) NULL,
    message_id VARCHAR(255) NULL,
    email_provider VARCHAR(50) NOT NULL DEFAULT 'wordpress',
    provider_message_id VARCHAR(255) NULL,
    tags JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    unsubscribed_at DATETIME NULL,
    unsubscribe_reason VARCHAR(255) NULL,
    cost_per_email DECIMAL(8,4) NULL,
    total_cost DECIMAL(10,4) NULL,
    PRIMARY KEY (log_id),
    INDEX idx_recipient_email (recipient_email),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_email_template (email_template),
    INDEX idx_send_status (send_status),
    INDEX idx_queued_at (queued_at),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- STEP 1 COMPLETE!
-- Now run: step-2-foreign-keys.sql
-- =====================================================