<?php
/**
 * MyProtector Platform - Stars Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$rating = isset($rating) ? (float) $rating : 0;
$max = 5;
$full_stars = floor($rating);
$has_half = ($rating - $full_stars) >= 0.5;
$empty_stars = $max - $full_stars - ($has_half ? 1 : 0);
?>

<div class="mp-rating-stars">
    <?php for ($i = 0; $i < $full_stars; $i++): ?>
    <span class="mp-rating-star mp-rating-star-filled">★</span>
    <?php endfor; ?>
    
    <?php if ($has_half): ?>
    <span class="mp-rating-star mp-rating-star-half" style="position: relative; display: inline-block;">
        <span style="position: absolute; top: 0; left: 0; overflow: hidden; width: 50%;">★</span>
        <span style="color: var(--mp-gray-300);">★</span>
    </span>
    <?php endif; ?>
    
    <?php for ($i = 0; $i < $empty_stars; $i++): ?>
    <span class="mp-rating-star">★</span>
    <?php endfor; ?>
</div>
