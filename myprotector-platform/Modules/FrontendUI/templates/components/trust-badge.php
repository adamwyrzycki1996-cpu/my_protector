<?php
/**
 * MyProtector Platform - Trust Badge Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$status = isset($status) ? $status : 'green';

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

$icon = $trust_icons[$status];
$label = $trust_labels[$status];
?>

<span class="mp-trust-badge mp-trust-badge-<?php echo esc_attr($status); ?>">
    <span class="mp-trust-badge-icon"><?php echo $icon; ?></span>
    <?php echo esc_html($label); ?>
</span>
