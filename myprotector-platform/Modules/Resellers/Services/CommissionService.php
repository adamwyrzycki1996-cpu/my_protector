<?php
/**
 * MyProtector Platform - Commission Service
 * 
 * @package MyProtector\Modules\Resellers\Services
 */

namespace MyProtector\Modules\Resellers\Services;

class CommissionService {
    /**
     * Create signup commission
     * 
     * @param int $reseller_id
     * @param int $user_id
     * @return int|false
     */
    public function createSignupCommission(int $reseller_id, int $user_id) {
        $signup_commission = (float) apply_filters('mp_signup_commission_amount', 5.00);
        
        return $this->create($reseller_id, [
            'commission_type' => 'signup',
            'commission_amount' => $signup_commission,
            'commission_rate' => 100, // Fixed amount
            'reference_type' => 'user',
            'reference_id' => $user_id,
        ]);
    }

    /**
     * Create subscription commission
     * 
     * @param int $reseller_id
     * @param int $business_id
     * @param float $subscription_amount
     * @return int|false
     */
    public function createSubscriptionCommission(int $reseller_id, int $business_id, float $subscription_amount = 50.00) {
        $reseller = $this->getReseller($reseller_id);
        $rate = $reseller->commission_rate ?? 10;
        
        return $this->create($reseller_id, [
            'commission_type' => 'subscription',
            'commission_amount' => ($subscription_amount * $rate / 100),
            'commission_rate' => $rate,
            'reference_type' => 'business',
            'reference_id' => $business_id,
            'reference_amount' => $subscription_amount,
        ]);
    }

    /**
     * Create commission record
     * 
     * @param int $reseller_id
     * @param array $data
     * @return int|false
     */
    public function create(int $reseller_id, array $data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_commissions',
            [
                'reseller_id' => $reseller_id,
                'business_id' => $data['business_id'] ?? null,
                'commission_type' => $data['commission_type'],
                'commission_amount' => $data['commission_amount'],
                'commission_rate' => $data['commission_rate'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'reference_amount' => $data['reference_amount'] ?? null,
                'commission_status' => 'pending',
            ],
            ['%d', '%d', '%s', '%f', '%f', '%s', '%s', '%f', '%s']
        );
        
        if ($result !== false) {
            // Update reseller pending earnings
            $this->updateResellerPendingEarnings($reseller_id, $data['commission_amount']);
        }
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get commissions for reseller
     * 
     * @param int $reseller_id
     * @param array $args
     * @return array
     */
    public function getCommissions(int $reseller_id, array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'status' => null,
            'type' => null,
            'limit' => 50,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['reseller_id = %d'];
        $values = [$reseller_id];
        
        if ($args['status']) {
            $where[] = 'commission_status = %s';
            $values[] = $args['status'];
        }
        
        if ($args['type']) {
            $where[] = 'commission_type = %s';
            $values[] = $args['type'];
        }
        
        $sql = "SELECT * FROM {$wpdb->prefix}mp_commissions 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d";
        
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        return $wpdb->get_results(
            $wpdb->prepare($sql, $values),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get pending commissions for admin approval
     * 
     * @return array
     */
    public function getPendingCommissions(): array {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT c.*, r.company_name, r.referral_code 
            FROM {$wpdb->prefix}mp_commissions c 
            JOIN {$wpdb->prefix}mp_resellers r ON c.reseller_id = r.reseller_id 
            WHERE c.commission_status = 'pending' 
            ORDER BY c.created_at ASC",
            ARRAY_A
        ) ?: [];
    }

    /**
     * Approve commission
     * 
     * @param int $commission_id
     * @param int $admin_id
     * @return bool|\WP_Error
     */
    public function approve(int $commission_id, int $admin_id) {
        global $wpdb;
        
        $commission = $this->getCommission($commission_id);
        
        if (!$commission) {
            return new \WP_Error('not_found', __('Commission not found.', 'myprotector-platform'));
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_commissions',
            [
                'commission_status' => 'approved',
                'approved_at' => current_time('mysql'),
                'approved_by' => $admin_id,
            ],
            ['commission_id' => $commission_id],
            ['%s', '%s', '%d'],
            ['%d']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Database error.', 'myprotector-platform'));
        }
        
        // Move from pending to approved earnings
        $reseller_service = new ResellerService();
        $reseller_service->updateEarnings($commission->reseller_id, 0, 'pending');
        
        return true;
    }

    /**
     * Mark commission as paid
     * 
     * @param int $commission_id
     * @return bool|\WP_Error
     */
    public function markAsPaid(int $commission_id) {
        global $wpdb;
        
        $commission = $this->getCommission($commission_id);
        
        if (!$commission) {
            return new \WP_Error('not_found', __('Commission not found.', 'myprotector-platform'));
        }
        
        if ($commission->commission_status !== 'approved') {
            return new \WP_Error('invalid_status', __('Commission must be approved before marking as paid.', 'myprotector-platform'));
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_commissions',
            [
                'commission_status' => 'paid',
                'paid_at' => current_time('mysql'),
                'paid_amount' => $commission->commission_amount,
            ],
            ['commission_id' => $commission_id],
            ['%s', '%s', '%f'],
            ['%d']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Database error.', 'myprotector-platform'));
        }
        
        // Update reseller earnings
        $reseller_service = new ResellerService();
        $reseller_service->moveEarningsToPaid($commission->reseller_id, $commission->commission_amount);
        
        return true;
    }

    /**
     * Get commission by ID
     * 
     * @param int $commission_id
     * @return object|null
     */
    public function getCommission(int $commission_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_commissions WHERE commission_id = %d",
                $commission_id
            )
        );
    }

