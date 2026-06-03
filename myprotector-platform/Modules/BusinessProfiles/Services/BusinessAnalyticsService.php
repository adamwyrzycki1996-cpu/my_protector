<?php
/**
 * MyProtector Platform - Business Analytics Service
 * 
 * Analytics and statistics for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Services;

use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Repositories\CompanyRepository;
use MyProtector\Core\Services\Container\ServiceContainer;

class BusinessAnalyticsService {
    /**
     * Service container
     * 
     * @var ServiceContainer
     */
    protected $container;

    /**
     * Repository instance
     * 
     * @var CompanyRepository
     */
    protected $repository;

    /**
     * Constructor
     * 
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container) {
        $this->container = $container;
        $this->repository = new CompanyRepository();
    }

    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats(): array {
        return [
            'total_businesses' => $this->getTotalCount(),
            'pending_businesses' => $this->getCountByStatus(Company::STATUS_PENDING),
            'approved_businesses' => $this->getCountByStatus(Company::STATUS_APPROVED),
            'rejected_businesses' => $this->getCountByStatus(Company::STATUS_REJECTED),
            'suspended_businesses' => $this->getCountByStatus(Company::STATUS_SUSPENDED),
            'avg_trust_score' => $this->getAverageTrustScore(),
            'featured_businesses' => $this->getFeaturedCount(),
        ];
    }

    /**
     * Get total business count
     * 
     * @return int
     */
    public function getTotalCount(): int {
        global $wpdb;
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mp_companies");
    }

    /**
     * Get count by status
     * 
     * @param string $status
     * @return int
     */
    public function getCountByStatus(string $status): int {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_companies WHERE status = %s",
                $status
            )
        );
    }

    /**
     * Get average trust score
     * 
     * @return float
     */
    public function getAverageTrustScore(): float {
        global $wpdb;
        $avg = $wpdb->get_var(
            "SELECT AVG(trust_score) FROM {$wpdb->prefix}mp_companies WHERE status = 'approved'"
        );
        return round((float) $avg, 2);
    }

    /**
     * Get featured businesses count
     * 
     * @return int
     */
    public function getFeaturedCount(): int {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mp_companies WHERE is_featured = 1 AND status = 'approved'"
        );
    }

    /**
     * Get top rated businesses
     * 
     * @param int $limit
     * @return array
     */
    public function getTopRated(int $limit = 10): array {
        $companies = $this->repository->getAll([
            'status' => Company::STATUS_APPROVED,
            'limit' => $limit,
            'orderby' => 'avg_rating',
            'order' => 'DESC',
        ]);

        return array_map(fn($c) => $c->toShortArray(), $companies);
    }

    /**
     * Get businesses by category
     * 
     * @param int $category_id
     * @param int $limit
     * @return array
     */
    public function getByCategory(int $category_id, int $limit = 20): array {
        $companies = $this->repository->getAll([
            'status' => Company::STATUS_APPROVED,
            'category' => $category_id,
            'limit' => $limit,
        ]);

        return array_map(fn($c) => $c->toShortArray(), $companies);
    }

    /**
     * Search businesses
     * 
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 20): array {
        $companies = $this->repository->getAll([
            'status' => Company::STATUS_APPROVED,
            'search' => $query,
            'limit' => $limit,
        ]);

        return array_map(fn($c) => $c->toShortArray(), $companies);
    }

    /**
     * Get recent submissions
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentSubmissions(int $limit = 10): array {
        $companies = $this->repository->getAll([
            'status' => Company::STATUS_PENDING,
            'limit' => $limit,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ]);

        return array_map(function($company) {
            $data = $company->toShortArray();
            $owner = get_userdata($company->user_id);
            $data['owner_name'] = $owner ? $owner->display_name : '';
            $data['owner_email'] = $owner ? $owner->user_email : '';
            return $data;
        }, $companies);
    }

    /**
     * Get category distribution
     * 
     * @return array
     */
    public function getCategoryDistribution(): array {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT company_category, COUNT(*) as count 
             FROM {$wpdb->prefix}mp_companies 
             WHERE status = 'approved' AND company_category > 0 
             GROUP BY company_category"
        );

        $distribution = [];
        foreach ($results as $row) {
            $term = get_term($row->company_category, 'mp_company_category');
            $distribution[] = [
                'category_id' => (int) $row->company_category,
                'category_name' => $term ? $term->name : __('Uncategorized', 'myprotector-platform'),
                'count' => (int) $row->count,
            ];
        }

        return $distribution;
    }

    /**
     * Get trust level distribution
     * 
     * @return array
     */
    public function getTrustLevelDistribution(): array {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT 
                CASE 
                    WHEN trust_score >= 100 THEN 'shopping'
                    WHEN trust_score >= 66.67 THEN 'walking'
                    ELSE 'bad'
                END as trust_level,
                COUNT(*) as count
             FROM {$wpdb->prefix}mp_companies 
             WHERE status = 'approved'
             GROUP BY trust_level"
        );

        $labels = [
            'shopping' => __('Shopping', 'myprotector-platform'),
            'walking' => __('Walking', 'myprotector-platform'),
            'bad' => __('Bad', 'myprotector-platform'),
        ];

        $distribution = [];
        foreach ($results as $row) {
            $distribution[] = [
                'trust_level' => $row->trust_level,
                'label' => $labels[$row->trust_level] ?? $row->trust_level,
                'count' => (int) $row->count,
            ];
        }

        return $distribution;
    }

    /**
     * Get company growth over time
     * 
     * @param int $days
     * @return array
     */
    public function getGrowthData(int $days = 30): array {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(created_at) as date, COUNT(*) as count
             FROM {$wpdb->prefix}mp_companies
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            $days
        ));

        return array_map(fn($row) => [
            'date' => $row->date,
            'count' => (int) $row->count,
        ], $results);
    }

    /**
     * Get owner statistics for a user
     * 
     * @param int $user_id
     * @return array
     */
    public function getOwnerStats(int $user_id): array {
        $company = $this->repository->findByUserId($user_id);
        
        if (!$company) {
            return [
                'has_business' => false,
                'company' => null,
            ];
        }

        return [
            'has_business' => true,
            'company' => $company->toArray(),
            'review_stats' => $this->getCompanyReviewStats($company->company_id),
            'trust_requirements' => $company->getTrustRequirementsStatus(),
        ];
    }

    /**
     * Get review statistics for a company
     * 
     * @param int $company_id
     * @return array
     */
    protected function getCompanyReviewStats(int $company_id): array {
        global $wpdb;
        
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_reviews,
                AVG(review_rating) as avg_rating,
                SUM(CASE WHEN review_rating >= 4 THEN 1 ELSE 0 END) as positive_reviews,
                SUM(CASE WHEN review_rating <= 2 THEN 1 ELSE 0 END) as negative_reviews
             FROM {$wpdb->prefix}mp_reviews
             WHERE company_id = %d AND review_status = 'approved'",
            $company_id
        ));

        return [
            'total_reviews' => (int) ($stats->total_reviews ?? 0),
            'avg_rating' => round((float) ($stats->avg_rating ?? 0), 1),
            'positive_reviews' => (int) ($stats->positive_reviews ?? 0),
            'negative_reviews' => (int) ($stats->negative_reviews ?? 0),
        ];
    }

    /**
     * Export businesses data
     * 
     * @param array $args
     * @return array
     */
    public function export(array $args = []): array {
        $companies = $this->repository->getAll(array_merge($args, ['limit' => -1]));

        $data = [];
        foreach ($companies as $company) {
            $owner = get_userdata($company->user_id);
            $data[] = [
                'company_id' => $company->company_id,
                'company_name' => $company->company_name,
                'company_slug' => $company->company_slug,
                'company_website' => $company->company_website,
                'company_email' => $company->company_email,
                'owner_name' => $owner ? $owner->display_name : '',
                'owner_email' => $owner ? $owner->user_email : '',
                'status' => $company->status,
                'trust_score' => $company->trust_score,
                'total_reviews' => $company->total_reviews,
                'avg_rating' => $company->avg_rating,
                'created_at' => date_i18n('Y-m-d H:i:s', $company->created_at),
            ];
        }

        return $data;
    }
}