<?php
/**
 * MyProtector Platform - Emails Module
 * 
 * Complete email system supporting 40+ templates:
 * - Review invitation
 * - New review notification
 * - Account verification
 * - Password reset
 * - Business signup
 * - And more...
 * 
 * @package MyProtector\Modules\Emails
 * @version 1.0.0
 */

namespace MyProtector\Modules\Emails;

use MyProtector\Core\Module;

class Emails extends Module {
    /**
     * Module name
     * 
     * @var string
     */
    protected $name = 'emails';

    /**
     * Email templates
     * 
     * @var array
     */
    public const TEMPLATES = [
        // User emails
        'welcome' => 'Welcome Email',
        'welcome_verified' => 'Welcome (Verified)',
        'email_verification' => 'Email Verification',
        'password_reset' => 'Password Reset',
        'password_changed' => 'Password Changed',
        'account_upgraded' => 'Account Upgraded',
        
        // Review emails
        'review_invitation' => 'Review Invitation',
        'review_invitation_reminder' => 'Review Reminder',
        'review_confirmation' => 'Review Submitted Confirmation',
        'review_published' => 'Review Published',
        'review_rejected' => 'Review Rejected',
        'review_response_received' => 'Response Received',
        
        // Business emails
        'business_signup' => 'Business Signup',
        'business_approved' => 'Business Approved',
        'business_rejected' => 'Business Rejected',
        'business_verified' => 'Business Verified',
        'new_review_business' => 'New Review Received',
        'review_needs_response' => 'Review Needs Response',
        
        // Trust updates
        'trust_upgrade' => 'Trust Status Upgraded',
        'trust_downgrade' => 'Trust Status Downgraded',
        'trust_requirements' => 'Trust Requirements Update',
        
        // Reseller emails
        'reseller_application' => 'Reseller Application',
        'reseller_approved' => 'Reseller Approved',
        'reseller_rejected' => 'Reseller Rejected',
        'commission_earned' => 'Commission Earned',
        'commission_approved' => 'Commission Approved',
        'commission_paid' => 'Commission Paid',
        'referral_signup' => 'Referral Signup',
        
        // Support emails
        'support_ticket_created' => 'Support Ticket Created',
        'support_ticket_response' => 'Support Ticket Response',
        'support_ticket_resolved' => 'Support Ticket Resolved',
        
        // Subscription emails
        'subscription_created' => 'Subscription Created',
        'subscription_renewed' => 'Subscription Renewed',
        'subscription_cancelled' => 'Subscription Cancelled',
        'subscription_expiring' => 'Subscription Expiring',
        'subscription_upgraded' => 'Subscription Upgraded',
        
        // Notification emails
        'notification' => 'General Notification',
        'reminder' => 'Reminder',
        'alert' => 'Alert',
        'system_update' => 'System Update',
        
        // Marketing emails
        'newsletter' => 'Newsletter',
        'promotion' => 'Promotion',
        'new_feature' => 'New Feature Announcement',
    ];

    /**
     * Get module directory
     * 
     * @return string
     */
    protected function getModuleDirectory(): string {
        return 'Emails';
    }

    /**
     * Boot the module
     * 
     * @return void
     */
    public function boot(): void {
        $this->registerServices();
        $this->setupEmailActions();
    }

    /**
     * Register module hooks
     * 
     * @return void
     */
    public function registerHooks(): void {
        // Email triggers
        $this->addAction('user_register', [$this, 'sendWelcomeEmail'], 10, 2);
        $this->addAction('after_password_reset', [$this, 'sendPasswordResetConfirmation']);
        
        // Admin menu
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // AJAX
        $this->addAction('wp_ajax_mp_send_test_email', [$this, 'ajaxSendTestEmail']);
        $this->addAction('wp_ajax_mp_save_email_template', [$this, 'ajaxSaveEmailTemplate']);
        $this->addAction('wp_ajax_mp_preview_email', [$this, 'ajaxPreviewEmail']);
    }

    /**
     * Register services
     * 
     * @return void
     */
    protected function registerServices(): void {
        $this->registerService('emails.sender', new Services\EmailSender());
        $this->registerService('emails.templates', new Services\EmailTemplateManager());
        $this->registerService('emails.queue', new Services\EmailQueue());
    }

