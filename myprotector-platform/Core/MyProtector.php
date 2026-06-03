<?php
/**
 * MyProtector Core - Main Plugin Class
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

class MyProtector {
    /**
     * Plugin instance
     * 
     * @var MyProtector
     */
    protected static $instance = null;

    /**
     * Bootstrap instance
     * 
     * @var Bootstrap|null
     */
    protected $bootstrap = null;

    /**
     * Service container (simple array-based)
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Singleton instances
     * 
     * @var array
     */
    protected $singletons = [];

    /**
     * Registered modules
     * 
     * @var array
     */
    protected $modules = [];

    /**
     * Get plugin instance (Singleton)
     * 
     * @return MyProtector
     */
    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    protected function __construct() {
        // Initialize Bootstrap if class exists
        if (class_exists('MyProtector\\Core\\Bootstrap')) {
            $this->bootstrap = new Bootstrap($this);
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception('Cannot unserialize singleton');
    }

    /**
     * Run the plugin
     * 
     * @return void
     */
    public function run(): void {
        // Load plugin textdomain
        $this->loadTextdomain();
        
        // Register modules
        $this->registerModules();
        
        // Boot all modules
        if ($this->bootstrap) {
            $this->bootstrap->bootModules();
            $this->bootstrap->registerHooks();
            $this->bootstrap->initRestApi();
            $this->bootstrap->registerShortcodes();
        }
    }

    /**
     * Load plugin textdomain
     * 
     * @return void
     */
    protected function loadTextdomain(): void {
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain(
                'myprotector-platform',
                false,
                dirname(MYPROTECTOR_BASENAME) . '/languages'
            );
        }
    }

    /**
     * Register all modules
     * 
     * @return void
     */
    protected function registerModules(): void {
        $moduleClasses = [
            'MyProtector\\Modules\\Reviews\\Reviews',
            'MyProtector\\Modules\\BusinessProfiles\\BusinessProfiles',
            'MyProtector\\Modules\\TrafficSignals\\TrafficSignals',
            'MyProtector\\Modules\\Dashboards\\Dashboards',
            'MyProtector\\Modules\\Resellers\\Resellers',
            'MyProtector\\Modules\\Widgets\\Widgets',
            'MyProtector\\Modules\\Emails\\Emails',
            'MyProtector\\Modules\\WooCommerce\\WooCommerce',
            'MyProtector\\Modules\\Admin\\Admin',
            'MyProtector\\Modules\\FrontendUI\\FrontendUI',
            'MyProtector\\Modules\\Trustignals\\TrustSignals',
        ];

        foreach ($moduleClasses as $moduleClass) {
            if (class_exists($moduleClass) && method_exists($moduleClass, 'getName')) {
                try {
                    $module = new $moduleClass($this);
                    $moduleName = $module->getName();
                    $this->modules[$moduleName] = $module;
                } catch (\Throwable $e) {
                    // Skip modules that fail to load
                    error_log('MyProtector: Failed to load module ' . $moduleClass . ': ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get module by name
     * 
     * @param string $name
     * @return ModuleInterface|null
     */
    public function getModule(string $name): ?ModuleInterface {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all registered modules
     * 
     * @return array
     */
    public function getModules(): array {
        return $this->modules;
    }

    /**
     * Get service from container
     * 
     * @param string $id
     * @return mixed
     */
    public function get(string $id) {
        return $this->services[$id] ?? null;
    }

    /**
     * Check if service exists in container
     * 
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool {
        return isset($this->services[$id]);
    }

    /**
     * Register a service in the container
     * 
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function register(string $id, callable $factory): void {
        $this->services[$id] = $factory($this);
    }

    /**
     * Register a singleton service
     * 
     * @param string $id
     * @param callable $factory
     * @return void
     */
    public function singleton(string $id, callable $factory): void {
        if (!isset($this->singletons[$id])) {
            $this->singletons[$id] = $factory($this);
        }
        $this->services[$id] = $this->singletons[$id];
    }

    /**
     * Get plugin path
     * 
     * @param string $path
     * @return string
     */
    public function getPath(string $path = ''): string {
        return MYPROTECTOR_PATH . ltrim($path, '/');
    }

    /**
     * Get plugin URL
     * 
     * @param string $path
     * @return string
     */
    public function getUrl(string $path = ''): string {
        return MYPROTECTOR_URL . ltrim($path, '/');
    }

    /**
     * Get plugin version
     * 
     * @return string
     */
    public function getVersion(): string {
        return MYPROTECTOR_VERSION;
    }

    /**
     * Get plugin basename
     * 
     * @return string
     */
    public function getBasename(): string {
        return MYPROTECTOR_BASENAME;
    }

    /**
     * Get bootstrap instance
     * 
     * @return Bootstrap|null
     */
    public function getBootstrap(): ?Bootstrap {
        return $this->bootstrap;
    }

    /**
     * Get container (returns services array for compatibility)
     * 
     * @return array
     */
    public function getContainer(): array {
        return $this->services;
    }
}