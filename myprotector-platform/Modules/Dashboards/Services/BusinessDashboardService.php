<?php
/**
 * MyProtector Platform - Business Dashboard Service
 * 
 * @package MyProtector\Modules\Dashboards\Services
 */

namespace MyProtector\Modules\Dashboards\Services;

class BusinessDashboardService {
    /**
     * Get business owner stats
     * 
     * @param int $user_id
     * @return array
     */
    public function getStats(int $user_id): array {
        $business = $this->getUserBusiness($user_id);
        
        if (!$business) {
            return [
                'has_business' => false,
            ];
        }
        
        $reviews = $this->getBusinessReviews($business->business_id);
        $analytics = $this->getReviewAnalytics($business->business_id);
        
        return [
            'has_business' => true,
            'business_id' => $business->business_id,
            'business_name' => $business->business_name,
            'business_status' => $business->business_status,
            'avg_rating' => $business->avg_rating,
            'total_reviews' => $business->total_reviews,
            'response_rate' => $business->response_rate,
            'trust_status' => $this->getTrustStatus($business->business_id),
            'recent_reviews' => array_slice($reviews, 0, 5),
            'analytics' => $analytics,
            'missing_requirements' => $this->getMissingRequirements($business->business_id),
        ];
    }

    /**
     * Get user's business
     * 
     * @param int $user_id
     * @return object|null
     */
    public function getUserBusiness(int $user_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE user_id = %d LIMIT 1",
                $user_id
            )
        );
    }

    /**
     * Get business reviews
     * 
     * @param int $business_id
     * @return array
     */
    public function getBusinessReviews(int $business_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE business_id = %d AND review_status = 'approved' ORDER BY published_at DESC",
                $business_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get review analytics
     * 
     * @param int $business_id
     * @return array
     */
    public function getReviewAnalytics(int $business_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        // Rating distribution
        $distribution = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT review_rating, COUNT(*) as count FROM {$table} 
                WHERE business_id = %d AND review_status = 'approved' 
                GROUP BY review_rating ORDER BY review_rating DESC",
                $business_id
            ),
            ARRAY_A
        );
        
        // Monthly trends (last 6 months)
        $monthly = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT(published_at, '%%Y-%%m') as month, COUNT(*) as count, AVG(review_rating) as avg_rating 
                FROM {$table} 
                WHERE business_id = %d AND review_status = 'approved' 
                AND published_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(published_at, '%%Y-%%m')
                ORDER BY month ASC",
                $business_id
            ),
            ARRAY_A
        );
        
        return [
            'distribution' => $distribution,
            'monthly_trends' => $monthly,
        ];
    }

    /**
     * Get trust status
     * 
     * @param int $business_id
     * @return array
     */
    protected function getTrustStatus(int $business_id): array {
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
            ];
        }
        
        return [
            'status' => $signal->trust_status,
            'score' => $signal->trust_score,
            'requirements_fulfilled' => $signal->requirements_fulfilled,
            'requirements_total' => $signal->requirements_total,
        ];
    }

    /**
     * Get missing requirements
     * 
     * @param int $business_id
     * @return array
     */
    protected function getMissingRequirements(int $business_id): array {
        global $wpdb;
        
        $signal = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT requirements_met FROM {$wpdb->prefix}mp_traffic_signals WHERE business_id = %d",
                $business_id
            )
        );
        
        $business = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE business_id = %d",
                $business_id
            )
        );
        
        $missing = [];
        
        if (!$signal || $signal->requirements_met === null) {
            $missing[] = [
                'type' => 'reviews',
                'label' => 'Minimum Reviews',
                'description' => 'Get at least 5 reviews to improve your trust score.',
            ];
        }
        
        if (empty($business->insurance_url)) {
            $missing[] = [
                'type' => 'insurance',
                'label' => 'Insurance Information',
                'description' => 'Add your insurance details to build trust.',
            ];
        }
        
        if (empty($business->terms_url)) {
            $missing[] = [
                'type' => 'terms',
                'label' => 'Terms of Service',
                'description' => 'Add your terms page URL.',
            ];
        }
        
        if (empty($business->promise_page_url)) {
            $missing[] = [
                'type' => 'promise',
                'label' => 'Customer Promise',
                'description' => 'Create a promise page to set customer expectations.',
            ];
        }
        
        return $missing;
    }

    /**
     * Get pending reviews
     * 
     * @param int $business_id
     * @return array
     */
    public function getPendingReviews(int $business_id): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_reviews 
                WHERE business_id = %d AND review_status = 'pending' 
                ORDER BY created_at DESC",
                $business_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get recent reviews needing response
     * 
     * @param int $business_id
     * @param int $days
     * @return array
     */
    public function getReviewsNeedingResponse(int $business_id, int $days = 30): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_reviews 
                WHERE business_id = %d AND review_status = 'approved' 
                AND has_response = 0 
                AND published_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                ORDER BY published_at DESC",
                $business_id,
                $days
            ),
            ARRAY_A
        ) ?: [];
    }
}