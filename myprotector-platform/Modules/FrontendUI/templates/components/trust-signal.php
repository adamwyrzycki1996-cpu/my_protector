<?php
/**
 * MyProtector Platform - Trust Signal Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

// Determine size
$size = isset($size) ? $size : 'medium';
$light_size = $size === 'large' ? '120px' : ($size === 'small' ? '60px' : '80px');
$icon_size = $size === 'large' ? '48px' : ($size === 'small' ? '24px' : '36px');

// Trust status data
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

$trust_descs = [
    'green' => 'This business meets all trust criteria. Safe to engage.',
    'amber' => 'Partial verification. Exercise normal caution.',
    'red' => 'Limited verification. Proceed with care.',
];

$status = isset($business) ? $business['trust_status'] : (isset($status) ? $status : 'green');
$score = isset($business) ? $business['trust_score'] : 100;

$icon = $trust_icons[$status];
$label = $trust_labels[$status];
$desc = $trust_descs[$status];
?>

<div class="mp-trust-signal">
    <div class="mp-trust-light mp-trust-light-<?php echo esc_attr($status); ?>" style="width: <?php echo esc_attr($light_size); ?>; height: <?php echo esc_attr($light_size); ?>;">
        <span class="mp-trust-icon" style="font-size: <?php echo esc_attr($icon_size); ?>;"><?php echo $icon; ?></span>
    </div>
    <div class="mp-trust-label mp-trust-label-<?php echo esc_attr($status); ?>"><?php echo esc_html($label); ?></div>
    <p class="mp-trust-desc"><?php echo esc_html($desc); ?></p>
    
    <?php if (isset($show_checklist) && $show_checklist && isset($business)): ?>
    <div class="mp-trust-checklist">
        <div class="mp-trust-checklist-title">Trust Requirements</div>
        
        <?php
        $requirements = [
            ['key' => 'insurance', 'label' => 'Insurance', 'met' => !empty($business['insurance_url'])],
            ['key' => 'terms', 'label' => 'Terms & Conditions', 'met' => !empty($business['terms_url'])],
            ['key' => 'promise', 'label' => 'Promise Page', 'met' => !empty($business['promise_url'])],
        ];
        
        $met_count = 0;
        foreach ($requirements as $req) {
            if ($req['met']) $met_count++;
        }
        ?>
        
        <?php foreach ($requirements as $req): ?>
        <div class="mp-trust-checklist-item">
            <span class="mp-trust-check-icon <?php echo $req['met'] ? 'mp-trust-check-pass' : 'mp-trust-check-fail'; ?>">
                <?php echo $req['met'] ? '✓' : '✕'; ?>
            </span>
            <span><?php echo esc_html($req['label']); ?></span>
        </div>
        <?php endforeach; ?>
        
        <div class="mp-trust-progress">
            <div class="mp-trust-progress-bar">
                <div class="mp-trust-progress-fill mp-trust-progress-fill-<?php echo esc_attr($status); ?>" style="width: <?php echo esc_attr($score); ?>%;"></div>
            </div>
            <div class="mp-trust-progress-text"><?php echo esc_html($met_count); ?>/3 requirements met</div>
        </div>
    </div>
    <?php endif; ?>
</div>
