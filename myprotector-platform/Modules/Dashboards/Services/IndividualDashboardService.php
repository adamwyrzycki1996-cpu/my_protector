<?php
/**
 * MyProtector Platform - Individual Dashboard Service
 * 
 * @package MyProtector\Modules\Dashboards\Services
 */

namespace MyProtector\Modules\Dashboards\Services;

class IndividualDashboardService {
    /**
     * Get user stats
     * 
     * @param int $user_id
     * @return array
     */
    public function getStats(int $user_id): array {
        $reviews = $this->getUserReviews($user_id);
        $notifications = $this->getUnreadNotificationCount($user_id);
        
        return [
            'total_reviews' => count($reviews),
            'unread_notifications' => $notifications,
            'member_since' => $this->getMemberSince($user_id),
            'recent_reviews' => array_slice($reviews, 0, 5),
        ];
    }

    /**
     * Get user reviews
     * 
     * @param int $user_id
     * @return array
     */
    public function getUserReviews(int $user_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, b.business_name 
                FROM {$table} r 
                JOIN {$wpdb->prefix}mp_businesses b ON r.business_id = b.business_id 
                WHERE r.user_id = %d AND r.review_status = 'approved' 
                ORDER BY r.published_at DESC",
                $user_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get unread notification count
     * 
     * @param int $user_id
     * @return int
     */
    protected function getUnreadNotificationCount(int $user_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_notifications WHERE user_id = %d AND is_read = 0",
                $user_id
            )
        );
    }

    /**
     * Get member since date
     * 
     * @param int $user_id
     * @return string
     */
    protected function getMemberSince(int $user_id): string {
        $user = get_userdata($user_id);
        return $user->user_registered ?? '';
    }

    /**
     * Get user activity
     * 
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getActivity(int $user_id, int $limit = 10): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_notifications';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
                $user_id,
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }
}