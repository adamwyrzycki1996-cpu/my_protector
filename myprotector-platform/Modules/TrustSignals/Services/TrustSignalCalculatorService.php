<?php
/**
 * MyProtector Platform - Trust Signal Calculator Service
 * 
 * Calculates trust signal status based on requirements
 * 
 * @package MyProtector\Modules\TrustSignals\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Services;

use MyProtector\Modules\TrustSignals\TrustSignals;

class TrustSignalCalculatorService {
    /**
     * Green requirements (all must be met)
     * 
     * @var array
     */
    protected $greenRequirements = [
        TrustSignals::REQUIREMENT_INSURANCE_PAGE,
        TrustSignals::REQUIREMENT_REFUND_HISTORY,
        TrustSignals::REQUIREMENT_CLAIMS_PAGE,
        TrustSignals::REQUIREMENT_TERMS_PAGE,
        TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION,
    ];

    /**
     * Amber requirements (at least one must be met)
     * 
     * @var array
     */
    protected $amberRequirements = [
        TrustSignals::REQUIREMENT_INSURANCE_PAGE,
        TrustSignals::REQUIREMENT_TERMS_PAGE,
    ];

    /**
     * Check if calculation can be performed
     * 
     * @param int $companyId
     * @return bool
     */
    public function canCalculate(int $companyId): bool {
        global $wpdb;
        
        $company = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT company_id FROM {$wpdb->prefix}mp_companies WHERE company_id = %d",
                $companyId
            )
        );

        return $company !== null;
    }

    /**
     * Calculate trust signal for a company
     * 
     * @param int $companyId
     * @param array $companyData
     * @return array
     */
    public function calculate(int $companyId, array $companyData): array {
        $requirements = $this->evaluateAllRequirements($companyId, $companyData);
        
        $status = $this->determineStatus($requirements);

        return [
            'status' => $status,
            'requirements' => $requirements,
            'requirements_met' => $this->countMetRequirements($requirements),
            'requirements_total' => count($requirements),
            'calculated_at' => current_time('mysql'),
        ];
    }

    /**
     * Evaluate all requirements for a company
     * 
     * @param int $companyId
     * @param array $companyData
     * @return array
     */
    public function evaluateAllRequirements(int $companyId, array $companyData): array {
        $requirements = [];

        foreach ($this->greenRequirements as $req) {
            $requirements[$req] = $this->evaluateRequirement($req, $companyId, $companyData);
        }

        return $requirements;
    }

    /**
     * Evaluate a single requirement
     * 
     * @param string $requirement
     * @param int $companyId
     * @param array $companyData
     * @return array
     */
    protected function evaluateRequirement(string $requirement, int $companyId, array $companyData): array {
        $result = [
            'requirement' => $requirement,
            'met' => false,
            'label' => $this->getRequirementLabel($requirement),
            'description' => $this->getRequirementDescription($requirement),
            'value' => null,
            'check_method' => 'automatic',
        ];

        switch ($requirement) {
            case TrustSignals::REQUIREMENT_INSURANCE_PAGE:
                $result['met'] = !empty($companyData['insurance_url']);
                $result['value'] = $companyData['insurance_url'] ?: null;
                break;

            case TrustSignals::REQUIREMENT_REFUND_HISTORY:
                $result = $this->checkRefundHistory($companyId, $result);
                break;

            case TrustSignals::REQUIREMENT_CLAIMS_PAGE:
                $result = $this->checkClaimsPage($companyId, $result);
                break;

            case TrustSignals::REQUIREMENT_TERMS_PAGE:
                $result['met'] = !empty($companyData['terms_url']);
                $result['value'] = $companyData['terms_url'] ?: null;
                break;

            case TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION:
                $result = $this->checkActiveSubscription($companyId, $result);
                break;
        }

        return $result;
    }

    /**
     * Check refund history requirement
     * 
     * @param int $companyId
     * @param array $result
     * @return array
     */
    protected function checkRefundHistory(int $companyId, array $result): array {
        global $wpdb;
        
        $refundCount = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_refunds 
                WHERE company_id = %d AND refunded_at IS NOT NULL",
                $companyId
            )
        );

        $result['met'] = $refundCount > 0;
        $result['value'] = $refundCount;

        return $result;
    }

    /**
     * Check claims page requirement
     * 
     * @param int $companyId
     * @param array $result
     * @return array
     */
    protected function checkClaimsPage(int $companyId, array $result): array {
        global $wpdb;
        
        $claimsCount = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_claims 
                WHERE company_id = %d AND status = 'approved'",
                $companyId
            )
        );

        $result['met'] = $claimsCount > 0;
        $result['value'] = $claimsCount;

        return $result;
    }

    /**
     * Check active subscription requirement
     * 
     * @param int $companyId
     * @param array $result
     * @return array
     */
    protected function checkActiveSubscription(int $companyId, array $result): array {
        global $wpdb;
        
        $activeCount = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_subscriptions 
                WHERE company_id = %d AND status = 'active'",
                $companyId
            )
        );

        $result['met'] = $activeCount > 0;
        $result['value'] = $activeCount;

        return $result;
    }

    /**
     * Determine status based on requirements
     * 
     * @param array $requirements
     * @return string
     */
    protected function determineStatus(array $requirements): string {
        // GREEN: All requirements met
        $allMet = true;
        foreach ($requirements as $req) {
            if (!$req['met']) {
                $allMet = false;
                break;
            }
        }

        if ($allMet) {
            return TrustSignals::STATUS_GREEN;
        }

        // AMBER: At least amber requirements met (insurance OR terms)
        $amberMet = false;
        foreach ($this->amberRequirements as $req) {
            if (isset($requirements[$req]) && $requirements[$req]['met']) {
                $amberMet = true;
                break;
            }
        }

        if ($amberMet) {
            return TrustSignals::STATUS_AMBER;
        }

        // RED: No requirements met
        return TrustSignals::STATUS_RED;
    }

    /**
     * Count met requirements
     * 
     * @param array $requirements
     * @return int
     */
    protected function countMetRequirements(array $requirements): int {
        $count = 0;
        foreach ($requirements as $req) {
            if ($req['met']) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get requirement label
     * 
     * @param string $requirement
     * @return string
     */
    public function getRequirementLabel(string $requirement): string {
        $labels = [
            TrustSignals::REQUIREMENT_INSURANCE_PAGE => __('Insurance Page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_REFUND_HISTORY => __('Refund History', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_CLAIMS_PAGE => __('Claims Page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_TERMS_PAGE => __('Terms Page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION => __('Active Subscription', 'myprotector-platform'),
        ];

        return $labels[$requirement] ?? $requirement;
    }

    /**
     * Get requirement description
     * 
     * @param string $requirement
     * @return string
     */
    public function getRequirementDescription(string $requirement): string {
        $descriptions = [
            TrustSignals::REQUIREMENT_INSURANCE_PAGE => __('The business displays insurance information on their page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_REFUND_HISTORY => __('The business has processed at least one refund', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_CLAIMS_PAGE => __('The business has a claims page with approved claims', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_TERMS_PAGE => __('The business displays terms and conditions on their page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION => __('The business has an active subscription with us', 'myprotector-platform'),
        ];

        return $descriptions[$requirement] ?? '';
    }

    /**
     * Get requirements for display
     * 
     * @return array
     */
    public function getRequirementsForDisplay(): array {
        $requirements = [];

        foreach ($this->greenRequirements as $req) {
            $requirements[$req] = [
                'label' => $this->getRequirementLabel($req),
                'description' => $this->getRequirementDescription($req),
                'required_for' => TrustSignals::STATUS_GREEN,
            ];
        }

        return $requirements;
    }
}