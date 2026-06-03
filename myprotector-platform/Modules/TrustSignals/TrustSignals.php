<?php
/**
 * MyProtector Platform - Trust Signals Module
 * 
 * Handles the trust signal system with RED/AMBER/GREEN status indicators.
 * Status is automatically calculated based on business profile completeness
 * and can be manually overridden by admins.
 * 
 * @package MyProtector\Modules\TrustSignals
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals;

use MyProtector\Core\Module;

class TrustSignals extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'trust-signals';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles'];

    /**
     * Status constants
     */
    public const STATUS_GREEN = 'green';
    public const STATUS_AMBER = 'amber';
    public const STATUS_RED = 'red';

    /**
     * Green requirements
     */
    public const REQUIREMENT_INSURANCE_PAGE = 'insurance_page';
    public const REQUIREMENT_REFUND_HISTORY = 'refund_history';
    public const REQUIREMENT_CLAIMS_PAGE = 'claims_page';
    public const REQUIREMENT_TERMS_PAGE = 'terms_page';
    public const REQUIREMENT_ACTIVE_SUBSCRIPTION = 'active_subscription';

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'TrustSignals';
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
        // Trust signal calculation hooks
        $this->addAction('mp_company_updated', [$this, 'recalculateTrustSignal'], 10, 2);
        $this->addAction('mp_subscription_activated', [$this, 'recalculateTrustSignal'], 10, 2);
        $this->addAction('mp_subscription_cancelled', [$this, 'handleSubscriptionCancelled'], 10, 2);
        $this->addAction('mp_daily_trust_update', [$this, 'batchRecalculateTrustSignals']);

        // Admin hooks
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Public hooks
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueuePublicAssets']);

        // AJAX handlers
        $this->addAction('wp_ajax_mp_override_trust_signal', [$this, 'handleTrustSignalOverride']);
        $this->addAction('wp_ajax_mp_refresh_trust_signal', [$this, 'handleRefreshTrustSignal']);

        // REST API
        $this->registerApiRoutes();
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('trust_signals.service', new Services\TrustSignalService());
        $this->registerService('trust_signals.calculator', new Services\TrustSignalCalculatorService());
        $this->registerService('trust_signals.admin', new Services\TrustSignalAdminService());
    }

    /**
     * Initialize controllers
     * 
     * @return void
     */
    protected function initControllers(): void {
        if (is_admin()) {
            $this->adminController = new Admin\TrustSignalsAdminController($this);
        }
        
        $this->publicController = new Public\TrustSignalsPublicController($this);
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
        // Get trust signal for a company
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/trust-signals/(?P<company_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getTrustSignalApi'],
            'permission_callback' => '__return_true',
        ]);

        // Get trust signal status details (requirements breakdown)
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/trust-signals/(?P<company_id>\d+)/details', [
            'methods' => 'GET',
            'callback' => [$this, 'getTrustSignalDetailsApi'],
            'permission_callback' => '__return_true',
        ]);

        // Admin: Override trust signal
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/trust-signals/(?P<company_id>\d+)/override', [
            'methods' => 'POST',
            'callback' => [$this, 'overrideTrustSignalApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);

        // Admin: Clear override
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/trust-signals/(?P<company_id>\d+)/clear-override', [
            'methods' => 'POST',
            'callback' => [$this, 'clearOverrideApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);

        // Admin: List all trust signals
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/trust-signals', [
            'methods' => 'GET',
            'callback' => [$this, 'listTrustSignalsApi'],
            'permission_callback' => function() {
                return current_user_can('manage_myprotector');
            },
        ]);

        // Admin: Batch recalculate
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/admin/trust-signals/recalculate', [
            'methods' => 'POST',
            'callback' => [$this, 'batchRecalculateApi'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            },
        ]);
    }

    /**
     * Add admin menu items
     * 
     * @return void
     */
    public function addAdminMenu(): void {
        add_submenu_page(
            'mp-businesses',
            __('Trust Signals', 'myprotector-platform'),
            __('Trust Signals', 'myprotector-platform'),
            'manage_myprotector',
            'mp-trust-signals',
            [$this->adminController, 'renderListPage']
        );
    }

    /**
     * Enqueue admin assets
     * 
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'mp-trust-signals') === false) {
            return;
        }

        wp_enqueue_style(
            'mp-trust-signals-admin',
            $this->getUrl('assets/css/trust-signals-admin.css'),
            [],
            $this->version
        );

        wp_enqueue_script(
            'mp-trust-signals-admin',
            $this->getUrl('assets/js/trust-signals-admin.js'),
            ['jquery'],
            $this->version,
            true
        );

        wp_localize_script('mp-trust-signals-admin', 'mpTrustSignals', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_trust_signals_admin'),
            'strings' => [
                'confirmOverride' => __('Are you sure you want to override the trust signal?', 'myprotector-platform'),
                'overrideSuccess' => __('Trust signal overridden successfully.', 'myprotector-platform'),
                'overrideError' => __('Failed to override trust signal.', 'myprotector-platform'),
            ],
        ]);
    }

    /**
     * Enqueue public assets
     * 
     * @return void
     */
    public function enqueuePublicAssets(): void {
        // Assets loaded conditionally via shortcode/block
    }

    /**
     * Recalculate trust signal for a company
     * 
     * @param int $companyId
     * @param array $data
     * @return void
     */
    public function recalculateTrustSignal(int $companyId, array $data = []): void {
        $service = $this->getService('trust_signals.service');
        $service->recalculateForCompany($companyId);
    }

    /**
     * Handle subscription cancelled
     * 
     * @param int $companyId
     * @param array $data
     * @return void
     */
    public function handleSubscriptionCancelled(int $companyId, array $data = []): void {
        $service = $this->getService('trust_signals.service');
        $service->recalculateForCompany($companyId);
    }

    /**
     * Batch recalculate trust signals (cron job)
     * 
     * @return void
     */
    public function batchRecalculateTrustSignals(): void {
        $service = $this->getService('trust_signals.service');
        $service->batchRecalculate();
    }

    /**
     * Handle trust signal override (AJAX)
     * 
     * @return void
     */
    public function handleTrustSignalOverride(): void {
        check_ajax_referer('mp_trust_signals_admin', 'nonce');

        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }

        $companyId = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';

        if (!in_array($status, [self::STATUS_GREEN, self::STATUS_AMBER, self::STATUS_RED], true)) {
            wp_send_json_error(['message' => __('Invalid status.', 'myprotector-platform')]);
        }

        $service = $this->getService('trust_signals.admin');
        $result = $service->overrideStatus($companyId, $status, $reason);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Trust signal overridden.', 'myprotector-platform')]);
    }

    /**
     * Handle refresh trust signal (AJAX)
     * 
     * @return void
     */
    public function handleRefreshTrustSignal(): void {
        check_ajax_referer('mp_trust_signals_admin', 'nonce');

        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }

        $companyId = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;

        $service = $this->getService('trust_signals.service');
        $signal = $service->recalculateForCompany($companyId);

        wp_send_json_success([
            'status' => $signal['status'],
            'signal' => $signal,
        ]);
    }

    /**
     * REST API - Get trust signal
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getTrustSignalApi(\WP_REST_Request $request): \WP_REST_Response {
        $companyId = (int) $request->get_param('company_id');
        
        $service = $this->getService('trust_signals.service');
        $signal = $service->getForCompany($companyId);

        if (!$signal) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Trust signal not found.', 'myprotector-platform'),
            ], 404);
        }

        return new \WP_REST_Response([
            'success' => true,
            'data' => $signal,
        ], 200);
    }

    /**
     * REST API - Get trust signal details
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getTrustSignalDetailsApi(\WP_REST_Request $request): \WP_REST_Response {
        $companyId = (int) $request->get_param('company_id');
        
        $service = $this->getService('trust_signals.service');
        $details = $service->getDetailsForCompany($companyId);

        return new \WP_REST_Response([
            'success' => true,
            'data' => $details,
        ], 200);
    }

    /**
     * REST API - Override trust signal (admin)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function overrideTrustSignalApi(\WP_REST_Request $request): \WP_REST_Response {
        $companyId = (int) $request->get_param('company_id');
        $status = $request->get_param('status');
        $reason = $request->get_param('reason') ?: '';

        if (!in_array($status, [self::STATUS_GREEN, self::STATUS_AMBER, self::STATUS_RED], true)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Invalid status.', 'myprotector-platform'),
            ], 400);
        }

        $service = $this->getService('trust_signals.admin');
        $result = $service->overrideStatus($companyId, $status, $reason);

        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Trust signal overridden.', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - Clear override (admin)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function clearOverrideApi(\WP_REST_Request $request): \WP_REST_Response {
        $companyId = (int) $request->get_param('company_id');

        $service = $this->getService('trust_signals.admin');
        $result = $service->clearOverride($companyId);

        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Override cleared.', 'myprotector-platform'),
        ], 200);
    }

    /**
     * REST API - List trust signals (admin)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function listTrustSignalsApi(\WP_REST_Request $request): \WP_REST_Response {
        $args = [
            'status' => $request->get_param('status'),
            'search' => $request->get_param('search'),
            'limit' => (int) $request->get_param('per_page') ?: 20,
            'page' => (int) $request->get_param('page') ?: 1,
        ];

        $service = $this->getService('trust_signals.service');
        $signals = $service->list($args);

        return new \WP_REST_Response([
            'success' => true,
            'data' => $signals,
        ], 200);
    }

    /**
     * REST API - Batch recalculate (admin)
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function batchRecalculateApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('trust_signals.service');
        $result = $service->batchRecalculate();

        return new \WP_REST_Response([
            'success' => true,
            'processed' => $result['processed'],
            'message' => sprintf(__('Recalculated %d trust signals.', 'myprotector-platform'), $result['processed']),
        ], 200);
    }

    /**
     * Get the service
     * 
     * @param string $id
     * @return mixed
     */
    public function getTrustSignalService(string $id = 'trust_signals.service') {
        return $this->getService($id);
    }
}