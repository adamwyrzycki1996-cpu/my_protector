<?php
/**
 * MyProtector Platform - Email Sender Service
 * 
 * @package MyProtector\Modules\Emails\Services
 */

namespace MyProtector\Modules\Emails\Services;

class EmailSender {
    /**
     * Send an email
     * 
     * @param string $to
     * @param string $template_id
     * @param array $data
     * @return bool
     */
    public function send(string $to, string $template_id, array $data = []): bool {
        if (empty($to)) {
            return false;
        }
        
        $template_manager = new EmailTemplateManager();
        $template = $template_manager->getTemplate($template_id);
        
        if (!$template) {
            return false;
        }
        
        // Parse template
        $subject = $this->parseTemplate($template['subject'], $data);
        $content = $this->parseTemplate($template['content'], $data);
        $html = $this->wrapInHtml($subject, $content);
        
        // Send email
        add_filter('wp_mail_content_type', function() { return 'text/html'; });
        
        $result = wp_mail($to, $subject, $html);
        
        remove_filter('wp_mail_content_type', function() { return 'text/html'; });
        
        // Log email
        $this->logEmail($to, $template_id, $subject, $html, $result);
        
        return $result;
    }

    /**
     * Send email via SMTP provider (e.g., SendGrid)
     * 
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param array $headers
     * @return bool
     */
    public function sendDirect(string $to, string $subject, string $html, array $headers = []): bool {
        $to = $this->formatToAddress($to);
        
        add_filter('wp_mail_content_type', function() { return 'text/html'; });
        $result = wp_mail($to, $subject, $html, $headers);
        remove_filter('wp_mail_content_type', function() { return 'text/html'; });
        
        return $result;
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
        
        // Add common variables
        $parsed = str_replace('{{site_name}}', get_bloginfo('name'), $parsed);
        $parsed = str_replace('{{site_url}}', home_url('/'), $parsed);
        $parsed = str_replace('{{date}}', date_i18n(get_option('date_format')), $parsed);
        $parsed = str_replace('{{time}}', date_i18n(get_option('time_format')), $parsed);
        
        return $parsed;
    }

    /**
     * Wrap content in HTML template
     * 
     * @param string $subject
     * @param string $content
     * @return string
     */
    protected function wrapInHtml(string $subject, string $content): string {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($subject); ?></title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4;">
                <tr>
                    <td align="center" style="padding: 40px 20px;">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <!-- Header -->
                            <tr>
                                <td style="background-color: #2563eb; padding: 24px; text-align: center;">
                                    <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                        <?php echo esc_html(get_bloginfo('name')); ?>
                                    </h1>
                                </td>
                            </tr>
                            
                            <!-- Content -->
                            <tr>
                                <td style="padding: 32px;">
                                    <?php echo wpautop($content); ?>
                                </td>
                            </tr>
                            
                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb;">
                                    <p style="margin: 0 0 8px; color: #6b7280; font-size: 14px;">
                                        <?php echo esc_html(get_bloginfo('name')); ?>
                                    </p>
                                    <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                                        This email was sent to {{user_email}}
                                    </p>
                                    <p style="margin: 16px 0 0; color: #9ca3af; font-size: 12px;">
                                        <a href="{{site_url}}" style="color: #2563eb; text-decoration: none;">Visit Website</a> |
                                        <a href="{{unsubscribe_url}}" style="color: #6b7280; text-decoration: none;">Unsubscribe</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Format to address
     * 
     * @param string $to
     * @return string
     */
    protected function formatToAddress(string $to): string {
        if (strpos($to, '<') !== false) {
            return $to;
        }
        return $to;
    }

    /**
     * Log email to database
     * 
     * @param string $to
     * @param string $template_id
     * @param string $subject
     * @param string $html
     * @param bool $success
     * @return void
     */
    protected function logEmail(string $to, string $template_id, string $subject, string $html, bool $success): void {
        global $wpdb;
        
        $user = get_user_by('email', $to);
        
        $wpdb->insert(
            $wpdb->prefix . 'mp_email_logs',
            [
                'email_id' => uniqid('email_'),
                'recipient_email' => $to,
                'recipient_name' => $user ? $user->display_name : '',
                'recipient_id' => $user ? $user->ID : null,
                'recipient_type' => $user ? $this->getUserType($user) : 'guest',
                'email_subject' => $subject,
                'email_template' => $template_id,
                'email_body_html' => $html,
                'email_type' => 'transactional',
                'email_category' => $this->getTemplateCategory($template_id),
                'send_status' => $success ? 'sent' : 'failed',
                'sent_at' => current_time('mysql'),
                'ip_address' => $this->getClientIp(),
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }

    /**
     * Get user type
     * 
     * @param \WP_User $user
     * @return string
     */
    protected function getUserType(\WP_User $user): string {
        if (in_array('administrator', $user->roles) || in_array('mp_admin', $user->roles)) {
            return 'admin';
        }
        if (in_array('mp_business', $user->roles)) {
            return 'business';
        }
        if (in_array('mp_reseller', $user->roles)) {
            return 'reseller';
        }
        return 'user';
    }

    /**
     * Get template category
     * 
     * @param string $template_id
     * @return string
     */
    protected function getTemplateCategory(string $template_id): string {
        $categories = [
            'welcome' => 'user',
            'password_reset' => 'user',
            'review_' => 'review',
            'business_' => 'business',
            'reseller_' => 'reseller',
            'commission_' => 'reseller',
            'subscription_' => 'subscription',
            'support_' => 'support',
        ];
        
        foreach ($categories as $prefix => $category) {
            if (strpos($template_id, $prefix) === 0) {
                return $category;
            }
        }
        
        return 'general';
    }

    /**
     * Get client IP
     * 
     * @return string
     */
    protected function getClientIp(): string {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}