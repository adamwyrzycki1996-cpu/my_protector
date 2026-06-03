<?php
/**
 * Trust Signals Admin - Dashboard
 * 
 * @package MyProtector\Modules\TrustSignals\templates\admin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap mp-trust-signals-dashboard">
    <h1><?php _e('Trust Signals Dashboard', 'myprotector-platform'); ?></h1>
    <a href="<?php echo esc_url(add_query_arg(['page' => 'mp-trust-signals'], admin_url('admin.php'))); ?>" class="page-title-action"><?php _e('View All', 'myprotector-platform'); ?></a>
    <hr class="wp-header-end">

    <!-- Overview Cards -->
    <div class="mp-dashboard-cards">
        <div class="mp-dashboard-card mp-card-green">
            <div class="mp-card-icon dashicons dashicons-yes-alt"></div>
            <div class="mp-card-content">
                <div class="mp-card-number"><?php echo esc_html($distribution['green']); ?></div>
                <div class="mp-card-label"><?php _e('GREEN Status', 'myprotector-platform'); ?></div>
            </div>
        </div>
        <div class="mp-dashboard-card mp-card-amber">
            <div class="mp-card-icon dashicons dashicons-warning"></div>
            <div class="mp-card-content">
                <div class="mp-card-number"><?php echo esc_html($distribution['amber']); ?></div>
                <div class="mp-card-label"><?php _e('AMBER Status', 'myprotector-platform'); ?></div>
            </div>
        </div>
        <div class="mp-dashboard-card mp-card-red">
            <div class="mp-card-icon dashicons dashicons-dismiss"></div>
            <div class="mp-card-content">
                <div class="mp-card-number"><?php echo esc_html($distribution['red']); ?></div>
                <div class="mp-card-label"><?php _e('RED Status', 'myprotector-platform'); ?></div>
            </div>
        </div>
        <div class="mp-dashboard-card mp-card-total">
            <div class="mp-card-icon dashicons dashicons-building"></div>
            <div class="mp-card-content">
                <div class="mp-card-number"><?php echo esc_html($distribution['total']); ?></div>
                <div class="mp-card-label"><?php _e('Total Companies', 'myprotector-platform'); ?></div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="mp-dashboard-stats">
        <div class="mp-stat-box">
            <h3><?php _e('Override Statistics', 'myprotector-platform'); ?></h3>
            <p><?php printf(__('Manually overridden: %d (%.1f%%)', 'myprotector-platform'), $overrideStats['overridden'], $overrideStats['override_rate']); ?></p>
            <p><?php printf(__('Automatically calculated: %d', 'myprotector-platform'), $overrideStats['automatic']); ?></p>
        </div>
        <div class="mp-stat-box">
            <h3><?php _e('Green Requirements', 'myprotector-platform'); ?></h3>
            <ul>
                <li><?php _e('Insurance Page', 'myprotector-platform'); ?></li>
                <li><?php _e('Refund History', 'myprotector-platform'); ?></li>
                <li><?php _e('Claims Page', 'myprotector-platform'); ?></li>
                <li><?php _e('Terms Page', 'myprotector-platform'); ?></li>
                <li><?php _e('Active Subscription', 'myprotector-platform'); ?></li>
            </ul>
        </div>
    </div>

    <!-- Attention Required -->
    <?php if (!empty($attentionRequired)): ?>
        <div class="mp-attention-section">
            <h2><?php _e('Companies Needing Attention', 'myprotector-platform'); ?></h2>
            <p class="description"><?php _e('RED status companies with active subscriptions:', 'myprotector-platform'); ?></p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Company', 'myprotector-platform'); ?></th>
                        <th><?php _e('Current Status', 'myprotector-platform'); ?></th>
                        <th><?php _e('Action', 'myprotector-platform'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attentionRequired as $company): ?>
                        <tr>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['company_id' => $company['company_id']], admin_url('admin.php?page=mp-trust-signals-edit'))); ?>">
                                    <?php echo esc_html($company['company_name']); ?>
                                </a>
                            </td>
                            <td><span class="mp-status-badge mp-status-red">RED</span></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['company_id' => $company['company_id']], admin_url('admin.php?page=mp-trust-signals-edit'))); ?>" class="button button-small">
                                    <?php _e('Review', 'myprotector-platform'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.mp-trust-signals-dashboard .mp-dashboard-cards { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
.mp-trust-signals-dashboard .mp-dashboard-card { 
    background: #fff; 
    border: 1px solid #ddd; 
    border-radius: 8px; 
    padding: 20px; 
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 200px;
}
.mp-trust-signals-dashboard .mp-card-icon { font-size: 32px; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.mp-trust-signals-dashboard .mp-card-number { font-size: 28px; font-weight: bold; }
.mp-trust-signals-dashboard .mp-card-label { color: #666; font-size: 14px; }
.mp-trust-signals-dashboard .mp-card-green .mp-card-icon { color: #28a745; }
.mp-trust-signals-dashboard .mp-card-amber .mp-card-icon { color: #ffc107; }
.mp-trust-signals-dashboard .mp-card-red .mp-card-icon { color: #dc3545; }
.mp-trust-signals-dashboard .mp-card-total .mp-card-icon { color: #0073aa; }

.mp-trust-signals-dashboard .mp-dashboard-stats { display: flex; gap: 20px; margin: 20px 0; }
.mp-trust-signals-dashboard .mp-stat-box { 
    background: #fff; 
    border: 1px solid #ddd; 
    border-radius: 8px; 
    padding: 20px; 
    flex: 1;
}
.mp-trust-signals-dashboard .mp-stat-box h3 { margin-top: 0; }
.mp-trust-signals-dashboard .mp-stat-box ul { margin: 0; padding-left: 20px; }

.mp-trust-signals-dashboard .mp-attention-section { margin-top: 30px; }
.mp-trust-signals-dashboard .mp-status-badge { 
    display: inline-block; 
    padding: 2px 8px; 
    border-radius: 4px; 
    font-size: 11px; 
    font-weight: bold; 
    color: #fff;
}
.mp-trust-signals-dashboard .mp-status-red { background: #dc3545; }
</style>