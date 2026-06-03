<?php
namespace MyProtector\Modules\WooCommerce\Services;

class SubscriptionService {
    public function getSubscriptionStatus(int $user_id): array {
        $business = $this->getUserBusiness($user_id);
        
        if (!$business) {
            return ['is_active' => false, 'message' => 'No business found'];
        }
        
        // Check WooCommerce subscriptions
        if (class_exists('WC_Subscriptions')) {
            $subscriptions = wcs_get_subscriptions([
                'customer_id' => $user_id,
                'status' => 'active',
            ]);
            
            if (!empty($subscriptions)) {
                return [
                    'is_active' => true,
                    'subscription_id' => array_key_first($subscriptions),
                    'next_payment' => $subscriptions[array_key_first($subscriptions)]->get_date('next_payment'),
                ];
            }
        }
        
        return ['is_active' => false];
    }

    public function activateSubscription(int $user_id): void {
        global $wpdb;
        
        $business = $this->getUserBusiness($user_id);
        
        if ($business) {
            $wpdb->update(
                $wpdb->prefix . 'mp_businesses',
                ['subscription_status' => 'active'],
                ['business_id' => $business->business_id],
                ['%s'],
                ['%d']
            );
        }
    }

    public function deactivateSubscription(int $user_id): void {
        global $wpdb;
        
        $business = $this->getUserBusiness($user_id);
        
        if ($business) {
            $wpdb->update(
                $wpdb->prefix . 'mp_businesses',
                ['subscription_status' => 'cancelled'],
                ['business_id' => $business->business_id],
                ['%s'],
                ['%d']
            );
        }
    }

    protected function getUserBusiness(int $user_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE user_id = %d LIMIT 1",
                $user_id
            )
        );
    }
}