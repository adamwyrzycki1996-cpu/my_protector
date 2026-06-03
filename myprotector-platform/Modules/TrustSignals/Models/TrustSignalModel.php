<?php
/**
 * MyProtector Platform - Trust Signal Model
 * 
 * Database operations for trust signals
 * 
 * @package MyProtector\Modules\TrustSignals\Models
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Models;

class TrustSignalModel {
    /**
     * Database table name
     * 
     * @var string
     */
    protected $table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'mp_trust_signals';
    }

    /**
     * Get trust signal for a company
     * 
     * @param int $companyId
     * @return array|null
     */
    public function getForCompany(int $companyId): ?array {
        global $wpdb;
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE company_id = %d",
                $companyId
            ),
            ARRAY_A
        );

        return $result ?: null;
    }

    /**
     * Create or update trust signal
     * 
     * @param array $data
     * @return int
     */
    public function upsert(array $data): int {
        global $wpdb;
        
        $existing = $this->getForCompany($data['company_id']);
        
        $data = wp_parse_args($data, [
            'updated_at' => current_time('mysql'),
        ]);

        if ($existing) {
            $wpdb->update(
                $this->table,
                $data,
                ['company_id' => $data['company_id']],
                array_keys($data),
                ['%d']
            );
            return (int) $existing['signal_id'];
        }

        $data['created_at'] = current_time('mysql');
        $wpdb->insert($this->table, $data);
        return (int) $wpdb->insert_id;
    }

    /**
     * Update status for a company
     * 
     * @param int $companyId
     * @param string $status
     * @param array $additionalData
     * @return bool
     */
    public function updateStatus(int $companyId, string $status, array $additionalData = []): bool {
        global $wpdb;
        
        $data = wp_parse_args($additionalData, [
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ]);

        $result = $wpdb->update(
            $this->table,
            $data,
            ['company_id' => $companyId],
            array_keys($data),
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Set override
     * 
     * @param int $companyId
     * @param string $status
     * @param string $reason
     * @param int $adminId
     * @return bool
     */
    public function setOverride(int $companyId, string $status, string $reason, int $adminId): bool {
        global $wpdb;
        
        $existing = $this->getForCompany($companyId);
        
        $data = [
            'is_overridden' => 1,
            'overridden_status' => $status,
            'override_reason' => $reason,
            'overridden_by' => $adminId,
            'overridden_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        if ($existing) {
            $result = $wpdb->update(
                $this->table,
                $data,
                ['company_id' => $companyId],
                array_keys($data),
                ['%d']
            );
        } else {
            $data['company_id'] = $companyId;
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($this->table, $data);
        }

        return $result !== false;
    }

    /**
     * Clear override for a company
     * 
     * @param int $companyId
     * @return bool
     */
    public function clearOverride(int $companyId): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table,
            [
                'is_overridden' => 0,
                'overridden_status' => null,
                'override_reason' => null,
                'overridden_by' => null,
                'overridden_at' => null,
                'updated_at' => current_time('mysql'),
            ],
            ['company_id' => $companyId],
            ['%d', '%s', '%s', '%d', '%s', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Update requirements for a company
     * 
     * @param int $companyId
     * @param array $requirements
     * @return bool
     */
    public function updateRequirements(int $companyId, array $requirements): bool {
        global $wpdb;
        
        $requirementsJson = json_encode($requirements);
        
        $result = $wpdb->update(
            $this->table,
            [
                'requirements' => $requirementsJson,
                'updated_at' => current_time('mysql'),
            ],
            ['company_id' => $companyId],
            ['%s', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * List trust signals with filters
     * 
     * @param array $args
     * @return array
     */
    public function list(array $args = []): array {
        global $wpdb;
        
        $defaults = [
            'status' => null,
            'search' => null,
            'limit' => 20,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['1=1'];
        $params = [];

        if ($args['status']) {
            $where[] = 'ts.status = %s';
            $params[] = $args['status'];
        }

        if ($args['search']) {
            $where[] = '(c.company_name LIKE %s OR c.company_slug LIKE %s)';
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $whereClause = implode(' AND ', $where);
        
        $sql = "
            SELECT ts.*, c.company_name, c.company_slug, c.company_logo
            FROM {$this->table} ts
            LEFT JOIN {$wpdb->prefix}mp_companies c ON ts.company_id = c.company_id
            WHERE {$whereClause}
            ORDER BY ts.updated_at DESC
            LIMIT %d OFFSET %d
        ";
        
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        if (!empty($params)) {
            $sql = $wpdb->prepare($sql, $params);
        }

        return $wpdb->get_results($sql, ARRAY_A) ?: [];
    }

    /**
     * Count trust signals by status
     * 
     * @param string|null $status
     * @return int
     */
    public function countByStatus(?string $status = null): int {
        global $wpdb;
        
        if ($status) {
            return (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE status = %s",
                    $status
                )
            );
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table}");
    }

    /**
     * Get companies needing recalculation
     * 
     * @param int $limit
     * @return array
     */
    public function getCompaniesNeedingRecalculation(int $limit = 100): array {
        global $wpdb;
        
        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT company_id FROM {$this->table} 
                WHERE is_overridden = 0 
                ORDER BY updated_at ASC 
                LIMIT %d",
                $limit
            )
        ) ?: [];
    }

    /**
     * Log trust signal change
     * 
     * @param int $companyId
     * @param string $oldStatus
     * @param string $newStatus
     * @param string $reason
     * @param int|null $adminId
     * @return bool
     */
    public function logChange(int $companyId, string $oldStatus, string $newStatus, string $reason, ?int $adminId = null): bool {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_trust_signal_history',
            [
                'company_id' => $companyId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'change_reason' => $reason,
                'changed_by' => $adminId,
                'is_override' => $adminId ? 1 : 0,
                'created_at' => current_time('mysql'),
            ]
        );

        return $result !== false;
    }

    /**
     * Get trust signal history for a company
     * 
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public function getHistory(int $companyId, int $limit = 20): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT h.*, u.display_name as admin_name
                FROM {$wpdb->prefix}mp_trust_signal_history h
                LEFT JOIN {$wpdb->users} u ON h.changed_by = u.ID
                WHERE h.company_id = %d
                ORDER BY h.created_at DESC
                LIMIT %d",
                $companyId,
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }
}