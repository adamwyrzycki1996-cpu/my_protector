<?php
/**
 * MyProtector Platform - Business Public Controller
 * 
 * Public-facing functionality for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Public
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Public;

use MyProtector\Modules\BusinessProfiles\BusinessProfiles;
use MyProtector\Modules\BusinessProfiles\Models\Company;
use MyProtector\Modules\BusinessProfiles\Services\BusinessService;
use MyProtector\Modules\BusinessProfiles\Services\BusinessVerificationService;

class BusinessPublicController {
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
    }

    /**
     * Render business profile page
     * 
     * @param int $company_id
     * @return void
     */
    public function renderProfilePage(int $company_id): void {
        $company_data = $this->services['business']->getBusiness($company_id);
        
        if (!$company_data || $company_data['status'] !== Company::STATUS_APPROVED) {
            $this->renderNotFound();
            return;
        }
        
        $company = (object) $company_data;
        $trust_requirements = $this->services['verification']->getMissingRequirements(
            $this->createCompanyFromData($company_data)
        );
        
        $this->renderProfileTemplate($company, $trust_requirements);
    }

    /**
     * Render business profile shortcode content
     * 
     * @param array $atts
     * @return string
     */
    public function renderProfileShortcode(array $atts): string {
        $atts = shortcode_atts([
            'id' => 0,
            'style' => 'card',
            'show_description' => true,
            'show_reviews' => true,
            'show_contact' => true,
            'show_trust' => true,
        ], $atts);
        
        $company_id = (int) $atts['id'];
        
        if (!$company_id) {
            return '<p class="mp-error">' . __('Company ID required.', 'myprotector-platform') . '</p>';
        }
        
        $company_data = $this->services['business']->getBusiness($company_id);
        
        if (!$company_data || $company_data['status'] !== Company::STATUS_APPROVED) {
            return '<p class="mp-error">' . __('Business not found.', 'myprotector-platform') . '</p>';
        }
        
        ob_start();
        $this->renderProfileTemplate((object) $company_data, [], $atts);
        return ob_get_clean();
    }

    /**
     * Render business listing shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderListingShortcode(array $atts): string {
        $atts = shortcode_atts([
            'category' => '',
            'limit' => 10,
            'columns' => 3,
            'show_search' => true,
            'orderby' => 'company_name',
            'order' => 'ASC',
        ], $atts);
        
        $args = [
            'status' => Company::STATUS_APPROVED,
            'limit' => (int) $atts['limit'],
            'orderby' => sanitize_text_field($atts['orderby']),
            'order' => sanitize_text_field($atts['order']),
        ];
        
        if (!empty($atts['category'])) {
            $args['category'] = (int) $atts['category'];
        }
        
        $businesses = $this->services['business']->getBusinesses($args);
        
        ob_start();
        $this->renderListingTemplate($businesses, $atts);
        return ob_get_clean();
    }

    /**
     * Render trust badge shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustBadgeShortcode(array $atts): string {
        $atts = shortcode_atts([
            'company_id' => 0,
            'style' => 'light',
            'size' => 'medium',
        ], $atts);
        
        $company_id = (int) $atts['company_id'];
        
        if (!$company_id) {
            $user_id = get_current_user_id();
            $business = $this->services['business']->getUserBusiness();
            if ($business) {
                $company_id = $business['company_id'];
            }
        }
        
        if (!$company_id) {
            return '';
        }
        
        $company_data = $this->services['business']->getBusiness($company_id);
        
        if (!$company_data) {
            return '';
        }
        
        $company = $this->createCompanyFromData($company_data);
        $trust_status = $this->services['verification']->calculateTrustStatus($company);
        
        $colors = [
            'shopping' => '#10b981',
            'walking' => '#f59e0b',
            'bad' => '#ef4444',
        ];
        
        $labels = [
            'shopping' => __('Shopping Safe', 'myprotector-platform'),
            'walking' => __('Walking Safe', 'myprotector-platform'),
            'bad' => __('Caution', 'myprotector-platform'),
        ];
        
        $icons = [
            'shopping' => '🛒',
            'walking' => '🚶',
            'bad' => '⚠️',
        ];
        
        $color = $colors[$trust_status];
        $label = $labels[$trust_status];
        $icon = $icons[$trust_status];
        
        $sizes = [
            'small' => '16px',
            'medium' => '24px',
            'large' => '32px',
        ];
        
        $icon_size = $sizes[$atts['size']] ?? '24px';
        
        $styles = $atts['style'] === 'dark' ? 'background:#1f2937;color:#fff;' : 'background:#f3f4f6;color:#1f2937;';
        
        $html = sprintf(
            '<div class="mp-trust-badge mp-trust-badge-%s" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;%s">',
            esc_attr($trust_status),
            esc_attr($styles)
        );
        
        $html .= sprintf(
            '<span style="font-size:%s">%s</span>',
            esc_attr($icon_size),
            $icon
        );
        
        $html .= sprintf(
            '<span style="font-weight:600;font-size:14px;">%s</span>',
            esc_html($label)
        );
        
        if ($company->trust_score > 0) {
            $html .= sprintf(
                '<span style="font-size:12px;opacity:0.8;">%s%%</span>',
                esc_html(number_format($company->trust_score, 0))
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render profile form shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderProfileFormShortcode(array $atts): string {
        if (!is_user_logged_in()) {
            return '<p class="mp-login-required">' . __('Please log in to manage your business profile.', 'myprotector-platform') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $existing = $this->services['business']->getUserBusiness();
        $is_edit = !empty($existing);
        
        ob_start();
        $this->renderFormTemplate($existing, $is_edit);
        return ob_get_clean();
    }

    /**
     * Render profile template
     * 
     * @param object $company
     * @param array $missing_requirements
     * @param array $atts
     * @return void
     */
    protected function renderProfileTemplate(object $company, array $missing_requirements = [], array $atts = []): void {
        $show_description = $atts['show_description'] ?? true;
        $show_contact = $atts['show_contact'] ?? true;
        $show_trust = $atts['show_trust'] ?? true;
        
        ?>
        <div class="mp-business-profile" itemscope itemtype="https://schema.org/Organization">
            <?php if (!empty($company->company_logo)): ?>
                <div class="mp-profile-logo">
                    <img src="<?php echo esc_url($company->company_logo); ?>" alt="<?php echo esc_attr($company->company_name); ?>" itemprop="logo">
                </div>
            <?php endif; ?>
            
            <header class="mp-profile-header">
                <h1 class="mp-profile-name" itemprop="name"><?php echo esc_html($company->company_name); ?></h1>
                
                <?php if ($show_trust): ?>
                    <div class="mp-profile-trust">
                        <?php echo $this->renderTrustBadgeShortcode(['company_id' => $company->company_id]); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($company->total_reviews > 0): ?>
                    <div class="mp-profile-rating">
                        <span class="mp-stars"><?php echo str_repeat('★', (int) $company->avg_rating); ?></span>
                        <span class="mp-rating-text">
                            <?php echo esc_html(number_format($company->avg_rating, 1)); ?> 
                            (<?php printf(_n('%d review', '%d reviews', $company->total_reviews, 'myprotector-platform'), $company->total_reviews); ?>)
                        </span>
                    </div>
                <?php endif; ?>
            </header>
            
            <?php if ($show_description && !empty($company->company_description)): ?>
                <div class="mp-profile-description" itemprop="description">
                    <?php echo wp_kses_post(wpautop($company->company_description)); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($show_trust): ?>
                <div class="mp-profile-trust-info">
                    <h3><?php _e('Trust Information', 'myprotector-platform'); ?></h3>
                    
                    <div class="mp-trust-items">
                        <?php
                        $trust_items = [
                            [
                                'key' => 'insurance',
                                'label' => __('Insurance', 'myprotector-platform'),
                                'value' => $company->insurance_name,
                                'url' => $company->insurance_url,
                                'icon' => '🛡️',
                            ],
                            [
                                'key' => 'terms',
                                'label' => __('Terms & Conditions', 'myprotector-platform'),
                                'url' => $company->terms_url,
                                'icon' => '📄',
                            ],
                            [
                                'key' => 'promise',
                                'label' => __('Promise', 'myprotector-platform'),
                                'title' => $company->promise_page_title,
                                'url' => $company->promise_page_url,
                                'icon' => '✅',
                            ],
                        ];
                        
                        foreach ($trust_items as $item):
                            $is_met = !empty($item['url']);
                            $status_class = $is_met ? 'mp-trust-met' : 'mp-trust-unmet';
                            ?>
                            <div class="mp-trust-item <?php echo esc_attr($status_class); ?>" data-requirement="<?php echo esc_attr($item['key']); ?>">
                                <span class="mp-trust-icon"><?php echo esc_html($item['icon']); ?></span>
                                <span class="mp-trust-label"><?php echo esc_html($item['label']); ?>:</span>
                                <?php if ($is_met): ?>
                                    <?php if (!empty($item['value'])): ?>
                                        <span class="mp-trust-value"><?php echo esc_html($item['value']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['url'])): ?>
                                        <a href="<?php echo esc_url($item['url']); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo esc_html($item['title'] ?? __('View', 'myprotector-platform')); ?>
                                        </a>
                                    <?php endif; ?>
                                    <span class="mp-trust-status">✓</span>
                                <?php else: ?>
                                    <span class="mp-trust-notice"><?php _e('Not provided', 'myprotector-platform'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_contact): ?>
                <div class="mp-profile-contact">
                    <h3><?php _e('Contact Information', 'myprotector-platform'); ?></h3>
                    
                    <ul class="mp-contact-list">
                        <?php if (!empty($company->company_website)): ?>
                            <li>
                                <span class="mp-contact-icon">🌐</span>
                                <a href="<?php echo esc_url($company->company_website); ?>" target="_blank" rel="noopener noreferrer" itemprop="url">
                                    <?php echo esc_html($company->company_website); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($company->company_phone)): ?>
                            <li>
                                <span class="mp-contact-icon">📞</span>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $company->company_phone)); ?>" itemprop="telephone">
                                    <?php echo esc_html($company->company_phone); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($company->company_email)): ?>
                            <li>
                                <span class="mp-contact-icon">✉️</span>
                                <a href="mailto:<?php echo esc_attr($company->company_email); ?>" itemprop="email">
                                    <?php echo esc_html($company->company_email); ?>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($company->company_address)): ?>
                            <li>
                                <span class="mp-contact-icon">📍</span>
                                <span itemprop="address"><?php echo nl2br(esc_html($company->company_address)); ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render listing template
     * 
     * @param array $businesses
     * @param array $atts
     * @return void
     */
    protected function renderListingTemplate(array $businesses, array $atts): void {
        $columns = (int) $atts['columns'];
        $show_search = filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN);
        ?>
        <div class="mp-business-listing" data-columns="<?php echo esc_attr($columns); ?>">
            <?php if ($show_search): ?>
                <div class="mp-listing-search">
                    <input type="text" id="mp-business-search" placeholder="<?php esc_attr_e('Search businesses...', 'myprotector-platform'); ?>">
                </div>
            <?php endif; ?>
            
            <div class="mp-businesses-grid">
                <?php foreach ($businesses as $business): ?>
                    <div class="mp-business-card" itemscope itemtype="https://schema.org/Organization">
                        <?php if (!empty($business['company_logo'])): ?>
                            <div class="mp-card-logo">
                                <img src="<?php echo esc_url($business['company_logo']); ?>" alt="<?php echo esc_attr($business['company_name']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="mp-card-logo mp-card-logo-placeholder">
                                <?php echo esc_html(substr($business['company_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="mp-card-name" itemprop="name">
                            <a href="<?php echo esc_url(get_permalink($business['company_id'])); ?>">
                                <?php echo esc_html($business['company_name']); ?>
                            </a>
                        </h3>
                        
                        <?php if ($business['avg_rating'] > 0): ?>
                            <div class="mp-card-rating">
                                <span class="mp-stars"><?php echo str_repeat('★', (int) $business['avg_rating']); ?></span>
                                <span class="mp-rating-text">
                                    <?php echo esc_html(number_format($business['avg_rating'], 1)); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($business['trust_score'] > 0): ?>
                            <div class="mp-card-trust">
                                <?php 
                                $trust_status = $business['trust_score'] >= 100 ? 'shopping' : ($business['trust_score'] >= 66.67 ? 'walking' : 'bad');
                                $trust_labels = [
                                    'shopping' => __('Shopping Safe', 'myprotector-platform'),
                                    'walking' => __('Walking Safe', 'myprotector-platform'),
                                    'bad' => __('Caution', 'myprotector-platform'),
                                ];
                                ?>
                                <span class="mp-trust-label mp-trust-<?php echo esc_attr($trust_status); ?>">
                                    <?php echo esc_html($trust_labels[$trust_status]); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mp-card-reviews">
                            <?php printf(
                                _n('%d review', '%d reviews', $business['total_reviews'], 'myprotector-platform'),
                                $business['total_reviews']
                            ); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($businesses)): ?>
                <p class="mp-no-businesses"><?php _e('No businesses found.', 'myprotector-platform'); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render form template
     * 
     * @param array|null $existing
     * @param bool $is_edit
     * @return void
     */
    protected function renderFormTemplate(?array $existing, bool $is_edit): void {
        $form_id = 'mp-business-form-' . ($is_edit ? $existing['company_id'] : 'new');
        ?>
        <div class="mp-business-form-container">
            <h2><?php echo $is_edit ? esc_html__('Edit Your Business Profile', 'myprotector-platform') : esc_html__('Create Your Business Profile', 'myprotector-platform'); ?></h2>
            
            <?php if ($is_edit && $existing['status'] === Company::STATUS_PENDING): ?>
                <div class="mp-notice mp-notice-info">
                    <?php _e('Your profile is pending admin review. Changes will be visible once approved.', 'myprotector-platform'); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($is_edit && $existing['status'] === Company::STATUS_REJECTED): ?>
                <div class="mp-notice mp-notice-warning">
                    <?php _e('Your previous submission was rejected. Please update and resubmit.', 'myprotector-platform'); ?>
                    <?php if (!empty($existing['rejection_reason'])): ?>
                        <p><strong><?php _e('Reason:', 'myprotector-platform'); ?></strong> <?php echo esc_html($existing['rejection_reason']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form id="<?php echo esc_attr($form_id); ?>" class="mp-business-form" method="post">
                <?php wp_nonce_field('mp_business_public_form', 'mp_form_nonce'); ?>
                <input type="hidden" name="action" value="mp_submit_business_profile">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="company_id" value="<?php echo esc_attr($existing['company_id']); ?>">
                <?php endif; ?>
                
                <div class="mp-form-section">
                    <h3><?php _e('Basic Information', 'myprotector-platform'); ?></h3>
                    
                    <div class="mp-form-group">
                        <label for="company_name"><?php _e('Company Name', 'myprotector-platform'); ?> *</label>
                        <input type="text" id="company_name" name="company_name" required
                            value="<?php echo esc_attr($existing['company_name'] ?? ''); ?>"
                            class="regular-text">
                    </div>
                    
                    <div class="mp-form-group">
                        <label for="company_description"><?php _e('Description', 'myprotector-platform'); ?></label>
                        <textarea id="company_description" name="company_description" rows="4" class="widefat">
                            <?php echo esc_textarea($existing['company_description'] ?? ''); ?>
                        </textarea>
                        <p class="description"><?php _e('Tell visitors about your business.', 'myprotector-platform'); ?></p>
                    </div>
                </div>
                
                <div class="mp-form-section">
                    <h3><?php _e('Company Logo', 'myprotector-platform'); ?></h3>
                    
                    <div class="mp-logo-upload">
                        <div class="mp-logo-preview" id="mp-logo-preview">
                            <?php if (!empty($existing['company_logo'])): ?>
                                <img src="<?php echo esc_url($existing['company_logo']); ?>" alt="">
                            <?php else: ?>
                                <div class="mp-logo-placeholder">
                                    <?php esc_html_e('No logo uploaded', 'myprotector-platform'); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mp-logo-actions">
                            <input type="hidden" name="company_logo" id="mp-logo-url" value="<?php echo esc_url($existing['company_logo'] ?? ''); ?>">
                            <button type="button" class="button" id="mp-upload-logo-btn">
                                <?php echo $existing['company_logo'] ? esc_html__('Change Logo', 'myprotector-platform') : esc_html__('Upload Logo', 'myprotector-platform'); ?>
                            </button>
                            <?php if (!empty($existing['company_logo'])): ?>
                                <button type="button" class="button button-link-delete" id="mp-remove-logo-btn">
                                    <?php esc_html_e('Remove', 'myprotector-platform'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        <p class="description"><?php _e('JPG, PNG, or WebP. Max 2MB.', 'myprotector-platform'); ?></p>
                    </div>
                </div>
                
                <div class="mp-form-section">
                    <h3><?php _e('Contact Details', 'myprotector-platform'); ?></h3>
                    
                    <div class="mp-form-row">
                        <div class="mp-form-group">
                            <label for="company_website"><?php _e('Website URL', 'myprotector-platform'); ?></label>
                            <input type="url" id="company_website" name="company_website"
                                value="<?php echo esc_url($existing['company_website'] ?? ''); ?>"
                                placeholder="https://example.com" class="regular-text">
                        </div>
                        
                        <div class="mp-form-group">
                            <label for="company_phone"><?php _e('Phone Number', 'myprotector-platform'); ?></label>
                            <input type="text" id="company_phone" name="company_phone"
                                value="<?php echo esc_attr($existing['company_phone'] ?? ''); ?>" class="regular-text">
                        </div>
                    </div>
                    
                    <div class="mp-form-row">
                        <div class="mp-form-group">
                            <label for="company_email"><?php _e('Email Address', 'myprotector-platform'); ?></label>
                            <input type="email" id="company_email" name="company_email"
                                value="<?php echo esc_attr($existing['company_email'] ?? ''); ?>" class="regular-text">
                        </div>
                        
                        <div class="mp-form-group">
                            <label for="company_address"><?php _e('Physical Address', 'myprotector-platform'); ?></label>
                            <textarea id="company_address" name="company_address" rows="2" class="widefat"><?php echo esc_textarea($existing['company_address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="mp-form-section mp-form-trust">
                    <h3><?php _e('Trust Information', 'myprotector-platform'); ?></h3>
                    <p class="description"><?php _e('Add trust information to improve your trust score and help customers feel safe.', 'myprotector-platform'); ?></p>
                    
                    <div class="mp-form-row">
                        <div class="mp-form-group">
                            <label for="insurance_name"><?php _e('Insurance Provider Name', 'myprotector-platform'); ?></label>
                            <input type="text" id="insurance_name" name="insurance_name"
                                value="<?php echo esc_attr($existing['insurance_name'] ?? ''); ?>" class="regular-text">
                        </div>
                        
                        <div class="mp-form-group">
                            <label for="insurance_url"><?php _e('Insurance Information URL', 'myprotector-platform'); ?></label>
                            <input type="url" id="insurance_url" name="insurance_url"
                                value="<?php echo esc_url($existing['insurance_url'] ?? ''); ?>"
                                placeholder="https://" class="regular-text">
                        </div>
                    </div>
                    
                    <div class="mp-form-group">
                        <label for="terms_url"><?php _e('Terms & Conditions URL', 'myprotector-platform'); ?></label>
                        <input type="url" id="terms_url" name="terms_url"
                            value="<?php echo esc_url($existing['terms_url'] ?? ''); ?>"
                            placeholder="https://" class="regular-text">
                    </div>
                    
                    <div class="mp-form-row">
                        <div class="mp-form-group">
                            <label for="promise_page_url"><?php _e('Promise Page URL', 'myprotector-platform'); ?></label>
                            <input type="url" id="promise_page_url" name="promise_page_url"
                                value="<?php echo esc_url($existing['promise_page_url'] ?? ''); ?>"
                                placeholder="https://" class="regular-text">
                        </div>
                        
                        <div class="mp-form-group">
                            <label for="promise_page_title"><?php _e('Promise Page Title', 'myprotector-platform'); ?></label>
                            <input type="text" id="promise_page_title" name="promise_page_title"
                                value="<?php echo esc_attr($existing['promise_page_title'] ?? ''); ?>" class="regular-text">
                            <p class="description"><?php _e('The title of your promise page (e.g., "Our Customer Promise")', 'myprotector-platform'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="mp-form-actions">
                    <button type="submit" class="button button-primary button-large" id="mp-submit-btn">
                        <?php echo $is_edit ? esc_html__('Update Profile', 'myprotector-platform') : esc_html__('Submit for Review', 'myprotector-platform'); ?>
                    </button>
                </div>
                
                <div class="mp-form-message" id="mp-form-message"></div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var form = $('#<?php echo esc_js($form_id); ?>');
            var submitBtn = form.find('#mp-submit-btn');
            var messageDiv = form.find('#mp-form-message');
            
            // Logo upload
            var logoFrame;
            var logoUrlInput = form.find('#mp-logo-url');
            var logoPreview = form.find('#mp-logo-preview');
            
            form.on('click', '#mp-upload-logo-btn', function(e) {
                e.preventDefault();
                
                if (logoFrame) {
                    logoFrame.open();
                    return;
                }
                
                logoFrame = wp.media({
                    title: '<?php esc_attr_e('Select Logo', 'myprotector-platform'); ?>',
                    multiple: false,
                    library: { type: 'image' }
                });
                
                logoFrame.on('select', function() {
                    var attachment = logoFrame.state().get('selection').first().toJSON();
                    logoUrlInput.val(attachment.url);
                    logoPreview.html('<img src="' + attachment.url + '" alt="">');
                    form.find('#mp-remove-logo-btn').show();
                    form.find('#mp-upload-logo-btn').text('<?php esc_attr_e('Change Logo', 'myprotector-platform'); ?>');
                });
                
                logoFrame.open();
            });
            
            form.on('click', '#mp-remove-logo-btn', function(e) {
                e.preventDefault();
                logoUrlInput.val('');
                logoPreview.html('<div class="mp-logo-placeholder"><?php esc_attr_e('No logo uploaded', 'myprotector-platform'); ?></div>');
                $(this).hide();
                form.find('#mp-upload-logo-btn').text('<?php esc_attr_e('Upload Logo', 'myprotector-platform'); ?>');
            });
            
            // Form submission
            form.on('submit', function(e) {
                e.preventDefault();
                
                submitBtn.prop('disabled', true).text('<?php esc_attr_e('Submitting...', 'myprotector-platform'); ?>');
                messageDiv.removeClass('mp-success mp-error').html('');
                
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: 'POST',
                    data: {
                        action: '<?php echo $is_edit ? 'mp_update_business_profile' : 'mp_submit_business_profile'; ?>',
                        nonce: form.find('#mp_form_nonce').val(),
                        company_id: form.find('input[name="company_id"]').val() || 0,
                        company_name: form.find('#company_name').val(),
                        company_description: form.find('#company_description').val(),
                        company_logo: logoUrlInput.val(),
                        company_website: form.find('#company_website').val(),
                        company_phone: form.find('#company_phone').val(),
                        company_email: form.find('#company_email').val(),
                        company_address: form.find('#company_address').val(),
                        insurance_name: form.find('#insurance_name').val(),
                        insurance_url: form.find('#insurance_url').val(),
                        terms_url: form.find('#terms_url').val(),
                        promise_page_url: form.find('#promise_page_url').val(),
                        promise_page_title: form.find('#promise_page_title').val(),
                    },
                    success: function(response) {
                        if (response.success) {
                            messageDiv.addClass('mp-success').html(response.data.message || '<?php esc_attr_e('Success!', 'myprotector-platform'); ?>');
                            if (!<?php echo $is_edit ? 'true' : 'false'; ?>) {
                                form[0].reset();
                                logoUrlInput.val('');
                                logoPreview.html('<div class="mp-logo-placeholder"><?php esc_attr_e('No logo uploaded', 'myprotector-platform'); ?></div>');
                            }
                        } else {
                            messageDiv.addClass('mp-error').html(response.data.message || '<?php esc_attr_e('An error occurred.', 'myprotector-platform'); ?>');
                        }
                    },
                    error: function() {
                        messageDiv.addClass('mp-error').html('<?php esc_attr_e('An error occurred. Please try again.', 'myprotector-platform'); ?>');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text('<?php echo $is_edit ? esc_attr__('Update Profile', 'myprotector-platform') : esc_attr__('Submit for Review', 'myprotector-platform'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render not found page
     * 
     * @return void
     */
    protected function renderNotFound(): void {
        ?>
        <div class="mp-business-not-found">
            <h2><?php _e('Business Not Found', 'myprotector-platform'); ?></h2>
            <p><?php _e('The business profile you are looking for could not be found.', 'myprotector-platform'); ?></p>
            <a href="<?php echo esc_url(home_url()); ?>" class="button">
                <?php _e('Go Home', 'myprotector-platform'); ?>
            </a>
        </div>
        <?php
    }

    /**
     * Create Company model from data array
     * 
     * @param array $data
     * @return Company
     */
    protected function createCompanyFromData(array $data): Company {
        $row = (object) $data;
        return Company::fromRow($row);
    }
}