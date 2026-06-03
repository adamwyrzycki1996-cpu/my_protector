<?php
/**
 * MyProtector Core - Bootstrap
 * 
 * Initializes plugin components and registers hooks
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

class Bootstrap {
    /**
     * Plugin instance
     * 
     * @var MyProtector
     */
    protected $plugin;

    /**
     * Constructor
     * 
     * @param MyProtector $plugin
     */
    public function __construct(MyProtector $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Boot all registered modules
     * 
     * @return void
     */
    public function bootModules(): void {
        $modules = $this->plugin->getModules();
        
        foreach ($modules as $module) {
            if (method_exists($module, 'boot')) {
                try {
                    $module->boot();
                } catch (\Throwable $e) {
                    error_log('MyProtector Bootstrap: Error booting module - ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Register all plugin hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Register module hooks
        $modules = $this->plugin->getModules();
        foreach ($modules as $module) {
            if (method_exists($module, 'registerHooks')) {
                try {
                    $module->registerHooks();
                } catch (\Throwable $e) {
                    error_log('MyProtector Bootstrap: Error registering hooks - ' . $e->getMessage());
                }
            }
        }
        
        // Register core hooks
        $this->registerCoreHooks();
    }

    /**
     * Register core WordPress hooks
     * 
     * @return void
     */
    protected function registerCoreHooks(): void {
        // Initialize hook
        add_action('init', [$this, 'onInit'], 1);
        
        // Widgets init
        add_action('widgets_init', [$this, 'onWidgetsInit'], 10);
        
        // Cron schedules
        add_filter('cron_schedules', [$this, 'addCronSchedules'], 10, 1);
        
        // Plugin row meta
        add_filter('plugin_row_meta', [$this, 'addPluginRowMeta'], 10, 2);
        
        // Plugin action links
        add_filter('plugin_action_links_' . MYPROTECTOR_BASENAME, [$this, 'addPluginActionLinks'], 10, 1);
    }

    /**
     * WordPress init hook
     * 
     * @return void
     */
    public function onInit(): void {
        // Check WordPress version
        $this->checkWordPressVersion();
        
        // Check PHP version
        $this->checkPhpVersion();
        
        // Register custom post types
        $this->registerPostTypes();
        
        // Register custom taxonomies
        $this->registerTaxonomies();
        
        // Add rewrite rules
        $this->addRewriteRules();
    }

    /**
     * Widgets init hook
     * 
     * @return void
     */
    public function onWidgetsInit(): void {
        // Register widgets if widget classes exist
        if (class_exists('MyProtector\\Modules\\Widgets\\Handlers\\ClassicBadgeWidget')) {
            register_widget('MyProtector\\Modules\\Widgets\\Handlers\\ClassicBadgeWidget');
        }
        if (class_exists('MyProtector\\Modules\\Widgets\\Handlers\\MiniBadgeWidget')) {
            register_widget('MyProtector\\Modules\\Widgets\\Handlers\\MiniBadgeWidget');
        }
        if (class_exists('MyProtector\\Modules\\Widgets\\Handlers\\ReviewsSliderWidget')) {
            register_widget('MyProtector\\Modules\\Widgets\\Handlers\\ReviewsSliderWidget');
        }
    }

    /**
     * Initialize REST API
     * 
     * @return void
     */
    public function initRestApi(): void {
        add_action('rest_api_init', [$this, 'registerApiRoutes'], 20);
    }

    /**
     * Register REST API routes
     * 
     * @return void
     */
    public function registerApiRoutes(): void {
        // Reviews API
        register_rest_route(MYPROTECTOR_API_NAMESPACE, '/reviews', [
            'methods'  => 'GET',
            'callback' => [$this, 'getReviews'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(MYPROTECTOR_API_NAMESPACE, '/reviews', [
            'methods'  => 'POST',
            'callback' => [$this, 'createReview'],
            'permission_callback' => function() {
                return is_user_logged_in();
            },
        ]);

        // Companies API
        register_rest_route(MYPROTECTOR_API_NAMESPACE, '/companies', [
            'methods'  => 'GET',
            'callback' => [$this, 'getCompanies'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(MYPROTECTOR_API_NAMESPACE, '/companies/(?P<id>\d+)', [
            'methods'  => 'GET',
            'callback' => [$this, 'getCompany'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Register shortcodes
     * 
     * @return void
     */
    public function registerShortcodes(): void {
        add_shortcode('mp_reviews', [$this, 'renderReviewsShortcode']);
        add_shortcode('mp_company_search', [$this, 'renderSearchShortcode']);
        add_shortcode('mp_trust_badge', [$this, 'renderTrustBadgeShortcode']);
        add_shortcode('mp_traffic_light', [$this, 'renderTrafficLightShortcode']);
    }

    /**
     * Render reviews shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderReviewsShortcode($atts): string {
        $atts = shortcode_atts([
            'company_id' => null,
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ], $atts, 'mp_reviews');

        ob_start();
        echo '<div class="mp-reviews-shortcode">';
        echo '<p>' . esc_html__('Reviews will be displayed here.', 'myprotector-platform') . '</p>';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render company search shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderSearchShortcode($atts): string {
        ob_start();
        echo '<div class="mp-company-search">';
        echo '<input type="search" placeholder="' . esc_attr__('Search companies...', 'myprotector-platform') . '">';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render trust badge shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustBadgeShortcode($atts): string {
        $atts = shortcode_atts([
            'company_id' => null,
            'style' => 'default',
        ], $atts, 'mp_trust_badge');

        ob_start();
        echo '<div class="mp-trust-badge">';
        echo '<span class="mp-badge-placeholder">' . esc_html__('Trust Badge', 'myprotector-platform') . '</span>';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render traffic light shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrafficLightShortcode($atts): string {
        $atts = shortcode_atts([
            'company_id' => null,
            'size' => 'medium',
        ], $atts, 'mp_traffic_light');

        ob_start();
        echo '<div class="mp-traffic-light">';
        echo '<div class="mp-traffic-icon mp-traffic-unknown">?</div>';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Add custom cron schedules
     * 
     * @param array $schedules
     * @return array
     */
    public function addCronSchedules(array $schedules): array {
        $schedules['every_five_minutes'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display' => __('Every 5 Minutes', 'myprotector-platform'),
        ];

        $schedules['twice_daily'] = [
            'interval' => 12 * HOUR_IN_SECONDS,
            'display' => __('Twice Daily', 'myprotector-platform'),
        ];

        return $schedules;
    }

    /**
     * Register custom post types
     * 
     * @return void
     */
    protected function registerPostTypes(): void {
        // Reviews CPT
        register_post_type('mp_review', [
            'labels' => [
                'name' => __('Reviews', 'myprotector-platform'),
                'singular_name' => __('Review', 'myprotector-platform'),
            ],
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => get_option('mp_review_slug_base', 'reviews')],
            'supports' => ['title', 'editor', 'author', 'custom-fields'],
        ]);

        // Companies CPT
        register_post_type('mp_company', [
            'labels' => [
                'name' => __('Companies', 'myprotector-platform'),
                'singular_name' => __('Company', 'myprotector-platform'),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => get_option('mp_company_slug_base', 'companies')],
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        ]);
    }

    /**
     * Register custom taxonomies
     * 
     * @return void
     */
    protected function registerTaxonomies(): void {
        // Company Categories
        register_taxonomy('mp_company_category', 'mp_company', [
            'labels' => [
                'name' => __('Company Categories', 'myprotector-platform'),
                'singular_name' => __('Category', 'myprotector-platform'),
            ],
            'hierarchical' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'company-category'],
        ]);

        // Review Tags
        register_taxonomy('mp_review_tag', 'mp_review', [
            'labels' => [
                'name' => __('Review Tags', 'myprotector-platform'),
                'singular_name' => __('Tag', 'myprotector-platform'),
            ],
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'review-tag'],
        ]);
    }

    /**
     * Add rewrite rules
     * 
     * @return void
     */
    protected function addRewriteRules(): void {
        add_rewrite_rule(
            'company/([^/]+)/?$',
            'index.php?post_type=mp_company&name=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            'review/([^/]+)/?$',
            'index.php?post_type=mp_review&name=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            'dashboard/?$',
            'index.php?mp_dashboard=1',
            'top'
        );
    }

    /**
     * Add plugin row meta
     * 
     * @param array $links
     * @param string $file
     * @return array
     */
    public function addPluginRowMeta(array $links, string $file): array {
        if ($file === MYPROTECTOR_BASENAME) {
            $links[] = '<a href="https://docs.myprotector.example.com" target="_blank">' . 
                       __('Documentation', 'myprotector-platform') . '</a>';
            $links[] = '<a href="https://support.myprotector.example.com" target="_blank">' . 
                       __('Support', 'myprotector-platform') . '</a>';
        }
        return $links;
    }

    /**
     * Add plugin action links
     * 
     * @param array $links
     * @return array
     */
    public function addPluginActionLinks(array $links): array {
        $new_links = [
            '<a href="' . admin_url('admin.php?page=myprotector-settings') . '">' . 
                         __('Settings', 'myprotector-platform') . '</a>',
        ];
        return array_merge($new_links, $links);
    }

    /**
     * Check WordPress version
     * 
     * @return void
     */
    protected function checkWordPressVersion(): void {
        global $wp_version;
        
        if (version_compare($wp_version, MYPROTECTOR_WP_MIN_VERSION, '<')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>' .
                     sprintf(
                         __('MyProtector Platform requires WordPress %s or higher.', 'myprotector-platform'),
                         MYPROTECTOR_WP_MIN_VERSION
                     ) . '</p></div>';
            });
        }
    }

    /**
     * Check PHP version
     * 
     * @return void
     */
    protected function checkPhpVersion(): void {
        if (version_compare(PHP_VERSION, MYPROTECTOR_PHP_MIN_VERSION, '<')) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>' .
                     sprintf(
                         __('MyProtector Platform requires PHP %s or higher.', 'myprotector-platform'),
                         MYPROTECTOR_PHP_MIN_VERSION
                     ) . '</p></div>';
            });
        }
    }

    /**
     * API: Get reviews
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getReviews(\WP_REST_Request $request): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'data' => [],
            'message' => 'Reviews API working',
        ], 200);
    }

    /**
     * API: Create review
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createReview(\WP_REST_Request $request): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'message' => 'Review created',
        ], 201);
    }

    /**
     * API: Get companies
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getCompanies(\WP_REST_Request $request): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'data' => [],
            'message' => 'Companies API working',
        ], 200);
    }

    /**
     * API: Get company
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getCompany(\WP_REST_Request $request): \WP_REST_Response {
        return new \WP_REST_Response([
            'success' => true,
            'data' => null,
            'message' => 'Company not found',
        ], 404);
    }
}