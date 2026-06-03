<?php
/**
 * MyProtector Platform - Resellers Module
 * 
 * Handles reseller functionality:
 * - Referral links and tracking
 * - Business referrals management
 * - Commission tracking
 * - Commission reports
 * - Admin approval and payout
 * 
 * @package MyProtector\Modules\Resellers
 * @version 1.0.0
 */

namespace MyProtector\Modules\Resellers;

use MyProtector\Core\Module;

class Resellers extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'resellers';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles', 'dashboards'];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'Resellers';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->setupReferralTracking();
        $this->registerShortcodes();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Frontend hooks
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        
        // AJAX handlers
        $this->addAction('wp_ajax_mp_reseller_apply', [$this, 'ajaxResellerApply']);
        $this->addAction('wp_ajax_mp_approve_commission', [$this, 'ajaxApproveCommission']);
        $this->addAction('wp_ajax_mp_pay_commission', [$this, 'ajaxPayCommission']);
        $this->addAction('wp_ajax_mp_get_commission_report', [$this, 'ajaxGetCommissionReport']);
        
        // Referral tracking
        $this->addAction('init', [$this, 'trackReferral']);
        
        // User registration hook
        $this->addAction('user_register', [$this, 'handleUserRegistration'], 10, 2);
        
        // Business creation hook
        $this->addAction('mp_business_created', [$this, 'handleBusinessCreated']);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('resellers.service', new Services\ResellerService());
        $this->registerService('resellers.commission', new Services\CommissionService());
        $this->registerService('resellers.tracking', new Services\ReferralTrackingService());
    }

    /**
     * Setup referral tracking
     * 
     * @return void
     */
    protected function setupReferralTracking(): void {
        // Track referral cookie
        add_action('init', [$this, 'setReferralCookie']);
    }

    /**
     * Register shortcodes
     * 
     * @return void
     */
    protected function registerShortcodes(): void {
        add_shortcode('mp_reseller_apply', [$this, 'renderResellerApplicationForm']);
        add_shortcode('mp_referral_link', [$this, 'renderReferralLink']);
        add_shortcode('mp_reseller_dashboard_link', [$this, 'renderResellerDashboardLink']);
    }

    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueueFrontendAssets(): void {
        if (!is_singular('mp_reseller')) {
            return;
        }
        
        $this->enqueueStyle('resellers-frontend', 'css/resellers-frontend.css');
        $this->enqueueScript('resellers-frontend', 'js/resellers-frontend.js', ['jquery']);
    }

    /**
     * Set referral cookie
     * 
     * @return void
     */
    public function setReferralCookie(): void {
        if (isset($_GET['ref']) && !isset($_COOKIE['mp_referral'])) {
            $referral_code = sanitize_text_field($_GET['ref']);
            
            // Verify referral code exists
            $service = $this->getService('resellers.service');
            $reseller = $service->getResellerByCode($referral_code);
            
            if ($reseller && $reseller->reseller_status === 'active') {
                setcookie('mp_referral', $referral_code, time() + (86400 * 30), '/');
                $_COOKIE['mp_referral'] = $referral_code;
            }
        }
    }

    /**
     * Track referral
     * 
     * @return void
     */
    public function trackReferral(): void {
        if (empty($_COOKIE['mp_referral'])) {
            return;
        }
        
        $referral_code = sanitize_text_field($_COOKIE['mp_referral']);
        
        // Store in session for later use
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['mp_referral_code'] = $referral_code;
    }

    /**
     * Handle user registration (check for referral)
     * 
     * @param int $user_id
     * @param array $userdata
     * @return void
     */
    public function handleUserRegistration(int $user_id, array $userdata): void {
        if (empty($_COOKIE['mp_referral']) && empty($_SESSION['mp_referral_code'])) {
            return;
        }
        
        $referral_code = sanitize_text_field($_COOKIE['mp_referral'] ?? $_SESSION['mp_referral_code']);
        
        $service = $this->getService('resellers.service');
        $reseller = $service->getResellerByCode($referral_code);
        
        if (!$reseller) {
            return;
        }
        
        // Update referral count
        $service->incrementReferralCount($reseller->reseller_id);
        
        // Create commission record for signup
        $commission_service = $this->getService('resellers.commission');
        $commission_service->createSignupCommission($reseller->reseller_id, $user_id);
    }

    /**
     * Handle business created
     * 
     * @param int $business_id
     * @param array $data
     * @return void
     */
    public function handleBusinessCreated(int $business_id, array $data): void {
        $referral_code = $_COOKIE['mp_referral'] ?? $_SESSION['mp_referral_code'] ?? null;
        
        if (!$referral_code) {
            return;
        }
        
        $service = $this->getService('resellers.service');
        $reseller = $service->getResellerByCode($referral_code);
        
        if (!$reseller) {
            return;
        }
        
        // Link business to reseller
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'mp_businesses',
            ['reseller_id' => $reseller->reseller_id],
            ['business_id' => $business_id],
            ['%d'],
            ['%d']
        );
        
        // Create subscription commission
        $commission_service = $this->getService('resellers.commission');
        $commission_service->createSubscriptionCommission($reseller->reseller_id, $business_id);
    }

    /**
     * AJAX: Reseller application
     * 
     * @return void
     */
    public function ajaxResellerApply(): void {
        check_ajax_referer('mp_reseller_apply', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in first.', 'myprotector-platform')]);
        }
        
        $data = [
            'user_id' => get_current_user_id(),
            'company_name' => sanitize_text_field($_POST['company_name'] ?? ''),
            'company_url' => esc_url_raw($_POST['company_url'] ?? ''),
            'payout_method' => sanitize_text_field($_POST['payout_method'] ?? 'bank_transfer'),
            'payout_details' => $_POST['payout_details'] ?? [],
        ];
        
        $service = $this->getService('resellers.service');
        $result = $service->apply($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Application submitted! We will review it shortly.', 'myprotector-platform'),
        ]);
    }

    /**
     * AJAX: Approve commission
     * 
     * @return void
     */
    public function ajaxApproveCommission(): void {
        check_ajax_referer('mp_admin', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }
        
        $commission_id = (int)($_POST['commission_id'] ?? 0);
        
        $service = $this->getService('resellers.commission');
        $result = $service->approve($commission_id, get_current_user_id());
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Commission approved.', 'myprotector-platform'),
        ]);
    }

    /**
     * AJAX: Pay commission
     * 
     * @return void
     */
    public function ajaxPayCommission(): void {
        check_ajax_referer('mp_admin', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }
        
        $commission_id = (int)($_POST['commission_id'] ?? 0);
        
        $service = $this->getService('resellers.commission');
        $result = $service->markAsPaid($commission_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Commission marked as paid.', 'myprotector-platform'),
        ]);
    }

    /**
     * AJAX: Get commission report
     * 
     * @return void
     */
    public function ajaxGetCommissionReport(): void {
        check_ajax_referer('mp_reseller', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $reseller = $this->getService('resellers.service')->getResellerByUserId(get_current_user_id());
        
        if (!$reseller) {
            wp_send_json_error(['message' => __('Reseller account not found.', 'myprotector-platform')]);
        }
        
        $start_date = sanitize_text_field($_POST['start_date'] ?? date('Y-01-01'));
        $end_date = sanitize_text_field($_POST['end_date'] ?? date('Y-m-d'));
        
        $report = $this->getService('resellers.commission')->generateReport($reseller->reseller_id, $start_date, $end_date);
        
        wp_send_json_success($report);
    }

    /**
     * Render reseller application form
     * 
     * @param array $atts
     * @return string
     */
    public function renderResellerApplicationForm(array $atts): string {
        if (!is_user_logged_in()) {
            return '<p class="mp-reseller-login">' . __('Please log in to apply.', 'myprotector-platform') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $service = $this->getService('resellers.service');
        $existing = $service->getResellerByUserId($user_id);
        
        if ($existing) {
            return '<p class="mp-reseller-exists">' . __('You already have a reseller account.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        $this->includeTemplate('reseller-application-form.php');
        return ob_get_clean();
    }

    /**
     * Render referral link
     * 
     * @param array $atts
     * @return string
     */
    public function renderReferralLink(array $atts): string {
        $atts = shortcode_atts([
            'show_code' => 'true',
            'style' => 'link',
        ], $atts);
        
        if (!is_user_logged_in()) {
            return '';
        }
        
        $service = $this->getService('resellers.service');
        $reseller = $service->getResellerByUserId(get_current_user_id());
        
        if (!$reseller) {
            return '';
        }
        
        $referral_link = home_url('/?ref=' . $reseller->referral_code);
        
        if ($atts['style'] === 'box') {
            ob_start();
            ?>
            <div class="mp-referral-box">
                <p><?php _e('Your Referral Link:', 'myprotector-platform'); ?></p>
                <input type="text" readonly value="<?php echo esc_url($referral_link); ?>" class="mp-referral-input">
                <button class="mp-copy-btn" data-copy="<?php echo esc_url($referral_link); ?>">
                    <i class="fas fa-copy"></i> <?php _e('Copy', 'myprotector-platform'); ?>
                </button>
            </div>
            <?php
            return ob_get_clean();
        }
        
        return '<a href="' . esc_url($referral_link) . '">' . __('Your Referral Link', 'myprotector-platform') . '</a>';
    }

    /**
     * Render reseller dashboard link
     * 
     * @param array $atts
     * @return string
     */
    public function renderResellerDashboardLink(array $atts): string {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $service = $this->getService('resellers.service');
        $reseller = $service->getResellerByUserId(get_current_user_id());
        
        if (!$reseller) {
            return '';
        }
        
        $label = $atts['label'] ?? __('Reseller Dashboard', 'myprotector-platform');
        
        return '<a href="' . esc_url(home_url('/reseller-dashboard/')) . '" class="mp-reseller-dashboard-link">' . esc_html($label) . '</a>';
    }

    /**
     * Include template file
     * 
     * @param string $template
     * @param array $data
     * @return void
     */
    protected function includeTemplate(string $template, array $data = []): void {
        $path = $this->getPath('templates/' . $template);
        
        if (file_exists($path)) {
            include $path;
        }
    }

    /**
     * Get referral code from cookie/session
     * 
     * @return string|null
     */
    public static function getReferralCode(): ?string {
        return $_COOKIE['mp_referral'] ?? $_SESSION['mp_referral_code'] ?? null;
    }
}