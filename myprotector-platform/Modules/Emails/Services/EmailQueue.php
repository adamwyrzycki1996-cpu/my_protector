<?php
/**
 * MyProtector Platform - Email Queue Service
 * 
 * @package MyProtector\Modules\Emails\Services
 */

namespace MyProtector\Modules\Emails\Services;

class EmailQueue {
    /**
     * Add email to queue
     * 
     * @param string $to
     * @param string $template_id
     * @param array $data
     * @param int $delay Delay in seconds
     * @return bool
     */
    public function add(string $to, string $template_id, array $data = [], int $delay = 0): bool {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_email_queue',
            [
                'email_to' => $to,
                'email_template' => $template_id,
                'email_data' => json_encode($data),
                'scheduled_at' => date('Y-m-d H:i:s', time() + $delay),
                'status' => 'pending',
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        return $result !== false;
    }

    /**
     * Process queue
     * 
     * @param int $limit
     * @return int Number of emails processed
     */
    public function process(int $limit = 50): int {
        global $wpdb;
        
        $queue_table = $wpdb->prefix . 'mp_email_queue';
        
        // Get pending emails
        $emails = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$queue_table} 
                WHERE status = 'pending' AND scheduled_at <= %s 
                ORDER BY created_at ASC 
                LIMIT %d",
                current_time('mysql'),
                $limit
            ),
            ARRAY_A
        );
        
        $processed = 0;
        $sender = new EmailSender();
        
        foreach ($emails as $email) {
            $data = json_decode($email['email_data'], true);
            
            $result = $sender->send($email['email_to'], $email['email_template'], $data);
            
            $wpdb->update(
                $queue_table,
                [
                    'status' => $result ? 'sent' : 'failed',
                    'processed_at' => current_time('mysql'),
                    'error_message' => $result ? null : 'Send failed',
                ],
                ['id' => $email['id']],
                ['%s', '%s', '%s'],
                ['%d']
            );
            
            $processed++;
        }
        
        return $processed;
    }

    /**
     * Schedule review invitation
     * 
     * @param string $email
     * @param int $business_id
     * @param int $order_id
     * @param int $delay_days
     * @return bool
     */
    public function scheduleReviewInvitation(string $email, int $business_id, int $order_id = 0, int $delay_days = 7): bool {
        $business = get_post($business_id);
        
        return $this->add($email, 'review_invitation', [
            'business_id' => $business_id,
            'business_name' => $business ? $business->post_title : '',
            'order_id' => $order_id,
        ], $delay_days * DAY_IN_SECONDS);
    }

    /**
     * Schedule review reminder
     * 
     * @param string $email
     * @param int $business_id
     * @param int $delay_days
     * @return bool
     */
    public function scheduleReviewReminder(string $email, int $business_id, int $delay_days = 3): bool {
        $business = get_post($business_id);
        
        return $this->add($email, 'review_invitation_reminder', [
            'business_id' => $business_id,
            'business_name' => $business ? $business->post_title : '',
        ], $delay_days * DAY_IN_SECONDS);
    }

    /**
     * Get queue stats
     * 
     * @return array
     */
    public function getStats(): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_email_queue';
        
        return [
            'pending' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$table} WHERE status = 'pending'"
            ),
            'sent' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$table} WHERE status = 'sent'"
            ),
            'failed' => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$table} WHERE status = 'failed'"
            ),
        ];
    }

    /**
     * Clear failed emails
     * 
     * @return int Number of deleted rows
     */
    public function clearFailed(): int {
        global $wpdb;
        
        return $wpdb->query(
            "DELETE FROM {$wpdb->prefix}mp_email_queue WHERE status = 'failed'"
        );
    }
}