<?php
/**
 * MyProtector Platform - Reseller Dashboard Service
 * 
 * @package MyProtector\Modules\Dashboards\Services
 */

namespace MyProtector\Modules\Dashboards\Services;

class ResellerDashboardService {
    /**
     * Get reseller stats
     * 
     * @param int $user_id
     * @return array
     */
    public function getStats(int $user_id): array {
        $reseller = $this->getResellerByUserId($user_id);
        
        if (!$reseller) {
            return ['has_reseller_account' => false];
        }
        
        $referrals = $this->getReferrals($reseller->reseller_id);
        $commissions = $this->getCommissions($reseller->reseller_id);
        
        return [
            'has_reseller_account' => true,
            'reseller_id' => $reseller->reseller_id,
            'company_name' => $reseller->company_name,
            'referral_code' => $reseller->referral_code,
            'commission_rate' => $reseller->commission_rate,
            'commission_tier' => $reseller->commission_tier,
            'total_referrals' => $reseller->total_referrals,
            'total_earnings' => $reseller->total_earnings,
            'pending_earnings' => $reseller->pending_earnings,
            'paid_earnings' => $reseller->paid_earnings,
            'referral_link' => $this->getReferralLink($reseller->referral_code),
            'recent_referrals' => array_slice($referrals, 0, 5),
            'recent_commissions' => array_slice($commissions, 0, 5),
        ];
    }

    /**
     * Get reseller by user ID
     * 
     * @param int $user_id
     * @return object|null
     */
    public function getResellerByUserId(int $user_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_resellers WHERE user_id = %d AND reseller_status = 'active'",
                $user_id
            )
        );
    }

    /**
     * Get referral link
     * 
     * @param string $referral_code
     * @return string
     */
    protected function getReferralLink(string $referral_code): string {
        return home_url('/?ref=' . $referral_code);
    }

    /**
     * Get referrals
     * 
     * @param int $reseller_id
     * @return array
     */
    public function getReferrals(int $reseller_id): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT b.*, r.created_at as referred_at 
                FROM {$wpdb->prefix}mp_businesses b 
                JOIN {$wpdb->prefix}mp_resellers r ON b.reseller_id = r.reseller_id 
                WHERE b.reseller_id = %d 
                ORDER BY b.created_at DESC",
                $reseller_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get commissions
     * 
     * @param int $reseller_id
     * @return array
     */
    public function getCommissions(int $reseller_id): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, b.business_name 
                FROM {$wpdb->prefix}mp_commissions c 
                LEFT JOIN {$wpdb->prefix}mp_businesses b ON c.business_id = b.business_id 
                WHERE c.reseller_id = %d 
                ORDER BY c.created_at DESC",
                $reseller_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get commission summary
     * 
     * @param int $reseller_id
     * @return array
     */
    public function getCommissionSummary(int $reseller_id): array {
        global $wpdb;
        
        $summary = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    COUNT(*) as total_count,
                    SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as pending_total,
                    SUM(CASE WHEN commission_status = 'approved' THEN commission_amount ELSE 0 END) as approved_total,
                    SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as paid_total
                FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d",
                $reseller_id
            )
        );
        
        // Monthly breakdown
        $monthly = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT(created_at, '%%Y-%%m') as month, 
                    SUM(commission_amount) as total 
                FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d 
                GROUP BY DATE_FORMAT(created_at, '%%Y-%%m') 
                ORDER BY month DESC LIMIT 12",
                $reseller_id
            ),
            ARRAY_A
        );
        
        return [
            'summary' => $summary,
            'monthly' => $monthly,
        ];
    }

    /**
     * Get pending payouts
     * 
     * @param int $reseller_id
     * @return array
     */
    public function getPendingPayouts(int $reseller_id): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d AND commission_status = 'approved' 
                ORDER BY approved_at DESC",
                $reseller_id
            ),
            ARRAY_A
        ) ?: [];
    }
}