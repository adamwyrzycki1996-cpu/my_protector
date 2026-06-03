<?php
/**
 * MyProtector Platform - Mini Rating Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */
?>
<span class="mp-widget-mini">
    <span class="mp-mini-stars">
        <?php
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= floor($data['avg_rating'])) {
                echo '<i class="fas fa-star"></i>';
            } elseif ($i - 0.5 <= $data['avg_rating']) {
                echo '<i class="fas fa-star-half-alt"></i>';
            } else {
                echo '<i class="far fa-star"></i>';
            }
        }
        ?>
    </span>
    <span class="mp-mini-rating"><?php echo esc_html(number_format($data['avg_rating'], 1)); ?></span>
    <span class="mp-mini-count">(<?php echo esc_html($data['total_reviews']); ?>)</span>
</span>