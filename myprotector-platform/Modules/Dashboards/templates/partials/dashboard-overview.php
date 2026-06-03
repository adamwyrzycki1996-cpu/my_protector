<?php
/**
 * MyProtector Platform - Dashboard Overview Partial
 * 
 * @package MyProtector\Modules\Dashboards\templates\partials
 */

if (!defined('ABSPATH')) {
    exit;
}

$dashboard_type = get_query_var('mp_dashboard_type', 'individual');
?>
<h1 class="mp-dash-page-title"><?php _e('Dashboard Overview', 'myprotector-platform'); ?></h1>

<!-- Stats Grid -->
<div class="mp-dash-stats">
    <?php if ($dashboard_type === 'individual'): ?>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('My Reviews', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['total_reviews'] ?? 0); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Notifications', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['unread_notifications'] ?? 0); ?></div>
    </div>
    <?php elseif ($dashboard_type === 'business'): ?>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Average Rating', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html(number_format($stats['avg_rating'] ?? 0, 1)); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Total Reviews', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['total_reviews'] ?? 0); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Response Rate', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html(number_format($stats['response_rate'] ?? 0, 0)); ?>%</div>
    </div>
    <?php elseif ($dashboard_type === 'reseller'): ?>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Total Referrals', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['total_referrals'] ?? 0); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Total Earnings', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value">$<?php echo esc_html(number_format($stats['total_earnings'] ?? 0, 2)); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Pending', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value">$<?php echo esc_html(number_format($stats['pending_earnings'] ?? 0, 2)); ?></div>
    </div>
    <?php elseif ($dashboard_type === 'support'): ?>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Open Tickets', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['open_tickets'] ?? 0); ?></div>
    </div>
    <div class="mp-dash-stat">
        <div class="mp-dash-stat-label"><?php _e('Resolved Today', 'myprotector-platform'); ?></div>
        <div class="mp-dash-stat-value"><?php echo esc_html($stats['resolved_today'] ?? 0); ?></div>
    </div>
    <?php endif; ?>
</div>

<!-- Trust Status (Business Only) -->
<?php if ($dashboard_type === 'business' && !empty($stats['trust_status'])): ?>
<div class="mp-dash-trust mp-dash-trust-<?php echo esc_attr($stats['trust_status']['status']); ?>">
    <span class="mp-dash-trust-icon">
        <?php
        $icons = [
            'walking' => '🚶',
            'shopping' => '🛒',
            'bad' => '⚠️',
        ];
        echo $icons[$stats['trust_status']['status']] ?? '⚠️';
        ?>
    </span>
    <div class="mp-dash-trust-info">
        <h3><?php echo ucfirst($stats['trust_status']['status']); ?> Status</h3>
        <p>Trust Score: <?php echo esc_html($stats['trust_status']['score']); ?>% | 
        <?php printf(
            _n('%d requirement met', '%d requirements met', $stats['trust_status']['requirements_fulfilled'] ?? 0, 'myprotector-platform'),
            $stats['trust_status']['requirements_fulfilled'] ?? 0
        ); ?>
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="mp-dash-card">
    <div class="mp-dash-card-header">
        <h2 class="mp-dash-card-title"><?php _e('Quick Actions', 'myprotector-platform'); ?></h2>
    </div>
    <div class="mp-dash-actions-grid">
        <?php if ($dashboard_type === 'individual'): ?>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-plus"></i>
            <?php _e('Write a Review', 'myprotector-platform'); ?>
        </a>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-user-edit"></i>
            <?php _e('Edit Profile', 'myprotector-platform'); ?>
        </a>
        <?php elseif ($dashboard_type === 'business'): ?>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-reply"></i>
            <?php _e('Respond to Reviews', 'myprotector-platform'); ?>
        </a>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-code"></i>
            <?php _e('Get Widget Code', 'myprotector-platform'); ?>
        </a>
        <?php elseif ($dashboard_type === 'reseller'): ?>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-link"></i>
            <?php _e('Copy Referral Link', 'myprotector-platform'); ?>
        </a>
        <a href="#" class="mp-dash-action-btn">
            <i class="fas fa-file-export"></i>
            <?php _e('View Reports', 'myprotector-platform'); ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Activity -->
<div class="mp-dash-card">
    <div class="mp-dash-card-header">
        <h2 class="mp-dash-card-title"><?php _e('Recent Activity', 'myprotector-platform'); ?></h2>
    </div>
    <p class="mp-dash-empty-state">
        <?php _e('No recent activity.', 'myprotector-platform'); ?>
    </p>
</div>

<style>
.mp-dash-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.mp-dash-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: var(--mp-dash-bg);
    border-radius: 8px;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s;
}

.mp-dash-action-btn:hover {
    background: var(--mp-dash-primary);
    color: white;
}

.mp-dash-action-btn i {
    font-size: 20px;
}

.mp-dash-empty-state {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}
</style>