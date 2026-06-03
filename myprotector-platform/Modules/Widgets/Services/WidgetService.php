<?php
/**
 * MyProtector Platform - Widget Service
 * 
 * @package MyProtector\Modules\Widgets\Services
 */

namespace MyProtector\Modules\Widgets\Services;

class WidgetService {
    /**
     * Get business rating data
     * 
     * @param int $business_id
     * @return array|null
     */
    public function getBusinessRatingData(int $business_id): ?array {
        global $wpdb;
        
        $business = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT business_id, business_name, avg_rating, total_reviews, logo_url 
                FROM {$wpdb->prefix}mp_businesses 
                WHERE business_id = %d AND business_status = 'active'",
                $business_id
            )
        );
        
        if (!$business) {
            return null;
        }
        
        return [
            'business_id' => $business->business_id,
            'business_name' => $business->business_name,
            'avg_rating' => (float) $business->avg_rating,
            'total_reviews' => (int) $business->total_reviews,
            'logo_url' => $business->logo_url,
        ];
    }

    /**
     * Get trust data
     * 
     * @param int $business_id
     * @return array|null
     */
    public function getTrustData(int $business_id): ?array {
        global $wpdb;
        
        $signal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_traffic_signals WHERE business_id = %d",
                $business_id
            )
        );
        
        if (!$signal) {
            return [
                'status' => 'bad',
                'score' => 0,
                'label' => 'Caution',
            ];
        }
        
        $statuses = [
            'walking' => ['Walking Safe', '🚶'],
            'shopping' => ['Shopping Safe', '🛒'],
            'bad' => ['Caution', '⚠️'],
        ];
        
        $info = $statuses[$signal->trust_status] ?? $statuses['bad'];
        
        return [
            'status' => $signal->trust_status,
            'score' => (float) $signal->trust_score,
            'label' => $info[0],
            'icon' => $info[1],
            'requirements_fulfilled' => (int) $signal->requirements_fulfilled,
            'requirements_total' => (int) $signal->requirements_total,
        ];
    }

    /**
     * Get reviews for widget
     * 
     * @param int $business_id
     * @param int $limit
     * @return array
     */
    public function getReviewsForWidget(int $business_id, int $limit = 5): array {
        global $wpdb;
        
        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.review_id, r.review_title, r.review_content, r.review_rating, 
                        r.published_at, r.helpful_count, u.display_name as reviewer_name,
                        (SELECT meta_value FROM {$wpdb->prefix}usermeta WHERE user_id = r.user_id AND meta_key = 'mp_avatar_url' LIMIT 1) as reviewer_avatar
                FROM {$wpdb->prefix}mp_reviews r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE r.business_id = %d AND r.review_status = 'approved'
                ORDER BY r.published_at DESC
                LIMIT %d",
                $business_id,
                $limit
            ),
            ARRAY_A
        );
        
        return $reviews ?: [];
    }

    /**
     * Get full business data
     * 
     * @param int $business_id
     * @return array|null
     */
    public function getBusinessData(int $business_id): ?array {
        $rating_data = $this->getBusinessRatingData($business_id);
        $trust_data = $this->getTrustData($business_id);
        $reviews = $this->getReviewsForWidget($business_id, 3);
        
        if (!$rating_data) {
            return null;
        }
        
        return [
            'business' => $rating_data,
            'trust' => $trust_data,
            'reviews' => $reviews,
        ];
    }

    /**
     * Get rating distribution
     * 
     * @param int $business_id
     * @return array
     */
    public function getRatingDistribution(int $business_id): array {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT review_rating, COUNT(*) as count 
                FROM {$wpdb->prefix}mp_reviews 
                WHERE business_id = %d AND review_status = 'approved' 
                GROUP BY review_rating 
                ORDER BY review_rating DESC",
                $business_id
            ),
            ARRAY_A
        );
        
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = 0;
        }
        
        foreach ($results as $row) {
            $distribution[(int)$row['review_rating']] = (int)$row['count'];
        }
        
        return $distribution;
    }
}