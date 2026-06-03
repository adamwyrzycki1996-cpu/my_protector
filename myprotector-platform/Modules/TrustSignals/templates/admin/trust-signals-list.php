<?php
/**
 * Trust Signals Admin - List Page
 * 
 * @package MyProtector\Modules\TrustSignals\templates\admin
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap mp-trust-signals-admin">
    <h1 class="wp-heading-inline"><?php _e('Trust Signals', 'myprotector-platform'); ?></h1>
    <a href="<?php echo esc_url(add_query_arg(['page' => 'mp-trust-signals-dashboard'], admin_url('admin.php'))); ?>" class="page-title-action"><?php _e('Dashboard', 'myprotector-platform'); ?></a>
    <hr class="wp-header-end">

    <!-- Status Summary -->
    <div class="mp-trust-signals-summary">
        <div class="mp-trust-signal-card mp-signal-green">
            <div class="mp-signal-count"><?php echo esc_html($distribution['green']); ?></div>
            <div class="mp-signal-label"><?php _e('GREEN', 'myprotector-platform'); ?></div>
            <div class="mp-signal-desc"><?php _e('All requirements met', 'myprotector-platform'); ?></div>
        </div>
        <div class="mp-trust-signal-card mp-signal-amber">
            <div class="mp-signal-count"><?php echo esc_html($distribution['amber']); ?></div>
            <div class="mp-signal-label"><?php _e('AMBER', 'myprotector-platform'); ?></div>
            <div class="mp-signal-desc"><?php _e('Some requirements met', 'myprotector-platform'); ?></div>
        </div>
        <div class="mp-trust-signal-card mp-signal-red">
            <div class="mp-signal-count"><?php echo esc_html($distribution['red']); ?></div>
            <div class="mp-signal-label"><?php _e('RED', 'myprotector-platform'); ?></div>
            <div class="mp-signal-desc"><?php _e('Requirements not met', 'myprotector-platform'); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="mp-trust-signals-filters">
        <input type="hidden" name="page" value="mp-trust-signals">
        
        <select name="status" id="status-filter">
            <option value=""><?php _e('All Statuses', 'myprotector-platform'); ?></option>
            <option value="green" <?php selected($statusFilter, 'green'); ?>><?php _e('GREEN', 'myprotector-platform'); ?></option>
            <option value="amber" <?php selected($statusFilter, 'amber'); ?>><?php _e('AMBER', 'myprotector-platform'); ?></option>
            <option value="red" <?php selected($statusFilter, 'red'); ?>><?php _e('RED', 'myprotector-platform'); ?></option>
        </select>

        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search company...', 'myprotector-platform'); ?>">
        
        <?php submit_button(__('Filter', 'myprotector-platform'), 'secondary', 'filter_action', false); ?>
    </form>

    <!-- Trust Signals Table -->
    <table class="wp-list-table widefat fixed striped mp-trust-signals-table">
        <thead>
            <tr>
                <th class="column-company"><?php _e('Company', 'myprotector-platform'); ?></th>
                <th class="column-status"><?php _e('Status', 'myprotector-platform'); ?></th>
                <th class="column-requirements"><?php _e('Requirements', 'myprotector-platform'); ?></th>
                <th class="column-override"><?php _e('Override', 'myprotector-platform'); ?></th>
                <th class="column-updated"><?php _e('Updated', 'myprotector-platform'); ?></th>
                <th class="column-actions"><?php _e('Actions', 'myprotector-platform'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($signals)): ?>
                <tr>
                    <td colspan="6" class="mp-no-items">
                        <?php _e('No trust signals found.', 'myprotector-platform'); ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($signals as $signal): ?>
                    <?php
                    $requirements = json_decode($signal['requirements'] ?? '[]', true) ?: [];
                    $metCount = count(array_filter($requirements, fn($r) => $r['met'] ?? false));
                    $totalCount = count($requirements);
                    ?>
                    <tr data-company-id="<?php echo esc_attr($signal['company_id']); ?>">
                        <td class="column-company">
                            <strong>
                                <?php if ($signal['company_name']): ?>
                                    <a href="<?php echo esc_url(get_edit_post_link($signal['company_id'])); ?>">
                                        <?php echo esc_html($signal['company_name']); ?>
                                    </a>
                                <?php else: ?>
                                    <?php _e('Unknown', 'myprotector-platform'); ?>
                                <?php endif; ?>
                            </strong>
                        </td>
                        <td class="column-status">
                            <span class="mp-status-badge mp-status-<?php echo esc_attr($signal['status']); ?>">
                                <?php echo strtoupper(esc_html($signal['status'])); ?>
                            </span>
                            <?php if ($signal['is_overridden']): ?>
                                <span class="mp-override-indicator" title="<?php esc_attr_e('Manually overridden', 'myprotector-platform'); ?>">
                                    ⭐
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="column-requirements">
                            <div class="mp-requirements-progress">
                                <span class="mp-req-count"><?php echo esc_html("{$metCount}/{$totalCount}"); ?></span>
                                <div class="mp-progress-bar">
                                    <div class="mp-progress-fill" style="width: <?php echo esc_attr(($metCount / max(1, $totalCount)) * 100); ?>%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="column-override">
                            <?php if ($signal['is_overridden']): ?>
                                <span class="mp-overridden-yes">
                                    <?php _e('Yes', 'myprotector-platform'); ?>
                                    <span class="mp-override-reason" title="<?php echo esc_attr($signal['override_reason'] ?? ''); ?>">
                                        (<?php echo esc_html(wp_trim_words($signal['override_reason'] ?? '', 3)); ?>)
                                    </span>
                                </span>
                            <?php else: ?>
                                <span class="mp-overridden-no">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-updated">
                            <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signal['updated_at']))); ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small mp-view-details-btn" data-company-id="<?php echo esc_attr($signal['company_id']); ?>">
                                <?php _e('View', 'myprotector-platform'); ?>
                            </button>
                            <button type="button" class="button button-small button-primary mp-override-btn" data-company-id="<?php echo esc_attr($signal['company_id']); ?>">
                                <?php _e('Override', 'myprotector-platform'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Override Modal -->
<div id="mp-override-modal" class="mp-modal" style="display: none;">
    <div class="mp-modal-content">
        <div class="mp-modal-header">
            <h2><?php _e('Override Trust Signal', 'myprotector-platform'); ?></h2>
            <button type="button" class="mp-modal-close">&times;</button>
        </div>
        <div class="mp-modal-body">
            <input type="hidden" id="mp-override-company-id" value="">
            <p>
                <label for="mp-override-status"><?php _e('New Status:', 'myprotector-platform'); ?></label>
                <select id="mp-override-status" class="mp-override-status-select">
                    <option value="green"><?php _e('GREEN', 'myprotector-platform'); ?></option>
                    <option value="amber"><?php _e('AMBER', 'myprotector-platform'); ?></option>
                    <option value="red"><?php _e('RED', 'myprotector-platform'); ?></option>
                </select>
            </p>
            <p>
                <label for="mp-override-reason"><?php _e('Reason:', 'myprotector-platform'); ?></label>
                <textarea id="mp-override-reason" rows="4" placeholder="<?php esc_attr_e('Enter reason for override (min 10 characters)...', 'myprotector-platform'); ?>"></textarea>
                <span class="mp-reason-hint"><?php _e('Required for audit trail.', 'myprotector-platform'); ?></span>
            </p>
        </div>
        <div class="mp-modal-footer">
            <button type="button" class="button mp-modal-cancel"><?php _e('Cancel', 'myprotector-platform'); ?></button>
            <button type="button" class="button button-primary mp-modal-submit"><?php _e('Apply Override', 'myprotector-platform'); ?></button>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="mp-details-modal" class="mp-modal" style="display: none;">
    <div class="mp-modal-content mp-modal-large">
        <div class="mp-modal-header">
            <h2><?php _e('Trust Signal Details', 'myprotector-platform'); ?></h2>
            <button type="button" class="mp-modal-close">&times;</button>
        </div>
        <div class="mp-modal-body" id="mp-details-content"></div>
        <div class="mp-modal-footer">
            <button type="button" class="button mp-modal-close"><?php _e('Close', 'myprotector-platform'); ?></button>
        </div>
    </div>
</div>