    /**
     * Setup email actions
     * 
     * @return void
     */
    protected function setupEmailActions(): void {
        // Review actions
        add_action('mp_review_submitted', [$this, 'onReviewSubmitted'], 10, 2);
        add_action('mp_review_approved', [$this, 'onReviewApproved'], 10, 2);
        add_action('mp_review_rejected', [$this, 'onReviewRejected'], 10, 2);
        
        // Business actions
        add_action('mp_business_created', [$this, 'onBusinessCreated'], 10, 2);
        add_action('mp_business_approved', [$this, 'onBusinessApproved'], 10, 2);
        
        // Reseller actions
        add_action('mp_reseller_approved', [$this, 'onResellerApproved'], 10, 2);
        add_action('mp_commission_earned', [$this, 'onCommissionEarned'], 10, 2);
        
        // Trust actions
        add_action('mp_trust_updated', [$this, 'onTrustUpdated'], 10, 3);
    }

    /**
     * Add admin menu
     * 
     * @return void
     */
    public function addAdminMenu(): void {
        add_submenu_page(
            'myprotector',
            __('Email Templates', 'myprotector-platform'),
            __('Email Templates', 'myprotector-platform'),
            'manage_myprotector',
            'mp-emails',
            [$this, 'renderEmailTemplatesPage']
        );
        
        add_submenu_page(
            'myprotector',
            __('Email Logs', 'myprotector-platform'),
            __('Email Logs', 'myprotector-platform'),
            'manage_myprotector',
            'mp-email-logs',
            [$this, 'renderEmailLogsPage']
        );
    }

    /**
     * Enqueue admin assets
     * 
     * @param string $hook
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void {
        if (strpos($hook, 'mp-emails') === false) {
            return;
        }
        
        $this->enqueueStyle('emails-admin', 'css/emails-admin.css');
        $this->enqueueScript('emails-admin', 'js/emails-admin.js', ['jquery']);
        
        wp_localize_script('mp-emails-admin', 'mpEmails', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mp_emails_admin'),
        ]);
    }

    /**
     * Send welcome email
     * 
     * @param int $user_id
     * @param array $userdata
     * @return void
     */
    public function sendWelcomeEmail(int $user_id, array $userdata): void {
        $user = get_userdata($user_id);
        
        $this->getService('emails.sender')->send($user->user_email, 'welcome', [
            'user_name' => $user->display_name,
            'user_email' => $user->user_email,
            'login_url' => wp_login_url(),
        ]);
    }

    /**
     * Send password reset confirmation
     * 
     * @param \WP_User $user
     * @return void
     */
    public function sendPasswordResetConfirmation(\WP_User $user): void {
        $this->getService('emails.sender')->send($user->user_email, 'password_changed', [
            'user_name' => $user->display_name,
        ]);
    }

    /**
     * On review submitted
     * 
     * @param int $review_id
     * @param array $data
     * @return void
     */
    public function onReviewSubmitted(int $review_id, array $data): void {
        // Send confirmation to reviewer
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'review_confirmation', [
            'review_id' => $review_id,
            'business_name' => $data['business_name'] ?? '',
        ]);
        
