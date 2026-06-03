<?php
/**
 * MyProtector Platform - Trust Badge Widget Template
 * 
 * @package MyProtector\Modules\Widgets\templates
 */

$style = $atts['style'] ?? 'standard';
$show_label = filter_var($atts['show_label'], FILTER_VALIDATE_BOOLEAN);
$show_score = filter_var($atts['show_score'], FILTER_VALIDATE_BOOLEAN);
$size = $atts['size'] ?? 'medium';
?>
<div class="mp-widget-trust mp-widget-trust-<?php echo esc_attr($data['status']); ?> mp-widget-trust-<?php echo esc_attr($style); ?>">
    <div class="mp-widget-trust-icon">
        <?php echo esc_html($data['icon']); ?>
    </div>
    
    <?php if ($show_label): ?>
    <div class="mp-widget-trust-label">
        <?php echo esc_html($data['label']); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($show_score): ?>
    <div class="mp-widget-trust-score">
        <?php echo esc_html(number_format($data['score'], 0)); ?>%
    </div>
    <?php endif; ?>
</div>