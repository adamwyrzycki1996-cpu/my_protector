<?php
/**
 * MyProtector - Resellers Module
 * 
 * @package MyProtector\Modules\Resellers
 */

namespace MyProtector\Modules\Resellers;

use MyProtector\Core\Module;

class Resellers extends Module {
    protected $name = 'resellers';

    protected function getModuleDirectory(): string {
        return 'Resellers';
    }

    public function boot(): void {
        // Initialize reseller functionality
    }

    public function registerHooks(): void {
        // Register reseller hooks
    }
}