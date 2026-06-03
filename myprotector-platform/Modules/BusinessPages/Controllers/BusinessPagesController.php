<?php
/**
 * MyProtector Platform - Business Pages Controller
 * 
 * Handles rendering of business page components
 * 
 * @package MyProtector\Modules\BusinessPages\Controllers
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessPages\Controllers;

use MyProtector\Modules\BusinessPages\BusinessPages;

class BusinessPagesController {
    /**
     * Module instance
     * 
     * @var BusinessPages
     */
    protected $module;

    /**
     * Service instance
     * 
     * @var \MyProtector\Modules\BusinessPages\Services\BusinessPagesService
     */
    protected $service;

    /**
     * Constructor
     * 
     * @param BusinessPages $module
     */
    public function __construct(BusinessPages $module) {
        $this->module = $module;
        $this->service = $module->getService('business-pages.service');
    }

    /**
     * Render business page shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderBusinessPage(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'style' => 'full',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            // Try to get from current page
            $business = get_query_var('mp_current_business');
            if ($business) {
                $business_id = $business->business_id;
            }
        }
        
        if (!$business_id) {
            return '<p class="mp-error">' . __('Business ID required.', 'myprotector-platform') . '</p>';
        }
        
        $business = $this->getBusiness($business_id);
        
        if (!$business) {
            return '<p class="mp-error">' . __('Business not found.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        $this->renderBusinessPageTemplate($business, $atts);
        return ob_get_clean();
    }

    /**
     * Render business page template
     * 
     * @param object $business
     * @param array $atts
     * @return void
     */
    protected function renderBusinessPageTemplate(object $business, array $atts): void {
        $style = $atts['style'] ?? 'full';
        $reviews = $this->service->getBusinessReviews($business->business_id, ['per_page' => 5]);
        $distribution = $this->service->getRatingDistribution($business->business_id);
        $total_reviews = array_sum($distribution);
        
        $trust_status = $business->traffic_signal->trust_status ?? 'bad';
        $trust_score = $business->traffic_signal->trust_score ?? 0;
        
        ?>
        <div class="mp-business-page mp-business-page-<?php echo esc_attr($style); ?>">
            <!-- Trust Signal Banner -->
            <?php if ($style === 'full'): ?>
            <div class="mp-trust-banner mp-trust-banner-<?php echo esc_attr($trust_status); ?>">
                <div class="mp-trust-signal">
                    <span class="mp-trust-icon">
                        <?php echo $this->getTrustIcon($trust_status); ?>
                    </span>
                    <span class="mp-trust-label"><?php echo $this->getTrustLabel($trust_status); ?></span>
                    <?php if ($trust_score > 0): ?>
                    <span class="mp-trust-score">(<?php echo esc_html(number_format($trust_score, 0)); ?>%)</span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo esc_url($this->getTrustLearnMoreUrl($trust_status)); ?>" class="mp-trust-learn-more">
                    <?php _e('Learn More', 'myprotector-platform'); ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Business Header -->
            <div class="mp-business-header">
                <?php if (!empty($business->logo_url)): ?>
                <div class="mp-business-logo">
                    <img src="<?php echo esc_url($business->logo_url); ?>" alt="<?php echo esc_attr($business->business_name); ?>">
                </div>
                <?php endif; ?>
                
                <div class="mp-business-info">
                    <h1 class="mp-business-name"><?php echo esc_html($business->business_name); ?></h1>
                    
                    <?php if (!empty($business->business_tagline)): ?>
                    <p class="mp-business-tagline"><?php echo esc_html($business->business_tagline); ?></p>
                    <?php endif; ?>
                    
                    <!-- Rating Summary -->
                    <div class="mp-rating-summary">
                        <div class="mp-star-rating" data-rating="<?php echo esc_attr($business->avg_rating); ?>">
                            <?php echo $this->renderStars($business->avg_rating); ?>
                        </div>
                        <span class="mp-rating-value"><?php echo esc_html(number_format($business->avg_rating, 1)); ?></span>
                        <span class="mp-review-count">
                            (<?php echo esc_html(sprintf(_n('%d review', '%d reviews', $business->total_reviews, 'myprotector-platform'), $business->total_reviews)); ?>)
                        </span>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="mp-business-actions">
                        <a href="<?php echo esc_url($this->getReviewsUrl($business->business_slug)); ?>" class="mp-btn mp-btn-primary">
                            <?php _e('See All Reviews', 'myprotector-platform'); ?>
                        </a>
                        <a href="<?php echo esc_url($this->getWriteReviewUrl($business->business_slug)); ?>" class="mp-btn mp-btn-outline">
                            <?php _e('Write a Review', 'myprotector-platform'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution -->
            <?php if ($style === 'full' && $total_reviews > 0): ?>
            <div class="mp-rating-distribution">
                <h3><?php _e('Rating Distribution', 'myprotector-platform'); ?></h3>
                <div class="mp-distribution-bars">
                    <?php foreach ($distribution as $stars => $count): 
                        $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                    ?>
                    <div class="mp-distribution-row">
                        <span class="mp-distribution-stars"><?php echo esc_html($stars); ?> <?php _e('stars', 'myprotector-platform'); ?></span>
                        <div class="mp-distribution-bar-container">
                            <div class="mp-distribution-bar" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                        </div>
                        <span class="mp-distribution-count"><?php echo esc_html($count); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Latest Reviews Preview -->
            <?php if ($style === 'full' && !empty($reviews['items'])): ?>
            <div class="mp-latest-reviews">
                <h3><?php _e('Latest Reviews', 'myprotector-platform'); ?></h3>
                <div class="mp-reviews-list">
                    <?php foreach ($reviews['items'] as $review): ?>
                    <div class="mp-review-item">
                        <div class="mp-review-header">
                            <div class="mp-review-author">
                                <img src="<?php echo esc_url($review['reviewer']['avatar']); ?>" alt="" class="mp-review-avatar">
                                <div class="mp-review-author-info">
                                    <span class="mp-review-author-name"><?php echo esc_html($review['reviewer']['name']); ?></span>
                                    <span class="mp-review-date"><?php echo esc_html(human_time_diff(strtotime($review['published_at']))); ?></span>
                                </div>
                            </div>
                            <div class="mp-review-rating">
                                <?php echo $this->renderStars($review['review_rating']); ?>
                            </div>
                        </div>
                        <?php if (!empty($review['review_title'])): ?>
                        <h4 class="mp-review-title"><?php echo esc_html($review['review_title']); ?></h4>
                        <?php endif; ?>
                        <p class="mp-review-content"><?php echo esc_html($this->truncateText($review['review_content'], 200)); ?></p>
                        <div class="mp-review-footer">
                            <button class="mp-btn-helpful" data-review-id="<?php echo esc_attr($review['review_id']); ?>">
                                <i class="far fa-thumbs-up"></i>
                                <?php echo esc_html(sprintf(__('Helpful (%d)', 'myprotector-platform'), $review['helpful_count'] ?? 0)); ?>
                            </button>
                            <a href="<?php echo esc_url($this->getReviewUrl($business->business_slug, $review['review_id'])); ?>">
                                <?php _e('Read More', 'myprotector-platform'); ?>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($reviews['total'] > 5): ?>
                <a href="<?php echo esc_url($this->getReviewsUrl($business->business_slug)); ?>" class="mp-view-all-reviews">
                    <?php _e('View All Reviews', 'myprotector-platform'); ?> (<?php echo esc_html($reviews['total']); ?>)
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Business Response Section -->
            <?php if ($style === 'full'): ?>
            <div class="mp-business-contact">
                <h3><?php _e('Contact Information', 'myprotector-platform'); ?></h3>
                <div class="mp-contact-grid">
                    <?php if (!empty($business->business_website)): ?>
                    <div class="mp-contact-item">
                        <i class="fas fa-globe"></i>
                        <a href="<?php echo esc_url($business->business_website); ?>" target="_blank" rel="noopener">
                            <?php _e('Visit Website', 'myprotector-platform'); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business->business_phone)): ?>
                    <div class="mp-contact-item">
                        <i class="fas fa-phone"></i>
                        <a href="tel:<?php echo esc_attr($business->business_phone); ?>">
                            <?php echo esc_html($business->business_phone); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business->business_email)): ?>
                    <div class="mp-contact-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:<?php echo esc_attr($business->business_email); ?>">
                            <?php echo esc_html($business->business_email); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($business->address_line1)): ?>
                    <div class="mp-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>
                            <?php echo esc_html($this->formatAddress($business)); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Trust Badges -->
            <?php if ($style === 'full'): ?>
            <div class="mp-trust-badges">
                <?php if (!empty($business->insurance_url)): ?>
                <a href="<?php echo esc_url($business->insurance_url); ?>" target="_blank" rel="noopener" class="mp-trust-badge-link">
                    <i class="fas fa-shield-alt"></i>
                    <?php _e('Insured', 'myprotector-platform'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($business->terms_url)): ?>
                <a href="<?php echo esc_url($business->terms_url); ?>" target="_blank" rel="noopener" class="mp-trust-badge-link">
                    <i class="fas fa-file-contract"></i>
                    <?php _e('Terms Provided', 'myprotector-platform'); ?>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($business->promise_page_url)): ?>
                <a href="<?php echo esc_url($business->promise_page_url); ?>" target="_blank" rel="noopener" class="mp-trust-badge-link">
                    <i class="fas fa-handshake"></i>
                    <?php _e('Promise Page', 'myprotector-platform'); ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render trust badge shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustBadge(array $atts): string {
        $atts = shortcode_atts([
            'company_id' => 0,
            'style' => 'light',
            'size' => 'medium',
            'show_score' => true,
        ], $atts);
        
        $business_id = (int) $atts['company_id'];
        
        if (!$business_id) {
            return '';
        }
        
        $business = $this->getBusiness($business_id);
        
        if (!$business) {
            return '';
        }
        
        $trust_status = $business->traffic_signal->trust_status ?? 'bad';
        $trust_score = $business->traffic_signal->trust_score ?? 0;
        
        $colors = [
            'walking' => '#10b981',
            'shopping' => '#f59e0b',
            'bad' => '#ef4444',
        ];
        
        $labels = [
            'walking' => __('Walking Safe', 'myprotector-platform'),
            'shopping' => __('Shopping Safe', 'myprotector-platform'),
            'bad' => __('Caution', 'myprotector-platform'),
        ];
        
        $icons = [
            'walking' => '🚶',
            'shopping' => '🛒',
            'bad' => '⚠️',
        ];
        
        $color = $colors[$trust_status];
        $label = $labels[$trust_status];
        $icon = $icons[$trust_status];
        
        $sizes = [
            'small' => '14px',
            'medium' => '16px',
            'large' => '20px',
        ];
        
        $font_size = $sizes[$atts['size']] ?? '16px';
        $bg_style = $atts['style'] === 'dark' ? 'background:#1f2937;color:#fff;' : 'background:#f3f4f6;color:#1f2937;';
        
        $html = sprintf(
            '<div class="mp-trust-badge mp-trust-badge-%s" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;%s">',
            esc_attr($trust_status),
            $bg_style
        );
        
        $html .= sprintf('<span style="font-size:%s">%s</span>', esc_attr($font_size), $icon);
        $html .= sprintf('<span style="font-weight:600;font-size:14px;">%s</span>', esc_html($label));
        
        if ($atts['show_score'] && $trust_score > 0) {
            $html .= sprintf('<span style="font-size:12px;opacity:0.8;">(%d%%)</span>', (int)$trust_score);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render reviews list shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderReviewsList(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'limit' => 5,
            'sort' => 'recent',
            'style' => 'list',
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '<p class="mp-error">' . __('Business ID required.', 'myprotector-platform') . '</p>';
        }
        
        $reviews = $this->service->getBusinessReviews($business_id, [
            'per_page' => (int) $atts['limit'],
            'sort' => $atts['sort'],
        ]);
        
        if (empty($reviews['items'])) {
            return '<p class="mp-no-reviews">' . __('No reviews yet.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="mp-reviews-list mp-reviews-list-<?php echo esc_attr($atts['style']); ?>">
            <?php foreach ($reviews['items'] as $review): ?>
            <div class="mp-review-item">
                <div class="mp-review-header">
                    <img src="<?php echo esc_url($review['reviewer']['avatar']); ?>" alt="" class="mp-review-avatar">
                    <div class="mp-review-author-info">
                        <span class="mp-review-author-name"><?php echo esc_html($review['reviewer']['name']); ?></span>
                        <span class="mp-review-date"><?php echo esc_html(human_time_diff(strtotime($review['published_at']))); ?></span>
                    </div>
                    <div class="mp-review-rating">
                        <?php echo $this->renderStars($review['review_rating']); ?>
                    </div>
                </div>
                <?php if (!empty($review['review_title'])): ?>
                <h4 class="mp-review-title"><?php echo esc_html($review['review_title']); ?></h4>
                <?php endif; ?>
                <p class="mp-review-content"><?php echo esc_html($review['review_content']); ?></p>
                
                <?php if (!empty($review['response'])): ?>
                <div class="mp-business-response">
                    <strong><?php _e('Business Response:', 'myprotector-platform'); ?></strong>
                    <p><?php echo esc_html($review['response']['response_content']); ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render rating display shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderRatingDisplay(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'show_count' => true,
            'show_distribution' => false,
        ], $atts);
        
        $business_id = (int) $atts['id'];
        
        if (!$business_id) {
            return '';
        }
        
        $business = $this->getBusiness($business_id);
        
        if (!$business) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="mp-rating-display">
            <div class="mp-rating-large">
                <span class="mp-rating-number"><?php echo esc_html(number_format($business->avg_rating, 1)); ?></span>
                <div class="mp-stars">
                    <?php echo $this->renderStars($business->avg_rating, true); ?>
                </div>
                <?php if ($atts['show_count'] && $business->total_reviews > 0): ?>
                <span class="mp-review-count-text">
                    <?php echo esc_html(sprintf(_n('%d review', '%d reviews', $business->total_reviews, 'myprotector-platform'), $business->total_reviews)); ?>
                </span>
                <?php endif; ?>
            </div>
            
            <?php if ($atts['show_distribution']): ?>
            <?php 
            $distribution = $this->service->getRatingDistribution($business_id);
            $total = array_sum($distribution);
            ?>
            <div class="mp-rating-distribution-mini">
                <?php foreach ($distribution as $stars => $count): 
                    $percentage = $total > 0 ? ($count / $total) * 100 : 0;
                ?>
                <div class="mp-dist-row">
                    <span><?php echo esc_html($stars); ?></span>
                    <div class="mp-dist-bar"><div style="width:<?php echo esc_attr($percentage); ?>%"></div></div>
                    <span><?php echo esc_html($count); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render search form shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderSearchForm(array $atts): string {
        ob_start();
        ?>
        <div class="mp-business-search">
            <form action="<?php echo esc_url(home_url('/businesses/')); ?>" method="get">
                <input type="text" name="search" placeholder="<?php esc_attr_e('Search businesses...', 'myprotector-platform'); ?>" class="mp-search-input">
                <button type="submit" class="mp-search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render categories shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderCategories(array $atts): string {
        $atts = shortcode_atts([
            'style' => 'grid',
            'show_count' => true,
        ], $atts);
        
        $categories = $this->getCategories();
        
        if (empty($categories)) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="mp-categories mp-categories-<?php echo esc_attr($atts['style']); ?>">
            <?php foreach ($categories as $category): ?>
            <a href="<?php echo esc_url(home_url('/businesses/?category=' . $category->term_id)); ?>" class="mp-category-item">
                <span class="mp-category-name"><?php echo esc_html($category->name); ?></span>
                <?php if ($atts['show_count']): ?>
                <span class="mp-category-count">(<?php echo esc_html($category->count); ?>)</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // Helper Methods

    /**
     * Get business by ID
     * 
     * @param int $business_id
     * @return object|null
     */
    protected function getBusiness(int $business_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE business_id = %d AND business_status = 'active'",
                $business_id
            )
        );
    }

    /**
     * Get categories
     * 
     * @return array
     */
    protected function getCategories(): array {
        // Would use WordPress categories/taxonomies
        return [];
    }

    /**
     * Render star rating
     * 
     * @param float $rating
     * @param bool $large
     * @return string
     */
    protected function renderStars(float $rating, bool $large = false): string {
        $full_stars = floor($rating);
        $has_half = ($rating - $full_stars) >= 0.5;
        $empty_stars = 5 - $full_stars - ($has_half ? 1 : 0);
        
        $html = '<div class="mp-stars">';
        
        for ($i = 0; $i < $full_stars; $i++) {
            $html .= '<i class="fas fa-star' . ($large ? ' mp-star-large' : '') . '"></i>';
        }
        
        if ($has_half) {
            $html .= '<i class="fas fa-star-half-alt' . ($large ? ' mp-star-large' : '') . '"></i>';
        }
        
        for ($i = 0; $i < $empty_stars; $i++) {
            $html .= '<i class="far fa-star' . ($large ? ' mp-star-large' : '') . '"></i>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get trust icon
     * 
     * @param string $status
     * @return string
     */
    protected function getTrustIcon(string $status): string {
        return [
            'walking' => '🚶',
            'shopping' => '🛒',
            'bad' => '⚠️',
        ][$status] ?? '⚠️';
    }

    /**
     * Get trust label
     * 
     * @param string $status
     * @return string
     */
    protected function getTrustLabel(string $status): string {
        return [
            'walking' => __('Walking Safe', 'myprotector-platform'),
            'shopping' => __('Shopping Safe', 'myprotector-platform'),
            'bad' => __('Caution', 'myprotector-platform'),
        ][$status] ?? __('Unknown', 'myprotector-platform');
    }

    /**
     * Get trust learn more URL
     * 
     * @param string $status
     * @return string
     */
    protected function getTrustLearnMoreUrl(string $status): string {
        return '#trust-' . $status;
    }

    /**
     * Truncate text
     * 
     * @param string $text
     * @param int $length
     * @return string
     */
    protected function truncateText(string $text, int $length): string {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }

    /**
     * Format address
     * 
     * @param object $business
     * @return string
     */
    protected function formatAddress(object $business): string {
        $parts = array_filter([
            $business->address_line1 ?? '',
            $business->address_line2 ?? '',
            $business->city ?? '',
            $business->state ?? '',
            $business->postal_code ?? '',
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Get reviews URL
     * 
     * @param string $slug
     * @return string
     */
    protected function getReviewsUrl(string $slug): string {
        return BusinessPages::getReviewsUrl($slug);
    }

    /**
     * Get write review URL
     * 
     * @param string $slug
     * @return string
     */
    protected function getWriteReviewUrl(string $slug): string {
        return BusinessPages::getWriteReviewUrl($slug);
    }

    /**
     * Get review URL
     * 
     * @param string $business_slug
     * @param int $review_id
     * @return string
     */
    protected function getReviewUrl(string $business_slug, int $review_id): string {
        return home_url('/business/' . $business_slug . '/reviews/#review-' . $review_id);
    }
}