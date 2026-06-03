<?php
/**
 * MyProtector Platform - Reviews Slider Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */
?>
<div class="mp-widget-slider">
    <div class="mp-reviews-slider">
        <?php foreach ($reviews as $review): ?>
        <div class="mp-slider-slide">
            <div class="mp-slider-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="<?php echo $i <= $review['review_rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                <?php endfor; ?>
            </div>
            <blockquote class="mp-slider-quote">
                <?php echo esc_html($review['review_content']); ?>
            </blockquote>
            <cite class="mp-slider-author">
                — <?php echo esc_html($review['reviewer_name']); ?>
            </cite>
        </div>
        <?php endforeach; ?>
    </div>
</div>