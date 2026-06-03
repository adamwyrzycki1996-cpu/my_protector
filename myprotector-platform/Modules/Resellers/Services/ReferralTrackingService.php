<?php
/**
 * MyProtector Platform - Referral Tracking Service
 * 
 * @package MyProtector\Modules\Resellers\Services
 */

namespace MyProtector\Modules\Resellers\Services;

class ReferralTrackingService {
    /**
     * Track click
     * 
     * @param string $referral_code
     * @return void
     */
    public function trackClick(string $referral_code): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}mp_resellers 
                SET total_clicks = total_clicks + 1, 
                    last_activity_at = NOW() 
                WHERE referral_code = %s",
                $referral_code
            )
        );
    }

    /**
     * Get referral statistics
     * 
     * @param int $reseller_id
     * @return array
     */
    public function getStats(int $reseller_id): array {
        global $wpdb;
        
        $stats = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT total_clicks, total_referrals, total_earnings, pending_earnings, paid_earnings
                FROM {$wpdb->prefix}mp_resellers 
                WHERE reseller_id = %d",
                $reseller_id
            ),
            ARRAY_A
        );
        
        $conversion_rate = $stats['total_clicks'] > 0 
            ? ($stats['total_referrals'] / $stats['total_clicks']) * 100 
            : 0;
        
        return array_merge($stats, ['conversion_rate' => $conversion_rate]);
    }
}