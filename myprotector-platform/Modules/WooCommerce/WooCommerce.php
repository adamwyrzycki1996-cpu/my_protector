<?php
/**
 * MyProtector - WooCommerce Module
 * 
 * @package MyProtector\Modules\WooCommerce
 */

namespace MyProtector\Modules\WooCommerce;

use MyProtector\Core\Module;

class WooCommerce extends Module {
    protected $name = 'woocommerce';

    protected function getModuleDirectory(): string {
        return 'WooCommerce';
    }

    public function boot(): void {
        // Check if WooCommerce is active
        if (!$this->isWooCommerceActive()) {
            return;
        }
        // Initialize WooCommerce integration
    }

    public function registerHooks(): void {
        if (!$this->isWooCommerceActive()) {
            return;
        }
        // Register WooCommerce hooks
    }

    /**
     * Check if WooCommerce is active
     * 
     * @return bool
     */
    protected function isWooCommerceActive(): bool {
        return class_exists('WooCommerce');
    }
}