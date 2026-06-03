<?php
/**
 * MyProtector Core - Base Module
 * 
 * Abstract base class for all plugin modules
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

abstract class Module implements ModuleInterface {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name;

    /**
     * Module path
     * 
     * @var string
     */
    protected $path;

    /**
     * Module URL
     * 
     * @var string
     */
    protected $url;

    /**
     * Module version
     * 
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = [];

    /**
     * Is module enabled
     * 
     * @var bool
     */
    protected $enabled = true;

    /**
     * Registered services
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Constructor
     * 
     * @param MyProtector $plugin
     */
    public function __construct(MyProtector $plugin) {
        $this->path = MYPROTECTOR_PATH . 'Modules/' . $this->getModuleDirectory() . '/';
        $this->url = MYPROTECTOR_URL . 'Modules/' . $this->getModuleDirectory() . '/';
    }

    /**
     * Get module directory name
     * 
     * @return string
     */
    abstract protected function getModuleDirectory(): string;

    /**
     * Get module name
     * 
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * Get module path
     * 
     * @param string $path
     * @return string
     */
    public function getPath(string $path = ''): string {
        return $this->path . ltrim($path, '/');
    }

    /**
     * Get module URL
     * 
     * @param string $path
     * @return string
     */
    public function getUrl(string $path = ''): string {
        return $this->url . ltrim($path, '/');
    }

    /**
     * Get module version
     * 
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }

    /**
     * Get module dependencies
     * 
     * @return array
     */
    public function getDependencies(): array {
        return $this->dependencies;
    }

    /**
     * Check if module is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool {
        return $this->enabled;
    }

    /**
     * Enable module
     * 
     * @return $this
     */
    public function enable(): self {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable module
     * 
     * @return $this
     */
    public function disable(): self {
        $this->enabled = false;
        return $this;
    }

    /**
     * Boot the module
     * 
     * Override in child classes to initialize module-specific functionality
     * 
     * @return void
     */
    public function boot(): void {
        // Override in child modules
    }

    /**
     * Register module hooks
     * 
     * Override in child classes to add WordPress hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Override in child modules
    }

    /**
     * Register a service
     * 
     * @param string $id
     * @param object $service
     * @return void
     */
    protected function registerService(string $id, $service): void {
        $this->services[$id] = $service;
    }

    /**
     * Get a registered service
     * 
     * @param string $id
     * @return mixed
     */
    protected function getService(string $id) {
        return $this->services[$id] ?? null;
    }

    /**
     * Add WordPress action hook
     * 
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addAction(string $hook, $callback, int $priority = 10, int $accepted_args = 2): void {
        add_action($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Add WordPress filter hook
     * 
     * @param string $hook
     * @param callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return void
     */
    protected function addFilter(string $hook, $callback, int $priority = 10, int $accepted_args = 2): void {
        add_filter($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Add WordPress shortcode
     * 
     * @param string $tag
     * @param callable $callback
     * @return void
     */
    protected function addShortcode(string $tag, $callback): void {
        add_shortcode($tag, $callback);
    }

    /**
     * Register REST API route
     * 
     * @param string $namespace
     * @param string $route
     * @param array $args
     * @return void
     */
    protected function registerApiRoute(string $namespace, string $route, array $args): void {
        add_action('rest_api_init', function () use ($namespace, $route, $args) {
            register_rest_route($namespace, $route, $args);
        });
    }

    /**
     * Enqueue module CSS
     * 
     * @param string $handle
     * @param string $file
     * @param array $deps
     * @return void
     */
    protected function enqueueStyle(string $handle, string $file, array $deps = []): void {
        wp_enqueue_style(
            'mp-' . $this->name . '-' . $handle,
            $this->getUrl('assets/css/' . $file),
            $deps,
            $this->version
        );
    }

    /**
     * Enqueue module JavaScript
     * 
     * @param string $handle
     * @param string $file
     * @param array $deps
     * @param bool $in_footer
     * @return void
     */
    protected function enqueueScript(string $handle, string $file, array $deps = [], bool $in_footer = true): void {
        wp_enqueue_script(
            'mp-' . $this->name . '-' . $handle,
            $this->getUrl('assets/js/' . $file),
            $deps,
            $this->version,
            $in_footer
        );
    }

    /**
     * Get plugin instance
     * 
     * @return MyProtector
     */
    protected function plugin(): MyProtector {
        return myprotector();
    }

    /**
     * Get service from plugin container
     * 
     * @param string $id
     * @return mixed
     */
    protected function service(string $id) {
        return $this->plugin()->get($id);
    }

    /**
     * Check if WooCommerce is active
     * 
     * @return bool
     */
    protected function isWooCommerceActive(): bool {
        return class_exists('WooCommerce');
    }

    /**
     * Check if current user has capability
     * 
     * @param string $cap
     * @return bool
     */
    protected function userCan(string $cap): bool {
        return current_user_can($cap);
    }
}