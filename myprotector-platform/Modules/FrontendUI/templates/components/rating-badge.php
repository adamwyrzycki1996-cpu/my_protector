<?php
/**
 * MyProtector Platform - Rating Badge Widget Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$style = isset($style) ? $style : 'compact';
$size = isset($size) ? $size : 'medium';
?>

<div class="mp-widget mp-rating-widget mp-rating-widget-<?php echo esc_attr($style); ?> mp-rating-widget-<?php echo esc_attr($size); ?>">
    <?php if ($style === 'compact'): ?>
    <!-- Compact Style -->
    <div class="mp-flex mp-items-center mp-gap-sm">
        <span style="font-size: var(--mp-font-size-lg); font-weight: 700; color: var(--mp-dark-navy);">
            <?php echo esc_html($business['rating']); ?>
        </span>
        <div class="mp-rating-stars" style="gap: 1px;">
            <?php 
            $full = floor($business['rating']);
            for ($i = 0; $i < 5; $i++): 
            ?>
            <span class="mp-rating-star <?php echo $i < $full ? 'mp-rating-star-filled' : ''; ?>" style="font-size: 14px;">
                ★
            </span>
            <?php endfor; ?>
        </div>
        <span style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">
            (<?php echo esc_html($business['total_reviews']); ?>)
        </span>
    </div>
    
    <?php elseif ($style === 'full'): ?>
    <!-- Full Style -->
    <div class="mp-flex mp-items-center mp-gap-md">
        <div style="text-align: center; min-width: 60px;">
            <div style="font-size: var(--mp-font-size-3xl); font-weight: 800; color: var(--mp-dark-navy); line-height: 1;">
                <?php echo esc_html($business['rating']); ?>
            </div>
            <div style="font-size: var(--mp-font-size-xs); color: var(--mp-gray-500);">out of 5</div>
        </div>
        <div>
            <div class="mp-rating-stars" style="gap: 2px; margin-bottom: 4px;">
                <?php 
                $full = floor($business['rating']);
                for ($i = 0; $i < 5; $i++): 
                ?>
                <span class="mp-rating-star <?php echo $i < $full ? 'mp-rating-star-filled' : ''; ?>">
                    ★
                </span>
                <?php endfor; ?>
            </div>
            <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">
                Based on <?php echo esc_html($business['total_reviews']); ?> reviews
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Badge Style -->
    <div class="mp-flex mp-items-center mp-gap-sm" style="padding: var(--mp-spacing-sm) var(--mp-spacing-md); background: var(--mp-white); border: 2px solid var(--mp-gray-200); border-radius: var(--mp-radius-lg);">
        <span style="font-size: var(--mp-font-size-xl); font-weight: 800; color: var(--mp-dark-navy);">
            <?php echo esc_html($business['rating']); ?>
        </span>
        <div class="mp-rating-stars" style="gap: 1px;">
            <?php 
            $full = floor($business['rating']);
            for ($i = 0; $i < 5; $i++): 
            ?>
            <span class="mp-rating-star <?php echo $i < $full ? 'mp-rating-star-filled' : ''; ?>" style="font-size: 12px;">
                ★
            </span>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
