<?php
/**
 * MyProtector Platform - Review Carousel Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */

$style = $atts['style'] ?? 'carousel';
$autoplay = $atts['autoplay'] ?? 'false';
$arrows = $atts['arrows'] ?? 'true';
?>
<div class="mp-widget-reviews mp-widget-reviews-<?php echo esc_attr($style); ?>" 
     data-autoplay="<?php echo esc_attr($autoplay); ?>" 
     data-arrows="<?php echo esc_attr($arrows); ?>">
    <div class="mp-reviews-carousel">
        <?php foreach ($reviews as $review): ?>
        <div class="mp-review-card">
            <div class="mp-review-card-header">
                <div class="mp-review-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="<?php echo $i <= $review['review_rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                    <?php endfor; ?>
                </div>
                <span class="mp-review-date">
                    <?php echo esc_html(human_time_diff(strtotime($review['published_at']))); ?> ago
                </span>
            </div>
            
            <?php if (!empty($review['review_title'])): ?>
            <h4 class="mp-review-title"><?php echo esc_html($review['review_title']); ?></h4>
            <?php endif; ?>
            
            <p class="mp-review-text"><?php echo esc_html(wp_trim_words($review['review_content'], 30)); ?></p>
            
            <div class="mp-review-author">
                <?php if (!empty($review['reviewer_avatar'])): ?>
                <img src="<?php echo esc_url($review['reviewer_avatar']); ?>" alt="" class="mp-review-avatar">
                <?php endif; ?>
                <span class="mp-review-name"><?php echo esc_html($review['reviewer_name']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>