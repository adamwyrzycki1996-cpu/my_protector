<?php
/**
 * MyProtector Platform - WooCommerce Module
 * 
 * Integration with WooCommerce for Stage 1 ($50/month subscription):
 * - Business subscription required
 * - Subscription controls trust signal eligibility
 * - Account upgrade page
 * - Billing page
 * 
 * @package MyProtector\Modules\WooCommerce
 * @version 1.0.0
 */

namespace MyProtector\Modules\WooCommerce;

use MyProtector\Core\Module;

class WooCommerce extends Module {
    protected $name = 'woocommerce';
    protected $dependencies = ['business-profiles', 'trust-signals', 'emails'];
    const SUBSCRIPTION_PRICE = 50.00;

    protected function getModuleDirectory(): string {
        return 'WooCommerce';
    }

    public function boot(): void {
        if (!$this->isWooCommerceActive()) {
            return;
        }
        
        $this->registerServices();
        $this->registerHooks();
        $this->createSubscriptionProduct();
    }

    public function registerHooks(): void {
        $this->addAction('woocommerce_order_status_completed', [$this, 'onOrderCompleted'], 10, 2);
        $this->addAction('woocommerce_order_complete', [$this, 'sendReviewInvitationEmail'], 10, 2);
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->addAction('wp_ajax_mp_check_subscription', [$this, 'ajaxCheckSubscription']);
        $this->addAction('wp_ajax_mp_upgrade_subscription', [$this, 'ajaxUpgradeSubscription']);
        add_shortcode('mp_account_upgrade', [$this, 'renderAccountUpgradePage']);
        add_shortcode('mp_billing', [$this, 'renderBillingPage']);
    }

    protected function registerServices(): void {
        $this->registerService('woocommerce.subscription', new Services\SubscriptionService());
        $this->registerService('woocommerce.review_trigger', new Services\ReviewTriggerService());
    }

    protected function createSubscriptionProduct(): void {
        $product_id = get_option('mp_woocommerce_subscription_product_id', 0);
        
        if (!$product_id || !get_post($product_id)) {
            $product_id = $this->createProduct();
            update_option('mp_woocommerce_subscription_product_id', $product_id);
        }
    }

    protected function createProduct(): int {
        $product = new \WC_Product_Subscription();
        $product->set_name('MyProtector Business Subscription');
        $product->set_status('publish');
        $product->set_regular_price(self::SUBSCRIPTION_PRICE);
        $product->set_sku('mp-subscription-monthly');
        $product->save();
        return $product->get_id();
    }

    public function onOrderCompleted(int $order_id, \WC_Order $order): void {
        $this->getService('woocommerce.review_trigger')->scheduleReviewInvitation($order);
    }

    public function sendReviewInvitationEmail(int $order_id, \WC_Order $order): void {
        $user_id = $order->get_user_id();
        $business = $this->getUserBusiness($user_id);
        
        if (!$business) {
            return;
        }
        
        $email = $order->get_billing_email();
        
        if ($email) {
            $queue = new \MyProtector\Modules\Emails\Services\EmailQueue();
            $queue->scheduleReviewInvitation($email, $business->business_id, $order_id, 7);
        }
    }

    protected function getUserBusiness(int $user_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE user_id = %d LIMIT 1",
                $user_id
            )
        );
    }

    public function addAdminMenu(): void {
        add_submenu_page(
            'myprotector',
            __('WooCommerce Settings', 'myprotector-platform'),
            __('WooCommerce', 'myprotector-platform'),
            'manage_myprotector',
            'mp-woocommerce',
            [$this, 'renderSettingsPage']
        );
    }

    public function renderSettingsPage(): void {
        $product_id = get_option('mp_woocommerce_subscription_product_id', 0);
        $product = $product_id ? wc_get_product($product_id) : null;
        $is_active = $this->isWooCommerceActive();
        
        include $this->getPath('templates/admin/woocommerce-settings.php');
    }

    public function ajaxCheckSubscription(): void {
        check_ajax_referer('mp_woocommerce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('woocommerce.subscription');
        $status = $service->getSubscriptionStatus(get_current_user_id());
        
        wp_send_json_success($status);
    }

    public function ajaxUpgradeSubscription(): void {
        check_ajax_referer('mp_woocommerce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in.', 'myprotector-platform')]);
        }
        
        $product_id = get_option('mp_woocommerce_subscription_product_id', 0);
        
        if (!$product_id) {
            wp_send_json_error(['message' => __('Subscription product not found.', 'myprotector-platform')]);
        }
        
        WC()->cart->add_to_cart($product_id);
        
        wp_send_json_success([
            'redirect_url' => wc_get_checkout_url(),
        ]);
    }

    public function renderAccountUpgradePage(array $atts): string {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to upgrade your account.', 'myprotector-platform') . '</p>';
        }
        
        $service = $this->getService('woocommerce.subscription');
        $status = $service->getSubscriptionStatus(get_current_user_id());
        
        if ($status['is_active']) {
            return '<p>' . __('You already have an active subscription.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        include $this->getPath('templates/account-upgrade.php');
        return ob_get_clean();
    }

    public function renderBillingPage(array $atts): string {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your billing.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        include $this->getPath('templates/billing.php');
        return ob_get_clean();
    }

    public function isWooCommerceActive(): bool {
        return class_exists('WooCommerce') && defined('WC_VERSION');
    }

    public static function getCheckoutUrl(): string {
        $product_id = get_option('mp_woocommerce_subscription_product_id', 0);
        
        if ($product_id) {
            return add_query_arg('add-to-cart', $product_id, wc_get_checkout_url());
        }
        
        return wc_get_checkout_url();
    }
}