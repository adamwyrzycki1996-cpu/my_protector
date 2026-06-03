<?php
/**
 * MyProtector Platform - Business Page Template
 * 
 * @package MyProtector\Modules\BusinessPages\templates
 */

if (!defined('ABSPATH')) {
    exit;
}

$business = get_query_var('mp_current_business');

if (!$business) {
    status_header(404);
    echo '<div class="mp-error"><h2>' . esc_html__('Business Not Found', 'myprotector-platform') . '</h2></div>';
    return;
}

$seo = $module->getService('business-pages.seo');
$service = $module->getService('business-pages.service');

$reviews = $service->getBusinessReviews($business->business_id, ['per_page' => 10]);
$distribution = $service->getRatingDistribution($business->business_id);
$total_reviews = array_sum($distribution);

$trust_status = $business->traffic_signal->trust_status ?? 'bad';
$trust_score = $business->traffic_signal->trust_score ?? 0;

// SEO
$canonical_url = $seo->generateCanonicalUrl($business);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="<?php echo esc_url($canonical_url); ?>">
    <?php echo $seo->generateBusinessSchema($business); ?>
    <?php echo $seo->generateReviewSchema($business); ?>
    <?php echo $seo->generateBreadcrumbSchema($business); ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="mp-business-page-wrapper">
        <!-- Breadcrumb -->
        <div class="mp-breadcrumb">
            <div class="mp-container">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'myprotector-platform'); ?></a>
                <span>/</span>
                <a href="<?php echo esc_url(home_url('/businesses/')); ?>"><?php _e('Businesses', 'myprotector-platform'); ?></a>
                <span>/</span>
                <span><?php echo esc_html($business->business_name); ?></span>
            </div>
        </div>

        <!-- Trust Signal Banner -->
        <div class="mp-trust-banner mp-trust-banner-<?php echo esc_attr($trust_status); ?>">
            <div class="mp-container">
                <div class="mp-trust-signal">
                    <span class="mp-trust-icon">
                        <?php
                        $trust_icons = [
                            'walking' => '🚶',
                            'shopping' => '🛒',
                            'bad' => '⚠️',
                        ];
                        echo $trust_icons[$trust_status] ?? '⚠️';
                        ?>
                    </span>
                    <span class="mp-trust-label"><?php echo $trust_labels[$trust_status] ?? __('Caution', 'myprotector-platform'); ?></span>
                    <?php if ($trust_score > 0): ?>
                    <span class="mp-trust-score">(<?php echo esc_html(number_format($trust_score, 0)); ?>%)</span>
                    <?php endif; ?>
                </div>
                <a href="#about-trust" class="mp-trust-learn-more">
                    <?php _e('Learn More', 'myprotector-platform'); ?> →
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mp-container mp-business-main">
            <div class="mp-business-content">
                <!-- Business Header -->
                <header class="mp-business-header">
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
                            <div class="mp-rating-value"><?php echo esc_html(number_format($business->avg_rating, 1)); ?></div>
                            <div class="mp-stars">
                                <?php echo str_repeat('<i class="fas fa-star"></i>', floor($business->avg_rating)); ?>
                                <?php if (($business->avg_rating - floor($business->avg_rating)) >= 0.5): ?>
                                <i class="fas fa-star-half-alt"></i>
                                <?php endif; ?>
                                <?php for ($i = 0; $i < (5 - ceil($business->avg_rating)); $i++): ?>
                                <i class="far fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="mp-review-count">
                                (<?php echo esc_html(sprintf(_n('%d review', '%d reviews', $business->total_reviews, 'myprotector-platform'), $business->total_reviews)); ?>)
                            </span>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mp-business-actions">
                            <a href="<?php echo esc_url(\MyProtector\Modules\BusinessPages\BusinessPages::getWriteReviewUrl($business->business_slug)); ?>" class="mp-btn mp-btn-primary">
                                <i class="fas fa-star"></i>
                                <?php _e('Write a Review', 'myprotector-platform'); ?>
                            </a>
                            <a href="<?php echo esc_url($business->business_website ?: '#'); ?>" class="mp-btn mp-btn-outline" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt"></i>
                                <?php _e('Visit Website', 'myprotector-platform'); ?>
                            </a>
                        </div>
                    </div>
                </header>

                <!-- About Section -->
                <?php if (!empty($business->business_description)): ?>
                <section class="mp-business-description">
                    <h2><?php _e('About', 'myprotector-platform'); ?></h2>
                    <div class="mp-description-content">
                        <?php echo wp_kses_post(wpautop($business->business_description)); ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Rating Distribution -->
                <?php if ($total_reviews > 0): ?>
                <section class="mp-rating-distribution">
                    <h2><?php _e('Rating Distribution', 'myprotector-platform'); ?></h2>
                    <div class="mp-distribution-bars">
                        <?php foreach ($distribution as $stars => $count): 
                            $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                        ?>
                        <div class="mp-distribution-row">
                            <span class="mp-distribution-label"><?php echo esc_html($stars); ?> <?php _e('stars', 'myprotector-platform'); ?></span>
                            <div class="mp-distribution-bar-container">
                                <div class="mp-distribution-bar" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                            </div>
                            <span class="mp-distribution-count"><?php echo esc_html($count); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Latest Reviews -->
                <section class="mp-reviews-section">
                    <div class="mp-reviews-header">
                        <h2><?php _e('Reviews', 'myprotector-platform'); ?></h2>
                        <div class="mp-reviews-controls">
                            <select class="mp-review-sort">
                                <option value="recent"><?php _e('Most Recent', 'myprotector-platform'); ?></option>
                                <option value="highest"><?php _e('Highest Rated', 'myprotector-platform'); ?></option>
                                <option value="lowest"><?php _e('Lowest Rated', 'myprotector-platform'); ?></option>
                                <option value="helpful"><?php _e('Most Helpful', 'myprotector-platform'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="mp-reviews-list" data-business-id="<?php echo esc_attr($business->business_id); ?>">
                        <?php if (!empty($reviews['items'])): ?>
                            <?php foreach ($reviews['items'] as $review): ?>
                            <article class="mp-review-item" id="review-<?php echo esc_attr($review['review_id']); ?>">
                                <div class="mp-review-header">
                                    <div class="mp-review-author">
                                        <img src="<?php echo esc_url($review['reviewer']['avatar']); ?>" alt="" class="mp-review-avatar">
                                        <div class="mp-review-author-info">
                                            <span class="mp-review-author-name"><?php echo esc_html($review['reviewer']['name']); ?></span>
                                            <span class="mp-review-meta">
                                                <?php echo esc_html(human_time_diff(strtotime($review['published_at']))); ?> ago
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mp-review-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?php echo $i <= $review['review_rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($review['review_title'])): ?>
                                <h3 class="mp-review-title"><?php echo esc_html($review['review_title']); ?></h3>
                                <?php endif; ?>
                                
                                <div class="mp-review-content">
                                    <?php echo esc_html($review['review_content']); ?>
                                </div>
                                
                                <?php if (!empty($review['images'])): ?>
                                <div class="mp-review-images">
                                    <?php foreach ($review['images'] as $image): ?>
                                    <img src="<?php echo esc_url($image['thumbnail_url'] ?: $image['image_url']); ?>" alt="" class="mp-review-image">
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($review['response'])): ?>
                                <div class="mp-business-response">
                                    <strong><?php _e('Business Response', 'myprotector-platform'); ?></strong>
                                    <p><?php echo esc_html($review['response']['response_content']); ?></p>
                                    <span class="mp-response-date">
                                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($review['response']['response_date']))); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mp-review-actions">
                                    <button class="mp-btn-helpful" data-review-id="<?php echo esc_attr($review['review_id']); ?>">
                                        <i class="far fa-thumbs-up"></i>
                                        <span class="mp-helpful-count">(<?php echo esc_html($review['helpful_count'] ?? 0); ?>)</span>
                                    </button>
                                    <button class="mp-btn-report" data-review-id="<?php echo esc_attr($review['review_id']); ?>">
                                        <?php _e('Report', 'myprotector-platform'); ?>
                                    </button>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div class="mp-no-reviews">
                            <p><?php _e('No reviews yet. Be the first to share your experience!', 'myprotector-platform'); ?></p>
                            <a href="<?php echo esc_url(\MyProtector\Modules\BusinessPages\BusinessPages::getWriteReviewUrl($business->business_slug)); ?>" class="mp-btn mp-btn-primary">
                                <?php _e('Write a Review', 'myprotector-platform'); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($reviews['total'] > 10): ?>
                    <div class="mp-reviews-pagination">
                        <a href="<?php echo esc_url(\MyProtector\Modules\BusinessPages\BusinessPages::getReviewsUrl($business->business_slug)); ?>" class="mp-btn mp-btn-outline">
                            <?php _e('View All Reviews', 'myprotector-platform'); ?> (<?php echo esc_html($reviews['total']); ?>)
                        </a>
                    </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="mp-business-sidebar">
                <!-- Contact Information -->
                <div class="mp-sidebar-widget">
                    <h3><?php _e('Contact Information', 'myprotector-platform'); ?></h3>
                    <div class="mp-contact-list">
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
                                <?php echo esc_html(implode(', ', array_filter([
                                    $business->address_line1,
                                    $business->address_line2,
                                    $business->city,
                                    $business->state,
                                    $business->postal_code
                                ]))); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Trust Badges -->
                <div class="mp-sidebar-widget">
                    <h3><?php _e('Trust Indicators', 'myprotector-platform'); ?></h3>
                    <div class="mp-trust-list">
                        <?php if (!empty($business->insurance_url)): ?>
                        <a href="<?php echo esc_url($business->insurance_url); ?>" target="_blank" rel="noopener" class="mp-trust-item">
                            <i class="fas fa-shield-alt"></i>
                            <span><?php _e('Insured Business', 'myprotector-platform'); ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($business->terms_url)): ?>
                        <a href="<?php echo esc_url($business->terms_url); ?>" target="_blank" rel="noopener" class="mp-trust-item">
                            <i class="fas fa-file-contract"></i>
                            <span><?php _e('Terms of Service', 'myprotector-platform'); ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($business->promise_page_url)): ?>
                        <a href="<?php echo esc_url($business->promise_page_url); ?>" target="_blank" rel="noopener" class="mp-trust-item">
                            <i class="fas fa-handshake"></i>
                            <span><?php echo esc_html($business->promise_page_title ?: __('Customer Promise', 'myprotector-platform')); ?></span>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($business->is_verified): ?>
                        <div class="mp-trust-item mp-trust-verified">
                            <i class="fas fa-check-circle"></i>
                            <span><?php _e('Verified Business', 'myprotector-platform'); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Response Rate -->
                <div class="mp-sidebar-widget">
                    <h3><?php _e('Response Rate', 'myprotector-platform'); ?></h3>
                    <div class="mp-response-rate">
                        <div class="mp-rate-value"><?php echo esc_html(number_format($business->response_rate, 0)); ?>%</div>
                        <p><?php _e('of reviews get a response', 'myprotector-platform'); ?></p>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>