        // Notify business owner
        if (!empty($data['business_email'])) {
            $this->getService('emails.sender')->send($data['business_email'], 'new_review_business', [
                'review_id' => $review_id,
                'business_name' => $data['business_name'] ?? '',
            ]);
        }
    }

    /**
     * On review approved
     * 
     * @param int $review_id
     * @param array $data
     * @return void
     */
    public function onReviewApproved(int $review_id, array $data): void {
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'review_published', [
            'review_id' => $review_id,
            'business_name' => $data['business_name'] ?? '',
            'review_url' => $data['review_url'] ?? '',
        ]);
    }

    /**
     * On review rejected
     * 
     * @param int $review_id
     * @param array $data
     * @return void
     */
    public function onReviewRejected(int $review_id, array $data): void {
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'review_rejected', [
            'review_id' => $review_id,
            'business_name' => $data['business_name'] ?? '',
            'reason' => $data['reason'] ?? '',
        ]);
    }

    /**
     * On business created
     * 
     * @param int $business_id
     * @param array $data
     * @return void
     */
    public function onBusinessCreated(int $business_id, array $data): void {
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'business_signup', [
            'business_id' => $business_id,
            'business_name' => $data['business_name'] ?? '',
        ]);
    }

    /**
     * On business approved
     * 
     * @param int $business_id
     * @param array $data
     * @return void
     */
    public function onBusinessApproved(int $business_id, array $data): void {
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'business_approved', [
            'business_id' => $business_id,
            'business_name' => $data['business_name'] ?? '',
        ]);
    }

    /**
     * On reseller approved
     * 
     * @param int $reseller_id
     * @param array $data
     * @return void
     */
    public function onResellerApproved(int $reseller_id, array $data): void {
        $this->getService('emails.sender')->send($data['user_email'] ?? '', 'reseller_approved', [
            'reseller_id' => $reseller_id,
            'referral_code' => $data['referral_code'] ?? '',
        ]);
    }

    /**
     * On commission earned
     * 
     * @param int $commission_id
     * @param array $data
     * @return void
     */
    public function onCommissionEarned(int $commission_id, array $data): void {
        $this->getService('emails.sender')->send($data['reseller_email'] ?? '', 'commission_earned', [
            'commission_id' => $commission_id,
            'amount' => $data['amount'] ?? 0,
            'type' => $data['type'] ?? '',
        ]);
    }

    /**
     * On trust updated
     * 
     * @param int $business_id
     * @param string $old_status
     * @param string $new_status
     * @return void
     */
    public function onTrustUpdated(int $business_id, string $old_status, string $new_status): void {
        $business = get_post($business_id);
        
        if (!$business) {
            return;
        }
        
        $user = get_userdata($business->post_author);
        
        if ($new_status === 'walking' || $new_status === 'shopping') {
            $this->getService('emails.sender')->send($user->user_email ?? '', 'trust_upgrade', [
                'business_id' => $business_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
            ]);
        } else {
            $this->getService('emails.sender')->send($user->user_email ?? '', 'trust_downgrade', [
                'business_id' => $business_id,
                'old_status' => $old_status,
                'new_status' => $new_status,
            ]);
        }
    }

    /**
     * Send review invitation
     * 
     * @param string $email
     * @param array $data
     * @return bool
     */
    public function sendReviewInvitation(string $email, array $data): bool {
        return $this->getService('emails.sender')->send($email, 'review_invitation', $data);
    }

    /**
     * Send password reset
     * 
     * @param string $email
     * @param string $reset_key
     * @return bool
     */
    public function sendPasswordReset(string $email, string $reset_key): bool {
        $user = get_user_by('email', $email);
        
        return $this->getService('emails.sender')->send($email, 'password_reset', [
            'reset_key' => $reset_key,
            'reset_url' => network_site_url("wp-login.php?action=rp&key=$reset_key&login=" . rawurlencode($user->user_login), 'login'),
            'user_name' => $user->display_name,
        ]);
    }

    /**
     * AJAX: Send test email
     * 
     * @return void
     */
    public function ajaxSendTestEmail(): void {
        check_ajax_referer('mp_emails_admin', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }
        
        $template = sanitize_text_field($_POST['template'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        
        if (empty($email)) {
            $email = get_option('admin_email');
        }
        
        $result = $this->getService('emails.sender')->send($email, $template, [
            'test' => true,
            'user_name' => 'Test User',
        ]);
        
        if ($result) {
            wp_send_json_success(['message' => __('Test email sent.', 'myprotector-platform')]);
        } else {
            wp_send_json_error(['message' => __('Failed to send test email.', 'myprotector-platform')]);
        }
    }

    /**
     * AJAX: Save email template
     * 
     * @return void
     */
    public function ajaxSaveEmailTemplate(): void {
        check_ajax_referer('mp_emails_admin', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $content = wp_kses_post($_POST['content'] ?? '');
        
        $manager = $this->getService('emails.templates');
        $result = $manager->saveTemplate($template_id, [
            'subject' => $subject,
            'content' => $content,
        ]);
        
        if ($result) {
            wp_send_json_success(['message' => __('Template saved.', 'myprotector-platform')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save template.', 'myprotector-platform')]);
        }
    }

    /**
     * AJAX: Preview email
     * 
     * @return void
     */
    public function ajaxPreviewEmail(): void {
        check_ajax_referer('mp_emails_admin', 'nonce');
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        $manager = $this->getService('emails.templates');
        $html = $manager->renderPreview($template_id);
        
        wp_send_json_success(['html' => $html]);
    }

    /**
     * Render email templates page
     * 
     * @return void
     */
    public function renderEmailTemplatesPage(): void {
        $manager = $this->getService('emails.templates');
        $templates = $manager->getAllTemplates();
        $templates_dir = $this->getPath('templates/');
        
        include $templates_dir . 'admin/emails-list.php';
    }
}