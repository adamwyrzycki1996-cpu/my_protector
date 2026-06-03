<?php
/**
 * MyProtector Platform - Dashboards Module
 * 
 * Handles all dashboard functionality:
 * - Individual User Dashboard
 * - Business Owner Dashboard
 * - Reseller Dashboard
 * - Customer Support Dashboard
 * 
 * Features:
 * - Profile editing
 * - Notifications
 * - Review management
 * - Account settings
 * - Modern responsive UI
 * 
 * @package MyProtector\Modules\Dashboards
 * @version 1.0.0
 */

namespace MyProtector\Modules\Dashboards;

use MyProtector\Core\Module;

class Dashboards extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'dashboards';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles', 'reviews', 'emails'];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'Dashboards';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->registerEndpoints();
        $this->setupRewriteRules();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Enqueue dashboard assets
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        
        // AJAX handlers for dashboards
        $this->addAction('wp_ajax_mp_update_profile', [$this, 'ajaxUpdateProfile']);
        $this->addAction('wp_ajax_mp_update_avatar', [$this, 'ajaxUpdateAvatar']);
        $this->addAction('wp_ajax_mp_change_password', [$this, 'ajaxChangePassword']);
        $this->addAction('wp_ajax_mp_get_notifications', [$this, 'ajaxGetNotifications']);
        $this->addAction('wp_ajax_mp_mark_notification_read', [$this, 'ajaxMarkNotificationRead']);
        $this->addAction('wp_ajax_mp_get_dashboard_stats', [$this, 'ajaxGetDashboardStats']);
        
        // Dashboard pages
        $this->addAction('template_redirect', [$this, 'handleDashboardRedirect']);
        
        // User menu
        $this->addFilter('wp_nav_menu_items', [$this, 'addDashboardMenuItem'], 10, 2);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('dashboards.individual', new Services\IndividualDashboardService());
        $this->registerService('dashboards.business', new Services\BusinessDashboardService());
        $this->registerService('dashboards.reseller', new Services\ResellerDashboardService());
        $this->registerService('dashboards.support', new Services\SupportDashboardService());
        $this->registerService('dashboards.notifications', new Services\NotificationService());
    }

    /**
     * Register REST endpoints
     * 
     * @return void
     */
    protected function registerEndpoints(): void {
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE . '/v1', '/dashboards/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'getDashboardStats'],
            'permission_callback' => '__return_true',
        ]);
        
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE . '/v1', '/dashboards/profile', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'handleProfileRequest'],
            'permission_callback' => '__return_true',
        ]);
        
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE . '/v1', '/dashboards/notifications', [
            'methods' => 'GET',
            'callback' => [$this, 'getNotifications'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Setup rewrite rules for dashboard pages
     * 
     * @return void
     */
    protected function setupRewriteRules(): void {
        add_action('init', [$this, 'addDashboardRewriteRules']);
        add_filter('query_vars', [$this, 'addDashboardQueryVars']);
    }

    /**
     * Add dashboard rewrite rules
     * 
     * @return void
     */
    public function addDashboardRewriteRules(): void {
        // Individual dashboard
        add_rewrite_rule(
            '^dashboard/?$',
            'index.php?mp_dashboard_type=individual&mp_dashboard_page=overview',
            'top'
        );
        
        // Dashboard with page
        add_rewrite_rule(
            '^dashboard/([a-z-]+)/?$',
            'index.php?mp_dashboard_type=individual&mp_dashboard_page=$matches[1]',
            'top'
        );
        
        // Business dashboard
        add_rewrite_rule(
            '^business-dashboard/?$',
            'index.php?mp_dashboard_type=business&mp_dashboard_page=overview',
            'top'
        );
        
        add_rewrite_rule(
            '^business-dashboard/([a-z-]+)/?$',
            'index.php?mp_dashboard_type=business&mp_dashboard_page=$matches[1]',
            'top'
        );
        
        // Reseller dashboard
        add_rewrite_rule(
            '^reseller-dashboard/?$',
            'index.php?mp_dashboard_type=reseller&mp_dashboard_page=overview',
            'top'
        );
        
        add_rewrite_rule(
            '^reseller-dashboard/([a-z-]+)/?$',
            'index.php?mp_dashboard_type=reseller&mp_dashboard_page=$matches[1]',
            'top'
        );
        
        // Support dashboard
        add_rewrite_rule(
            '^support-dashboard/?$',
            'index.php?mp_dashboard_type=support&mp_dashboard_page=tickets',
            'top'
        );
        
        add_rewrite_rule(
            '^support-dashboard/([a-z-]+)/?$',
            'index.php?mp_dashboard_type=support&mp_dashboard_page=$matches[1]',
            'top'
        );
    }

    /**
     * Add dashboard query vars
     * 
     * @param array $vars
     * @return array
     */
    public function addDashboardQueryVars(array $vars): array {
        $vars[] = 'mp_dashboard_type';
        $vars[] = 'mp_dashboard_page';
        return $vars;
    }

    /**
     * Enqueue admin assets
     * 
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'dashboard') === false) {
            return;
        }
        
        $this->enqueueStyle('admin-dashboard', 'css/dashboards-admin.css');
        $this->enqueueScript('admin-dashboard', 'js/dashboards-admin.js', ['jquery']);
    }

    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueueFrontendAssets(): void {
        if (!is_page_template('dashboard')) {
            return;
        }
        
        $this->enqueueStyle('frontend-dashboard', 'css/dashboards-frontend.css');
        $this->enqueueScript('frontend-dashboard', 'js/dashboards-frontend.js', ['jquery']);
        
        wp_localize_script('mp-dashboards-frontend-dashboard', 'mpDashboard', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_dashboard'),
            'currentUser' => wp_get_current_user()->ID,
            'strings' => [
                'saving' => __('Saving...', 'myprotector-platform'),
                'saved' => __('Saved!', 'myprotector-platform'),
                'error' => __('An error occurred.', 'myprotector-platform'),
                'confirm' => __('Are you sure?', 'myprotector-platform'),
            ],
        ]);
    }

    /**
     * Handle dashboard template redirect
     * 
     * @return void
     */
    public function handleDashboardRedirect(): void {
        $type = get_query_var('mp_dashboard_type');
        
        if (empty($type)) {
            return;
        }
        
        // Check user access
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url());
            exit;
        }
        
        $this->loadDashboardTemplate($type);
    }

    /**
     * Load dashboard template
     * 
     * @param string $type
     * @return void
     */
    protected function loadDashboardTemplate(string $type): void {
        $page = get_query_var('mp_dashboard_page', 'overview');
        
        // Get appropriate template path
        $template = $this->getPath('templates/dashboard-' . $type . '.php');
        
        if (!file_exists($template)) {
            $template = $this->getPath('templates/dashboard-base.php');
        }
        
        if (file_exists($template)) {
            include $template;
            exit;
        }
        
        // Fallback
        wp_die('Dashboard not found');
    }

    /**
     * Add dashboard menu item to user menu
     * 
     * @param string $items
     * @param array $args
     * @return string
     */
    public function addDashboardMenuItem(string $items, array $args): string {
        if (!is_user_logged_in()) {
            return $items;
        }
        
        $dashboard_url = $this->getDashboardUrl();
        
        if (empty($dashboard_url)) {
            return $items;
        }
        
        $items .= '<li><a href="' . esc_url($dashboard_url) . '">';
        $items .= '<i class="fas fa-user-circle"></i> ';
        $items .= __('My Dashboard', 'myprotector-platform');
        $items .= '</a></li>';
        
        return $items;
    }

    /**
     * Get dashboard URL based on user role
     * 
     * @return string
     */
    public function getDashboardUrl(): string {
        $user = wp_get_current_user();
        
        if (in_array('administrator', $user->roles) || in_array('mp_admin', $user->roles)) {
            return admin_url('admin.php?page=mp-dashboard');
        }
        
        if (in_array('mp_business', $user->roles)) {
            return home_url('/business-dashboard/');
        }
        
        if (in_array('mp_reseller', $user->roles)) {
            return home_url('/reseller-dashboard/');
        }
        
        if (in_array('mp_support', $user->roles)) {
            return home_url('/support-dashboard/');
        }
        
        return home_url('/dashboard/');
    }

    /**
     * AJAX: Update profile
     * 
     * @return void
     */
    public function ajaxUpdateProfile(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $user_id = get_current_user_id();
        
        $data = [
            'display_name' => sanitize_text_field($_POST['display_name'] ?? ''),
            'first_name' => sanitize_text_field($_POST['first_name'] ?? ''),
            'last_name' => sanitize_text_field($_POST['last_name'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
        ];
        
        // Update WordPress user
        wp_update_user([
            'ID' => $user_id,
            'display_name' => $data['display_name'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'description' => $data['description'],
        ]);
        
        // Update custom meta
        update_user_meta($user_id, 'mp_user_bio', $data['description']);
        
        wp_send_json_success([
            'message' => __('Profile updated successfully!', 'myprotector-platform'),
            'data' => $data,
        ]);
    }

    /**
     * AJAX: Update avatar
     * 
     * @return void
     */
    public function ajaxUpdateAvatar(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        if (empty($_FILES['avatar'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 'myprotector-platform')]);
        }
        
        $user_id = get_current_user_id();
        
        // Handle file upload
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $attachment_id = media_handle_upload('avatar', 0);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => $attachment_id->get_error_message()]);
        }
        
        $avatar_url = wp_get_attachment_url($attachment_id);
        update_user_meta($user_id, 'mp_avatar_url', $avatar_url);
        update_user_meta($user_id, 'mp_avatar_id', $attachment_id);
        
        wp_send_json_success([
            'message' => __('Avatar updated!', 'myprotector-platform'),
            'avatar_url' => $avatar_url,
        ]);
    }

    /**
     * AJAX: Change password
     * 
     * @return void
     */
    public function ajaxChangePassword(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $user = wp_get_current_user();
        
        if (!wp_check_password($current_password, $user->user_pass, $user->ID)) {
            wp_send_json_error(['message' => __('Current password is incorrect.', 'myprotector-platform')]);
        }
        
        if (strlen($new_password) < 8) {
            wp_send_json_error(['message' => __('Password must be at least 8 characters.', 'myprotector-platform')]);
        }
        
        if ($new_password !== $confirm_password) {
            wp_send_json_error(['message' => __('Passwords do not match.', 'myprotector-platform')]);
        }
        
        wp_set_password($new_password, $user->ID);
        
        // Re-authenticate
        wp_signon([
            'user_login' => $user->user_login,
            'user_password' => $new_password,
            'remember' => true,
        ]);
        
        wp_send_json_success([
            'message' => __('Password changed successfully!', 'myprotector-platform'),
        ]);
    }

    /**
     * AJAX: Get notifications
     * 
     * @return void
     */
    public function ajaxGetNotifications(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('dashboards.notifications');
        $notifications = $service->getUserNotifications(get_current_user_id());
        
        wp_send_json_success([
            'notifications' => $notifications['items'],
            'unread_count' => $notifications['unread_count'],
        ]);
    }

    /**
     * AJAX: Mark notification as read
     * 
     * @return void
     */
    public function ajaxMarkNotificationRead(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $notification_id = (int)($_POST['notification_id'] ?? 0);
        
        $service = $this->getService('dashboards.notifications');
        $service->markAsRead($notification_id, get_current_user_id());
        
        wp_send_json_success([
            'message' => __('Notification marked as read.', 'myprotector-platform'),
        ]);
    }

    /**
     * AJAX: Get dashboard stats
     * 
     * @return void
     */
    public function ajaxGetDashboardStats(): void {
        check_ajax_referer('mp_dashboard', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        
        $stats = [];
        
        // Individual stats
        if (in_array('subscriber', $user->roles) || !empty(array_diff($user->roles, ['administrator', 'editor', 'author', 'contributor']))) {
            $service = $this->getService('dashboards.individual');
            $stats = $service->getStats($user_id);
        }
        
        // Business stats
        if (in_array('mp_business', $user->roles)) {
            $service = $this->getService('dashboards.business');
            $stats = $service->getStats($user_id);
        }
        
        // Reseller stats
        if (in_array('mp_reseller', $user->roles)) {
            $service = $this->getService('dashboards.reseller');
            $stats = $service->getStats($user_id);
        }
        
        // Support stats
        if (in_array('mp_support', $user->roles)) {
            $service = $this->getService('dashboards.support');
            $stats = $service->getStats($user_id);
        }
        
        wp_send_json_success($stats);
    }

    /**
     * REST API: Get dashboard stats
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getDashboardStats(\WP_REST_Request $request): \WP_REST_Response {
        if (!is_user_logged_in()) {
            return new \WP_REST_Response(['error' => 'Unauthorized'], 401);
        }
        
        $user_id = get_current_user_id();
        $service = $this->getService('dashboards.individual');
        
        return new \WP_REST_Response([
            'success' => true,
            'stats' => $service->getStats($user_id),
        ], 200);
    }

    /**
     * REST API: Handle profile request
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function handleProfileRequest(\WP_REST_Request $request): \WP_REST_Response {
        if (!is_user_logged_in()) {
            return new \WP_REST_Response(['error' => 'Unauthorized'], 401);
        }
        
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        
        if ($request->get_method() === 'GET') {
            return new \WP_REST_Response([
                'success' => true,
                'profile' => [
                    'id' => $user->ID,
                    'email' => $user->user_email,
                    'display_name' => $user->display_name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'description' => $user->description,
                    'avatar_url' => get_user_meta($user_id, 'mp_avatar_url', true) ?: get_avatar_url($user_id),
                    'registered' => $user->user_registered,
                ],
            ], 200);
        }
        
        // POST - Update profile
        $data = $request->get_json_params();
        
        wp_update_user([
            'ID' => $user_id,
            'display_name' => sanitize_text_field($data['display_name'] ?? $user->display_name),
            'first_name' => sanitize_text_field($data['first_name'] ?? ''),
            'last_name' => sanitize_text_field($data['last_name'] ?? ''),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
        ]);
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Profile updated.', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API: Get notifications
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getNotifications(\WP_REST_Request $request): \WP_REST_Response {
        if (!is_user_logged_in()) {
            return new \WP_REST_Response(['error' => 'Unauthorized'], 401);
        }
        
        $service = $this->getService('dashboards.notifications');
        $notifications = $service->getUserNotifications(get_current_user_id());
        
        return new \WP_REST_Response([
            'success' => true,
            'notifications' => $notifications['items'],
            'unread_count' => $notifications['unread_count'],
        ], 200);
    }
}