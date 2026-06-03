<?php
/**
 * MyProtector Core - Plugin Deactivator
 * 
 * Handles all plugin deactivation tasks
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

class Deactivator {
    /**
     * Run deactivation tasks
     * 
     * @return void
     */
    public static function deactivate(): void {
        $instance = new self();
        
        // Clear all transients
        $instance->clearTransients();
        
        // Clear object cache
        $instance->clearCache();
        
        // Unschedule cron events
        $instance->unscheduleEvents();
        
        // Clear scheduled events
        $instance->clearScheduledEvents();
        
        // Clean temporary data
        $instance->cleanTempData();
        
        // Flush rewrite rules
        $instance->flushRewriteRules();
        
        // Clear cron locks
        $instance->clearCronLocks();
        
        // Log deactivation
        $instance->logDeactivation();
        
        // Trigger deactivation hook
        do_action('mp_deactivated');
    }

    /**
     * Clear all plugin transients
     * 
     * @return void
     */
    protected function clearTransients(): void {
        global $wpdb;
        
        // Clear all MP transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_mp_%' 
             OR option_name LIKE '_transient_timeout_mp_%'"
        );

        // Clear network transients on multisite
        if (is_multisite()) {
            $wpdb->query(
                "DELETE FROM {$wpdb->sitemeta} 
                 WHERE meta_key LIKE '_transient_mp_%' 
                 OR meta_key LIKE '_transient_timeout_mp_%'"
            );
        }
    }

    /**
     * Clear object cache
     * 
     * @return void
     */
    protected function clearCache(): void {
        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        // Clear WordPress object cache
        wp_cache_flush();
    }

    /**
     * Unschedule specific cron events
     * 
     * @return void
     */
    protected function unscheduleEvents(): void {
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

    /**
     * Clear all scheduled MP events
     * 
     * @return void
     */
    protected function clearScheduledEvents(): void {
        wp_clear_scheduled_hook('mp_daily_trust_update');
        wp_clear_scheduled_hook('mp_process_email_queue');
        wp_clear_scheduled_hook('mp_weekly_analytics');
        wp_clear_scheduled_hook('mp_cleanup_old_data');
        wp_clear_scheduled_hook('mp_generate_sitemap');
    }

    /**
     * Clean temporary data
     * 
     * @return void
     */
    protected function cleanTempData(): void {
        $upload_dir = wp_upload_dir();
        $mp_temp_dir = $upload_dir['basedir'] . '/myprotector-temp';

        if (is_dir($mp_temp_dir)) {
            // Only remove if older than 1 hour for safety
            if (filemtime($mp_temp_dir) < time() - 3600) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
                global $wp_filesystem;
                $wp_filesystem->delete($mp_temp_dir, true);
            }
        }
    }

    /**
     * Flush rewrite rules
     * 
     * @return void
     */
    protected function flushRewriteRules(): void {
        flush_rewrite_rules();
    }

    /**
     * Clear cron locks
     * 
     * @return void
     */
    protected function clearCronLocks(): void {
        delete_option('mp_cron_lock');
        delete_transient('mp_doing_cron');
    }

    /**
     * Log deactivation
     * 
     * @return void
     */
    protected function logDeactivation(): void {
        global $wpdb;
        
        // Only log if audit table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}mp_audit_log'") === null) {
            return;
        }
        
        $wpdb->insert($wpdb->prefix . 'mp_audit_log', [
            'action_type' => 'plugin_deactivated',
            'entity_type' => 'plugin',
            'entity_id' => 0,
            'new_value' => json_encode([
                'version' => MYPROTECTOR_VERSION,
            ]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => current_time('mysql'),
        ]);
    }
}