<?php
/**
 * MyProtector Platform - Business Card Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

// Business data should be passed via $business variable
$logo = isset($business['logo']) ? $business['logo'] : 'https://ui-avatars.com/api/?name=' . urlencode($business['name']) . '&background=0A1F44&color=fff&size=128';
$name = isset($business['name']) ? $business['name'] : 'Unknown Business';
$category = isset($business['category']) ? $business['category'] : '';
$rating = isset($business['rating']) ? (float) $business['rating'] : 0;
$total_reviews = isset($business['total_reviews']) ? (int) $business['total_reviews'] : 0;
$trust_status = isset($business['trust_status']) ? $business['trust_status'] : 'amber';
$location = isset($business['location']) ? $business['location'] : '';
$claimed = isset($business['claimed']) ? $business['claimed'] : false;

// Trust icons and labels
$trust_icons = [
    'green' => '🛒',
    'amber' => '🚶',
    'red' => '⚠️',
];
$trust_labels = [
    'green' => 'Shopping Safe',
    'amber' => 'Walking Safe',
    'red' => 'Caution',
];

$full_stars = floor($rating);
?>

<div class="mp-card mp-business-card mp-card-clickable" data-business-id="<?php echo esc_attr($business['id'] ?? 0); ?>">
    <div class="mp-card-body">
        <div class="mp-flex mp-items-center mp-gap-md">
            <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($name); ?>" class="mp-business-logo">
            <div style="flex: 1; min-width: 0;">
                <h3 class="mp-business-name" style="margin-bottom: 0; font-size: var(--mp-font-size-base);">
                    <?php echo esc_html($name); ?>
                </h3>
                <?php if ($category): ?>
                <div class="mp-business-category"><?php echo esc_html($category); ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mp-business-rating" style="margin-top: var(--mp-spacing-md);">
            <div class="mp-rating-stars">
                <?php for ($i = 0; $i < 5; $i++): ?>
                <span class="mp-rating-star <?php echo $i < $full_stars ? 'mp-rating-star-filled' : ''; ?>">★</span>
                <?php endfor; ?>
            </div>
            <span class="mp-rating-value"><?php echo esc_html(number_format($rating, 1)); ?></span>
            <span class="mp-business-reviews">(<?php echo number_format($total_reviews); ?>)</span>
        </div>
        
        <?php if ($location): ?>
        <p class="mp-business-location">
            📍 <?php echo esc_html($location); ?>
        </p>
        <?php endif; ?>

        <div class="mp-flex mp-items-center mp-justify-between" style="margin-top: var(--mp-spacing-md); padding-top: var(--mp-spacing-md); border-top: 1px solid var(--mp-gray-100);">
            <span class="mp-trust-badge mp-trust-badge-<?php echo esc_attr($trust_status); ?>">
                <span class="mp-trust-badge-icon"><?php echo $trust_icons[$trust_status]; ?></span>
                <?php echo $trust_labels[$trust_status]; ?>
            </span>
            
            <?php if ($claimed): ?>
            <span class="mp-badge mp-badge-green">✓ Claimed</span>
            <?php else: ?>
            <span class="mp-badge">Unclaimed</span>
            <?php endif; ?>
        </div>
    </div>
</div>
