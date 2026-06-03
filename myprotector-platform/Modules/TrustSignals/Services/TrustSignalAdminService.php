<?php
/**
 * MyProtector Platform - Trust Signal Admin Service
 * 
 * Admin-specific operations for trust signals
 * 
 * @package MyProtector\Modules\TrustSignals\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Services;

use MyProtector\Modules\TrustSignals\Models\TrustSignalModel;
use MyProtector\Modules\TrustSignals\TrustSignals;

class TrustSignalAdminService {
    /**
     * Model instance
     * 
     * @var TrustSignalModel
     */
    protected $model;

    /**
     * Valid statuses
     * 
     * @var array
     */
    protected $validStatuses = [
        TrustSignals::STATUS_GREEN,
        TrustSignals::STATUS_AMBER,
        TrustSignals::STATUS_RED,
    ];

    /**
     * Constructor
     */
    public function __construct() {
        $this->model = new TrustSignalModel();
    }

    /**
     * Override trust signal status
     * 
     * @param int $companyId
     * @param string $status
     * @param string $reason
     * @return true|\WP_Error
     */
    public function overrideStatus(int $companyId, string $status, string $reason = ''): bool|\WP_Error {
        // Validate status
        if (!in_array($status, $this->validStatuses, true)) {
            return new \WP_Error(
                'invalid_status',
                __('Invalid trust signal status.', 'myprotector-platform')
            );
        }

        // Validate company exists
        if (!$this->companyExists($companyId)) {
            return new \WP_Error(
                'company_not_found',
                __('Company not found.', 'myprotector-platform')
            );
        }

        // Get current signal
        $current = $this->model->getForCompany($companyId);
        $oldStatus = $current['status'] ?? TrustSignals::STATUS_RED;

        // Set override
        $result = $this->model->setOverride(
            $companyId,
            $status,
            $reason,
            get_current_user_id()
        );

        if (!$result) {
            return new \WP_Error(
                'override_failed',
                __('Failed to set override.', 'myprotector-platform')
            );
        }

        // Log the change
        $this->model->logChange(
            $companyId,
            $oldStatus,
            $status,
            $reason ?: __('Manual override', 'myprotector-platform'),
            get_current_user_id()
        );

        // Trigger action hook
        do_action('mp_trust_signal_overridden', $companyId, $status, $reason);

        return true;
    }

    /**
     * Clear override for a company
     * 
     * @param int $companyId
     * @return bool|\WP_Error
     */
    public function clearOverride(int $companyId): bool|\WP_Error {
        // Validate company exists
        if (!$this->companyExists($companyId)) {
            return new \WP_Error(
                'company_not_found',
                __('Company not found.', 'myprotector-platform')
            );
        }

        // Get current signal
        $current = $this->model->getForCompany($companyId);
        
        if (!$current || !$current['is_overridden']) {
            return new \WP_Error(
                'no_override',
                __('No override exists for this company.', 'myprotector-platform')
            );
        }

        $oldStatus = $current['status'];

        // Clear override and recalculate
        $this->model->clearOverride($companyId);

        // Trigger recalculation
        $service = new TrustSignalService();
        $newSignal = $service->recalculateForCompany($companyId);
        $newStatus = $newSignal['status'];

        // Log the change
        $this->model->logChange(
            $companyId,
            $oldStatus,
            $newStatus,
            __('Override cleared - auto-recalculated', 'myprotector-platform'),
            get_current_user_id()
        );

        // Trigger action hook
        do_action('mp_trust_signal_override_cleared', $companyId, $newStatus);

        return true;
    }

    /**
     * Get override history for a company
     * 
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function getOverrideHistory(int $companyId, int $limit = 20): array {
        return $this->model->getHistory($companyId, $limit);
    }

    /**
     * Get trust signals requiring admin attention
     * 
     * @return array
     */
    public function getAttentionRequired(): array {
        global $wpdb;
        
        // Companies with RED status but have subscription
        return $wpdb->get_results(
            "SELECT ts.*, c.company_name, c.company_slug
            FROM {$wpdb->prefix}mp_trust_signals ts
            INNER JOIN {$wpdb->prefix}mp_companies c ON ts.company_id = c.company_id
            INNER JOIN {$wpdb->prefix}mp_subscriptions s ON ts.company_id = s.company_id AND s.status = 'active'
            WHERE ts.status = 'red'
            AND c.status = 'approved'
            LIMIT 50",
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get override statistics
     * 
     * @return array
     */
    public function getOverrideStats(): array {
        global $wpdb;
        
        $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mp_trust_signals");
        $overridden = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mp_trust_signals WHERE is_overridden = 1");

        return [
            'total' => $total,
            'overridden' => $overridden,
            'automatic' => $total - $overridden,
            'override_rate' => $total > 0 ? round(($overridden / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Batch override multiple companies
     * 
     * @param array $companyIds
     * @param string $status
     * @param string $reason
     * @return array
     */
    public function batchOverride(array $companyIds, string $status, string $reason): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($companyIds as $companyId) {
            $result = $this->overrideStatus((int) $companyId, $status, $reason);
            
            if ($result === true) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'company_id' => $companyId,
                    'error' => $result->get_error_message(),
                ];
            }
        }

        return $results;
    }

    /**
     * Check if company exists
     * 
     * @param int $companyId
     * @return bool
     */
    protected function companyExists(int $companyId): bool {
        global $wpdb;
        
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mp_companies WHERE company_id = %d",
                $companyId
            )
        );

        return (int) $exists > 0;
    }

    /**
     * Validate override reason
     * 
     * @param string $reason
     * @return bool
     */
    public function validateReason(string $reason): bool {
        // Reason must be at least 10 characters
        return strlen(trim($reason)) >= 10;
    }

    /**
     * Get valid statuses for admin
     * 
     * @return array
     */
    public function getValidStatuses(): array {
        return $this->validStatuses;
    }

    /**
     * Get status display info
     * 
     * @param string $status
     * @return array
     */
    public function getStatusDisplayInfo(string $status): array {
        $info = [
            TrustSignals::STATUS_GREEN => [
                'label' => __('GREEN', 'myprotector-platform'),
                'description' => __('All trust requirements met', 'myprotector-platform'),
                'color' => '#28a745',
                'icon' => 'dashicons-yes-alt',
            ],
            TrustSignals::STATUS_AMBER => [
                'label' => __('AMBER', 'myprotector-platform'),
                'description' => __('Some trust requirements met', 'myprotector-platform'),
                'color' => '#ffc107',
                'icon' => 'dashicons-warning',
            ],
            TrustSignals::STATUS_RED => [
                'label' => __('RED', 'myprotector-platform'),
                'description' => __('Trust requirements not met', 'myprotector-platform'),
                'color' => '#dc3545',
                'icon' => 'dashicons-dismiss',
            ],
        ];

        return $info[$status] ?? $info[TrustSignals::STATUS_RED];
    }
}