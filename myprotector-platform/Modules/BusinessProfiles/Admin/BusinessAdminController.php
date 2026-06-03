<?php
/**
 * MyProtector Platform - Business Admin Controller
 * 
 * Admin functionality for managing business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Admin
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Admin;

use MyProtector\Modules\BusinessProfiles\BusinessProfiles;
use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Services\BusinessService;
use MyProtector\Modules\BusinessProfiles\Services\BusinessVerificationService;
use MyProtector\Modules\BusinessProfiles\Services\BusinessAnalyticsService;

class BusinessAdminController {
    /**
     * Module instance
     * 
     * @var BusinessProfiles
     */
    protected $module;

    /**
     * Service instances
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Constructor
     * 
     * @param BusinessProfiles $module
     */
    public function __construct(BusinessProfiles $module) {
        $this->module = $module;
        
        $container = $module->plugin()->getContainer();
        $this->services['business'] = new BusinessService($container);
        $this->services['verification'] = new BusinessVerificationService($container);
        $this->services['analytics'] = new BusinessAnalyticsService($container);
    }

    /**
     * Render the businesses list page
     * 
     * @return void
     */
    public function renderListPage(): void {
        $stats = $this->services['analytics']->getDashboardStats();
        
        // Get businesses
        $args = [
            'status' => isset($_GET['status']) ? sanitize_text_field($_GET['status']) : null,
            'search' => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
            'limit' => 20,
            'page' => isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1,
        ];
        
        $businesses = $this->services['business']->getBusinesses($args);
        $total = $this->services['analytics']->getTotalCount();
        
        $this->render('admin/list', [
            'stats' => $stats,
            'businesses' => $businesses,
            'total' => $total,
            'current_status' => $args['status'],
        ]);
    }

    /**
     * Render the pending approval page
     * 
     * @return void
     */
    public function renderPendingPage(): void {
        $args = [
            'limit' => 20,
            'page' => isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1,
        ];
        
        $businesses = $this->services['business']->getPendingBusinesses($args);
        
        $this->render('admin/pending', [
            'businesses' => $businesses,
            'pending_count' => $this->services['analytics']->getCountByStatus(Company::STATUS_PENDING),
        ]);
    }

    /**
     * Render the add/edit business page
     * 
     * @return void
     */
    public function renderAddPage(): void {
        $company = null;
        $company_id = isset($_GET['company_id']) ? (int) $_GET['company_id'] : 0;
        
        if ($company_id) {
            $data = $this->services['business']->getBusiness($company_id);
            if ($data) {
                $company = (object) $data;
            }
        }
        
        $this->render('admin/form', [
            'company' => $company,
            'categories' => $this->getCategories(),
            'statuses' => Company::getStatuses(),
        ]);
    }

    /**
     * Render details meta box
     * 
     * @param \WP_Post $post
     * @return void
     */
    public function renderDetailsMetaBox(\WP_Post $post): void {
        $company_id = get_post_meta($post->ID, '_company_id', true);
        
        if (!$company_id) {
            echo '<p>' . __('No company linked to this post.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $company = $this->services['business']->getBusiness((int) $company_id);
        
        if (!$company) {
            echo '<p>' . __('Company not found.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $owner = get_userdata($company['user_id']);
        ?>
        <table class="widefat">
            <tr>
                <th><?php _e('Company Name', 'myprotector-platform'); ?></th>
                <td><?php echo esc_html($company['company_name']); ?></td>
            </tr>
            <tr>
                <th><?php _e('Owner', 'myprotector-platform'); ?></th>
                <td>
                    <?php if ($owner): ?>
                        <a href="<?php echo esc_url(get_edit_user_link($owner->ID)); ?>">
                            <?php echo esc_html($owner->display_name); ?>
                        </a>
                        (<?php echo esc_html($owner->user_email); ?>)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php _e('Phone', 'myprotector-platform'); ?></th>
                <td><?php echo esc_html($company['company_phone'] ?: '-'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Email', 'myprotector-platform'); ?></th>
                <td><?php echo esc_html($company['company_email'] ?: '-'); ?></td>
            </tr>
            <tr>
                <th><?php _e('Address', 'myprotector-platform'); ?></th>
                <td><?php echo nl2br(esc_html($company['company_address'] ?: '-')); ?></td>
            </tr>
            <tr>
                <th><?php _e('Created', 'myprotector-platform'); ?></th>
                <td><?php echo esc_html($company['created_at']); ?></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render URLs meta box
     * 
     * @param \WP_Post $post
     * @return void
     */
    public function renderUrlsMetaBox(\WP_Post $post): void {
        $company_id = get_post_meta($post->ID, '_company_id', true);
        
        if (!$company_id) {
            echo '<p>' . __('No company linked to this post.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $company = $this->services['business']->getBusiness((int) $company_id);
        
        if (!$company) {
            echo '<p>' . __('Company not found.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $requirements = [
            [
                'label' => __('Website', 'myprotector-platform'),
                'value' => $company['company_website'],
                'class' => 'website',
            ],
            [
                'label' => __('Insurance Provider', 'myprotector-platform'),
                'value' => $company['insurance_name'],
                'url' => $company['insurance_url'],
                'class' => 'insurance',
                'met' => !empty($company['insurance_url']),
            ],
            [
                'label' => __('Terms URL', 'myprotector-platform'),
                'value' => $company['terms_url'],
                'class' => 'terms',
                'met' => !empty($company['terms_url']),
            ],
            [
                'label' => __('Promise Page URL', 'myprotector-platform'),
                'value' => $company['promise_page_url'],
                'title' => $company['promise_page_title'],
                'class' => 'promise',
                'met' => !empty($company['promise_page_url']),
            ],
        ];
        
        foreach ($requirements as $req):
            $met_class = isset($req['met']) ? ($req['met'] ? 'mp-status-met' : 'mp-status-unmet') : '';
            ?>
            <div class="mp-url-item <?php echo esc_attr($req['class']); ?> <?php echo esc_attr($met_class); ?>">
                <strong><?php echo esc_html($req['label']); ?>:</strong>
                <?php if (!empty($req['url'])): ?>
                    <a href="<?php echo esc_url($req['url']); ?>" target="_blank">
                        <?php echo esc_html($req['value'] ?: $req['url']); ?>
                    </a>
                <?php elseif (!empty($req['value'])): ?>
                    <a href="<?php echo esc_url($req['value']); ?>" target="_blank">
                        <?php echo esc_html($req['value']); ?>
                    </a>
                <?php else: ?>
                    <span class="mp-not-set"><?php _e('Not set', 'myprotector-platform'); ?></span>
                <?php endif; ?>
                <?php if (isset($req['title']) && !empty($req['title'])): ?>
                    <br><small><?php echo esc_html($req['title']); ?></small>
                <?php endif; ?>
                <?php if (isset($req['met'])): ?>
                    <span class="mp-status-indicator">
                        <?php echo $req['met'] ? '✓' : '✗'; ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php
        endforeach;
        
        // Trust requirements progress
        $percentage = 0;
        $met_count = 0;
        foreach (['insurance', 'terms', 'promise'] as $type) {
            $key = $type . '_url';
            if (!empty($company[$key])) {
                $met_count++;
            }
        }
        $percentage = ($met_count / 3) * 100;
        ?>
        <div class="mp-trust-progress">
            <label><?php _e('Trust Requirements', 'myprotector-platform'); ?></label>
            <div class="mp-progress-bar">
                <div class="mp-progress-fill" style="width: <?php echo esc_attr($percentage); ?>%"></div>
            </div>
            <small><?php printf('%d/3 requirements met', $met_count); ?></small>
        </div>
        <?php
    }

    /**
     * Render approval meta box
     * 
     * @param \WP_Post $post
     * @return void
     */
    public function renderApprovalMetaBox(\WP_Post $post): void {
        $company_id = get_post_meta($post->ID, '_company_id', true);
        
        if (!$company_id) {
            echo '<p>' . __('No company linked to this post.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $company = $this->services['business']->getBusiness((int) $company_id);
        
        if (!$company) {
            echo '<p>' . __('Company not found.', 'myprotector-platform') . '</p>';
            return;
        }
        
        $status_class = Company::getStatusColorClass($company['status']);
        $status_label = Company::getStatusLabel($company['status']);
        
        wp_nonce_field('mp_business_approval', 'mp_business_approval_nonce');
        ?>
        <div class="mp-approval-section">
            <p>
                <label for="company_status"><?php _e('Status:', 'myprotector-platform'); ?></label>
                <select name="company_status" id="company_status" class="widefat">
                    <?php foreach (Company::getStatuses() as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($company['status'], $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <?php if ($company['status'] === Company::STATUS_REJECTED && !empty($company['rejection_reason'])): ?>
                <div class="mp-rejection-reason">
                    <strong><?php _e('Rejection Reason:', 'myprotector-platform'); ?></strong>
                    <p><?php echo esc_html($company['rejection_reason']); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($company['status'] === Company::STATUS_APPROVED): ?>
                <p>
                    <strong><?php _e('Trust Score:', 'myprotector-platform'); ?></strong>
                    <?php echo esc_html(number_format($company['trust_score'], 2)); ?>%
                </p>
            <?php endif; ?>
            
            <div class="mp-quick-actions">
                <?php if ($company['status'] === Company::STATUS_PENDING): ?>
                    <button type="button" class="button button-primary" onclick="mpApproveBusiness(<?php echo esc_attr($company['company_id']); ?>)">
                        <?php _e('Approve', 'myprotector-platform'); ?>
                    </button>
                    <button type="button" class="button" onclick="mpRejectBusiness(<?php echo esc_attr($company['company_id']); ?>)">
                        <?php _e('Reject', 'myprotector-platform'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ($company['status'] === Company::STATUS_APPROVED): ?>
                    <button type="button" class="button" onclick="mpSuspendBusiness(<?php echo esc_attr($company['company_id']); ?>)">
                        <?php _e('Suspend', 'myprotector-platform'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ($company['status'] === Company::STATUS_SUSPENDED): ?>
                    <button type="button" class="button button-primary" onclick="mpApproveBusiness(<?php echo esc_attr($company['company_id']); ?>)">
                        <?php _e('Reinstate', 'myprotector-platform'); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <script>
        function mpApproveBusiness(id) {
            if (!confirm('<?php esc_attr_e('Are you sure you want to approve this business profile?', 'myprotector-platform'); ?>')) return;
            
            jQuery.post(ajaxurl, {
                action: 'mp_approve_business',
                nonce: jQuery('#mp_business_approval_nonce').val(),
                company_id: id
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                }
            });
        }
        
        function mpRejectBusiness(id) {
            var reason = prompt('<?php esc_attr_e('Please enter a reason for rejection:', 'myprotector-platform'); ?>');
            if (reason === null) return;
            
            jQuery.post(ajaxurl, {
                action: 'mp_reject_business',
                nonce: jQuery('#mp_business_approval_nonce').val(),
                company_id: id,
                reason: reason
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                }
            });
        }
        
        function mpSuspendBusiness(id) {
            var reason = prompt('<?php esc_attr_e('Please enter a reason for suspension (optional):', 'myprotector-platform'); ?>');
            
            jQuery.post(ajaxurl, {
                action: 'mp_suspend_business',
                nonce: jQuery('#mp_business_approval_nonce').val(),
                company_id: id,
                reason: reason || ''
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                }
            });
        }
        </script>
        <?php
    }

    /**
     * Handle approve business AJAX
     * 
     * @return void
     */
    public function handleApprove(): void {
        check_ajax_referer('mp_business_approval', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Permission denied.', 'myprotector-platform')]);
        }
        
        $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
        
        if (!$company_id) {
            wp_send_json_error(['message' => __('Invalid company ID.', 'myprotector-platform')]);
        }
        
        $result = $this->services['business']->approve($company_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        // Update traffic light status
        $this->services['verification']->updateTrafficLightStatus($company_id);
        
        wp_send_json_success(['message' => __('Business profile approved!', 'myprotector-platform')]);
    }

    /**
     * Handle reject business AJAX
     * 
     * @return void
     */
    public function handleReject(): void {
        check_ajax_referer('mp_business_approval', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Permission denied.', 'myprotector-platform')]);
        }
        
        $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
        $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
        
        if (!$company_id) {
            wp_send_json_error(['message' => __('Invalid company ID.', 'myprotector-platform')]);
        }
        
        $result = $this->services['business']->reject($company_id, $reason);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Business profile rejected.', 'myprotector-platform')]);
    }

    /**
     * Handle suspend business AJAX
     * 
     * @return void
     */
    public function handleSuspend(): void {
        check_ajax_referer('mp_business_approval', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Permission denied.', 'myprotector-platform')]);
        }
        
        $company_id = isset($_POST['company_id']) ? (int) $_POST['company_id'] : 0;
        $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
        
        if (!$company_id) {
            wp_send_json_error(['message' => __('Invalid company ID.', 'myprotector-platform')]);
        }
        
        $result = $this->services['business']->suspend($company_id, $reason);
        
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        
        wp_send_json_success(['message' => __('Business profile suspended.', 'myprotector-platform')]);
    }

    /**
     * Get company categories
     * 
     * @return array
     */
    protected function getCategories(): array {
        $categories = get_terms([
            'taxonomy' => 'mp_company_category',
            'hide_empty' => false,
        ]);
        
        return is_array($categories) ? $categories : [];
    }

    /**
     * Render a template
     * 
     * @param string $template
     * @param array $data
     * @return void
     */
    protected function render(string $template, array $data = []): void {
        extract($data);
        
        $template_file = $this->module->getPath('Admin/templates/' . $template . '.php');
        
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            $this->renderDefaultTemplate($template, $data);
        }
    }

    /**
     * Render default template if file not found
     * 
     * @param string $template
     * @param array $data
     * @return void
     */
    protected function renderDefaultTemplate(string $template, array $data = []): void {
        switch ($template) {
            case 'admin/list':
                $this->renderListDefault($data);
                break;
            case 'admin/pending':
                $this->renderPendingDefault($data);
                break;
            case 'admin/form':
                $this->renderFormDefault($data);
                break;
        }
    }

    /**
     * Render list default template
     * 
     * @param array $data
     * @return void
     */
    protected function renderListDefault(array $data): void {
        extract($data);
        ?>
        <div class="wrap mp-admin-wrap">
            <h1><?php _e('Business Profiles', 'myprotector-platform'); ?></h1>
            
            <div class="mp-stats-cards">
                <div class="mp-stat-card">
                    <h3><?php _e('Total', 'myprotector-platform'); ?></h3>
                    <p class="mp-stat-number"><?php echo esc_html($stats['total_businesses']); ?></p>
                </div>
                <div class="mp-stat-card mp-stat-pending">
                    <h3><?php _e('Pending', 'myprotector-platform'); ?></h3>
                    <p class="mp-stat-number"><?php echo esc_html($stats['pending_businesses']); ?></p>
                </div>
                <div class="mp-stat-card mp-stat-approved">
                    <h3><?php _e('Approved', 'myprotector-platform'); ?></h3>
                    <p class="mp-stat-number"><?php echo esc_html($stats['approved_businesses']); ?></p>
                </div>
                <div class="mp-stat-card">
                    <h3><?php _e('Avg Trust Score', 'myprotector-platform'); ?></h3>
                    <p class="mp-stat-number"><?php echo esc_html(number_format($stats['avg_trust_score'], 1)); ?>%</p>
                </div>
            </div>
            
            <hr>
            
            <form method="get">
                <input type="hidden" name="page" value="mp-businesses">
                <p class="search-box">
                    <label for="business-search"><?php _e('Search Businesses:', 'myprotector-platform'); ?></label>
                    <input type="text" id="business-search" name="s" value="<?php echo esc_attr($search ?? ''); ?>">
                    <select name="status">
                        <option value=""><?php _e('All Status', 'myprotector-platform'); ?></option>
                        <?php foreach (Company::getStatuses() as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_status ?? '', $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button"><?php _e('Search', 'myprotector-platform'); ?></button>
                </p>
            </form>
            
            <table class="widefat fixed striped mp-businesses-table">
                <thead>
                    <tr>
                        <th width="60"><?php _e('Logo', 'myprotector-platform'); ?></th>
                        <th><?php _e('Company', 'myprotector-platform'); ?></th>
                        <th><?php _e('Status', 'myprotector-platform'); ?></th>
                        <th><?php _e('Trust Score', 'myprotector-platform'); ?></th>
                        <th><?php _e('Reviews', 'myprotector-platform'); ?></th>
                        <th><?php _e('Created', 'myprotector-platform'); ?></th>
                        <th width="120"><?php _e('Actions', 'myprotector-platform'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($businesses)): ?>
                        <tr>
                            <td colspan="7" class="mp-empty-row">
                                <?php _e('No businesses found.', 'myprotector-platform'); ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($businesses as $business): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($business['company_logo'])): ?>
                                        <img src="<?php echo esc_url($business['company_logo']); ?>" alt="" style="width:50px;height:50px;object-fit:contain;">
                                    <?php else: ?>
                                        <div class="mp-logo-placeholder"><?php echo esc_html(substr($business['company_name'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($business['company_name']); ?></strong>
                                    <?php if (!empty($business['company_website'])): ?>
                                        <br><small><a href="<?php echo esc_url($business['company_website']); ?>" target="_blank"><?php echo esc_html($business['company_website']); ?></a></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="mp-status-badge <?php echo esc_attr(Company::getStatusColorClass($business['status'])); ?>">
                                        <?php echo esc_html($business['status_label']); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(number_format($business['trust_score'], 1)); ?>%</td>
                                <td>
                                    <?php echo esc_html($business['total_reviews']); ?>
                                    <?php if ($business['avg_rating'] > 0): ?>
                                        <br><small>(<?php echo esc_html(number_format($business['avg_rating'], 1)); ?> ★)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(date_i18n('Y-m-d', strtotime($business['created_at']))); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mp-businesses-add&company_id=' . $business['company_id'])); ?>" class="button button-small">
                                        <?php _e('Edit', 'myprotector-platform'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render pending default template
     * 
     * @param array $data
     * @return void
     */
    protected function renderPendingDefault(array $data): void {
        extract($data);
        ?>
        <div class="wrap mp-admin-wrap">
            <h1><?php _e('Pending Business Profiles', 'myprotector-platform'); ?></h1>
            
            <?php if ($pending_count > 0): ?>
                <div class="mp-alert mp-alert-info">
                    <?php printf(
                        _n(
                            'There is %d business profile awaiting review.',
                            'There are %d business profiles awaiting review.',
                            $pending_count,
                            'myprotector-platform'
                        ),
                        $pending_count
                    ); ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($businesses)): ?>
                <p><?php _e('No pending business profiles.', 'myprotector-platform'); ?></p>
            <?php else: ?>
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="60"><?php _e('Logo', 'myprotector-platform'); ?></th>
                            <th><?php _e('Company', 'myprotector-platform'); ?></th>
                            <th><?php _e('Owner', 'myprotector-platform'); ?></th>
                            <th><?php _e('Submitted', 'myprotector-platform'); ?></th>
                            <th width="200"><?php _e('Actions', 'myprotector-platform'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($businesses as $business): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($business['company_logo'])): ?>
                                        <img src="<?php echo esc_url($business['company_logo']); ?>" alt="" style="width:50px;height:50px;object-fit:contain;">
                                    <?php else: ?>
                                        <div class="mp-logo-placeholder"><?php echo esc_html(substr($business['company_name'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($business['company_name']); ?></strong>
                                    <?php if (!empty($business['company_website'])): ?>
                                        <br><small><a href="<?php echo esc_url($business['company_website']); ?>" target="_blank"><?php echo esc_html($business['company_website']); ?></a></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($business['owner_name']); ?>
                                    <br><small><?php echo esc_html($business['owner_email']); ?></small>
                                </td>
                                <td><?php echo esc_html(date_i18n('Y-m-d H:i', strtotime($business['created_at']))); ?></td>
                                <td>
                                    <button type="button" class="button button-primary button-small" onclick="mpApprovePending(<?php echo esc_attr($business['company_id']); ?>)">
                                        <?php _e('Approve', 'myprotector-platform'); ?>
                                    </button>
                                    <button type="button" class="button button-small" onclick="mpRejectPending(<?php echo esc_attr($business['company_id']); ?>)">
                                        <?php _e('Reject', 'myprotector-platform'); ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <script>
            function mpApprovePending(id) {
                if (!confirm('<?php esc_attr_e('Approve this business profile?', 'myprotector-platform'); ?>')) return;
                
                jQuery.post(ajaxurl, {
                    action: 'mp_approve_business',
                    nonce: '<?php echo esc_attr(wp_create_nonce('mp_business_approval')); ?>',
                    company_id: id
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                    }
                });
            }
            
            function mpRejectPending(id) {
                var reason = prompt('<?php esc_attr_e('Reason for rejection:', 'myprotector-platform'); ?>');
                if (reason === null) return;
                
                jQuery.post(ajaxurl, {
                    action: 'mp_reject_business',
                    nonce: '<?php echo esc_attr(wp_create_nonce('mp_business_approval')); ?>',
                    company_id: id,
                    reason: reason
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                    }
                });
            }
            </script>
        </div>
        <?php
    }

    /**
     * Render form default template
     * 
     * @param array $data
     * @return void
     */
    protected function renderFormDefault(array $data): void {
        extract($data);
        $is_edit = !empty($company);
        ?>
        <div class="wrap mp-admin-wrap">
            <h1><?php echo $is_edit ? esc_html__('Edit Business Profile', 'myprotector-platform') : esc_html__('Add New Business Profile', 'myprotector-platform'); ?></h1>
            
            <form id="mp-business-form" method="post" action="">
                <?php wp_nonce_field('mp_business_form', 'mp_business_nonce'); ?>
                <input type="hidden" name="action" value="mp_save_business">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="company_id" value="<?php echo esc_attr($company->company_id); ?>">
                <?php endif; ?>
                
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="company_name"><?php _e('Company Name', 'myprotector-platform'); ?> *</label>
                                    </th>
                                    <td>
                                        <input type="text" id="company_name" name="company_name" class="regular-text" required
                                            value="<?php echo esc_attr($company->company_name ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="company_description"><?php _e('Description', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="company_description" name="company_description" rows="5" class="large-text"><?php echo esc_textarea($company->company_description ?? ''); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                            
                            <h2><?php _e('Contact Information', 'myprotector-platform'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="company_website"><?php _e('Website URL', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" id="company_website" name="company_website" class="regular-text"
                                            value="<?php echo esc_attr($company->company_website ?? ''); ?>"
                                            placeholder="https://example.com">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="company_phone"><?php _e('Phone', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="company_phone" name="company_phone" class="regular-text"
                                            value="<?php echo esc_attr($company->company_phone ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="company_email"><?php _e('Email', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="email" id="company_email" name="company_email" class="regular-text"
                                            value="<?php echo esc_attr($company->company_email ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="company_address"><?php _e('Address', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <textarea id="company_address" name="company_address" rows="3" class="large-text"><?php echo esc_textarea($company->company_address ?? ''); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                            
                            <h2><?php _e('Trust Information', 'myprotector-platform'); ?></h2>
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="insurance_name"><?php _e('Insurance Provider', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="insurance_name" name="insurance_name" class="regular-text"
                                            value="<?php echo esc_attr($company->insurance_name ?? ''); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="insurance_url"><?php _e('Insurance URL', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" id="insurance_url" name="insurance_url" class="regular-text"
                                            value="<?php echo esc_attr($company->insurance_url ?? ''); ?>"
                                            placeholder="https://">
                                        <p class="description"><?php _e('Link to your insurance policy information.', 'myprotector-platform'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="terms_url"><?php _e('Terms & Conditions URL', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" id="terms_url" name="terms_url" class="regular-text"
                                            value="<?php echo esc_attr($company->terms_url ?? ''); ?>"
                                            placeholder="https://">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="promise_page_url"><?php _e('Promise Page URL', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="url" id="promise_page_url" name="promise_page_url" class="regular-text"
                                            value="<?php echo esc_attr($company->promise_page_url ?? ''); ?>"
                                            placeholder="https://">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="promise_page_title"><?php _e('Promise Page Title', 'myprotector-platform'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="promise_page_title" name="promise_page_title" class="regular-text"
                                            value="<?php echo esc_attr($company->promise_page_title ?? ''); ?>">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div id="postbox-container-1" class="postbox-container">
                            <div id="submitdiv" class="postbox">
                                <h2 class="hndle"><span><?php _e('Publish', 'myprotector-platform'); ?></span></h2>
                                <div class="inside">
                                    <div class="submitbox" id="submitpost">
                                        <?php if ($is_edit): ?>
                                            <p>
                                                <strong><?php _e('Status:', 'myprotector-platform'); ?></strong>
                                                <select name="company_status" id="company_status" class="widefat">
                                                    <?php foreach ($statuses as $value => $label): ?>
                                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($company->status ?? '', $value); ?>>
                                                            <?php echo esc_html($label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div id="publishing-action">
                                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php echo $is_edit ? esc_attr__('Update', 'myprotector-platform') : esc_attr__('Create', 'myprotector-platform'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
}