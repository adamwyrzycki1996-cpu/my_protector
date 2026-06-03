<?php
/**
 * MyProtector Platform - Widgets Module
 * 
 * Trustpilot-style widgets:
 * - Rating Badge [mp_rating]
 * - Review Carousel [mp_reviews]
 * - Traffic Signal Badge [mp_trust]
 * 
 * @package MyProtector\Modules\Widgets
 * @version 1.0.0
 */

namespace MyProtector\Modules\Widgets;

use MyProtector\Core\Module;

class Widgets extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'widgets';

    /**
     * Module dependencies
     * 
     * @var array
     */
    protected $dependencies = ['business-profiles', 'reviews'];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'Widgets';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->registerShortcodes();
        $this->registerWidgets();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        $this->addAction('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('widgets.service', new Services\WidgetService());
    }

    /**
     * Register shortcodes
     * 
     * @return void
     */
    protected function registerShortcodes(): void {
        // Rating Badge: [mp_rating id="123"]
        add_shortcode('mp_rating', [$this, 'renderRatingBadge']);
        
        // Review Carousel: [mp_reviews id="123" limit="5"]
        add_shortcode('mp_reviews', [$this, 'renderReviewCarousel']);
        
        // Traffic Signal Badge: [mp_trust id="123"]
        add_shortcode('mp_trust', [$this, 'renderTrustBadge']);
        
        // Combined Widget: [mp_widget id="123" type="full"]
        add_shortcode('mp_widget', [$this, 'renderCombinedWidget']);
        
        // Mini Badge: [mp_mini_rating id="123"]
        add_shortcode('mp_mini_rating', [$this, 'renderMiniRating']);
        
        // Reviews Slider: [mp_slider id="123"]
        add_shortcode('mp_slider', [$this, 'renderReviewsSlider']);
    }

    /**
     * Register WordPress widgets
     * 
     * @return void
     */
    protected function registerWidgets(): void {
        add_action('widgets_init', function() {
            register_widget(new Widgets\RatingBadgeWidget());
            register_widget(new Widgets\TrustSignalWidget());
            register_widget(new Widgets\ReviewsListWidget());
        });
    }

    /**
     * Enqueue frontend assets
     * 
     * @return void
     */
    public function enqueueFrontendAssets(): void {
        global $post;
        
        // Check if any widget shortcode is used
        if ($post && has_shortcode($post->post_content, 'mp_rating')) {
            $this->enqueueStyle('widgets-rating', 'css/widget-rating.css');
            $this->enqueueScript('widgets-rating', 'js/widget-rating.js', ['jquery']);
        }
        
        if ($post && has_shortcode($post->post_content, 'mp_reviews')) {
            $this->enqueueStyle('widgets-reviews', 'css/widget-reviews.css');
            $this->enqueueScript('widgets-reviews', 'js/widget-reviews.js', ['jquery']);
            $this->enqueueScript('widgets-slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js', ['jquery'], '1.8.1');
        }
        
        if ($post && has_shortcode($post->post_content, 'mp_trust')) {
            $this->enqueueStyle('widgets-trust', 'css/widget-trust.css');
        }
        
        // Always load common styles
        $this->enqueueStyle('widgets-common', 'css/widgets-common.css');
    }

    /**
     * Render rating badge shortcode
     * 
     * Shortcode: [mp_rating id="123"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderRatingBadge(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'style' => 'standard',
            'show_count' => 'true',
            'show_stars' => 'true',
            'size' => 'medium',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $data = $service->getBusinessRatingData($business_id);
        
        if (!$data) {
            return '';
        }
        
        ob_start();
        include $this->getPath('templates/widget-rating.php');
        return ob_get_clean();
    }

    /**
     * Render review carousel shortcode
     * 
     * Shortcode: [mp_reviews id="123" limit="5"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderReviewCarousel(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'limit' => 5,
            'style' => 'carousel',
            'autoplay' => 'false',
            'arrows' => 'true',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $reviews = $service->getReviewsForWidget($business_id, (int) $atts['limit']);
        
        if (empty($reviews)) {
            return '<p class="mp-no-reviews">' . __('No reviews yet.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        include $this->getPath('templates/widget-reviews.php');
        return ob_get_clean();
    }

    /**
     * Render trust badge shortcode
     * 
     * Shortcode: [mp_trust id="123"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustBadge(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'style' => 'standard',
            'show_label' => 'true',
            'show_score' => 'true',
            'size' => 'medium',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $data = $service->getTrustData($business_id);
        
        if (!$data) {
            return '';
        }
        
        ob_start();
        include $this->getPath('templates/widget-trust.php');
        return ob_get_clean();
    }

    /**
     * Render combined widget
     * 
     * Shortcode: [mp_widget id="123" type="full"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderCombinedWidget(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'type' => 'full',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $data = $service->getBusinessData($business_id);
        
        if (!$data) {
            return '';
        }
        
        ob_start();
        include $this->getPath('templates/widget-combined.php');
        return ob_get_clean();
    }

    /**
     * Render mini rating
     * 
     * Shortcode: [mp_mini_rating id="123"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderMiniRating(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $data = $service->getBusinessRatingData($business_id);
        
        if (!$data) {
            return '';
        }
        
        ob_start();
        include $this->getPath('templates/widget-mini.php');
        return ob_get_clean();
    }

    /**
     * Render reviews slider
     * 
     * Shortcode: [mp_slider id="123"]
     * 
     * @param array $atts
     * @return string
     */
    public function renderReviewsSlider(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'limit' => 10,
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $service = $this->getService('widgets.service');
        $reviews = $service->getReviewsForWidget($business_id, (int) $atts['limit']);
        
        if (empty($reviews)) {
            return '';
        }
        
        ob_start();
        include $this->getPath('templates/widget-slider.php');
        return ob_get_clean();
    }

    /**
     * Generate embed code for widget
     * 
     * @param string $widget_type
     * @param int $business_id
     * @param array $options
     * @return string
     */
    public static function generateEmbedCode(string $widget_type, int $business_id, array $options = []): string {
        $base_url = home_url('/');
        $shortcode = '';
        
        switch ($widget_type) {
            case 'rating':
                $shortcode = sprintf('[mp_rating id="%d" style="%s" size="%s"]', 
                    $business_id, 
                    $options['style'] ?? 'standard',
                    $options['size'] ?? 'medium'
                );
                break;
            case 'reviews':
                $shortcode = sprintf('[mp_reviews id="%d" limit="%d"]', 
                    $business_id, 
                    $options['limit'] ?? 5
                );
                break;
            case 'trust':
                $shortcode = sprintf('[mp_trust id="%d" style="%s"]', 
                    $business_id, 
                    $options['style'] ?? 'standard'
                );
                break;
            case 'full':
                $shortcode = sprintf('[mp_widget id="%d" type="full"]', $business_id);
                break;
        }
        
        return $shortcode;
    }
}