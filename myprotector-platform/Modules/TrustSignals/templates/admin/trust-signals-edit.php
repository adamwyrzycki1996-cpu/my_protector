<?php
/**
 * Trust Signals Admin - Edit/Override Page
 * 
 * @package MyProtector\Modules\TrustSignals\templates\admin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap mp-trust-signals-edit">
    <h1>
        <?php _e('Trust Signal:', 'myprotector-platform'); ?> 
        <?php echo esc_html($signal['company_name'] ?? __('Unknown Company', 'myprotector-platform')); ?>
    </h1>
    
    <hr class="wp-header-end">

    <div class="mp-edit-container">
        <!-- Current Status -->
        <div class="mp-status-panel" style="border-color: <?php echo esc_attr($statusInfo['color']); ?>">
            <h2><?php _e('Current Status', 'myprotector-platform'); ?></h2>
            <div class="mp-current-status" style="background: <?php echo esc_attr($statusInfo['color']); ?>">
                <?php echo esc_html(strtoupper($signal['status'])); ?>
            </div>
            <p><?php echo esc_html($statusInfo['description']); ?></p>
            
            <?php if ($signal['is_overridden']): ?>
                <div class="mp-override-notice">
                    <strong><?php _e('Manually Overridden', 'myprotector-platform'); ?></strong>
                    <p><?php echo esc_html($signal['override_reason'] ?? ''); ?></p>
                    <p>
                        <?php 
                        printf(
                            __('By: %s on %s', 'myprotector-platform'),
                            esc_html($signal['overridden_by'] ? get_userdata($signal['overridden_by'])->display_name : 'Unknown'),
                            esc_html(wp_date(get_option('date_format'), strtotime($signal['overridden_at'])))
                        );
                        ?>
                    </p>
                    <button type="button" class="button mp-clear-override-btn" data-company-id="<?php echo esc_attr($signal['company_id']); ?>">
                        <?php _e('Clear Override & Recalculate', 'myprotector-platform'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Requirements Checklist -->
        <div class="mp-requirements-panel">
            <h2><?php _e('Requirements Checklist', 'myprotector-platform'); ?></h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th width="30">Status</th>
                        <th>Requirement</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $reqData = $signal['requirements_data'] ?? [];
                    foreach ($reqData as $key => $req): 
                    ?>
                        <tr>
                            <td>
                                <?php if ($req['met']): ?>
                                    <span style="color: #28a745; font-size: 20px;">✓</span>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-size: 20px;">✗</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo esc_html($req['label'] ?? $key); ?></strong></td>
                            <td><?php echo esc_html($req['description'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Manual Override Form -->
        <div class="mp-override-form-panel">
            <h2><?php _e('Manual Override', 'myprotector-platform'); ?></h2>
            <form id="mp-manual-override-form">
                <input type="hidden" name="company_id" value="<?php echo esc_attr($signal['company_id']); ?>">
                <input type="hidden" name="action" value="mp_override_trust_signal">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('mp_trust_signals_admin')); ?>">
                
                <p>
                    <label for="override_status"><?php _e('Set Status To:', 'myprotector-platform'); ?></label>
                    <select name="status" id="override_status">
                        <option value="green"><?php _e('GREEN - All requirements met', 'myprotector-platform'); ?></option>
                        <option value="amber"><?php _e('AMBER - Some requirements met', 'myprotector-platform'); ?></option>
                        <option value="red"><?php _e('RED - Requirements not met', 'myprotector-platform'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="override_reason"><?php _e('Reason (required):', 'myprotector-platform'); ?></label>
                    <textarea name="reason" id="override_reason" rows="4" required></textarea>
                </p>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Apply Override', 'myprotector-platform'); ?></button>
                    <button type="button" class="button mp-recalculate-btn" data-company-id="<?php echo esc_attr($signal['company_id']); ?>">
                        <?php _e('Recalculate Automatically', 'myprotector-platform'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- History -->
        <div class="mp-history-panel">
            <h2><?php _e('Change History', 'myprotector-platform'); ?></h2>
            <?php if (empty($history)): ?>
                <p><?php _e('No changes recorded yet.', 'myprotector-platform'); ?></p>
            <?php else: ?>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'myprotector-platform'); ?></th>
                            <th><?php _e('From', 'myprotector-platform'); ?></th>
                            <th><?php _e('To', 'myprotector-platform'); ?></th>
                            <th><?php _e('Reason', 'myprotector-platform'); ?></th>
                            <th><?php _e('By', 'myprotector-platform'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $entry): ?>
                            <tr>
                                <td><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['created_at']))); ?></td>
                                <td><span class="mp-status-badge mp-status-<?php echo esc_attr($entry['old_status']); ?>"><?php echo esc_html(strtoupper($entry['old_status'])); ?></span></td>
                                <td><span class="mp-status-badge mp-status-<?php echo esc_attr($entry['new_status']); ?>"><?php echo esc_html(strtoupper($entry['new_status'])); ?></span></td>
                                <td><?php echo esc_html($entry['change_reason']); ?></td>
                                <td>
                                    <?php if ($entry['admin_name']): ?>
                                        <?php echo esc_html($entry['admin_name']); ?>
                                    <?php else: ?>
                                        <?php _e('System', 'myprotector-platform'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.mp-trust-signals-edit .mp-edit-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.mp-trust-signals-edit .mp-status-panel { background: #fff; padding: 20px; border: 3px solid #ddd; border-radius: 8px; }
.mp-trust-signals-edit .mp-current-status { color: #fff; font-size: 48px; font-weight: bold; text-align: center; padding: 30px; border-radius: 8px; margin: 15px 0; }
.mp-trust-signals-edit .mp-override-notice { background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 15px; }
.mp-trust-signals-edit .mp-requirements-panel { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
.mp-trust-signals-edit .mp-override-form-panel { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
.mp-trust-signals-edit .mp-history-panel { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; grid-column: span 2; }
.mp-trust-signals-edit .mp-status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
.mp-trust-signals-edit .mp-status-green { background: #28a745; color: #fff; }
.mp-trust-signals-edit .mp-status-amber { background: #ffc107; color: #000; }
.mp-trust-signals-edit .mp-status-red { background: #dc3545; color: #fff; }
</style>