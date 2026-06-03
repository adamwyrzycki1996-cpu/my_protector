<?php
/**
 * MyProtector Platform - Review Summary Widget Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;
?>

<div class="mp-widget mp-reviews-widget">
    <div class="mp-widget-header">
        <h4 style="margin: 0;">Latest Reviews</h4>
        <a href="#" style="font-size: var(--mp-font-size-sm);">See all →</a>
    </div>
    
    <div class="mp-widget-body">
        <?php foreach ($reviews as $review): ?>
        <div class="mp-widget-review-item" style="padding: var(--mp-spacing-md) 0; border-bottom: 1px solid var(--mp-gray-100);">
            <div class="mp-flex mp-items-center mp-gap-sm" style="margin-bottom: var(--mp-spacing-xs);">
                <img src="<?php echo esc_attr($review['reviewer_avatar']); ?>" alt="" style="width: 32px; height: 32px; border-radius: var(--mp-radius-full);">
                <div style="flex: 1;">
                    <span style="font-weight: 600; font-size: var(--mp-font-size-sm);"><?php echo esc_html($review['reviewer']); ?></span>
                    <?php if ($review['verified']): ?>
                    <span class="mp-review-verified" style="font-size: 10px; padding: 1px 6px;">✓</span>
                    <?php endif; ?>
                </div>
                <div class="mp-rating-stars" style="gap: 0;">
                    <?php 
                    $full = (int) $review['rating'];
                    for ($i = 0; $i < 5; $i++): 
                    ?>
                    <span class="mp-rating-star <?php echo $i < $full ? 'mp-rating-star-filled' : ''; ?>" style="font-size: 12px;">★</span>
                    <?php endfor; ?>
                </div>
            </div>
            <p style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-600); margin: 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                <?php echo esc_html($review['content']); ?>
            </p>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($reviews)): ?>
        <p style="text-align: center; color: var(--mp-gray-500); padding: var(--mp-spacing-lg);">
            No reviews yet
        </p>
        <?php endif; ?>
    </div>
</div>

<style>
.mp-widget {
    background: var(--mp-white);
    border: 1px solid var(--mp-gray-200);
    border-radius: var(--mp-radius-xl);
    overflow: hidden;
}

.mp-widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--mp-spacing-md) var(--mp-spacing-lg);
    border-bottom: 1px solid var(--mp-gray-100);
}

.mp-widget-header h4 {
    color: var(--mp-dark-navy);
    font-size: var(--mp-font-size-base);
}

.mp-widget-body {
    padding: var(--mp-spacing-sm) var(--mp-spacing-lg);
}
</style>
