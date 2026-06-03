<?php
/**
 * MyProtector Platform - Business Pages Service
 * 
 * @package MyProtector\Modules\BusinessPages\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessPages\Services;

class BusinessPagesService {
    /**
     * Get business by slug
     * 
     * @param string $slug
     * @return object|null
     */
    public function getBusinessBySlug(string $slug) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_businesses';
        
        $business = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE business_slug = %s AND business_status = 'active' LIMIT 1",
                $slug
            )
        );
        
        if (!$business) {
            return null;
        }
        
        // Get traffic signal data
        $signal_table = $wpdb->prefix . 'mp_traffic_signals';
        $signal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$signal_table} WHERE business_id = %d",
                $business->business_id
            )
        );
        
        if ($signal) {
            $business->traffic_signal = $signal;
        }
        
        return $business;
    }

    /**
     * Get business reviews
     * 
     * @param int $business_id
     * @param array $args
     * @return array
     */
    public function getBusinessReviews(int $business_id, array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'page' => 1,
            'per_page' => 10,
            'sort' => 'recent',
            'rating' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $reviews_table = $wpdb->prefix . 'mp_reviews';
        $offset = ($args['page'] - 1) * $args['per_page'];
        
        // Build WHERE clause
        $where = [
            "business_id = {$business_id}",
            "review_status = 'approved'"
        ];
        
        if ($args['rating'] > 0) {
            $where[] = $wpdb->prepare("review_rating = %d", $args['rating']);
        }
        
        $where_clause = 'WHERE ' . implode(' AND ', $where);
        
        // Build ORDER BY clause
        $order_by = match($args['sort']) {
            'highest' => 'review_rating DESC, published_at DESC',
            'lowest' => 'review_rating ASC, published_at DESC',
            'helpful' => 'helpful_count DESC, published_at DESC',
            default => 'published_at DESC',
        };
        
        // Get total count
        $total = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$reviews_table} {$where_clause}"
        );
        
        // Get reviews
        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$reviews_table} {$where_clause} ORDER BY {$order_by} LIMIT %d OFFSET %d",
                $args['per_page'],
                $offset
            ),
            ARRAY_A
        );
        
        // Get reviewer info and responses
        foreach ($reviews as &$review) {
            $review['reviewer'] = $this->getReviewerInfo($review['user_id']);
            $review['images'] = $this->getReviewImages($review['review_id']);
            $review['response'] = $this->getReviewResponse($review['review_id']);
        }
        
        return [
            'items' => $reviews,
            'total' => $total,
            'pages' => ceil($total / $args['per_page']),
        ];
    }

    /**
     * Get reviewer info
     * 
     * @param int $user_id
     * @return array
     */
    protected function getReviewerInfo(int $user_id): array {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return [
                'name' => 'Anonymous',
                'avatar' => get_avatar_url('', ['default' => 'mystery']),
            ];
        }
        
        return [
            'name' => $user->display_name ?: $user->user_login,
            'avatar' => get_avatar_url($user_id),
            'joined' => human_time_diff(strtotime($user->user_registered)),
        ];
    }

    /**
     * Get review images
     * 
     * @param int $review_id
     * @return array
     */
    protected function getReviewImages(int $review_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_review_images';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT image_url, thumbnail_url FROM {$table} WHERE review_id = %d AND is_approved = 1",
                $review_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get review response
     * 
     * @param int $review_id
     * @return array|null
     */
    protected function getReviewResponse(int $review_id): ?array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        $response = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT response_content, response_date FROM {$table} WHERE review_id = %d AND response_content IS NOT NULL",
                $review_id
            ),
            ARRAY_A
        );
        
        return $response ?: null;
    }

    /**
     * Submit business response to review
     * 
     * @param int $review_id
     * @param string $content
     * @return array|\WP_Error
     */
    public function submitBusinessResponse(int $review_id, string $content) {
        global $wpdb;
        
        $review = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT r.*, b.user_id as business_user_id 
                FROM {$wpdb->prefix}mp_reviews r 
                JOIN {$wpdb->prefix}mp_businesses b ON r.business_id = b.business_id 
                WHERE r.review_id = %d",
                $review_id
            )
        );
        
        if (!$review) {
            return new \WP_Error('review_not_found', __('Review not found.', 'myprotector-platform'));
        }
        
        $current_user = get_current_user_id();
        
        // Check if user owns the business
        if ($review->business_user_id != $current_user && !current_user_can('manage_myprotector')) {
            return new \WP_Error('unauthorized', __('You are not authorized to respond to this review.', 'myprotector-platform'));
        }
        
        // Update the review with response
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_reviews',
            [
                'response_content' => $content,
                'response_date' => current_time('mysql'),
                'has_response' => 1,
            ],
            ['review_id' => $review_id],
            ['%s', '%s', '%d'],
            ['%d']
        );
        
        if ($result === false) {
            return new \WP_Error('db_error', __('Database error occurred.', 'myprotector-platform'));
        }
        
        // Update business response metrics
        $this->updateBusinessResponseMetrics($review->business_id);
        
        return [
            'content' => $content,
            'date' => current_time('mysql'),
        ];
    }

    /**
     * Update business response metrics
     * 
     * @param int $business_id
     * @return void
     */
    protected function updateBusinessResponseMetrics(int $business_id): void {
        global $wpdb;
        
        $reviews_table = $wpdb->prefix . 'mp_reviews';
        
        // Calculate metrics
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$reviews_table} WHERE business_id = %d AND review_status = 'approved'",
                $business_id
            )
        );
        
        $with_response = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$reviews_table} WHERE business_id = %d AND review_status = 'approved' AND has_response = 1",
                $business_id
            )
        );
        
        $response_rate = $total > 0 ? ($with_response / $total) * 100 : 0;
        
        // Update business
        $wpdb->update(
            $wpdb->prefix . 'mp_businesses',
            ['response_rate' => $response_rate],
            ['business_id' => $business_id],
            ['%f'],
            ['%d']
        );
    }

    /**
     * Mark review as helpful
     * 
     * @param int $review_id
     * @return int
     */
    public function markReviewHelpful(int $review_id): int {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        $result = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET helpful_count = helpful_count + 1 WHERE review_id = %d",
                $review_id
            )
        );
        
        return (int) $wpdb->get_var(
            $wpdb->prepare("SELECT helpful_count FROM {$table} WHERE review_id = %d", $review_id)
        );
    }

    /**
     * Report review
     * 
     * @param int $review_id
     * @param string $reason
     * @return true|\WP_Error
     */
    public function reportReview(int $review_id, string $reason) {
        global $wpdb;
        
        $review = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT review_id FROM {$wpdb->prefix}mp_reviews WHERE review_id = %d",
                $review_id
            )
        );
        
        if (!$review) {
            return new \WP_Error('review_not_found', __('Review not found.', 'myprotector-platform'));
        }
        
        // Insert report (would need a reports table - simplified for now)
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_notifications',
            [
                'user_id' => 1, // Admin
                'notification_type' => 'alert',
                'notification_title' => 'Review Report',
                'notification_message' => sprintf(
                    __('A review (ID: %d) was reported. Reason: %s', 'myprotector-platform'),
                    $review_id,
                    $reason
                ),
                'related_type' => 'review',
                'related_id' => $review_id,
                'priority' => 'high',
            ],
            ['%d', '%s', '%s', '%s', '%s', '%d', '%s']
        );
        
        return true;
    }

    /**
     * Get rating distribution
     * 
     * @param int $business_id
     * @return array
     */
    public function getRatingDistribution(int $business_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        $distribution = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT review_rating, COUNT(*) as count 
                FROM {$table} 
                WHERE business_id = %d AND review_status = 'approved' 
                GROUP BY review_rating 
                ORDER BY review_rating DESC",
                $business_id
            ),
            ARRAY_A
        );
        
        // Format as array with all ratings
        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = 0;
        }
        
        foreach ($distribution as $row) {
            $result[(int)$row['review_rating']] = (int)$row['count'];
        }
        
        return $result;
    }

    /**
     * Get latest reviews
     * 
     * @param int $business_id
     * @param int $limit
     * @return array
     */
    public function getLatestReviews(int $business_id, int $limit = 5): array {
        return $this->getBusinessReviews($business_id, [
            'page' => 1,
            'per_page' => $limit,
            'sort' => 'recent',
        ])['items'];
    }
}