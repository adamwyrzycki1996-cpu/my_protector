<?php
/**
 * Trust Signals - Public Display Template
 * 
 * @package MyProtector\Modules\TrustSignals\templates\public
 */

if (!defined('ABSPATH')) {
    exit;
}

// Status colors
$statusColors = [
    'green' => '#28a745',
    'amber' => '#ffc107',
    'red' => '#dc3545',
];

$status = $signal['status'];
$color = $statusColors[$status] ?? $statusColors['red'];
$statusLabel = strtoupper($status);
$requirements = $signal['requirements_data'] ?? [];
$metCount = count(array_filter($requirements, fn($r) => $r['met'] ?? false));
$totalCount = count($requirements);
$isOverridden = !empty($signal['is_overridden']);
$overrideReason = $signal['override_reason'] ?? '';
?>

<div class="mp-trust-signal-display" data-status="<?php echo esc_attr($status); ?>">
    <!-- Status Header -->
    <div class="mp-ts-header" style="background-color: <?php echo esc_attr($color); ?>">
        <div class="mp-ts-status-icon">
            <?php if ($status === 'green'): ?>
                ✓
            <?php elseif ($status === 'amber'): ?>
                ⚠
            <?php else: ?>
                ✗
            <?php endif; ?>
        </div>
        <div class="mp-ts-status-text">
            <span class="mp-ts-label"><?php echo esc_html($statusLabel); ?></span>
            <?php if ($isOverridden): ?>
                <span class="mp-ts-overridden"><?php _e('Manually Verified', 'myprotector-platform'); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Description -->
    <div class="mp-ts-description">
        <?php
        if ($status === 'green') {
            echo __('This business meets all trust requirements.', 'myprotector-platform');
        } elseif ($status === 'amber') {
            echo __('This business meets some trust requirements.', 'myprotector-platform');
        } else {
            echo __('This business does not yet meet all trust requirements.', 'myprotector-platform');
        }
        ?>
    </div>

    <!-- Requirements Checklist -->
    <?php if ($show_requirements === 'true' && !empty($requirements)): ?>
        <div class="mp-ts-requirements">
            <h4 class="mp-ts-req-title"><?php _e('Trust Requirements', 'myprotector-platform'); ?></h4>
            <div class="mp-ts-req-progress">
                <span class="mp-ts-req-count"><?php printf('%d / %d', esc_html($metCount), esc_html($totalCount)); ?></span>
                <div class="mp-ts-progress-bar">
                    <div class="mp-ts-progress-fill" style="width: <?php echo esc_attr(($metCount / max(1, $totalCount)) * 100); ?>%"></div>
                </div>
            </div>
            <ul class="mp-ts-req-list">
                <?php foreach ($requirements as $key => $req): ?>
                    <?php
                    $reqLabel = $req['label'] ?? $key;
                    $reqMet = $req['met'] ?? false;
                    ?>
                    <li class="mp-ts-req-item <?php echo $reqMet ? 'met' : 'unmet'; ?>">
                        <span class="mp-ts-req-icon">
                            <?php if ($reqMet): ?>
                                ✓
                            <?php else: ?>
                                ✗
                            <?php endif; ?>
                        </span>
                        <span class="mp-ts-req-label"><?php echo esc_html($reqLabel); ?></span>
                        <?php if (isset($req['value']) && $req['value']): ?>
                            <span class="mp-ts-req-value">
                                <?php 
                                if (is_string($req['value'])) {
                                    echo '<a href="' . esc_url($req['value']) . '" target="_blank" rel="noopener">' . __('View', 'myprotector-platform') . '</a>';
                                } else {
                                    echo esc_html($req['value']);
                                }
                                ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Override Notice -->
    <?php if ($isOverridden && $overrideReason): ?>
        <div class="mp-ts-override-notice">
            <strong><?php _e('Admin Note:', 'myprotector-platform'); ?></strong>
            <?php echo esc_html($overrideReason); ?>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="mp-ts-footer">
        <span class="mp-ts-powered"><?php _e('Powered by MyProtector', 'myprotector-platform'); ?></span>
    </div>
</div>

<style>
.mp-trust-signal-display {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    max-width: 400px;
}
.mp-trust-signal-display .mp-ts-header {
    color: #fff;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}
.mp-trust-signal-display .mp-ts-status-icon {
    font-size: 32px;
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mp-trust-signal-display .mp-ts-status-text {
    display: flex;
    flex-direction: column;
}
.mp-trust-signal-display .mp-ts-label {
    font-size: 24px;
    font-weight: bold;
}
.mp-trust-signal-display .mp-ts-overridden {
    font-size: 12px;
    opacity: 0.9;
}
.mp-trust-signal-display .mp-ts-description {
    padding: 15px 20px;
    background: #f9f9f9;
    font-size: 14px;
    color: #333;
}
.mp-trust-signal-display .mp-ts-requirements {
    padding: 20px;
}
.mp-trust-signal-display .mp-ts-req-title {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
}
.mp-trust-signal-display .mp-ts-req-progress {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}
.mp-trust-signal-display .mp-ts-req-count {
    font-weight: bold;
    font-size: 14px;
}
.mp-trust-signal-display .mp-ts-progress-bar {
    flex: 1;
    height: 6px;
    background: #ddd;
    border-radius: 3px;
    overflow: hidden;
}
.mp-trust-signal-display .mp-ts-progress-fill {
    height: 100%;
    background: #28a745;
}
.mp-trust-signal-display .mp-ts-req-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.mp-trust-signal-display .mp-ts-req-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.mp-trust-signal-display .mp-ts-req-list li:last-child {
    border-bottom: none;
}
.mp-trust-signal-display .mp-ts-req-icon {
    width: 20px;
    text-align: center;
    font-size: 14px;
}
.mp-trust-signal-display .mp-ts-req-list li.met .mp-ts-req-icon { color: #28a745; }
.mp-trust-signal-display .mp-ts-req-list li.unmet .mp-ts-req-icon { color: #dc3545; }
.mp-trust-signal-display .mp-ts-req-label {
    flex: 1;
    font-size: 13px;
}
.mp-trust-signal-display .mp-ts-req-value {
    font-size: 12px;
    color: #0073aa;
}
.mp-trust-signal-display .mp-ts-override-notice {
    margin: 0 20px 20px;
    padding: 12px;
    background: #fff3cd;
    border-radius: 4px;
    font-size: 12px;
}
.mp-trust-signal-display .mp-ts-footer {
    padding: 10px 20px;
    background: #f5f5f5;
    text-align: center;
    font-size: 11px;
    color: #999;
}
</style>