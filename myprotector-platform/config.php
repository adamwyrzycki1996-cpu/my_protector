<?php
/**
 * MyProtector Platform - Configuration
 * 
 * @package MyProtector
 * @version 1.0.0
 */

// Plugin Information
define('MYPROTECTOR_VERSION', '1.0.0');
define('MYPROTECTOR_DB_VERSION', '1.0.0');

// Paths
define('MYPROTECTOR_PATH', plugin_dir_path(__FILE__));
define('MYPROTECTOR_URL', plugin_dir_url(__FILE__));
define('MYPROTECTOR_BASENAME', plugin_basename(__FILE__));
define('MYPROTECTOR_SLUG', 'myprotector-platform');

// Plugin Names
define('MYPROTECTOR_PLUGIN_NAME', 'MyProtector Platform');
define('MYPROTECTOR_PLUGIN_DESC', 'Trustpilot-style review platform for WordPress');

// Environment
define('MYPROTECTOR_DEBUG', defined('WP_DEBUG') && WP_DEBUG);
define('MYPROTECTOR_LOG_LEVEL', MYPROTECTOR_DEBUG ? 'debug' : 'info');

// Database
define('MYPROTECTOR_TABLE_PREFIX', 'mp_');

// Cache Settings
define('MYPROTECTOR_CACHE_ENABLED', true);
define('MYPROTECTOR_CACHE_TTL', HOUR_IN_SECONDS);
define('MYPROTECTOR_CACHE_PREFIX', 'mp_');

// Email Settings
define('MYPROTECTOR_EMAIL_ENABLED', true);
define('MYPROTECTOR_EMAIL_BATCH_SIZE', 100);
define('MYPROTECTOR_EMAIL_QUEUE_PROCESS_LIMIT', 50);

// API Settings
define('MYPROTECTOR_API_NAMESPACE', 'myprotector/v1');
define('MYPROTECTOR_API_TIMEOUT', 30);
define('MYPROTECTOR_API_VERSION', 'v1');

// Performance
define('MYPROTECTOR_ASSETS_VERSION', MYPROTECTOR_VERSION);
define('MYPROTECTOR_MINIFY_ASSETS', !MYPROTECTOR_DEBUG);
define('MYPROTECTOR_ASSETS_INLINE_LIMIT', 5000);

// Trust Score Thresholds
define('MYPROTECTOR_TRUST_MIN_REVIEWS', 50);
define('MYPROTECTOR_TRUST_MIN_RATING', 4.5);
define('MYPROTECTOR_TRUST_REVIEW_WEIGHT', 0.6);
define('MYPROTECTOR_TRUST_RESPONSE_WEIGHT', 0.2);
define('MYPROTECTOR_TRUST_DOCUMENT_WEIGHT', 0.2);

// Review Settings
define('MYPROTECTOR_REVIEW_MIN_LENGTH', 20);
define('MYPROTECTOR_REVIEW_MAX_LENGTH', 5000);
define('MYPROTECTOR_REVIEW_IMAGE_LIMIT', 5);
define('MYPROTECTOR_REVIEW_IMAGE_MAX_SIZE', 5 * 1024 * 1024); // 5MB

// Reseller Settings
define('MYPROTECTOR_REFERRAL_COOKIE_DAYS', 30);
define('MYPROTECTOR_COMMISSION_DEFAULT_RATE', 10.00);
define('MYPROTECTOR_PAYOUT_MINIMUM', 50.00);

// WordPress Requirements
define('MYPROTECTOR_WP_MIN_VERSION', '6.0');
define('MYPROTECTOR_PHP_MIN_VERSION', '8.0');

// File Upload
define('MYPROTECTOR_UPLOAD_DIR', 'myprotector');
define('MYPROTECTOR_ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Security
define('MYPROTECTOR_NONCE_LIFE', DAY_IN_SECONDS);
define('MYPROTECTOR_RATE_LIMIT_REQUESTS', 100);
define('MYPROTECTOR_RATE_LIMIT_WINDOW', MINUTE_IN_SECONDS);

// Scheduled Events
define('MYPROTECTOR_TRUST_UPDATE_INTERVAL', 'daily');
define('MYPROTECTOR_EMAIL_QUEUE_INTERVAL', 'hourly');
define('MYPROTECTOR_ANALYTICS_INTERVAL', 'weekly');
define('MYPROTECTOR_CLEANUP_INTERVAL', 'daily');