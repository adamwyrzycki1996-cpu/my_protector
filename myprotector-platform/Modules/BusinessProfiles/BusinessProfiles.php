<?php
/**
 * MyProtector Platform - Business Profiles Module
 * 
 * Handles all business profile functionality including:
 * - Company profile creation and management
 * - Company logo upload
 * - Website, Insurance, Terms, and Promise URL management
 * - Admin approval workflow
 * 
 * @package MyProtector\Modules\BusinessProfiles
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles;

use MyProtector\Core\Module;

class BusinessProfiles extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'business-profiles';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = [];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'BusinessProfiles';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->initControllers();
        $this->setupAjaxEndpoints();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Profile submission
        $this->addAction('wp_ajax_submit_business_profile', [$this, 'handleProfileSubmission']);
        $this->addAction('wp_ajax_nopriv_submit_business_profile', [$this, 'handleProfileSubmission']);
        
        // Profile update
        $this->addAction('wp_ajax_update_business_profile', [$this, 'handleProfileUpdate']);
        $this->addAction('wp_ajax_nopriv_update_business_profile', [$this, 'handleProfileUpdate']);
        
        // Logo upload
        $this->addAction('wp_ajax_upload_company_logo', [$this, 'handleLogoUpload']);
        $this->addAction('wp_ajax_nopriv_upload_company_logo', [$this, 'handleLogoUpload']);
        
        // Delete logo
        $this->addAction('wp_ajax_delete_company_logo', [$this, 'handleLogoDelete']);
        
        // Admin hooks
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Public hooks
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);
        
        // Post type
        $this->addAction('init', [$this, 'registerPostType']);
        
        // Admin approval hooks
        $this->addAction('add_meta_boxes', [$this, 'addMetaBoxes']);
        $this->addAction('save_post_mp_company', [$this, 'handleMetaBoxSave'], 10, 2);
        
        // REST API
        $this->registerApiRoutes();
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        // Business service
        $this->registerService('business.service', new Services\BusinessService(
            $this->plugin()->getContainer()
        ));
        
        // Verification service
        $this->registerService('business.verification', new Services\BusinessVerificationService(
            $this->plugin()->getContainer()
        ));
        
        // Analytics service
        $this->registerService('business.analytics', new Services\BusinessAnalyticsService(
            $this->plugin()->getContainer()
        ));
    }

    /**
     * Initialize controllers
     * 
     * @return void
     */
    protected function initControllers(): void {
        // Admin controller
        if (is_admin()) {
            $this->adminController = new Admin\BusinessAdminController($this);
        }
        
        // Public controller
        $this->publicController = new Public\BusinessPublicController($this);
    }

    /**
     * Setup AJAX endpoints
     * 
     * @return void
     */
    protected function setupAjaxEndpoints(): void {
        // Additional AJAX endpoints registered in registerHooks
    }

    /**
     * Register REST API routes
     * 
     * @return void
     */
    protected function registerApiRoutes(): void {
        // Get business profiles
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses', [
            'methods' => 'GET',
            'callback' => [$this, 'getBusinessesApi'],
            'permission_callback' => '__return_true',
        ]);
        
        // Get single business profile
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getBusinessApi'],
            'permission_callback' => '__return_true',
        ]);
        
        // Create business profile
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses', [
            'methods' => 'POST',
            'callback' => [$this, 'createBusinessApi'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);
        
        // Update business profile
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'updateBusinessApi'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);
        
        // Delete business profile
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteBusinessApi'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
        
        // Upload logo
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/businesses/upload-logo', [
            'methods' => 'POST',
            'callback' => [$this, 'uploadLogoApi'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);
        
        // Admin: Approve business
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/businesses/(?P<id>\d+)/approve', [
            'methods' => 'POST',
            'callback' => [$this, 'approveBusinessApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);
        
        // Admin: Reject business
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/businesses/(?P<id>\d+)/reject', [
            'methods' => 'POST',
            'callback' => [$this, 'rejectBusinessApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);
        
        // Admin: List pending businesses
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/businesses/pending', [
            'methods' => 'GET',
            'callback' => [$this, 'getPendingBusinessesApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);
    }

    /**
     * Add admin menu items
     * 
     * @return void
     */
    public function addAdminMenu(): void {
        // Main menu
        add_menu_page(
            __('Business Profiles', 'myprotector-platform'),
            __('Business Profiles', 'myprotector-platform'),
            'manage_myprotector',
            'mp-businesses',
            [$this->adminController, 'renderListPage'],
            'dashicons-building',
            30
        );
        
        // Submenu items
        add_submenu_page(
            'mp-businesses',
            __('All Businesses', 'myprotector-platform'),
            __('All Businesses', 'myprotector-platform'),
            'manage_myprotector',
            'mp-businesses',
            [$this->adminController, 'renderListPage']
        );
        
        add_submenu_page(
            'mp-businesses',
            __('Pending Approval', 'myprotector-platform'),
            __('Pending Approval', 'myprotector-platform'),
            'manage_myprotector',
            'mp-businesses-pending',
            [$this->adminController, 'renderPendingPage']
        );
        
        add_submenu_page(
            'mp-businesses',
            __('Add New', 'myprotector-platform'),
            __('Add New', 'myprotector-platform'),
            'manage_myprotector',
            'mp-businesses-add',
            [$this->adminController, 'renderAddPage']
        );
    }

    /**
     * Enqueue admin assets
     * 
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'mp-businesses') === false) {
            return;
        }
        
        // Media uploader
        wp_enqueue_media();
        
        // Admin styles
        $this->enqueueStyle('admin', 'css/business-admin.css', ['wp-components']);
        
        // Admin scripts
        $this->enqueueScript('admin', 'js/business-admin.js', ['jquery', 'wp-util'], true);
        
        // Localize script
        wp_localize_script('mp-business-profiles-admin', 'mpBusinessAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_business_admin'),
            'apiUrl' => rest_url(MYPROTECTOR_API_NAMESPACE),
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this business profile?', 'myprotector-platform'),
                'approveSuccess' => __('Business profile approved successfully.', 'myprotector-platform'),
                'rejectSuccess' => __('Business profile rejected.', 'myprotector-platform'),
                'error' => __('An error occurred. Please try again.', 'myprotector-platform'),
            ],
        ]);
    }

    /**
     * Enqueue public assets
     * 
     * @return void
     */
    public function enqueuePublicAssets(): void {
        // Public styles
        $this->enqueueStyle('public', 'css/business-public.css');
        
        // Public scripts
        $this->enqueueScript('public', 'js/business-public.js', ['jquery'], true);
        
        // Localize script
        wp_localize_script('mp-business-profiles-public', 'mpBusinessPublic', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_business_public'),
            'apiUrl' => rest_url(MYPROTECTOR_API_NAMESPACE),
            'strings' => [
                'required' => __('This field is required.', 'myprotector-platform'),
                'invalidUrl' => __('Please enter a valid URL.', 'myprotector-platform'),
                'uploadError' => __('Failed to upload image. Please try again.', 'myprotector-platform'),
            ],
        ]);
    }

    /**
     * Register post type
     * 
     * @return void
     */
    public function registerPostType(): void {
        register_post_type('mp_company', [
            'labels' => [
                'name' => __('Businesses', 'myprotector-platform'),
                'singular_name' => __('Business', 'myprotector-platform'),
                'add_new' => __('Add New', 'myprotector-platform'),
                'add_new_item' => __('Add New Business', 'myprotector-platform'),
                'edit_item' => __('Edit Business', 'myprotector-platform'),
                'new_item' => __('New Business', 'myprotector-platform'),
                'view_item' => __('View Business', 'myprotector-platform'),
                'search_items' => __('Search Businesses', 'myprotector-platform'),
                'not_found' => __('No businesses found', 'myprotector-platform'),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => get_option('mp_company_slug_base', 'businesses')],
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'custom-fields', 'excerpt'],
            'menu_icon' => 'dashicons-building',
            'capabilities' => [
                'edit_post' => 'edit_mp_company',
                'read_post' => 'read_mp_company',
                'delete_post' => 'delete_mp_company',
                'edit_posts' => 'edit_mp_companies',
                'edit_others_posts' => 'edit_others_mp_companies',
                'publish_posts' => 'publish_mp_companies',
                'read_private_posts' => 'read_private_mp_companies',
                'delete_posts' => 'delete_mp_companies',
                'delete_others_posts' => 'delete_others_mp_companies',
            ],
        ]);
    }

    /**
     * Add meta boxes
     * 
     * @return void
     */
    public function addMetaBoxes(): void {
        add_meta_box(
            'mp_business_details',
            __('Business Details', 'myprotector-platform'),
            [$this->adminController, 'renderDetailsMetaBox'],
            'mp_company',
            'normal',
            'high'
        );
        
        add_meta_box(
            'mp_business_urls',
            __('Business URLs', 'myprotector-platform'),
            [$this->adminController, 'renderUrlsMetaBox'],
            'mp_company',
            'normal',
            'default'
        );
        
        add_meta_box(
            'mp_business_approval',
            __('Approval Status', 'myprotector-platform'),
            [$this->adminController, 'renderApprovalMetaBox'],
            'mp_company',
            'side',
            'default'
        );
    }

    /**
     * Handle meta box save
     * 
     * @param int $post_id
     * @param \WP_Post $post
     * @return void
     */
    public function handleMetaBoxSave(int $post_id, \WP_Post $post): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_mp_company', $post_id)) {
            return;
        }
        
        // Save meta fields
        $meta_fields = [
            'company_website',
            'company_logo',
            'company_phone',
            'company_email',
            'insurance_name',
            'insurance_url',
            'terms_url',
            'promise_page_url',
            'promise_page_title',
            'company_address',
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Save status
        if (isset($_POST['company_status'])) {
            $old_status = get_post_meta($post_id, '_company_status', true);
            $new_status = sanitize_text_field($_POST['company_status']);
            
            update_post_meta($post_id, '_company_status', $new_status);
            
            // Trigger status change hooks
            if ($old_status !== $new_status) {
                do_action('mp_business_status_changed', $post_id, $new_status, $old_status);
            }
        }
    }

    /**
     * Handle profile submission
     * 
     * @return void
     */
    public function handleProfileSubmission(): void {
        check_ajax_referer('mp_submit_business_profile', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to submit a business profile.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('business.service');
        $result = $service->create($_POST);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Business profile submitted successfully! It will be reviewed by an admin.', 'myprotector-platform'),
            'profile_id' => $result,
        ]);
    }

    /**
     * Handle profile update
     * 
     * @return void
     */
    public function handleProfileUpdate(): void {
        check_ajax_referer('mp_update_business_profile', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to update a business profile.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('business.service');
        $result = $service->update($_POST);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Business profile updated successfully!', 'myprotector-platform'),
        ]);
    }

    /**
     * Handle logo upload
     * 
     * @return void
     */
    public function handleLogoUpload(): void {
        check_ajax_referer('mp_upload_logo', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to upload a logo.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('business.service');
        $result = $service->uploadLogo($_FILES);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Logo uploaded successfully!', 'myprotector-platform'),
            'logo_url' => $result,
        ]);
    }

    /**
     * Handle logo delete
     * 
     * @return void
     */
    public function handleLogoDelete(): void {
        check_ajax_referer('mp_delete_logo', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('You must be logged in to delete a logo.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('business.service');
        $result = $service->deleteLogo($_POST);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Logo deleted successfully!', 'myprotector-platform'),
        ]);
    }

    /**
     * REST API - Get businesses
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getBusinessesApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        
        $args = [
            'status' => $request->get_param('status') ?? 'approved',
            'category' => $request->get_param('category'),
            'search' => $request->get_param('search'),
            'limit' => $request->get_param('per_page') ?? 10,
            'page' => $request->get_param('page') ?? 1,
        ];
        
        $businesses = $service->getBusinesses($args);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $businesses,
        ], 200);
    }

    /**
     * REST API - Get single business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        $business = $service->getBusiness($request->get_param('id'));
        
        if (!$business) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Business not found.', 'myprotector-platform'),
            ], 404);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $business,
        ], 200);
    }

    /**
     * REST API - Create business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        
        $data = [
            'company_name' => $request->get_param('company_name'),
            'company_description' => $request->get_param('company_description'),
            'company_website' => $request->get_param('company_website'),
            'company_phone' => $request->get_param('company_phone'),
            'company_email' => $request->get_param('company_email'),
            'company_address' => $request->get_param('company_address'),
            'company_logo' => $request->get_param('company_logo'),
            'insurance_name' => $request->get_param('insurance_name'),
            'insurance_url' => $request->get_param('insurance_url'),
            'terms_url' => $request->get_param('terms_url'),
            'promise_page_url' => $request->get_param('promise_page_url'),
            'promise_page_title' => $request->get_param('promise_page_title'),
            'user_id' => get_current_user_id(),
        ];
        
        $result = $service->create($data);
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Business profile created successfully!', 'myprotector-platform'),
            'profile_id' => $result,
        ], 201);
    }

    /**
     * REST API - Update business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function updateBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        
        $data = [
            'id' => $request->get_param('id'),
            'company_name' => $request->get_param('company_name'),
            'company_description' => $request->get_param('company_description'),
            'company_website' => $request->get_param('company_website'),
            'company_phone' => $request->get_param('company_phone'),
            'company_email' => $request->get_param('company_email'),
            'company_address' => $request->get_param('company_address'),
            'company_logo' => $request->get_param('company_logo'),
            'insurance_name' => $request->get_param('insurance_name'),
            'insurance_url' => $request->get_param('insurance_url'),
            'terms_url' => $request->get_param('terms_url'),
            'promise_page_url' => $request->get_param('promise_page_url'),
            'promise_page_title' => $request->get_param('promise_page_title'),
        ];
        
        $result = $service->update($data);
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Business profile updated successfully!', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - Delete business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        $result = $service->delete($request->get_param('id'));
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Business profile deleted successfully!', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - Upload logo
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function uploadLogoApi(\WP_REST_Request $request): \WP_REST_Response {
        // Handle logo upload via multipart
        if (empty($_FILES['logo'])) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('No logo file provided.', 'myprotector-platform'),
            ], 400);
        }
        
        $service = $this->getService('business.service');
        $result = $service->uploadLogo($_FILES);
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Logo uploaded successfully!', 'myprotector-platform'),
            'logo_url' => $result,
        ], 200);
    }

    /**
     * REST API - Approve business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function approveBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        $result = $service->approve($request->get_param('id'));
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Business profile approved successfully!', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - Reject business
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function rejectBusinessApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        $reason = $request->get_param('reason');
        
        $result = $service->reject($request->get_param('id'), $reason);
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Business profile rejected.', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - Get pending businesses
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getPendingBusinessesApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('business.service');
        
        $args = [
            'status' => 'pending',
            'limit' => $request->get_param('per_page') ?? 20,
            'page' => $request->get_param('page') ?? 1,
        ];
        
        $businesses = $service->getPendingBusinesses($args);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $businesses,
        ], 200);
    }
}