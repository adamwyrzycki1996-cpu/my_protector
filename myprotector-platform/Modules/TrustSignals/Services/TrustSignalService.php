<?php
/**
 * MyProtector Platform - Trust Signal Service
 * 
 * Core service for managing trust signals
 * 
 * @package MyProtector\Modules\TrustSignals\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Services;

use MyProtector\Modules\TrustSignals\Models\TrustSignalModel;
use MyProtector\Modules\TrustSignals\TrustSignals;

class TrustSignalService {
    /**
     * Model instance
     * 
     * @var TrustSignalModel
     */
    protected $model;

    /**
     * Calculator service
     * 
     * @var TrustSignalCalculatorService
     */
    protected $calculator;

    /**
     * Green requirements
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
     * Constructor
     */
    public function __construct() {
        $this->model = new TrustSignalModel();
        $this->calculator = new TrustSignalCalculatorService();
    }

    /**
     * Get trust signal for a company
     * 
     * @param int $companyId
     * @return array|null
     */
    public function getForCompany(int $companyId): ?array {
        $signal = $this->model->getForCompany($companyId);
        
        if (!$signal) {
            return $this->createDefaultSignal($companyId);
        }

        // Decode requirements JSON
        if (isset($signal['requirements'])) {
            $signal['requirements_data'] = json_decode($signal['requirements'], true) ?: [];
        }

        return $signal;
    }

    /**
     * Get detailed trust signal information
     * 
     * @param int $companyId
     * @return array
     */
    public function getDetailsForCompany(int $companyId): array {
        $signal = $this->getForCompany($companyId);
        $company = $this->getCompanyData($companyId);
        $requirements = $this->evaluateRequirements($companyId, $company);

        return [
            'signal' => $signal,
            'company' => $company,
            'requirements' => $requirements,
            'requirements_met' => array_filter($requirements, fn($r) => $r['met']),
            'requirements_missing' => array_filter($requirements, fn($r) => !$r['met']),
            'can_calculate' => $this->calculator->canCalculate($companyId),
        ];
    }

    /**
     * Recalculate trust signal for a company
     * 
     * @param int $companyId
     * @return array
     */
    public function recalculateForCompany(int $companyId): array {
        // Check if overridden
        $current = $this->model->getForCompany($companyId);
        
        if ($current && $current['is_overridden']) {
            // Don't recalculate if manually overridden
            return $current;
        }

        // Get company data
        $company = $this->getCompanyData($companyId);
        
        if (!$company) {
            return $this->createDefaultSignal($companyId);
        }

        // Calculate new signal
        $calculation = $this->calculator->calculate($companyId, $company);
        
        // Prepare data for storage
        $data = [
            'company_id' => $companyId,
            'status' => $calculation['status'],
            'calculated_status' => $calculation['status'],
            'requirements' => json_encode($calculation['requirements']),
            'is_overridden' => 0,
        ];

        $this->model->upsert($data);

        // Log the change if status changed
        if ($current && $current['status'] !== $calculation['status']) {
            $this->model->logChange(
                $companyId,
                $current['status'],
                $calculation['status'],
                'Automatic recalculation',
                null
            );
        }

        return $this->getForCompany($companyId);
    }

    /**
     * Batch recalculate all trust signals
     * 
     * @return array
     */
    public function batchRecalculate(): array {
        global $wpdb;
        
        // Get all companies with profiles
        $companyIds = $wpdb->get_col(
            "SELECT company_id FROM {$wpdb->prefix}mp_companies WHERE status = 'approved'"
        );

        $processed = 0;
        $errors = 0;

        foreach ($companyIds as $companyId) {
            try {
                $this->recalculateForCompany((int) $companyId);
                $processed++;
            } catch (\Exception $e) {
                $errors++;
                error_log('Trust Signal Recalculation Error: ' . $e->getMessage());
            }
        }

        return [
            'processed' => $processed,
            'errors' => $errors,
            'total' => count($companyIds),
        ];
    }

    /**
     * List trust signals with filters
     * 
     * @param array $args
     * @return array
     */
    public function list(array $args = []): array {
        return $this->model->list($args);
    }

    /**
     * Get status distribution
     * 
     * @return array
     */
    public function getStatusDistribution(): array {
        return [
            'green' => $this->model->countByStatus(TrustSignals::STATUS_GREEN),
            'amber' => $this->model->countByStatus(TrustSignals::STATUS_AMBER),
            'red' => $this->model->countByStatus(TrustSignals::STATUS_RED),
            'total' => $this->model->countByStatus(),
        ];
    }

    /**
     * Create default signal for new company
     * 
     * @param int $companyId
     * @return array
     */
    protected function createDefaultSignal(int $companyId): array {
        $default = [
            'signal_id' => 0,
            'company_id' => $companyId,
            'status' => TrustSignals::STATUS_RED,
            'calculated_status' => TrustSignals::STATUS_RED,
            'is_overridden' => 0,
            'overridden_status' => null,
            'override_reason' => null,
            'requirements' => json_encode([]),
            'requirements_data' => [],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        return $default;
    }

    /**
     * Get company data for calculation
     * 
     * @param int $companyId
     * @return array|null
     */
    protected function getCompanyData(int $companyId): ?array {
        global $wpdb;
        
        $company = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT c.*, 
                    (SELECT COUNT(*) FROM {$wpdb->prefix}mp_refunds WHERE company_id = c.company_id AND refunded_at IS NOT NULL) as refund_count,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}mp_claims WHERE company_id = c.company_id AND status = 'approved') as claims_count,
                    (SELECT COUNT(*) FROM {$wpdb->prefix}mp_subscriptions WHERE company_id = c.company_id AND status = 'active') as active_subscriptions
                FROM {$wpdb->prefix}mp_companies c
                WHERE c.company_id = %d",
                $companyId
            ),
            ARRAY_A
        );

        return $company ?: null;
    }

    /**
     * Evaluate requirements for a company
     * 
     * @param int $companyId
     * @param array $company
     * @return array
     */
    protected function evaluateRequirements(int $companyId, ?array $company): array {
        if (!$company) {
            return $this->getDefaultRequirements();
        }

        $requirements = [];

        // 1. Insurance Page
        $requirements[TrustSignals::REQUIREMENT_INSURANCE_PAGE] = [
            'met' => !empty($company['insurance_url']),
            'label' => __('Insurance Page', 'myprotector-platform'),
            'description' => __('Business has an insurance information page', 'myprotector-platform'),
            'value' => $company['insurance_url'] ?: null,
        ];

        // 2. Refund History
        $refundCount = (int) ($company['refund_count'] ?? 0);
        $requirements[TrustSignals::REQUIREMENT_REFUND_HISTORY] = [
            'met' => $refundCount > 0,
            'label' => __('Refund History', 'myprotector-platform'),
            'description' => __('Business has processed at least one refund', 'myprotector-platform'),
            'value' => $refundCount,
        ];

        // 3. Claims Page
        $requirements[TrustSignals::REQUIREMENT_CLAIMS_PAGE] = [
            'met' => !empty($company['claims_count']) && $company['claims_count'] > 0,
            'label' => __('Claims Page', 'myprotector-platform'),
            'description' => __('Business has a claims page with approved claims', 'myprotector-platform'),
            'value' => $company['claims_count'] ?: null,
        ];

        // 4. Terms Page
        $requirements[TrustSignals::REQUIREMENT_TERMS_PAGE] = [
            'met' => !empty($company['terms_url']),
            'label' => __('Terms Page', 'myprotector-platform'),
            'description' => __('Business has a terms and conditions page', 'myprotector-platform'),
            'value' => $company['terms_url'] ?: null,
        ];

        // 5. Active Subscription
        $activeCount = (int) ($company['active_subscriptions'] ?? 0);
        $requirements[TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION] = [
            'met' => $activeCount > 0,
            'label' => __('Active Subscription', 'myprotector-platform'),
            'description' => __('Business has an active subscription', 'myprotector-platform'),
            'value' => $activeCount,
        ];

        return $requirements;
    }

    /**
     * Get default requirements (all unmet)
     * 
     * @return array
     */
    protected function getDefaultRequirements(): array {
        $requirements = [];
        
        foreach ($this->greenRequirements as $req) {
            $requirements[$req] = [
                'met' => false,
                'label' => $this->getRequirementLabel($req),
                'description' => $this->getRequirementDescription($req),
                'value' => null,
            ];
        }

        return $requirements;
    }

    /**
     * Get requirement label
     * 
     * @param string $requirement
     * @return string
     */
    protected function getRequirementLabel(string $requirement): string {
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
    protected function getRequirementDescription(string $requirement): string {
        $descriptions = [
            TrustSignals::REQUIREMENT_INSURANCE_PAGE => __('Business has an insurance information page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_REFUND_HISTORY => __('Business has processed at least one refund', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_CLAIMS_PAGE => __('Business has a claims page with approved claims', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_TERMS_PAGE => __('Business has a terms and conditions page', 'myprotector-platform'),
            TrustSignals::REQUIREMENT_ACTIVE_SUBSCRIPTION => __('Business has an active subscription', 'myprotector-platform'),
        ];

        return $descriptions[$requirement] ?? '';
    }
}