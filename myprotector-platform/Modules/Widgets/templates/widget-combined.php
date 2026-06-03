<?php
/**
 * MyProtector Platform - Combined Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */
?>
<div class="mp-widget-combined">
    <!-- Trust Signal -->
    <div class="mp-widget-trust-banner mp-widget-trust-<?php echo esc_attr($data['trust']['status']); ?>">
        <span class="mp-trust-icon"><?php echo esc_html($data['trust']['icon']); ?></span>
        <span class="mp-trust-label"><?php echo esc_html($data['trust']['label']); ?></span>
        <span class="mp-trust-score">(<?php echo esc_html(number_format($data['trust']['score'], 0)); ?>%)</span>
    </div>
    
    <!-- Rating -->
    <div class="mp-widget-rating-display">
        <div class="mp-rating-value"><?php echo esc_html(number_format($data['business']['avg_rating'], 1)); ?></div>
        <div class="mp-stars">
            <?php
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= floor($data['business']['avg_rating'])) {
                    echo '<i class="fas fa-star"></i>';
                } elseif ($i - 0.5 <= $data['business']['avg_rating']) {
                    echo '<i class="fas fa-star-half-alt"></i>';
                } else {
                    echo '<i class="far fa-star"></i>';
                }
            }
            ?>
        </div>
        <span class="mp-review-count"><?php echo esc_html($data['business']['total_reviews']); ?> reviews</span>
    </div>
    
    <!-- Reviews Preview -->
    <?php if (!empty($data['reviews'])): ?>
    <div class="mp-widget-reviews-preview">
        <?php foreach (array_slice($data['reviews'], 0, 2) as $review): ?>
        <div class="mp-widget-review-item">
            <div class="mp-widget-review-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?php echo $i <= $review['review_rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                <?php endfor; ?>
            </div>
            <p class="mp-widget-review-text">"<?php echo esc_html(wp_trim_words($review['review_content'], 20)); ?>"</p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>