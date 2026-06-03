<?php
/**
 * MyProtector Platform - Business Verification Service
 * 
 * Handles verification logic for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Services;

use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Repositories\CompanyRepository;
use MyProtector\Core\Services\Container\ServiceContainer;

class BusinessVerificationService {
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
     * Trust score thresholds
     */
    const TRUST_THRESHOLD_WALKING = 66.67; // 2/3 requirements met
    const TRUST_THRESHOLD_SHOPPING = 100;  // All requirements met

    /**
     * Trust requirements
     */
    const REQUIREMENT_INSURANCE = 'insurance';
    const REQUIREMENT_TERMS = 'terms';
    const REQUIREMENT_PROMISE = 'promise';

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
     * Check if company meets all trust requirements
     * 
     * @param Company $company
     * @return bool
     */
    public function meetsAllRequirements(Company $company): bool {
        return $company->getTrustFillPercentage() === 100;
    }

    /**
     * Check if company meets specific requirement
     * 
     * @param Company $company
     * @param string $requirement
     * @return bool
     */
    public function meetsRequirement(Company $company, string $requirement): bool {
        $status = $company->getTrustRequirementsStatus();
        return $status[$requirement] ?? false;
    }

    /**
     * Get missing requirements for a company
     * 
     * @param Company $company
     * @return array
     */
    public function getMissingRequirements(Company $company): array {
        $status = $company->getTrustRequirementsStatus();
        return array_keys(array_filter($status, fn($met) => !$met));
    }

    /**
     * Calculate trust status based on requirements
     * 
     * @param Company $company
     * @return string 'walking', 'shopping', or 'bad'
     */
    public function calculateTrustStatus(Company $company): string {
        $percentage = $company->getTrustFillPercentage();

        if ($percentage >= self::TRUST_THRESHOLD_SHOPPING) {
            return 'shopping';
        }

        if ($percentage >= self::TRUST_THRESHOLD_WALKING) {
            return 'walking';
        }

        return 'bad';
    }

    /**
     * Calculate trust score
     * 
     * @param Company $company
     * @return float
     */
    public function calculateTrustScore(Company $company): float {
        $requirements_met = array_sum($company->getTrustRequirementsStatus());
        $total_requirements = 3;
        
        // Base score from requirements (0-66.67)
        $requirement_score = ($requirements_met / $total_requirements) * 66.67;
        
        // Add bonus for admin verification (if applicable)
        $admin_bonus = 0;
        if ($this->isAdminVerified($company->company_id)) {
            $admin_bonus = 33.33;
        }
        
        return min(100, $requirement_score + $admin_bonus);
    }

    /**
     * Check if company is admin verified
     * 
     * @param int $company_id
     * @return bool
     */
    public function isAdminVerified(int $company_id): bool {
        global $wpdb;
        
        $verified = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT admin_verified FROM {$wpdb->prefix}mp_traffic_light_status WHERE company_id = %d",
                $company_id
            )
        );

        return (bool) $verified;
    }

    /**
     * Mark company as admin verified
     * 
     * @param int $company_id
     * @param int $admin_id
     * @return bool
     */
    public function markAsVerified(int $company_id, int $admin_id): bool {
        global $wpdb;
        
        $result = $wpdb->replace(
            $wpdb->prefix . 'mp_traffic_light_status',
            [
                'company_id' => $company_id,
                'admin_verified' => 1,
                'last_calculated' => current_time('mysql'),
            ],
            ['%d', '%d', '%s']
        );

        if ($result !== false) {
            // Recalculate trust score
            $company = $this->repository->find($company_id);
            if ($company) {
                $new_score = $this->calculateTrustScore($company);
                $this->repository->updateTrustScore($company_id, $new_score);
            }
            
            do_action('mp_business_verified', $company_id, $admin_id);
        }

        return $result !== false;
    }

    /**
     * Remove admin verification
     * 
     * @param int $company_id
     * @return bool
     */
    public function removeVerification(int $company_id): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_traffic_light_status',
            [
                'admin_verified' => 0,
                'last_calculated' => current_time('mysql'),
            ],
            ['company_id' => $company_id],
            ['%d', '%s'],
            ['%d']
        );

        if ($result !== false) {
            // Recalculate trust score
            $company = $this->repository->find($company_id);
            if ($company) {
                $new_score = $this->calculateTrustScore($company);
                $this->repository->updateTrustScore($company_id, $new_score);
            }
            
            do_action('mp_business_unverified', $company_id);
        }

        return $result !== false;
    }

    /**
     * Update traffic light status for a company
     * 
     * @param int $company_id
     * @return void
     */
    public function updateTrafficLightStatus(int $company_id): void {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return;
        }

        $requirements = $company->getTrustRequirementsStatus();
        $trust_status = $this->calculateTrustStatus($company);
        $trust_score = $this->calculateTrustScore($company);
        $admin_verified = $this->isAdminVerified($company_id);

        global $wpdb;
        
        $wpdb->replace(
            $wpdb->prefix . 'mp_traffic_light_status',
            [
                'company_id' => $company_id,
                'trust_status' => $trust_status,
                'trust_score' => $trust_score,
                'traffic_light_color' => $this->getTrafficLightColor($trust_status),
                'requirements_met' => json_encode($requirements),
                'requirements_total' => 3,
                'insurance_added' => $requirements['insurance'] ? 1 : 0,
                'terms_added' => $requirements['terms'] ? 1 : 0,
                'promise_page_added' => $requirements['promise'] ? 1 : 0,
                'admin_verified' => $admin_verified ? 1 : 0,
                'last_calculated' => current_time('mysql'),
            ],
            ['%d', '%s', '%f', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s']
        );

        // Update company trust score
        $this->repository->updateTrustScore($company_id, $trust_score);
    }

    /**
     * Get traffic light color from status
     * 
     * @param string $status
     * @return string 'green', 'yellow', or 'red'
     */
    public function getTrafficLightColor(string $status): string {
        $colors = [
            'shopping' => 'green',
            'walking' => 'yellow',
            'bad' => 'red',
        ];

        return $colors[$status] ?? 'red';
    }

    /**
     * Recalculate trust status for all companies
     * 
     * @return int Number of companies updated
     */
    public function recalculateAllTrustScores(): int {
        $companies = $this->repository->getAll([
            'status' => Company::STATUS_APPROVED,
            'limit' => -1,
        ]);

        $count = 0;
        foreach ($companies as $company) {
            $this->updateTrafficLightStatus($company->company_id);
            $count++;
        }

        do_action('mp_trust_scores_recalculated', $count);

        return $count;
    }

    /**
     * Get requirement label
     * 
     * @param string $requirement
     * @return string
     */
    public static function getRequirementLabel(string $requirement): string {
        $labels = [
            self::REQUIREMENT_INSURANCE => __('Insurance Information', 'myprotector-platform'),
            self::REQUIREMENT_TERMS => __('Terms & Conditions', 'myprotector-platform'),
            self::REQUIREMENT_PROMISE => __('Promise Page', 'myprotector-platform'),
        ];

        return $labels[$requirement] ?? $requirement;
    }

    /**
     * Get requirement description
     * 
     * @param string $requirement
     * @return string
     */
    public static function getRequirementDescription(string $requirement): string {
        $descriptions = [
            self::REQUIREMENT_INSURANCE => __('Add your insurance provider name and policy URL to build trust.', 'myprotector-platform'),
            self::REQUIREMENT_TERMS => __('Include a link to your terms and conditions page.', 'myprotector-platform'),
            self::REQUIREMENT_PROMISE => __('Create a promise page showcasing your commitment to customers.', 'myprotector-platform'),
        ];

        return $descriptions[$requirement] ?? '';
    }

    /**
     * Validate URL for requirement
     * 
     * @param string $url
     * @return bool
     */
    public function validateRequirementUrl(string $url): bool {
        if (empty($url)) {
            return false;
        }

        // Must be a valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Must start with http:// or https://
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        return true;
    }
}