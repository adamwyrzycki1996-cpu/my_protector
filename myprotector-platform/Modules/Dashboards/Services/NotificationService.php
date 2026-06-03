<?php
/**
 * MyProtector Platform - Notification Service
 * 
 * @package MyProtector\Modules\Dashboards\Services
 */

namespace MyProtector\Modules\Dashboards\Services;

class NotificationService {
    /**
     * Get user notifications
     * 
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getUserNotifications(int $user_id, int $limit = 20): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_notifications';
        
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
                $user_id,
                $limit
            ),
            ARRAY_A
        ) ?: [];
        
        $unread_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE user_id = %d AND is_read = 0",
                $user_id
            )
        );
        
        return [
            'items' => $items,
            'unread_count' => $unread_count,
        ];
    }

    /**
     * Mark notification as read
     * 
     * @param int $notification_id
     * @param int $user_id
     * @return bool
     */
    public function markAsRead(int $notification_id, int $user_id): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_notifications',
            [
                'is_read' => 1,
                'read_at' => current_time('mysql'),
            ],
            [
                'notification_id' => $notification_id,
                'user_id' => $user_id,
            ],
            ['%d', '%s'],
            ['%d', '%d']
        );
        
        return $result !== false;
    }

    /**
     * Mark all notifications as read
     * 
     * @param int $user_id
     * @return bool
     */
    public function markAllAsRead(int $user_id): bool {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->prefix . 'mp_notifications',
            [
                'is_read' => 1,
                'read_at' => current_time('mysql'),
            ],
            ['user_id' => $user_id],
            ['%d', '%s'],
            ['%d']
        );
        
        return $result !== false;
    }

    /**
     * Create notification
     * 
     * @param array $data
     * @return int|false
     */
    public function create(array $data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'mp_notifications',
            [
                'user_id' => $data['user_id'],
                'notification_type' => $data['type'] ?? 'system',
                'notification_title' => sanitize_text_field($data['title']),
                'notification_message' => sanitize_textarea_field($data['message']),
                'notification_data' => isset($data['data']) ? json_encode($data['data']) : null,
                'related_type' => $data['related_type'] ?? null,
                'related_id' => $data['related_id'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'action_url' => $data['action_url'] ?? null,
                'action_label' => $data['action_label'] ?? null,
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete old notifications
     * 
     * @param int $days
     * @return int Number of deleted rows
     */
    public function deleteOld(int $days = 90): int {
        global $wpdb;
        
        return $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}mp_notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY) 
                AND is_read = 1",
                $days
            )
        );
    }
}