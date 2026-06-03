<?php
/**
 * MyProtector Platform - Reseller Service
 * 
 * @package MyProtector\Modules\Resellers\Services
 */

namespace MyProtector\Modules\Resellers\Services;

class ResellerService {
    /**
     * Apply to become a reseller
     * 
     * @param array $data
     * @return int|\WP_Error
     */
    public function apply(array $data) {
        global $wpdb;
        
        // Check if user already has a reseller account
        $existing = $this->getResellerByUserId($data['user_id']);
        if ($existing) {
            return new \WP_Error('already_exists', __('You already have a reseller account.', 'myprotector-platform'));
        }
        
        // Generate unique referral code
        $referral_code = $this->generateReferralCode();
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_resellers',
            [
                'user_id' => $data['user_id'],
                'company_name' => $data['company_name'],
                'company_url' => $data['company_url'],
                'referral_code' => $referral_code,
                'commission_rate' => 10.00, // Default 10%
                'payout_method' => $data['payout_method'],
                'payout_details' => json_encode($data['payout_details'] ?? []),
                'reseller_status' => 'pending',
            ],
            ['%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Database error occurred.', 'myprotector-platform'));
        }
        
        return $wpdb->insert_id;
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
                "SELECT * FROM {$wpdb->prefix}mp_resellers WHERE user_id = %d LIMIT 1",
                $user_id
            )
        );
    }

    /**
     * Get reseller by referral code
     * 
     * @param string $code
     * @return object|null
     */
    public function getResellerByCode(string $code) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_resellers WHERE referral_code = %s LIMIT 1",
                $code
            )
        );
    }

    /**
     * Get reseller by ID
     * 
     * @param int $reseller_id
     * @return object|null
     */
    public function getReseller(int $reseller_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_resellers WHERE reseller_id = %d LIMIT 1",
                $reseller_id
            )
        );
    }

    /**
     * Generate unique referral code
     * 
     * @return string
     */
    protected function generateReferralCode(): string {
        do {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            
            global $wpdb;
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}mp_resellers WHERE referral_code = %s",
                    $code
                )
            );
        } while ($exists > 0);
        
        return $code;
    }

    /**
     * Approve reseller application
     * 
     * @param int $reseller_id
     * @param int $admin_id
     * @return bool|\WP_Error
     */
    public function approve(int $reseller_id, int $admin_id): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_resellers',
            [
                'reseller_status' => 'active',
                'approved_at' => current_time('mysql'),
                'approved_by' => $admin_id,
            ],
            ['reseller_id' => $reseller_id],
            ['%s', '%s', '%d'],
            ['%d']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Database error.', 'myprotector-platform'));
        }
        
        // Assign reseller role to user
        $reseller = $this->getReseller($reseller_id);
        $user = get_userdata($reseller->user_id);
        $user->add_role('mp_reseller');
        
        return true;
    }

    /**
     * Suspend reseller
     * 
     * @param int $reseller_id
     * @return bool
     */
    public function suspend(int $reseller_id): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_resellers',
            ['reseller_status' => 'suspended'],
            ['reseller_id' => $reseller_id],
            ['%s'],
            ['%d']
        );
        
        return $result !== false;
    }

    /**
     * Increment referral count
     * 
     * @param int $reseller_id
     * @return void
     */
    public function incrementReferralCount(int $reseller_id): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}mp_resellers SET total_referrals = total_referrals + 1 WHERE reseller_id = %d",
                $reseller_id
            )
        );
    }

    /**
     * Get all resellers
     * 
     * @param array $args
     * @return array
     */
    public function getResellers(array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'status' => null,
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = '1=1';
        $values = [];
        
        if ($args['status']) {
            $where .= ' AND reseller_status = %s';
            $values[] = $args['status'];
        }
        
        $order = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $sql = "SELECT * FROM {$wpdb->prefix}mp_resellers WHERE {$where} ORDER BY {$order} LIMIT %d OFFSET %d";
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        return $wpdb->get_results(
            $wpdb->prepare($sql, $values),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Update reseller earnings
     * 
     * @param int $reseller_id
     * @param float $amount
     * @param string $type pending|total
     * @return void
     */
    public function updateEarnings(int $reseller_id, float $amount, string $type = 'pending'): void {
        global $wpdb;
        
        $column = $type === 'pending' ? 'pending_earnings' : 'total_earnings';
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}mp_resellers SET {$column} = {$column} + %f WHERE reseller_id = %d",
                $amount,
                $reseller_id
            )
        );
    }

    /**
     * Move earnings from pending to total (after payout)
     * 
     * @param int $reseller_id
     * @param float $amount
     * @return void
     */
    public function moveEarningsToPaid(int $reseller_id, float $amount): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}mp_resellers 
                SET pending_earnings = pending_earnings - %f, 
                    paid_earnings = paid_earnings + %f,
                    last_payout_at = NOW()
                WHERE reseller_id = %d",
                $amount,
                $amount,
                $reseller_id
            )
        );
    }
}