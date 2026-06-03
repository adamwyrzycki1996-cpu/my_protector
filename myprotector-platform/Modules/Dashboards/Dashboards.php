<?php
/**
 * MyProtector - Dashboards Module
 * 
 * @package MyProtector\Modules\Dashboards
 */

namespace MyProtector\Modules\Dashboards;

use MyProtector\Core\Module;

class Dashboards extends Module {
    protected $name = 'dashboards';

    protected function getModuleDirectory(): string {
        return 'Dashboards';
    }

    public function boot(): void {
        // Initialize dashboard functionality
    }

    public function registerHooks(): void {
        // Register dashboard hooks
    }
}