<?php
/**
 * MyProtector Platform - Business Pages Module
 * 
 * Handles public-facing business profile pages with:
 * - Company information display
 * - Trust signal visualization
 * - Rating and review display
 * - Business response section
 * - SEO optimization
 * 
 * @package MyProtector\Modules\BusinessPages
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessPages;

use MyProtector\Core\Module;

class BusinessPages extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'business-pages';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles', 'reviews', 'trust-signals'];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'BusinessPages';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->initControllers();
        $this->setupRewriteRules();
        $this->registerShortcodes();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Enqueue frontend assets
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        
        // AJAX handlers
        $this->addAction('wp_ajax_mp_get_reviews', [$this, 'ajaxGetReviews']);
        $this->addAction('wp_ajax_nopriv_mp_get_reviews', [$this, 'ajaxGetReviews']);
        $this->addAction('wp_ajax_mp_submit_response', [$this, 'ajaxSubmitResponse']);
        $this->addAction('wp_ajax_mp_mark_helpful', [$this, 'ajaxMarkHelpful']);
        $this->addAction('wp_ajax_nopriv_mp_mark_helpful', [$this, 'ajaxMarkHelpful']);
        $this->addAction('wp_ajax_mp_report_review', [$this, 'ajaxReportReview']);
        $this->addAction('wp_ajax_nopriv_mp_report_review', [$this, 'ajaxReportReview']);
        
        // Schema markup
        $this->addAction('wp_head', [$this, 'outputSchemaMarkup'], 1);
        
        // Template redirect
        $this->addAction('template_redirect', [$this, 'handleTemplateRedirect']);
        
        // Content filters
        $this->addFilter('the_title', [$this, 'filterPageTitle'], 10, 2);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('business-pages.service', new Services\BusinessPagesService());
        $this->registerService('business-pages.seo', new Services\BusinessPagesSEO());
    }

    /**
     * Initialize controllers
     * 
     * @return void
     */
    protected function initControllers(): void {
        $this->controller = new Controllers\BusinessPagesController($this);
    }

    /**
     * Setup rewrite rules for business pages
     * 
     * @return void
     */
    protected function setupRewriteRules(): void {
        add_action('init', [$this, 'addRewriteRules']);
        add_filter('query_vars', [$this, 'addQueryVars']);
    }

    /**
     * Add rewrite rules
     * 
     * @return void
     */
    public function addRewriteRules(): void {
        // Main business page: /business/{slug}/
        add_rewrite_rule(
            '^business/([^/]+)/?$',
            'index.php?mp_business_slug=$matches[1]',
            'top'
        );
        
        // Reviews page: /business/{slug}/reviews/
        add_rewrite_rule(
            '^business/([^/]+)/reviews/?$',
            'index.php?mp_business_slug=$matches[1]&mp_page_type=reviews',
            'top'
        );
        
        // Write review page: /business/{slug}/write-review/
        add_rewrite_rule(
            '^business/([^/]+)/write-review/?$',
            'index.php?mp_business_slug=$matches[1]&mp_page_type=write-review',
            'top'
        );
        
        // About page: /business/{slug}/about/
        add_rewrite_rule(
            '^business/([^/]+)/about/?$',
            'index.php?mp_business_slug=$matches[1]&mp_page_type=about',
            'top'
        );
    }

    /**
     * Add query vars
     * 
     * @param array $vars
     * @return array
     */
    public function addQueryVars(array $vars): array {
        $vars[] = 'mp_business_slug';
        $vars[] = 'mp_page_type';
        $vars[] = 'mp_review_page';
        return $vars;
    }

    /**
     * Register shortcodes
     * 
     * @return void
     */
    protected function registerShortcodes(): void {
        // [mp_business_page id="123"]
        add_shortcode('mp_business_page', [$this->controller, 'renderBusinessPage']);
        
        // [mp_business_trust id="123"]
        add_shortcode('mp_business_trust', [$this->controller, 'renderTrustBadge']);
        
        // [mp_business_reviews id="123" limit="5"]
        add_shortcode('mp_business_reviews', [$this->controller, 'renderReviewsList']);
        
        // [mp_business_rating id="123"]
        add_shortcode('mp_business_rating', [$this->controller, 'renderRatingDisplay']);
        
        // [mp_business_search]
        add_shortcode('mp_business_search', [$this->controller, 'renderSearchForm']);
        
        // [mp_business_categories]
        add_shortcode('mp_business_categories', [$this->controller, 'renderCategories']);
    }

    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueueFrontendAssets(): void {
        // Only load on business pages
        if (!$this->isBusinessPage()) {
            return;
        }
        
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        // Main styles
        wp_enqueue_style(
            'mp-business-pages',
            $this->getUrl('assets/css/business-pages.css'),
            [],
            $this->version
        );
        
        // Font Awesome for icons
        wp_enqueue_style(
            'mp-font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            [],
            '6.4.0'
        );
        
        // Main script
        wp_enqueue_script(
            'mp-business-pages',
            $this->getUrl('assets/js/business-pages.js'),
            ['jquery'],
            $this->version,
            true
        );
        
        // Localize script
        wp_localize_script('mp-business-pages', 'mpBusinessPages', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_business_pages'),
            'strings' => [
                'loading' => __('Loading...', 'myprotector-platform'),
                'error' => __('An error occurred. Please try again.', 'myprotector-platform'),
                'markHelpful' => __('Mark as Helpful', 'myprotector-platform'),
                'reportReview' => __('Report Review', 'myprotector-platform'),
            ],
        ]);
    }

    /**
     * Handle template redirect
     * 
     * @return void
     */
    public function handleTemplateRedirect(): void {
        $slug = get_query_var('mp_business_slug');
        
        if (empty($slug)) {
            return;
        }
        
        // Load business page template
        $this->loadBusinessPageTemplate($slug);
    }

    /**
     * Load business page template
     * 
     * @param string $slug
     * @return void
     */
    protected function loadBusinessPageTemplate(string $slug): void {
        $service = $this->getService('business-pages.service');
        $business = $service->getBusinessBySlug($slug);
        
        if (!$business) {
            $this->render404();
            exit;
        }
        
        // Store business data for template
        set_query_var('mp_current_business', $business);
        
        $page_type = get_query_var('mp_page_type', 'main');
        
        // Load appropriate template
        $template_path = $this->getTemplatePath($page_type);
        
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        }
        
        // Fallback to default template
        include $this->getPath('templates/business-page.php');
        exit;
    }

    /**
     * Get template path
     * 
     * @param string $page_type
     * @return string
     */
    protected function getTemplatePath(string $page_type): string {
        $templates_dir = $this->getPath('templates/');
        
        $templates = [
            'reviews' => $templates_dir . 'business-reviews.php',
            'write-review' => $templates_dir . 'business-write-review.php',
            'about' => $templates_dir . 'business-about.php',
        ];
        
        return $templates[$page_type] ?? $templates_dir . 'business-page.php';
    }

    /**
     * Render 404 page
     * 
     * @return void
     */
    protected function render404(): void {
        status_header(404);
        nocache_headers();
        
        include get_template_directory() . '/404.php';
        exit;
    }

    /**
     * Check if on business page
     * 
     * @return bool
     */
    protected function isBusinessPage(): bool {
        global $wp_query;
        
        return isset($wp_query->query_vars['mp_business_slug']);
    }

    /**
     * Output schema markup
     * 
     * @return void
     */
    public function outputSchemaMarkup(): void {
        if (!$this->isBusinessPage()) {
            return;
        }
        
        $business = get_query_var('mp_current_business');
        
        if (!$business) {
            return;
        }
        
        $seo = $this->getService('business-pages.seo');
        echo $seo->generateBusinessSchema($business);
        echo $seo->generateReviewSchema($business);
    }

    /**
     * Filter page title
     * 
     * @param string $title
     * @param int $id
     * @return string
     */
    public function filterPageTitle(string $title, $id): string {
        $business = get_query_var('mp_current_business');
        
        if (!$business) {
            return $title;
        }
        
        $page_type = get_query_var('mp_page_type', 'main');
        
        switch ($page_type) {
            case 'reviews':
                return sprintf('%s - Reviews', $business->business_name);
            case 'write-review':
                return sprintf('Write a Review for %s', $business->business_name);
            case 'about':
                return sprintf('About %s', $business->business_name);
            default:
                return sprintf('%s - Reviews & Ratings', $business->business_name);
        }
    }

    /**
     * AJAX: Get reviews
     * 
     * @return void
     */
    public function ajaxGetReviews(): void {
        check_ajax_referer('mp_business_pages', 'nonce');
        
        $business_id = (int)($_POST['business_id'] ?? 0);
        $page = (int)($_POST['page'] ?? 1);
        $per_page = (int)($_POST['per_page'] ?? 10);
        $sort = sanitize_text_field($_POST['sort'] ?? 'recent');
        $rating = (int)($_POST['rating'] ?? 0);
        
        $service = $this->getService('business-pages.service');
        $reviews = $service->getBusinessReviews($business_id, [
            'page' => $page,
            'per_page' => $per_page,
            'sort' => $sort,
            'rating' => $rating,
        ]);
        
        wp_send_json_success([
            'reviews' => $reviews['items'],
            'total' => $reviews['total'],
            'pages' => $reviews['pages'],
        ]);
    }

    /**
     * AJAX: Submit response
     * 
     * @return void
     */
    public function ajaxSubmitResponse(): void {
        check_ajax_referer('mp_business_pages', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Please log in to respond.', 'myprotector-platform')]);
        }
        
        $review_id = (int)($_POST['review_id'] ?? 0);
        $content = sanitize_textarea_field($_POST['content'] ?? '');
        
        if (empty($content)) {
            wp_send_json_error(['message' => __('Response content is required.', 'myprotector-platform')]);
        }
        
        $service = $this->getService('business-pages.service');
        $result = $service->submitBusinessResponse($review_id, $content);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Response submitted successfully!', 'myprotector-platform'),
            'response' => $result,
        ]);
    }

    /**
     * AJAX: Mark review as helpful
     * 
     * @return void
     */
    public function ajaxMarkHelpful(): void {
        check_ajax_referer('mp_business_pages', 'nonce');
        
        $review_id = (int)($_POST['review_id'] ?? 0);
        
        $service = $this->getService('business-pages.service');
        $count = $service->markReviewHelpful($review_id);
        
        wp_send_json_success([
            'message' => __('Marked as helpful!', 'myprotector-platform'),
            'count' => $count,
        ]);
    }

    /**
     * AJAX: Report review
     * 
     * @return void
     */
    public function ajaxReportReview(): void {
        check_ajax_referer('mp_business_pages', 'nonce');
        
        $review_id = (int)($_POST['review_id'] ?? 0);
        $reason = sanitize_text_field($_POST['reason'] ?? '');
        
        $service = $this->getService('business-pages.service');
        $result = $service->reportReview($review_id, $reason);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Report submitted. Thank you!', 'myprotector-platform'),
        ]);
    }

    /**
     * Get URL for business
     * 
     * @param string $slug
     * @param string $page_type
     * @return string
     */
    public static function getBusinessUrl(string $slug, string $page_type = 'main'): string {
        $base = home_url('/business/' . $slug . '/');
        
        if ($page_type !== 'main') {
            return rtrim($base, '/') . '/' . $page_type . '/';
        }
        
        return $base;
    }

    /**
     * Get reviews URL for business
     * 
     * @param string $slug
     * @return string
     */
    public static function getReviewsUrl(string $slug): string {
        return self::getBusinessUrl($slug, 'reviews');
    }

    /**
     * Get write review URL for business
     * 
     * @param string $slug
     * @return string
     */
    public static function getWriteReviewUrl(string $slug): string {
        return self::getBusinessUrl($slug, 'write-review');
    }
}