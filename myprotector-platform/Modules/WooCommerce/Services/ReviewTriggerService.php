<?php
namespace MyProtector\Modules\WooCommerce\Services;

class ReviewTriggerService {
    public function scheduleReviewInvitation(\WC_Order $order): void {
        $user_id = $order->get_user_id();
        $business = $this->getUserBusiness($user_id);
        
        if (!$business) {
            return;
        }
        
        $email = $order->get_billing_email();
        
        if (!$email) {
            return;
        }
        
        // Schedule review invitation after 7 days
        $queue = new \MyProtector\Modules\Emails\Services\EmailQueue();
        $queue->scheduleReviewInvitation($email, $business->business_id, $order->get_id(), 7);
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