<?php
/**
 * MyProtector - Admin Module
 * 
 * @package MyProtector\Modules\Admin
 */

namespace MyProtector\Modules\Admin;

use MyProtector\Core\Module;

class Admin extends Module {
    protected $name = 'admin';

    protected function getModuleDirectory(): string {
        return 'Admin';
    }

    public function boot(): void {
        // Initialize admin functionality
    }

    public function registerHooks(): void {
        // Register admin hooks
    }
}