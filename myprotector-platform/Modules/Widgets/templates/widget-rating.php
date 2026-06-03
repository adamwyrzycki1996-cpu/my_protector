<?php
/**
 * MyProtector Platform - Rating Badge Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */

$style = $atts['style'] ?? 'standard';
$show_count = filter_var($atts['show_count'], FILTER_VALIDATE_BOOLEAN);
$show_stars = filter_var($atts['show_stars'], FILTER_VALIDATE_BOOLEAN);
$size = $atts['size'] ?? 'medium';
?>
<div class="mp-widget-rating mp-widget-rating-<?php echo esc_attr($style); ?> mp-widget-size-<?php echo esc_attr($size); ?>">
    <?php if ($show_stars): ?>
    <div class="mp-widget-stars">
        <?php
        $rating = $data['avg_rating'];
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= floor($rating)) {
                echo '<i class="fas fa-star"></i>';
            } elseif ($i - 0.5 <= $rating) {
                echo '<i class="fas fa-star-half-alt"></i>';
            } else {
                echo '<i class="far fa-star"></i>';
            }
        }
        ?>
    </div>
    <?php endif; ?>
    
    <div class="mp-widget-rating-value">
        <?php echo esc_html(number_format($data['avg_rating'], 1)); ?>
    </div>
    
    <?php if ($show_count): ?>
    <div class="mp-widget-review-count">
        <?php echo esc_html(sprintf(_n('%d review', '%d reviews', $data['total_reviews'], 'myprotector-platform'), $data['total_reviews'])); ?>
    </div>
    <?php endif; ?>
    
    <div class="mp-widget-powered">
        <a href="<?php echo esc_url(home_url('/')); ?>">
            <?php esc_html_e('Powered by MyProtector', 'myprotector-platform'); ?>
        </a>
    </div>
</div>