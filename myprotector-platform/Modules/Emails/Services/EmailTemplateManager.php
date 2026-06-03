<?php
/**
 * MyProtector Platform - Email Template Manager Service
 * 
 * @package MyProtector\Modules\Emails\Services
 */

namespace MyProtector\Modules\Emails\Services;

class EmailTemplateManager {
    /**
     * Get template directory
     * 
     * @return string
     */
    protected function getTemplatesDir(): string {
        return MYPROTECTOR_PATH . 'Modules/Emails/templates/';
    }

    /**
     * Get all templates
     * 
     * @return array
     */
    public function getAllTemplates(): array {
        return [
            'welcome' => $this->getTemplate('welcome'),
            'password_reset' => $this->getTemplate('password_reset'),
            'review_invitation' => $this->getTemplate('review_invitation'),
            'review_confirmation' => $this->getTemplate('review_confirmation'),
            'review_published' => $this->getTemplate('review_published'),
            'business_signup' => $this->getTemplate('business_signup'),
            'business_approved' => $this->getTemplate('business_approved'),
            'commission_earned' => $this->getTemplate('commission_earned'),
            'trust_upgrade' => $this->getTemplate('trust_upgrade'),
        ];
    }

    /**
     * Get template
     * 
     * @param string $template_id
     * @return array
     */
    public function getTemplate(string $template_id): array {
        // Check database first
        $db_template = $this->getTemplateFromDb($template_id);
        
        if ($db_template) {
            return $db_template;
        }
        
        // Fall back to default template file
        $file_template = $this->getTemplateFromFile($template_id);
        
        if ($file_template) {
            return $file_template;
        }
        
        // Return default template
        return $this->getDefaultTemplate($template_id);
    }

    /**
     * Get template from database
     * 
     * @param string $template_id
     * @return array|null
     */
    protected function getTemplateFromDb(string $template_id): ?array {
        $option_key = 'mp_email_template_' . $template_id;
        $template = get_option($option_key, null);
        
        return $template ? json_decode($template, true) : null;
    }

    /**
     * Get template from file
     * 
     * @param string $template_id
     * @return array|null
     */
    protected function getTemplateFromFile(string $template_id): ?array {
        $file = $this->getTemplatesDir() . $template_id . '.php';
        
        if (!file_exists($file)) {
            return null;
        }
        
        return include $file;
    }

    /**
     * Get default template
     * 
     * @param string $template_id
     * @return array
     */
    protected function getDefaultTemplate(string $template_id): array {
        $defaults = [
            'welcome' => [
                'subject' => 'Welcome to {{site_name}}!',
                'content' => 'Hello {{user_name}},\n\nWelcome to {{site_name}}! We\'re excited to have you on board.\n\nGet started by exploring our platform and connecting with businesses.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'password_reset' => [
                'subject' => 'Reset Your Password - {{site_name}}',
                'content' => 'Hello {{user_name}},\n\nYou requested a password reset. Click the link below to reset your password:\n\n{{reset_url}}\n\nIf you didn\'t request this, please ignore this email.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'review_invitation' => [
                'subject' => 'Share Your Experience with {{business_name}}',
                'content' => 'Hello,\n\nYou recently interacted with {{business_name}}. We\'d love to hear about your experience!\n\nYour review helps other customers make informed decisions and helps businesses improve.\n\n[Write a Review Button]\n\nThank you!\nThe {{site_name}} Team',
            ],
            'review_confirmation' => [
                'subject' => 'Thank You for Your Review!',
                'content' => 'Hello {{user_name}},\n\nThank you for submitting your review of {{business_name}}!\n\nYour review is currently pending approval and will be published soon.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'review_published' => [
                'subject' => 'Your Review is Live!',
                'content' => 'Hello {{user_name}},\n\nGreat news! Your review of {{business_name}} has been published.\n\nView your review: {{review_url}}\n\nThank you for helping build trust in our community!\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'business_signup' => [
                'subject' => 'Your Business Registration - {{site_name}}',
                'content' => 'Hello,\n\nThank you for registering your business on {{site_name}}!\n\nYour business profile is now being reviewed by our team. You\'ll receive an email once it\'s approved.\n\nIn the meantime, feel free to prepare your business information.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'business_approved' => [
                'subject' => 'Congratulations! Your Business is Live',
                'content' => 'Hello,\n\nYour business profile on {{site_name}} has been approved!\n\nYour business is now visible to customers. Start collecting reviews and build your online reputation.\n\nGet started:\n- Add your business details\n- Share your profile link\n- Respond to customer reviews\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'commission_earned' => [
                'subject' => 'You Earned a Commission!',
                'content' => 'Hello,\n\nGreat news! You\'ve earned a commission of ${{amount}} from a {{type}} referral.\n\nThis commission will be added to your account and will be available for payout once approved.\n\nTrack your earnings in your reseller dashboard.\n\nBest regards,\nThe {{site_name}} Team',
            ],
            'trust_upgrade' => [
                'subject' => 'Your Trust Status Has Improved!',
                'content' => 'Hello,\n\nCongratulations! Your business trust status has been upgraded to {{new_status}}.\n\nThis improvement shows customers that your business is committed to providing excellent service.\n\nKeep up the great work!\n\nBest regards,\nThe {{site_name}} Team',
            ],
        ];
        
        return $defaults[$template_id] ?? [
            'subject' => 'Message from {{site_name}}',
            'content' => 'Hello {{user_name}},\n\nThis is an automated message from {{site_name}}.\n\nBest regards,\nThe {{site_name}} Team',
        ];
    }

