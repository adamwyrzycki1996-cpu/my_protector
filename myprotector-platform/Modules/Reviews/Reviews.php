<?php
/**
 * MyProtector Platform - Reviews Module
 * 
 * Handles all review-related functionality
 * 
 * @package MyProtector\Modules\Reviews
 * @version 1.0.0
 */

namespace MyProtector\Modules\Reviews;

use MyProtector\Core\Module;

class Reviews extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'reviews';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles'];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'Reviews';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        // Register review services
        $this->registerServices();
        
        // Initialize review handlers
        $this->initHandlers();
        
        // Setup AJAX endpoints
        $this->setupAjaxEndpoints();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Review submission
        $this->addAction('wp_ajax_submit_review', [$this, 'handleReviewSubmission']);
        $this->addAction('wp_ajax_nopriv_submit_review', [$this, 'handleReviewSubmission']);
        
        // Helpful marking
        $this->addAction('wp_ajax_mark_helpful', [$this, 'handleMarkHelpful']);
        $this->addAction('wp_ajax_nopriv_mark_helpful', [$this, 'handleMarkHelpful']);
        
        // Report review
        $this->addAction('wp_ajax_report_review', [$this, 'handleReportReview']);
        $this->addAction('wp_ajax_nopriv_report_review', [$this, 'handleReportReview']);
        
        // Content filter
        $this->addFilter('the_content', [$this, 'filterReviewContent'], 20);
        
        // Post type hooks
        $this->addAction('init', [$this, 'registerPostType']);
        
        // Status transition
        $this->addAction('transition_post_status', [$this, 'handleStatusChange'], 10, 3);
        
        // REST API
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/reviews', [
            'methods' => 'GET',
            'callback' => [$this, 'getReviewsApi'],
            'permission_callback' => '__return_true',
        ]);
        
        $this->registerApiRoute(MYPROTECTOR_API_NAMESPACE, '/reviews', [
            'methods' => 'POST',
            'callback' => [$this, 'createReviewApi'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        // Review service
        $this->registerService('reviews.service', new Services\ReviewService(
            $this->plugin()->getContainer()
        ));
        
        // Moderation service
        $this->registerService('reviews.moderation', new Services\ReviewModerationService(
            $this->plugin()->getContainer()
        ));
        
        // Analytics service
        $this->registerService('reviews.analytics', new Services\ReviewAnalyticsService(
            $this->plugin()->getContainer()
        ));
    }

    /**
     * Initialize handlers
     * 
     * @return void
     */
    protected function initHandlers(): void {
        // Admin handlers
        if (is_admin()) {
            $this->adminController = new Admin\ReviewsAdminController($this);
        }
        
        // Public handlers
        $this->publicController = new Public\ReviewsPublicController($this);
    }

    /**
     * Setup AJAX endpoints
     * 
     * @return void
     */
    protected function setupAjaxEndpoints(): void {
        // Additional AJAX endpoints can be registered here
    }

    /**
     * Handle review submission
     * 
     * @return void
     */
    public function handleReviewSubmission(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mp_submit_review')) {
            wp_send_json_error(['message' => __('Security check failed.', 'myprotector-platform')]);
        }

        // Get service
        $service = $this->getService('reviews.service');
        
        // Validate and create review
        $result = $service->create($_POST);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Review submitted successfully!', 'myprotector-platform'),
            'review_id' => $result,
        ]);
    }

    /**
     * Handle mark helpful
     * 
     * @return void
     */
    public function handleMarkHelpful(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mp_mark_helpful')) {
            wp_send_json_error(['message' => __('Security check failed.', 'myprotector-platform')]);
        }

        $service = $this->getService('reviews.service');
        $result = $service->markHelpful($_POST['review_id'] ?? 0);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Marked as helpful!', 'myprotector-platform'),
            'count' => $result,
        ]);
    }

    /**
     * Handle report review
     * 
     * @return void
     */
    public function handleReportReview(): void {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'mp_report_review')) {
            wp_send_json_error(['message' => __('Security check failed.', 'myprotector-platform')]);
        }

        $service = $this->getService('reviews.service');
        $result = $service->reportReview($_POST['review_id'] ?? 0, $_POST['reason'] ?? '');
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success([
            'message' => __('Report submitted. Thank you!', 'myprotector-platform'),
        ]);
    }

    /**
     * Filter review content
     * 
     * @param string $content
     * @return string
     */
    public function filterReviewContent(string $content): string {
        if (!is_singular('mp_review')) {
            return $content;
        }

        // Add review metadata
        $reviewId = get_queried_object_id();
        $rating = get_post_meta($reviewId, '_mp_rating', true);
        
        if ($rating) {
            $stars = str_repeat('⭐', (int) $rating);
            $content = '<div class="mp-review-rating">' . $stars . '</div>' . $content;
        }

        return $content;
    }

    /**
     * Register post type
     * 
     * @return void
     */
    public function registerPostType(): void {
        register_post_type('mp_review', [
            'labels' => [
                'name' => __('Reviews', 'myprotector-platform'),
                'singular_name' => __('Review', 'myprotector-platform'),
                'add_new' => __('Write Review', 'myprotector-platform'),
                'add_new_item' => __('Write New Review', 'myprotector-platform'),
                'edit_item' => __('Edit Review', 'myprotector-platform'),
                'new_item' => __('New Review', 'myprotector-platform'),
                'view_item' => __('View Review', 'myprotector-platform'),
                'search_items' => __('Search Reviews', 'myprotector-platform'),
                'not_found' => __('No reviews found', 'myprotector-platform'),
            ],
            'public' => true,
            'has_archive' => false,
            'show_in_rest' => true,
            'rewrite' => ['slug' => get_option('mp_review_slug_base', 'reviews')],
            'supports' => ['title', 'editor', 'author', 'custom-fields', 'comments'],
            'menu_icon' => 'dashicons-star-filled',
        ]);
    }

    /**
     * Handle post status change
     * 
     * @param string $new_status
     * @param string $old_status
     * @param \WP_Post $post
     * @return void
     */
    public function handleStatusChange(string $new_status, string $old_status, \WP_Post $post): void {
        if ($post->post_type !== 'mp_review') {
            return;
        }

        // Notify on approval
        if ($new_status === 'publish' && $old_status !== 'publish') {
            do_action('mp_review_approved', $post->ID);
        }
        
        // Notify on rejection
        if ($new_status === 'trash' && $old_status !== 'trash') {
            do_action('mp_review_rejected', $post->ID);
        }
    }

    /**
     * REST API - Get reviews
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getReviewsApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('reviews.service');
        
        $args = [
            'company_id' => $request->get_param('company_id'),
            'status' => 'approved',
            'limit' => $request->get_param('per_page') ?? 10,
            'page' => $request->get_param('page') ?? 1,
        ];
        
        $reviews = $service->getReviews($args);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $reviews,
        ], 200);
    }

    /**
     * REST API - Create review
     * 
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createReviewApi(\WP_REST_Request $request): \WP_REST_Response {
        $service = $this->getService('reviews.service');
        
        $data = [
            'company_id' => $request->get_param('company_id'),
            'user_id' => get_current_user_id(),
            'rating' => $request->get_param('rating'),
            'title' => $request->get_param('title'),
            'content' => $request->get_param('content'),
        ];
        
        $result = $service->create($data);
        
        if (is_wp_error($result)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => $result->get_error_message(),
            ], 400);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Review submitted successfully!', 'myprotector-platform'),
            'review_id' => $result,
        ], 201);
    }
}