    /**
     * Generate commission report
     * 
     * @param int $reseller_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function generateReport(int $reseller_id, string $start_date, string $end_date): array {
        global $wpdb;
        
        // Summary by type
        $by_type = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT commission_type, 
                        SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as pending,
                        SUM(CASE WHEN commission_status = 'approved' THEN commission_amount ELSE 0 END) as approved,
                        SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as paid,
                        COUNT(*) as count
                FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d 
                AND created_at BETWEEN %s AND %s
                GROUP BY commission_type",
                $reseller_id,
                $start_date,
                $end_date . ' 23:59:59'
            ),
            ARRAY_A
        );
        
        // Daily breakdown
        $daily = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE(created_at) as date, 
                        SUM(commission_amount) as total, 
                        COUNT(*) as count
                FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d 
                AND created_at BETWEEN %s AND %s
                GROUP BY DATE(created_at)
                ORDER BY date DESC",
                $reseller_id,
                $start_date,
                $end_date . ' 23:59:59'
            ),
            ARRAY_A
        );
        
        // Totals
        $totals = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT 
                    SUM(commission_amount) as total_amount,
                    COUNT(*) as total_count,
                    SUM(CASE WHEN commission_status = 'paid' THEN commission_amount ELSE 0 END) as total_paid,
                    SUM(CASE WHEN commission_status = 'pending' THEN commission_amount ELSE 0 END) as total_pending,
                    SUM(CASE WHEN commission_status = 'approved' THEN commission_amount ELSE 0 END) as total_approved
                FROM {$wpdb->prefix}mp_commissions 
                WHERE reseller_id = %d 
                AND created_at BETWEEN %s AND %s",
                $reseller_id,
                $start_date,
                $end_date . ' 23:59:59'
            ),
            ARRAY_A
        );
        
        return [
            'period' => [
                'start' => $start_date,
                'end' => $end_date,
            ],
            'totals' => $totals,
            'by_type' => $by_type,
            'daily' => $daily,
        ];
    }

    /**
     * Update reseller pending earnings
     * 
     * @param int $reseller_id
     * @param float $amount
     * @return void
     */
    protected function updateResellerPendingEarnings(int $reseller_id, float $amount): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}mp_resellers 
                SET pending_earnings = pending_earnings + %f 
                WHERE reseller_id = %d",
                $amount,
                $reseller_id
            )
        );
    }

    /**
     * Get reseller (helper)
     * 
     * @param int $reseller_id
     * @return object|null
     */
    protected function getReseller(int $reseller_id): ?object {
        $service = new ResellerService();
        return $service->getReseller($reseller_id);
    }
}