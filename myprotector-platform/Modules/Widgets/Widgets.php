<?php
/**
 * MyProtector - Widgets Module
 * 
 * @package MyProtector\Modules\Widgets
 */

namespace MyProtector\Modules\Widgets;

use MyProtector\Core\Module;

class Widgets extends Module {
    protected $name = 'widgets';

    protected function getModuleDirectory(): string {
        return 'Widgets';
    }

    public function boot(): void {
        // Initialize widget functionality
    }

    public function registerHooks(): void {
        // Register widget hooks
    }
}