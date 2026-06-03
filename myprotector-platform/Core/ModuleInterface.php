<?php
/**
 * MyProtector Core - Module Interface
 * 
 * Defines the contract for all plugin modules
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

interface ModuleInterface {
    /**
     * Get module name
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * Get module path
     * 
     * @return string
     */
    public function getPath(): string;

    /**
     * Get module URL
     * 
     * @return string
     */
    public function getUrl(): string;

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void;

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void;

    /**
     * Get module dependencies
     * 
     * @return array
     */
    public function getDependencies(): array;

    /**
     * Check if module is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get module version
     * 
     * @return string
     */
    public function getVersion(): string;
}