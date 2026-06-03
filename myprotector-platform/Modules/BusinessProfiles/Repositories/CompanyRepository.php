<?php
/**
 * MyProtector Platform - Company Repository
 * 
 * Data access layer for Company entities
 * 
 * @package MyProtector\Modules\BusinessProfiles\Repositories
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Repositories;

use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Models\CompanyDocument;
use wpdb;

class CompanyRepository {
    /**
     * WordPress database object
     * 
     * @var wpdb
     */
    protected $db;

    /**
     * Table name
     * 
     * @var string
     */
    protected $table;

    /**
     * Documents table name
     * 
     * @var string
     */
    protected $documents_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->table = $wpdb->prefix . 'mp_companies';
        $this->documents_table = $wpdb->prefix . 'mp_company_documents';
    }

    /**
     * Create a new company
     * 
     * @param array $data
     * @return int|\WP_Error
     */
    public function create(array $data) {
        $defaults = [
            'user_id' => 0,
            'company_name' => '',
            'company_slug' => '',
            'company_description' => '',
            'company_website' => '',
            'company_logo' => '',
            'company_address' => '',
            'company_phone' => '',
            'company_email' => '',
            'company_category' => 0,
            'insurance_name' => '',
            'insurance_url' => '',
            'terms_url' => '',
            'promise_page_url' => '',
            'promise_page_title' => '',
            'status' => Company::STATUS_PENDING,
            'trust_score' => 0,
            'total_reviews' => 0,
            'avg_rating' => 0,
            'is_featured' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        $data = wp_parse_args($data, $defaults);

        // Generate slug if not provided
        if (empty($data['company_slug'])) {
            $data['company_slug'] = $this->generateSlug($data['company_name']);
        }

        $result = $this->db->insert(
            $this->table,
            $data,
            [
                '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d',
                '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%d', '%f', '%d',
                '%s', '%s',
            ]
        );

        if ($result === false) {
            return new \WP_Error(
                'db_insert_error',
                __('Failed to create company.', 'myprotector-platform'),
                $this->db->last_error
            );
        }

        $company_id = $this->db->insert_id;

        // Create corresponding WordPress post
        $this->createPost($company_id, $data);

        return $company_id;
    }

    /**
     * Update a company
     * 
     * @param int $company_id
     * @param array $data
     * @return bool|\WP_Error
     */
    public function update(int $company_id, array $data) {
        $data['updated_at'] = current_time('mysql');

        // Remove fields that shouldn't be updated directly
        unset($data['company_id'], $data['created_at']);

        // Regenerate slug if name changed
        if (isset($data['company_name'])) {
            $data['company_slug'] = $this->generateSlug($data['company_name'], $company_id);
        }

        $result = $this->db->update(
            $this->table,
            $data,
            ['company_id' => $company_id],
            $this->getFormatForUpdate($data),
            ['%d']
        );

        if ($result === false) {
            return new \WP_Error(
                'db_update_error',
                __('Failed to update company.', 'myprotector-platform'),
                $this->db->last_error
            );
        }

        // Update WordPress post
        $this->updatePost($company_id, $data);

        return true;
    }

    /**
     * Find company by ID
     * 
     * @param int $company_id
     * @return Company|null
     */
    public function find(int $company_id): ?Company {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE company_id = %d",
                $company_id
            )
        );

        if (!$row) {
            return null;
        }

        return Company::fromRow($row);
    }

    /**
     * Find company by slug
     * 
     * @param string $slug
     * @return Company|null
     */
    public function findBySlug(string $slug): ?Company {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE company_slug = %s",
                $slug
            )
        );

        if (!$row) {
            return null;
        }

        return Company::fromRow($row);
    }

    /**
     * Find company by user ID
     * 
     * @param int $user_id
     * @return Company|null
     */
    public function findByUserId(int $user_id): ?Company {
        $row = $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->table} WHERE user_id = %d",
                $user_id
            )
        );

        if (!$row) {
            return null;
        }

        return Company::fromRow($row);
    }

    /**
     * Get all companies with filters
     * 
     * @param array $args
     * @return array
     */
    public function getAll(array $args = []): array {
        $defaults = [
            'status' => Company::STATUS_APPROVED,
            'category' => null,
            'search' => '',
            'user_id' => null,
            'limit' => 20,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        extract($args);

        $where = ['1=1'];
        $format = [];

        if ($status) {
            $where[] = 'status = %s';
            $format[] = $status;
        }

        if ($category) {
            $where[] = 'company_category = %d';
            $format[] = $category;
        }

        if ($search) {
            $where[] = '(company_name LIKE %s OR company_description LIKE %s)';
            $search_term = '%' . $this->db->esc_like($search) . '%';
            $format[] = $search_term;
            $format[] = $search_term;
        }

        if ($user_id) {
            $where[] = 'user_id = %d';
            $format[] = $user_id;
        }

        $allowed_orderby = ['company_id', 'company_name', 'status', 'trust_score', 'total_reviews', 'avg_rating', 'created_at', 'updated_at'];
        $orderby = in_array($orderby, $allowed_orderby) ? $orderby : 'created_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT * FROM {$this->table} 
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$orderby} {$order}
                LIMIT {$limit} OFFSET {$offset}";

        if (!empty($format)) {
            $sql = $this->db->prepare($sql, ...$format);
        }

        $rows = $this->db->get_results($sql);

        return array_map([Company::class, 'fromRow'], $rows);
    }

    /**
     * Count companies with filters
     * 
     * @param array $args
     * @return int
     */
    public function count(array $args = []): int {
        $defaults = [
            'status' => null,
            'category' => null,
            'search' => '',
        ];

        $args = wp_parse_args($args, $defaults);
        extract($args);

        $where = ['1=1'];
        $format = [];

        if ($status) {
            $where[] = 'status = %s';
            $format[] = $status;
        }

        if ($category) {
            $where[] = 'company_category = %d';
            $format[] = $category;
        }

        if ($search) {
            $where[] = '(company_name LIKE %s OR company_description LIKE %s)';
            $search_term = '%' . $this->db->esc_like($search) . '%';
            $format[] = $search_term;
            $format[] = $search_term;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $where);

        if (!empty($format)) {
            $sql = $this->db->prepare($sql, ...$format);
        }

        return (int) $this->db->get_var($sql);
    }

    /**
     * Get pending companies
     * 
     * @param array $args
     * @return array
     */
    public function getPending(array $args = []): array {
        $defaults = [
            'limit' => 20,
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);
        $args['status'] = Company::STATUS_PENDING;

        return $this->getAll($args);
    }

    /**
     * Delete a company
     * 
     * @param int $company_id
     * @return bool|\WP_Error
     */
    public function delete(int $company_id): bool {
        // Delete associated documents first
        $this->db->delete($this->documents_table, ['company_id' => $company_id]);

        // Delete the company
        $result = $this->db->delete($this->table, ['company_id' => $company_id]);

        if ($result === false) {
            return new \WP_Error(
                'db_delete_error',
                __('Failed to delete company.', 'myprotector-platform'),
                $this->db->last_error
            );
        }

        // Delete associated WordPress post
        $post_id = $this->getPostId($company_id);
        if ($post_id) {
            wp_delete_post($post_id, true);
        }

        return true;
    }

    /**
     * Update company status
     * 
     * @param int $company_id
     * @param string $status
     * @param string|null $reason
     * @return bool
     */
    public function updateStatus(int $company_id, string $status, ?string $reason = null): bool {
        $data = [
            'status' => $status,
            'updated_at' => current_time('mysql'),
        ];

        if ($status === Company::STATUS_APPROVED) {
            $data['approved_by'] = get_current_user_id();
            $data['approved_at'] = current_time('mysql');
        }

        if ($status === Company::STATUS_REJECTED && $reason) {
            $data['rejection_reason'] = $reason;
        }

        $result = $this->db->update(
            $this->table,
            $data,
            ['company_id' => $company_id],
            ['%s', '%s', '%d', '%s', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Update trust score
     * 
     * @param int $company_id
     * @param float $score
     * @return bool
     */
    public function updateTrustScore(int $company_id, float $score): bool {
        $result = $this->db->update(
            $this->table,
            [
                'trust_score' => $score,
                'updated_at' => current_time('mysql'),
            ],
            ['company_id' => $company_id],
            ['%f', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Update review statistics
     * 
     * @param int $company_id
     * @param int $total_reviews
     * @param float $avg_rating
     * @return bool
     */
    public function updateReviewStats(int $company_id, int $total_reviews, float $avg_rating): bool {
        $result = $this->db->update(
            $this->table,
            [
                'total_reviews' => $total_reviews,
                'avg_rating' => $avg_rating,
                'updated_at' => current_time('mysql'),
            ],
            ['company_id' => $company_id],
            ['%d', '%f', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Generate unique slug
     * 
     * @param string $name
     * @param int|null $exclude_id
     * @return string
     */
    public function generateSlug(string $name, ?int $exclude_id = null): string {
        $base_slug = sanitize_title($name);
        $slug = $base_slug;
        $counter = 1;

        while (true) {
            $query = $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE company_slug = %s" . ($exclude_id ? " AND company_id != %d" : ""),
                $exclude_id ? [$slug, $exclude_id] : [$slug]
            );

            if (!$this->db->get_var($query)) {
                break;
            }

            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Create WordPress post for company
     * 
     * @param int $company_id
     * @param array $data
     * @return int
     */
    protected function createPost(int $company_id, array $data): int {
        $post_data = [
            'post_type' => 'mp_company',
            'post_title' => $data['company_name'],
            'post_content' => $data['company_description'] ?? '',
            'post_status' => $data['status'] === Company::STATUS_APPROVED ? 'publish' : 'pending',
            'post_author' => $data['user_id'],
        ];

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_company_id', $company_id);
            $this->syncPostMeta($post_id, $data);
        }

        return $post_id;
    }

    /**
     * Update WordPress post
     * 
     * @param int $company_id
     * @param array $data
     * @return void
     */
    protected function updatePost(int $company_id, array $data): void {
        $post_id = $this->getPostId($company_id);

        if (!$post_id) {
            return;
        }

        $update_data = [];

        if (isset($data['company_name'])) {
            $update_data['post_title'] = $data['company_name'];
        }

        if (isset($data['company_description'])) {
            $update_data['post_content'] = $data['company_description'];
        }

        if (isset($data['status'])) {
            $update_data['post_status'] = $data['status'] === Company::STATUS_APPROVED ? 'publish' : 'pending';
        }

        if (!empty($update_data)) {
            $update_data['ID'] = $post_id;
            wp_update_post($update_data);
        }

        $this->syncPostMeta($post_id, $data);
    }

    /**
     * Sync post meta with company data
     * 
     * @param int $post_id
     * @param array $data
     * @return void
     */
    protected function syncPostMeta(int $post_id, array $data): void {
        $meta_keys = [
            'company_website',
            'company_logo',
            'company_phone',
            'company_email',
            'company_address',
            'insurance_name',
            'insurance_url',
            'terms_url',
            'promise_page_url',
            'promise_page_title',
            'company_status',
        ];

        foreach ($meta_keys as $key) {
            $meta_key = '_' . $key;
            if (isset($data[$key])) {
                update_post_meta($post_id, $meta_key, $data[$key]);
            }
        }
    }

    /**
     * Get post ID from company ID
     * 
     * @param int $company_id
     * @return int|null
     */
    public function getPostId(int $company_id): ?int {
        $posts = get_posts([
            'post_type' => 'mp_company',
            'meta_key' => '_company_id',
            'meta_value' => $company_id,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);

        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Get format array for update
     * 
     * @param array $data
     * @return array
     */
    protected function getFormatForUpdate(array $data): array {
        $format = [];

        foreach ($data as $key => $value) {
            if (is_int($value)) {
                $format[] = '%d';
            } elseif (is_float($value)) {
                $format[] = '%f';
            } else {
                $format[] = '%s';
            }
        }

        return $format;
    }

    // ==================== Document Methods ====================

    /**
     * Create a document
     * 
     * @param array $data
     * @return int|\WP_Error
     */
    public function createDocument(array $data) {
        $defaults = [
            'company_id' => 0,
            'document_type' => CompanyDocument::TYPE_OTHER,
            'document_name' => '',
            'document_url' => '',
            'document_path' => '',
            'mime_type' => '',
            'file_size' => 0,
            'is_verified' => 0,
            'verified_by' => 0,
            'verified_at' => null,
            'rejection_reason' => '',
            'uploaded_by' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        $data = wp_parse_args($data, $defaults);

        $result = $this->db->insert(
            $this->documents_table,
            $data,
            ['%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%s', '%s']
        );

        if ($result === false) {
            return new \WP_Error(
                'db_insert_error',
                __('Failed to create document.', 'myprotector-platform'),
                $this->db->last_error
            );
        }

        return $this->db->insert_id;
    }

    /**
     * Get documents for a company
     * 
     * @param int $company_id
     * @return array
     */
    public function getDocuments(int $company_id): array {
        $rows = $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->documents_table} WHERE company_id = %d ORDER BY created_at DESC",
                $company_id
            )
        );

        return array_map([CompanyDocument::class, 'fromRow'], $rows);
    }

    /**
     * Delete a document
     * 
     * @param int $document_id
     * @return bool
     */
    public function deleteDocument(int $document_id): bool {
        $result = $this->db->delete($this->documents_table, ['document_id' => $document_id]);
        return $result !== false;
    }
}