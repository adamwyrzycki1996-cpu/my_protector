<?php
/**
 * MyProtector Platform - Business Service
 * 
 * Core business logic for managing company profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Services;

use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Repositories\CompanyRepository;
use MyProtector\Modules\BusinessProfiles\Validators\BusinessValidator;
use MyProtector\Core\Services\Container\ServiceContainer;

class BusinessService {
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
     * Validator instance
     * 
     * @var BusinessValidator
     */
    protected $validator;

    /**
     * Constructor
     * 
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainer $container) {
        $this->container = $container;
        $this->repository = new CompanyRepository();
        $this->validator = new BusinessValidator();
    }

    /**
     * Create a new business profile
     * 
     * @param array $data
     * @return int|\WP_Error
     */
    public function create(array $data) {
        // Validate input
        $validation = $this->validator->validateCreate($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Check for duplicate
        if ($this->repository->findBySlug($this->generateSlug($data['company_name']))) {
            return new \WP_Error(
                'duplicate_company',
                __('A business with this name already exists.', 'myprotector-platform')
            );
        }

        // Prepare data
        $company_data = $this->prepareCompanyData($data);
        $company_data['user_id'] = $data['user_id'] ?? get_current_user_id();
        $company_data['status'] = Company::STATUS_PENDING;

        // Create company
        $company_id = $this->repository->create($company_data);

        if (is_wp_error($company_id)) {
            return $company_id;
        }

        // Send notification to admin
        $this->notifyAdminNewSubmission($company_id);

        return $company_id;
    }

    /**
     * Update a business profile
     * 
     * @param array $data
     * @return bool|\WP_Error
     */
    public function update(array $data) {
        if (empty($data['id'])) {
            return new \WP_Error('missing_id', __('Company ID is required.', 'myprotector-platform'));
        }

        $company_id = (int) $data['id'];
        $company = $this->repository->find($company_id);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        // Check ownership (unless admin)
        $user_id = get_current_user_id();
        if (!current_user_can('manage_myprotector') && !$company->isOwner($user_id)) {
            return new \WP_Error('unauthorized', __('You do not have permission to update this company.', 'myprotector-platform'));
        }

        // Validate input
        $validation = $this->validator->validateUpdate($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Prepare update data
        $update_data = $this->prepareCompanyData($data);

        // If company was rejected, set back to pending for re-approval
        if ($company->status === Company::STATUS_REJECTED) {
            $update_data['status'] = Company::STATUS_PENDING;
            $update_data['rejection_reason'] = '';
            $this->notifyAdminResubmission($company_id);
        }

        return $this->repository->update($company_id, $update_data);
    }

    /**
     * Get a business profile
     * 
     * @param int $company_id
     * @return array|null
     */
    public function getBusiness(int $company_id): ?array {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return null;
        }

        return $company->toArray();
    }

    /**
     * Get businesses with filters
     * 
     * @param array $args
     * @return array
     */
    public function getBusinesses(array $args = []): array {
        $companies = $this->repository->getAll($args);
        return array_map(fn($c) => $c->toShortArray(), $companies);
    }

    /**
     * Get pending businesses
     * 
     * @param array $args
     * @return array
     */
    public function getPendingBusinesses(array $args = []): array {
        $companies = $this->repository->getPending($args);
        
        // Add owner information
        return array_map(function($company) {
            $data = $company->toShortArray();
            $owner = get_userdata($company->user_id);
            $data['owner_name'] = $owner ? $owner->display_name : '';
            $data['owner_email'] = $owner ? $owner->user_email : '';
            return $data;
        }, $companies);
    }

    /**
     * Delete a business profile
     * 
     * @param int $company_id
     * @return bool|\WP_Error
     */
    public function delete(int $company_id): bool {
        $company = $this->repository->find($company_id);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        // Check ownership
        if (!current_user_can('manage_myprotector') && !$company->isOwner(get_current_user_id())) {
            return new \WP_Error('unauthorized', __('You do not have permission to delete this company.', 'myprotector-platform'));
        }

        return $this->repository->delete($company_id);
    }

    /**
     * Approve a business profile
     * 
     * @param int $company_id
     * @return bool|\WP_Error
     */
    public function approve(int $company_id): bool {
        $company = $this->repository->find($company_id);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        if ($company->status !== Company::STATUS_PENDING) {
            return new \WP_Error('invalid_status', __('Only pending companies can be approved.', 'myprotector-platform'));
        }

        $result = $this->repository->updateStatus($company_id, Company::STATUS_APPROVED);

        if ($result) {
            $this->notifyOwnerApproved($company_id);
            do_action('mp_business_approved', $company_id);
        }

        return $result;
    }

    /**
     * Reject a business profile
     * 
     * @param int $company_id
     * @param string|null $reason
     * @return bool|\WP_Error
     */
    public function reject(int $company_id, ?string $reason = null): bool {
        $company = $this->repository->find($company_id);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        if ($company->status !== Company::STATUS_PENDING) {
            return new \WP_Error('invalid_status', __('Only pending companies can be rejected.', 'myprotector-platform'));
        }

        $result = $this->repository->updateStatus($company_id, Company::STATUS_REJECTED, $reason);

        if ($result) {
            $this->notifyOwnerRejected($company_id, $reason);
            do_action('mp_business_rejected', $company_id, $reason);
        }

        return $result;
    }

    /**
     * Suspend a business profile
     * 
     * @param int $company_id
     * @param string|null $reason
     * @return bool|\WP_Error
     */
    public function suspend(int $company_id, ?string $reason = null): bool {
        $company = $this->repository->find($company_id);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        $result = $this->repository->updateStatus($company_id, Company::STATUS_SUSPENDED, $reason);

        if ($result) {
            $this->notifyOwnerSuspended($company_id, $reason);
            do_action('mp_business_suspended', $company_id, $reason);
        }

        return $result;
    }

    /**
     * Upload company logo
     * 
     * @param array $files
     * @return string|\WP_Error
     */
    public function uploadLogo(array $files) {
        if (empty($files['logo'])) {
            return new \WP_Error('no_file', __('No logo file provided.', 'myprotector-platform'));
        }

        $file = $files['logo'];

        // Validate file
        if (!file_exists($file['tmp_name'])) {
            return new \WP_Error('upload_error', __('Failed to upload file.', 'myprotector-platform'));
        }

        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            return new \WP_Error('invalid_type', __('Only JPG, PNG, GIF, and WebP images are allowed.', 'myprotector-platform'));
        }

        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return new \WP_Error('file_too_large', __('Logo must be less than 2MB.', 'myprotector-platform'));
        }

        // Upload using WordPress media handle
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Handle upload
        $upload = wp_handle_upload($file, [
            'test_form' => false,
            'mimes' => [
                'jpg|jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ],
        ]);

        if (isset($upload['error'])) {
            return new \WP_Error('upload_error', $upload['error']);
        }

        // Create attachment
        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name(basename($upload['file'])),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment_id = wp_insert_attachment($attachment, $upload['file']);

        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }

        // Generate attachment metadata
        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);

        return wp_get_attachment_url($attachment_id);
    }

    /**
     * Delete company logo
     * 
     * @param array $data
     * @return bool|\WP_Error
     */
    public function deleteLogo(array $data): bool {
        if (empty($data['company_id'])) {
            return new \WP_Error('missing_id', __('Company ID is required.', 'myprotector-platform'));
        }

        $company = $this->repository->find((int) $data['company_id']);

        if (!$company) {
            return new \WP_Error('not_found', __('Company not found.', 'myprotector-platform'));
        }

        // Check ownership
        if (!current_user_can('manage_myprotector') && !$company->isOwner(get_current_user_id())) {
            return new \WP_Error('unauthorized', __('You do not have permission to update this company.', 'myprotector-platform'));
        }

        // If logo is a WordPress attachment, delete it
        if ($company->company_logo) {
            $attachment_id = attachment_url_to_postid($company->company_logo);
            if ($attachment_id) {
                wp_delete_attachment($attachment_id, true);
            }
        }

        // Update company
        return $this->repository->update($company->company_id, ['company_logo' => '']);
    }

    /**
     * Get business for current user
     * 
     * @return array|null
     */
    public function getUserBusiness(): ?array {
        $company = $this->repository->findByUserId(get_current_user_id());
        
        if (!$company) {
            return null;
        }

        return $company->toArray();
    }

    /**
     * Check if user has a business profile
     * 
     * @param int|null $user_id
     * @return bool
     */
    public function userHasBusiness(?int $user_id = null): bool {
        $user_id = $user_id ?? get_current_user_id();
        return $this->repository->findByUserId($user_id) !== null;
    }

    /**
     * Prepare company data from input
     * 
     * @param array $data
     * @return array
     */
    protected function prepareCompanyData(array $data): array {
        return [
            'company_name' => sanitize_text_field($data['company_name'] ?? ''),
            'company_description' => sanitize_textarea_field($data['company_description'] ?? ''),
            'company_website' => esc_url_raw($data['company_website'] ?? ''),
            'company_logo' => esc_url_raw($data['company_logo'] ?? ''),
            'company_address' => sanitize_textarea_field($data['company_address'] ?? ''),
            'company_phone' => sanitize_text_field($data['company_phone'] ?? ''),
            'company_email' => sanitize_email($data['company_email'] ?? ''),
            'company_category' => (int) ($data['company_category'] ?? 0),
            'insurance_name' => sanitize_text_field($data['insurance_name'] ?? ''),
            'insurance_url' => esc_url_raw($data['insurance_url'] ?? ''),
            'terms_url' => esc_url_raw($data['terms_url'] ?? ''),
            'promise_page_url' => esc_url_raw($data['promise_page_url'] ?? ''),
            'promise_page_title' => sanitize_text_field($data['promise_page_title'] ?? ''),
        ];
    }

    /**
     * Generate slug from company name
     * 
     * @param string $name
     * @return string
     */
    protected function generateSlug(string $name): string {
        $base_slug = sanitize_title($name);
        $slug = $base_slug;
        $counter = 1;

        while ($this->repository->findBySlug($slug)) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Notify admin of new submission
     * 
     * @param int $company_id
     * @return void
     */
    protected function notifyAdminNewSubmission(int $company_id): void {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return;
        }

        $owner = get_userdata($company->user_id);
        
        $message = sprintf(
            __('A new business profile has been submitted for review: %s', 'myprotector-platform'),
            $company->company_name
        );
        
        if ($owner) {
            $message .= "\n" . sprintf(__('Submitted by: %s (%s)', 'myprotector-platform'), $owner->display_name, $owner->user_email);
        }
        
        $message .= "\n" . admin_url('admin.php?page=mp-businesses-pending');

        wp_mail(
            get_option('admin_email'),
            sprintf(__('[%s] New Business Profile Pending Review', 'myprotector-platform'), get_bloginfo('name')),
            $message
        );

        do_action('mp_new_business_submission', $company_id);
    }

    /**
     * Notify admin of resubmission
     * 
     * @param int $company_id
     * @return void
     */
    protected function notifyAdminResubmission(int $company_id): void {
        do_action('mp_business_resubmission', $company_id);
    }

    /**
     * Notify owner of approval
     * 
     * @param int $company_id
     * @return void
     */
    protected function notifyOwnerApproved(int $company_id): void {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return;
        }

        $owner = get_userdata($company->user_id);
        
        if (!$owner) {
            return;
        }

        $subject = sprintf(__('[%s] Your business profile has been approved!', 'myprotector-platform'), get_bloginfo('name'));
        
        $message = sprintf(
            __("Congratulations! Your business profile '%s' has been approved and is now live.\n\n", 'myprotector-platform'),
            $company->company_name
        );
        
        $message .= __("You can now:\n", 'myprotector-platform');
        $message .= __("- Respond to customer reviews\n", 'myprotector-platform');
        $message .= __("- Access your business dashboard\n", 'myprotector-platform');
        $message .= __("- Download review widgets\n", 'myprotector-platform');

        wp_mail($owner->user_email, $subject, $message);

        do_action('mp_business_approval_notification', $company_id, $owner->user_email);
    }

    /**
     * Notify owner of rejection
     * 
     * @param int $company_id
     * @param string|null $reason
     * @return void
     */
    protected function notifyOwnerRejected(int $company_id, ?string $reason): void {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return;
        }

        $owner = get_userdata($company->user_id);
        
        if (!$owner) {
            return;
        }

        $subject = sprintf(__('[%s] Business profile update', 'myprotector-platform'), get_bloginfo('name'));
        
        $message = sprintf(
            __("Your business profile '%s' has been reviewed.\n\n", 'myprotector-platform'),
            $company->company_name
        );
        
        if ($reason) {
            $message .= sprintf(__("Reason: %s\n\n", 'myprotector-platform'), $reason);
        }
        
        $message .= __("You can edit your profile and resubmit for review.\n", 'myprotector-platform');

        wp_mail($owner->user_email, $subject, $message);

        do_action('mp_business_rejection_notification', $company_id, $owner->user_email, $reason);
    }

    /**
     * Notify owner of suspension
     * 
     * @param int $company_id
     * @param string|null $reason
     * @return void
     */
    protected function notifyOwnerSuspended(int $company_id, ?string $reason): void {
        $company = $this->repository->find($company_id);
        
        if (!$company) {
            return;
        }

        $owner = get_userdata($company->user_id);
        
        if (!$owner) {
            return;
        }

        $subject = sprintf(__('[%s] Business profile suspended', 'myprotector-platform'), get_bloginfo('name'));
        
        $message = sprintf(
            __("Your business profile '%s' has been suspended.\n\n", 'myprotector-platform'),
            $company->company_name
        );
        
        if ($reason) {
            $message .= sprintf(__("Reason: %s\n\n", 'myprotector-platform'), $reason);
        }
        
        $message .= __("Please contact support if you believe this is an error.\n", 'myprotector-platform');

        wp_mail($owner->user_email, $subject, $message);

        do_action('mp_business_suspension_notification', $company_id, $owner->user_email, $reason);
    }
}