    /**
     * Save template
     * 
     * @param string $template_id
     * @param array $data
     * @return bool
     */
    public function saveTemplate(string $template_id, array $data): bool {
        $option_key = 'mp_email_template_' . $template_id;
        
        $template = [
            'subject' => $data['subject'] ?? '',
            'content' => $data['content'] ?? '',
            'updated_at' => current_time('mysql'),
        ];
        
        return update_option($option_key, json_encode($template), false);
    }

    /**
     * Delete template (reset to default)
     * 
     * @param string $template_id
     * @return bool
     */
    public function deleteTemplate(string $template_id): bool {
        $option_key = 'mp_email_template_' . $template_id;
        return delete_option($option_key);
    }

    /**
     * Render preview
     * 
     * @param string $template_id
     * @return string
     */
    public function renderPreview(string $template_id): string {
        $template = $this->getTemplate($template_id);
        
        $sample_data = [
            'user_name' => 'John Doe',
            'user_email' => 'john@example.com',
            'business_name' => 'Sample Business',
            'site_name' => get_bloginfo('name'),
            'site_url' => home_url('/'),
            'reset_url' => home_url('/reset-password/'),
            'review_url' => home_url('/review/123/'),
            'amount' => '25.00',
            'type' => 'subscription',
            'new_status' => 'Shopping Safe',
        ];
        
        $subject = $this->parseTemplate($template['subject'], $sample_data);
        $content = $this->parseTemplate($template['content'], $sample_data);
        
        return $this->wrapInHtml($subject, $content);
    }

    /**
     * Parse template variables
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function parseTemplate(string $template, array $data): string {
        $parsed = $template;
        
        foreach ($data as $key => $value) {
            $parsed = str_replace('{{' . $key . '}}', $value, $parsed);
        }
        
        return $parsed;
    }

    /**
     * Wrap content in HTML
     * 
     * @param string $subject
     * @param string $content
     * @return string
     */
    protected function wrapInHtml(string $subject, string $content): string {
        ob_start();
        ?>
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: #2563eb; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                <h2 style="margin: 0; color: white;"><?php echo esc_html(get_bloginfo('name')); ?></h2>
            </div>
            <div style="background: white; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
                <h3 style="margin-top: 0;"><?php echo esc_html($subject); ?></h3>
                <?php echo wpautop(esc_html($content)); ?>
            </div>
            <div style="background: #f9fafb; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border: 1px solid #e5e7eb; border-top: none;">
                <p style="margin: 0; color: #6b7280; font-size: 14px;">
                    &copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>
                </p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}