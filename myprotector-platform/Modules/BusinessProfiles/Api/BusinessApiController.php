<?php
/**
 * MyProtector Platform - Business API Controller
 * 
 * REST API endpoints for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Api
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Api;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class BusinessApiController extends WP_REST_Controller {
    /**
     * Namespace for API routes
     * 
     * @var string
     */
    protected $namespace = MYPROTECTOR_API_NAMESPACE;

    /**
     * Rest base
     * 
     * @var string
     */
    protected $rest_base = 'businesses';

    /**
     * Register routes
     * 
     * @return void
     */
    public function registerRoutes(): void {
        // Register routes in module instead for more control
    }

    /**
     * Check if user can manage businesses
     * 
     * @return bool
     */
    protected function userCanManage(): bool {
        return current_user_can('manage_myprotector');
    }

    /**
     * Check if user can edit business
     * 
     * @param int $company_id
     * @return bool
     */
    protected function userCanEdit(int $company_id): bool {
        if ($this->userCanManage()) {
            return true;
        }
        
        global $wpdb;
        $user_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}mp_companies WHERE company_id = %d",
                $company_id
            )
        );
        
        return $user_id == get_current_user_id();
    }

    /**
     * Validate required fields
     * 
     * @param array $data
     * @param array $required
     * @return bool|WP_Error
     */
    protected function validateRequired(array $data, array $required) {
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            return new WP_Error(
                'missing_required',
                sprintf(__('Missing required fields: %s', 'myprotector-platform'), implode(', ', $missing)),
                ['status' => 400]
            );
        }
        
        return true;
    }

    /**
     * Format company data for API response
     * 
     * @param array $company
     * @param bool $detailed
     * @return array
     */
    protected function formatCompany(array $company, bool $detailed = false): array {
        $formatted = [
            'company_id' => (int) $company['company_id'],
            'company_name' => $company['company_name'],
            'company_slug' => $company['company_slug'],
            'company_logo' => $company['company_logo'],
            'status' => $company['status'],
            'status_label' => $this->getStatusLabel($company['status']),
            'trust_score' => (float) $company['trust_score'],
            'total_reviews' => (int) $company['total_reviews'],
            'avg_rating' => (float) $company['avg_rating'],
        ];
        
        if ($detailed) {
            $formatted = array_merge($formatted, [
                'company_description' => $company['company_description'],
                'company_website' => $company['company_website'],
                'company_phone' => $company['company_phone'],
                'company_email' => $company['company_email'],
                'company_address' => $company['company_address'],
                'insurance_name' => $company['insurance_name'],
                'insurance_url' => $company['insurance_url'],
                'terms_url' => $company['terms_url'],
                'promise_page_url' => $company['promise_page_url'],
                'promise_page_title' => $company['promise_page_title'],
                'rejection_reason' => $company['rejection_reason'],
                'created_at' => $company['created_at'],
                'updated_at' => $company['updated_at'],
            ]);
            
            // Add owner info if admin
            if ($this->userCanManage()) {
                $owner = get_userdata($company['user_id']);
                $formatted['owner'] = [
                    'id' => (int) $company['user_id'],
                    'name' => $owner ? $owner->display_name : '',
                    'email' => $owner ? $owner->user_email : '',
                ];
            }
            
            // Add trust requirements
            $formatted['trust_requirements'] = [
                'insurance' => !empty($company['insurance_url']),
                'terms' => !empty($company['terms_url']),
                'promise' => !empty($company['promise_page_url']),
            ];
        }
        
        return $formatted;
    }

    /**
     * Get status label
     * 
     * @param string $status
     * @return string
     */
    protected function getStatusLabel(string $status): string {
        $labels = [
            'pending' => __('Pending', 'myprotector-platform'),
            'approved' => __('Approved', 'myprotector-platform'),
            'rejected' => __('Rejected', 'myprotector-platform'),
            'suspended' => __('Suspended', 'myprotector-platform'),
        ];
        
        return $labels[$status] ?? $status;
    }

    /**
     * Prepare pagination headers
     * 
     * @param WP_REST_Request $request
     * @param int $total
     * @return array
     */
    protected function preparePaginationHeaders(WP_REST_Request $request, int $total): array {
        $per_page = $request->get_param('per_page') ?: 10;
        $page = $request->get_param('page') ?: 1;
        $max_pages = ceil($total / $per_page);
        
        return [
            'X-WP-Total' => $total,
            'X-WP-TotalPages' => $max_pages,
            'Link' => sprintf(
                '<%s>; rel="first", <%s>; rel="last"',
                esc_url_raw($this->getFirstPageUrl($request)),
                esc_url_raw($this->getLastPageUrl($request, $max_pages))
            ),
        ];
    }

    /**
     * Get first page URL
     * 
     * @param WP_REST_Request $request
     * @return string
     */
    protected function getFirstPageUrl(WP_REST_Request $request): string {
        $base_url = get_rest_url(null, $this->namespace . '/' . $this->rest_base);
        return add_query_arg([
            'per_page' => $request->get_param('per_page') ?: 10,
            'page' => 1,
        ], $base_url);
    }

    /**
     * Get last page URL
     * 
     * @param WP_REST_Request $request
     * @param int $max_pages
     * @return string
     */
    protected function getLastPageUrl(WP_REST_Request $request, int $max_pages): string {
        $base_url = get_rest_url(null, $this->namespace . '/' . $this->rest_base);
        return add_query_arg([
            'per_page' => $request->get_param('per_page') ?: 10,
            'page' => $max_pages,
        ], $base_url);
    }

    /**
     * Sanitize company data
     * 
     * @param array $data
     * @return array
     */
    protected function sanitizeCompanyData(array $data): array {
        $sanitized = [];
        
        $string_fields = [
            'company_name', 'company_description', 'company_logo',
            'company_phone', 'company_email', 'company_address',
            'insurance_name', 'insurance_url', 'terms_url',
            'promise_page_url', 'promise_page_title',
        ];
        
        foreach ($string_fields as $field) {
            if (isset($data[$field])) {
                if (strpos($field, 'url') !== false) {
                    $sanitized[$field] = esc_url_raw($data[$field]);
                } elseif (strpos($field, 'email') !== false) {
                    $sanitized[$field] = sanitize_email($data[$field]);
                } elseif (strpos($field, 'description') !== false || strpos($field, 'address') !== false) {
                    $sanitized[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $sanitized[$field] = sanitize_text_field($data[$field]);
                }
            }
        }
        
        if (isset($data['company_category'])) {
            $sanitized['company_category'] = (int) $data['company_category'];
        }
        
        return $sanitized;
    }

    /**
     * Prepare links for response
     * 
     * @param array $company
     * @return array
     */
    protected function prepareLinks(array $company): array {
        $base_url = get_rest_url(null, $this->namespace . '/' . $this->rest_base);
        
        return [
            'self' => [
                'href' => sprintf('%s/%d', $base_url, $company['company_id']),
            ],
            'collection' => [
                'href' => $base_url,
            ],
        ];
    }
}