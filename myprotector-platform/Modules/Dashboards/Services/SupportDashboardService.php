<?php
/**
 * MyProtector Platform - Support Dashboard Service
 * 
 * @package MyProtector\Modules\Dashboards\Services
 */

namespace MyProtector\Modules\Dashboards\Services;

class SupportDashboardService {
    /**
     * Get support stats
     * 
     * @param int $user_id
     * @return array
     */
    public function getStats(int $user_id): array {
        $tickets = $this->getOpenTickets();
        $resolved = $this->getResolvedTickets();
        
        return [
            'open_tickets' => count($tickets),
            'resolved_today' => $this->getResolvedTodayCount(),
            'avg_response_time' => $this->getAvgResponseTime(),
            'tickets_by_priority' => $this->getTicketsByPriority(),
            'recent_tickets' => array_slice($tickets, 0, 10),
        ];
    }

    /**
     * Get open tickets
     * 
     * @param int $limit
     * @return array
     */
    public function getOpenTickets(int $limit = 50): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT n.*, u.display_name as user_name, u.user_email 
                FROM {$wpdb->prefix}mp_notifications n 
                LEFT JOIN {$wpdb->users} u ON n.user_id = u.ID 
                WHERE n.notification_type IN ('system', 'alert') 
                AND n.priority IN ('high', 'urgent') 
                ORDER BY n.priority DESC, n.created_at DESC 
                LIMIT %d",
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get resolved tickets
     * 
     * @param int $days
     * @return array
     */
    public function getResolvedTickets(int $days = 30): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_notifications 
                WHERE notification_type = 'system' 
                AND notification_data LIKE '%%resolved%%' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
                ORDER BY created_at DESC",
                $days
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Get resolved today count
     * 
     * @return int
     */
    protected function getResolvedTodayCount(): int {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}mp_notifications 
            WHERE notification_type = 'system' 
            AND notification_data LIKE '%%resolved%%' 
            AND DATE(created_at) = CURDATE()"
        );
    }

    /**
     * Get average response time (mock - would need ticket system)
     * 
     * @return string
     */
    protected function getAvgResponseTime(): string {
        return '2h 15m';
    }

    /**
     * Get tickets by priority
     * 
     * @return array
     */
    protected function getTicketsByPriority(): array {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT priority, COUNT(*) as count 
            FROM {$wpdb->prefix}mp_notifications 
            WHERE notification_type IN ('system', 'alert') 
            GROUP BY priority",
            ARRAY_A
        );
        
        $by_priority = [];
        foreach ($results as $row) {
            $by_priority[$row['priority']] = (int) $row['count'];
        }
        
        return $by_priority;
    }

    /**
     * Search users
     * 
     * @param string $query
     * @return array
     */
    public function searchUsers(string $query): array {
        $users = get_users([
            'search' => '*' . sanitize_text_field($query) . '*',
            'number' => 20,
        ]);
        
        return array_map(function($user) {
            return [
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'registered' => $user->user_registered,
            ];
        }, $users);
    }

    /**
     * Get user tickets
     * 
     * @param int $user_id
     * @return array
     */
    public function getUserTickets(int $user_id): array {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_notifications 
                WHERE user_id = %d 
                ORDER BY created_at DESC",
                $user_id
            ),
            ARRAY_A
        ) ?: [];
    }
}