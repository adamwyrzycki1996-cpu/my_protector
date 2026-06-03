<?php
/**
 * MyProtector - Emails Module
 * 
 * @package MyProtector\Modules\Emails
 */

namespace MyProtector\Modules\Emails;

use MyProtector\Core\Module;

class Emails extends Module {
    protected $name = 'emails';

    protected function getModuleDirectory(): string {
        return 'Emails';
    }

    public function boot(): void {
        // Initialize email functionality
    }

    public function registerHooks(): void {
        // Register email hooks
    }
}