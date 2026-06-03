<?php
/**
 * MyProtector Platform - Role Management
 * 
 * Handles WordPress user role registration and capabilities
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

namespace MyProtector\Core;

/**
 * Role Manager Class
 * 
 * Registers all custom roles and their capabilities
 */
class RoleManager {
    
    /**
     * Role definitions
     */
    const ROLES = [
        'mp_individual' => [
            'name' => 'Individual',
            'description' => 'Consumer who can browse businesses and submit reviews',
            'capabilities' => [],
        ],
        'mp_business' => [
            'name' => 'Business',
            'description' => 'Business owner who manages company profile',
            'capabilities' => [],
        ],
        'mp_reseller' => [
            'name' => 'Reseller',
            'description' => 'Partner who refers businesses and earns commissions',
            'capabilities' => [],
        ],
        'mp_support' => [
            'name' => 'Customer Support',
            'description' => 'Support agent who handles tickets and user inquiries',
            'capabilities' => [],
        ],
        'mp_admin' => [
            'name' => 'MyProtector Admin',
            'description' => 'Full platform administrator',
            'capabilities' => [],
        ],
    ];

    /**
     * All capabilities organized by category
     */
    const CAPABILITIES = [
        // Core - All Users
        'core' => [
            'read' => 'Read content',
        ],
        
        // Review Capabilities
        'reviews' => [
            'mp_view_businesses' => 'View business profiles',
            'mp_search_businesses' => 'Search businesses',
            'mp_view_reviews' => 'View reviews',
            'mp_create_reviews' => 'Submit reviews',
            'mp_edit_own_reviews' => 'Edit own reviews',
            'mp_delete_own_reviews' => 'Delete own reviews',
            'mp_upload_review_images' => 'Upload images with reviews',
            'mp_mark_reviews_helpful' => 'Mark reviews as helpful',
            'mp_report_reviews' => 'Report reviews',
            'mp_share_reviews' => 'Share reviews',
        ],
        
        // Business Capabilities
        'business' => [
            'mp_claim_business' => 'Claim business profile',
            'mp_manage_own_business' => 'Manage own business',
            'mp_update_business_info' => 'Update business info',
            'mp_upload_business_logo' => 'Upload business logo',
            'mp_upload_cover_image' => 'Upload cover image',
            'mp_add_business_gallery' => 'Add gallery images',
            'mp_add_insurance_info' => 'Add insurance info',
            'mp_add_terms_url' => 'Add terms URL',
            'mp_add_promise_page' => 'Add promise page',
            'mp_view_business_reviews' => 'View business reviews',
            'mp_respond_to_reviews' => 'Respond to reviews',
            'mp_edit_responses' => 'Edit responses',
            'mp_request_review_removal' => 'Request review removal',
            'mp_view_review_analytics' => 'View review analytics',
            'mp_access_widgets' => 'Access widgets',
            'mp_generate_widget_code' => 'Generate widget code',
            'mp_invite_team_members' => 'Invite team',
            'mp_manage_team_members' => 'Manage team',
            'mp_assign_team_roles' => 'Assign team roles',
            'mp_manage_notification_settings' => 'Manage notifications',
            'mp_access_review_invitations' => 'Access invitations',
            'mp_share_business' => 'Share business',
            'mp_view_billing_history' => 'View billing',
            'mp_manage_subscription' => 'Manage subscription',
            'mp_download_invoices' => 'Download invoices',
        ],
        
        // Reseller Capabilities
        'reseller' => [
            'mp_reseller_access' => 'Access reseller features',
            'mp_create_referral_links' => 'Create referral links',
            'mp_track_referrals' => 'Track referrals',
            'mp_view_referral_analytics' => 'View referral analytics',
            'mp_export_referral_data' => 'Export referral data',
            'mp_view_earnings' => 'View earnings',
            'mp_view_pending_commissions' => 'View pending commissions',
            'mp_view_paid_commissions' => 'View paid commissions',
            'mp_request_payout' => 'Request payout',
            'mp_view_payment_history' => 'View payment history',
            'mp_access_marketing_materials' => 'Access marketing materials',
            'mp_access_banners' => 'Access banners',
            'mp_access_email_templates' => 'Access email templates',
            'mp_share_referral_links' => 'Share referral links',
            'mp_api_access' => 'API access',
            'mp_view_api_documentation' => 'View API docs',
            'mp_manage_payout_settings' => 'Manage payout settings',
            'mp_create_reseller_tickets' => 'Create support tickets',
            'mp_view_reseller_faqs' => 'View FAQs',
        ],
        
        // Support Capabilities
        'support' => [
            'mp_support_access' => 'Access support features',
            'mp_view_all_tickets' => 'View all tickets',
            'mp_respond_to_tickets' => 'Respond to tickets',
            'mp_update_ticket_status' => 'Update ticket status',
            'mp_close_tickets' => 'Close tickets',
            'mp_escalate_tickets' => 'Escalate tickets',
            'mp_merge_tickets' => 'Merge tickets',
            'mp_view_user_accounts' => 'View user accounts',
            'mp_view_user_activity' => 'View user activity',
            'mp_reset_user_passwords' => 'Reset user passwords',
            'mp_suspend_users' => 'Suspend users',
            'mp_view_user_reviews' => 'View user reviews',
            'mp_view_flagged_reviews' => 'View flagged reviews',
            'mp_flag_for_moderation' => 'Flag for moderation',
            'mp_view_pending_reviews' => 'View pending reviews',
            'mp_view_business_profiles' => 'View business profiles',
            'mp_verify_business_claims' => 'Verify claims',
            'mp_update_business_contact' => 'Update contact',
            'mp_send_user_emails' => 'Send emails',
            'mp_create_canned_responses' => 'Create canned responses',
            'mp_broadcast_notifications' => 'Broadcast notifications',
            'mp_view_ticket_reports' => 'View ticket reports',
            'mp_export_ticket_data' => 'Export ticket data',
            'mp_view_response_time_stats' => 'View response stats',
            'mp_bypass_review_timeout' => 'Bypass review timeout',
        ],
        
        // Admin Capabilities
        'admin' => [
            'manage_myprotector' => 'Full MyProtector access',
            
            // Reviews
            'mp_edit_all_reviews' => 'Edit all reviews',
            'mp_delete_all_reviews' => 'Delete all reviews',
            'mp_moderate_reviews' => 'Moderate reviews',
            'mp_feature_reviews' => 'Feature reviews',
            'mp_bulk_review_actions' => 'Bulk review actions',
            'mp_access_ai_moderation' => 'AI moderation',
            'mp_view_review_reports' => 'View review reports',
            
            // Companies
            'mp_edit_all_companies' => 'Edit all companies',
            'mp_delete_companies' => 'Delete companies',
            'mp_verify_companies' => 'Verify companies',
            'mp_approve_verification' => 'Approve verification',
            'mp_override_trust_status' => 'Override trust',
            'mp_manage_featured_companies' => 'Manage featured',
            'mp_access_company_reports' => 'Company reports',
            
            // Users
            'mp_manage_all_users' => 'Manage all users',
            'mp_ban_users' => 'Ban users',
            'mp_impersonate_users' => 'Impersonate users',
            'mp_export_user_data' => 'Export user data',
            'mp_view_user_permissions' => 'View permissions',
            
            // Resellers
            'mp_manage_resellers' => 'Manage resellers',
            'mp_approve_reseller_applications' => 'Approve applications',
            'mp_release_commissions' => 'Release commissions',
            'mp_manage_reseller_tiers' => 'Manage tiers',
            'mp_view_reseller_reports' => 'Reseller reports',
            
            // Blacklist
            'mp_manage_blacklist' => 'Manage blacklist',
            'mp_approve_blacklist_entries' => 'Approve blacklist',
            'mp_view_blacklist_reports' => 'Blacklist reports',
            
            // Support
            'mp_manage_all_tickets' => 'Manage all tickets',
            'mp_manage_sla_settings' => 'Manage SLA',
            'mp_assign_tickets' => 'Assign tickets',
            
            // Communications
            'mp_manage_email_templates' => 'Manage email templates',
            'mp_send_email_campaigns' => 'Send campaigns',
            'mp_manage_notifications' => 'Manage notifications',
            'mp_view_email_logs' => 'View email logs',
            
            // Financial
            'mp_view_financial_reports' => 'Financial reports',
            'mp_manage_invoices' => 'Manage invoices',
            'mp_process_payouts' => 'Process payouts',
            'mp_view_commission_history' => 'Commission history',
            
            // SEO
            'mp_manage_page_seo' => 'Manage page SEO',
            'mp_edit_page_content' => 'Edit page content',
            'mp_manage_sitemap' => 'Manage sitemap',
            'mp_configure_schema' => 'Configure schema',
            
            // Settings
            'mp_manage_general_settings' => 'General settings',
            'mp_manage_email_settings' => 'Email settings',
            'mp_manage_widget_settings' => 'Widget settings',
            'mp_manage_woocommerce_settings' => 'WooCommerce settings',
            'mp_manage_api_keys' => 'API keys',
            'mp_manage_security_settings' => 'Security settings',
            
            // Tools
            'mp_access_import_export' => 'Import/export',
            'mp_clear_cache' => 'Clear cache',
            'mp_view_debug_logs' => 'View debug logs',
            'mp_run_system_checks' => 'System checks',
            
            // Audit
            'mp_view_audit_log' => 'View audit log',
            'mp_export_audit_data' => 'Export audit data',
            'mp_configure_audit_settings' => 'Audit settings',
        ],
        
        // Profile & Account
        'profile' => [
            'mp_edit_profile' => 'Edit profile',
            'mp_upload_avatar' => 'Upload avatar',
            'mp_change_password' => 'Change password',
            'mp_view_notifications' => 'View notifications',
            'mp_manage_notification_prefs' => 'Manage notification prefs',
            'mp_create_support_tickets' => 'Create tickets',
            'mp_view_own_tickets' => 'View own tickets',
            'mp_export_own_data' => 'Export own data',
            'mp_delete_account' => 'Delete account',
            'mp_update_profile' => 'Update profile',
        ],
    ];

    /**
     * Register all roles
     */
    public static function registerRoles(): void {
        // Register Individual role (default for new users)
        self::registerIndividualRole();
        
        // Register other custom roles
        self::registerBusinessRole();
        self::registerResellerRole();
        self::registerSupportRole();
        self::registerAdminRole();
        
        // Add capabilities to WordPress administrator role
        self::addCapabilitiesToAdmin();
    }

    /**
     * Register Individual role
     */
    private static function registerIndividualRole(): void {
        add_role('mp_individual', 'Individual', [
            // Core
            'read' => true,
            
            // Reviews - Browse
            'mp_view_businesses' => true,
            'mp_search_businesses' => true,
            'mp_view_reviews' => true,
            
            // Reviews - Create
            'mp_create_reviews' => true,
            'mp_edit_own_reviews' => true,
            'mp_delete_own_reviews' => true,
            'mp_upload_review_images' => true,
            
            // Engagement
            'mp_mark_reviews_helpful' => true,
            'mp_report_reviews' => true,
            'mp_share_reviews' => true,
            
            // Profile
            'mp_edit_profile' => true,
            'mp_upload_avatar' => true,
            'mp_change_password' => true,
            'mp_update_profile' => true,
            
            // Notifications
            'mp_view_notifications' => true,
            'mp_manage_notification_prefs' => true,
            
            // Support
            'mp_create_support_tickets' => true,
            'mp_view_own_tickets' => true,
            
            // Data
            'mp_export_own_data' => true,
            'mp_delete_account' => true,
            
            // Business (can claim)
            'mp_claim_business' => true,
        ]);
    }

    /**
     * Register Business role
     */
    private static function registerBusinessRole(): void {
        add_role('mp_business', 'Business', [
            // Core
            'read' => true,
            
            // Business Access
            'mp_business_access' => true,
            
            // Business Profile
            'mp_manage_own_business' => true,
            'mp_update_business_info' => true,
            'mp_upload_business_logo' => true,
            'mp_upload_cover_image' => true,
            'mp_add_business_gallery' => true,
            
            // Documents
            'mp_add_insurance_info' => true,
            'mp_add_terms_url' => true,
            'mp_add_promise_page' => true,
            
            // Reviews
            'mp_view_business_reviews' => true,
            'mp_respond_to_reviews' => true,
            'mp_edit_responses' => true,
            'mp_request_review_removal' => true,
            'mp_view_review_analytics' => true,
            
            // Widgets
            'mp_access_widgets' => true,
            'mp_generate_widget_code' => true,
            
            // Team
            'mp_invite_team_members' => true,
            'mp_manage_team_members' => true,
            'mp_assign_team_roles' => true,
            
            // Notifications
            'mp_manage_notification_settings' => true,
            'mp_view_notifications' => true,
            'mp_manage_notification_prefs' => true,
            
            // Marketing
            'mp_access_review_invitations' => true,
            'mp_share_business' => true,
            
            // Financial
            'mp_view_billing_history' => true,
            'mp_manage_subscription' => true,
            'mp_download_invoices' => true,
            
            // Profile
            'mp_edit_profile' => true,
            'mp_upload_avatar' => true,
            'mp_change_password' => true,
            'mp_update_profile' => true,
            
            // Support
            'mp_create_support_tickets' => true,
            'mp_view_own_tickets' => true,
            
            // Data
            'mp_export_own_data' => true,
            
            // Reviews (limited)
            'mp_view_reviews' => true,
        ]);
    }

    /**
     * Register Reseller role
     */
    private static function registerResellerRole(): void {
        add_role('mp_reseller', 'Reseller', [
            // Core
            'read' => true,
            
            // Reseller Access
            'mp_reseller_access' => true,
            
            // Referrals
            'mp_create_referral_links' => true,
            'mp_track_referrals' => true,
            'mp_view_referral_analytics' => true,
            'mp_export_referral_data' => true,
            
            // Earnings
            'mp_view_earnings' => true,
            'mp_view_pending_commissions' => true,
            'mp_view_paid_commissions' => true,
            'mp_request_payout' => true,
            'mp_view_payment_history' => true,
            
            // Marketing
            'mp_access_marketing_materials' => true,
            'mp_access_banners' => true,
            'mp_access_email_templates' => true,
            'mp_share_referral_links' => true,
            
            // API
            'mp_api_access' => true,
            'mp_view_api_documentation' => true,
            
            // Profile
            'mp_manage_payout_settings' => true,
            'mp_update_profile' => true,
            'mp_edit_profile' => true,
            'mp_upload_avatar' => true,
            'mp_change_password' => true,
            'mp_view_notifications' => true,
            'mp_manage_notification_prefs' => true,
            
            // Support
            'mp_create_reseller_tickets' => true,
            'mp_view_reseller_faqs' => true,
            'mp_create_support_tickets' => true,
            'mp_view_own_tickets' => true,
            
            // Data
            'mp_export_own_data' => true,
            
            // Limited business access
            'mp_view_businesses' => true,
            'mp_search_businesses' => true,
        ]);
    }

    /**
     * Register Customer Support role
     */
    private static function registerSupportRole(): void {
        add_role('mp_support', 'Customer Support', [
            // Core
            'read' => true,
            
            // Support Access
            'mp_support_access' => true,
            
            // Tickets
            'mp_view_all_tickets' => true,
            'mp_respond_to_tickets' => true,
            'mp_update_ticket_status' => true,
            'mp_close_tickets' => true,
            'mp_escalate_tickets' => true,
            'mp_merge_tickets' => true,
            
            // Users
            'mp_view_user_accounts' => true,
            'mp_view_user_activity' => true,
            'mp_reset_user_passwords' => true,
            'mp_suspend_users' => true,
            'mp_view_user_reviews' => true,
            
            // Reviews (limited)
            'mp_view_flagged_reviews' => true,
            'mp_flag_for_moderation' => true,
            'mp_view_pending_reviews' => true,
            'mp_view_reviews' => true,
            'mp_view_businesses' => true,
            'mp_search_businesses' => true,
            
            // Business
            'mp_view_business_profiles' => true,
            'mp_verify_business_claims' => true,
            'mp_update_business_contact' => true,
            
            // Communication
            'mp_send_user_emails' => true,
            'mp_create_canned_responses' => true,
            'mp_broadcast_notifications' => true,
            'mp_view_notifications' => true,
            
            // Reports
            'mp_view_ticket_reports' => true,
            'mp_export_ticket_data' => true,
            'mp_view_response_time_stats' => true,
            
            // Special
            'mp_bypass_review_timeout' => true,
            
            // Profile
            'mp_edit_profile' => true,
            'mp_upload_avatar' => true,
            'mp_change_password' => true,
            'mp_update_profile' => true,
            'mp_manage_notification_prefs' => true,
            
            // Data
            'mp_export_own_data' => true,
        ]);
    }

    /**
     * Register Admin role
     */
    private static function registerAdminRole(): void {
        add_role('mp_admin', 'MyProtector Admin', [
            // Core
            'read' => true,
            
            // Master capability
            'manage_myprotector' => true,
            
            // Reviews
            'mp_edit_all_reviews' => true,
            'mp_delete_all_reviews' => true,
            'mp_moderate_reviews' => true,
            'mp_feature_reviews' => true,
            'mp_bulk_review_actions' => true,
            'mp_access_ai_moderation' => true,
            'mp_view_review_reports' => true,
            
            // Companies
            'mp_edit_all_companies' => true,
            'mp_delete_companies' => true,
            'mp_verify_companies' => true,
            'mp_approve_verification' => true,
            'mp_override_trust_status' => true,
            'mp_manage_featured_companies' => true,
            'mp_access_company_reports' => true,
            
            // Users
            'mp_manage_all_users' => true,
            'mp_ban_users' => true,
            'mp_impersonate_users' => true,
            'mp_export_user_data' => true,
            'mp_view_user_permissions' => true,
            
            // Resellers
            'mp_manage_resellers' => true,
            'mp_approve_reseller_applications' => true,
            'mp_release_commissions' => true,
            'mp_manage_reseller_tiers' => true,
            'mp_view_reseller_reports' => true,
            
            // Blacklist
            'mp_manage_blacklist' => true,
            'mp_approve_blacklist_entries' => true,
            'mp_view_blacklist_reports' => true,
            
            // Support
            'mp_manage_all_tickets' => true,
            'mp_manage_sla_settings' => true,
            'mp_assign_tickets' => true,
            
            // Communications
            'mp_manage_email_templates' => true,
            'mp_send_email_campaigns' => true,
            'mp_manage_notifications' => true,
            'mp_view_email_logs' => true,
            
            // Financial
            'mp_view_financial_reports' => true,
            'mp_manage_invoices' => true,
            'mp_process_payouts' => true,
            'mp_view_commission_history' => true,
            
            // SEO
            'mp_manage_page_seo' => true,
            'mp_edit_page_content' => true,
            'mp_manage_sitemap' => true,
            'mp_configure_schema' => true,
            
            // Settings
            'mp_manage_general_settings' => true,
            'mp_manage_email_settings' => true,
            'mp_manage_widget_settings' => true,
            'mp_manage_woocommerce_settings' => true,
            'mp_manage_api_keys' => true,
            'mp_manage_security_settings' => true,
            
            // Tools
            'mp_access_import_export' => true,
            'mp_clear_cache' => true,
            'mp_view_debug_logs' => true,
            'mp_run_system_checks' => true,
            
            // Audit
            'mp_view_audit_log' => true,
            'mp_export_audit_data' => true,
            'mp_configure_audit_settings' => true,
            
            // Everything else
            'mp_view_businesses' => true,
            'mp_search_businesses' => true,
            'mp_view_reviews' => true,
            'mp_create_reviews' => true,
            'mp_edit_profile' => true,
            'mp_upload_avatar' => true,
            'mp_change_password' => true,
            'mp_view_notifications' => true,
            'mp_manage_notification_prefs' => true,
            'mp_create_support_tickets' => true,
            'mp_view_own_tickets' => true,
            'mp_claim_business' => true,
            'mp_manage_own_business' => true,
            'mp_view_billing_history' => true,
            'mp_download_invoices' => true,
        ]);
    }

    /**
     * Add capabilities to WordPress Administrator role
     */
    public static function addCapabilitiesToAdmin(): void {
        $admin = get_role('administrator');
        
        if (!$admin) {
            return;
        }
        
        // Add all MyProtector capabilities to admin
        $capabilities = array_merge(
            self::CAPABILITIES['admin'],
            self::CAPABILITIES['support'],
            self::CAPABILITIES['reseller'],
            self::CAPABILITIES['business'],
            self::CAPABILITIES['reviews'],
            self::CAPABILITIES['profile']
        );
        
        foreach (array_keys($capabilities) as $cap) {
            $admin->add_cap($cap);
        }
    }

    /**
     * Remove all custom roles
     */
    public static function removeRoles(): void {
        $roles = ['mp_admin', 'mp_support', 'mp_business', 'mp_reseller', 'mp_individual'];
        
        foreach ($roles as $role) {
            remove_role($role);
        }
    }

    /**
     * Check if user has specific capability
     */
    public static function userHasCapability(int $user_id, string $capability): bool {
        $user = get_user_by('id', $user_id);
        
        if (!$user) {
            return false;
        }
        
        return $user->has_cap($capability);
    }

    /**
     * Check if user has any of the given capabilities
     */
    public static function userHasAnyCapability(int $user_id, array $capabilities): bool {
        foreach ($capabilities as $cap) {
            if (self::userHasCapability($user_id, $cap)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all given capabilities
     */
    public static function userHasAllCapabilities(int $user_id, array $capabilities): bool {
        foreach ($capabilities as $cap) {
            if (!self::userHasCapability($user_id, $cap)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get user role
     */
    public static function getUserRole($user): ?string {
        if (!$user || is_wp_error($user)) {
            return null;
        }
        
        $roles = $user->roles;
        
        // Check for custom roles first
        $custom_roles = ['mp_admin', 'mp_support', 'mp_business', 'mp_reseller', 'mp_individual'];
        
        foreach ($custom_roles as $role) {
            if (in_array($role, $roles)) {
                return $role;
            }
        }
        
        // Check for administrator
        if (in_array('administrator', $roles)) {
            return 'administrator';
        }
        
        return null;
    }

    /**
     * Check if user is admin level
     */
    public static function isAdmin($user): bool {
        $role = self::getUserRole($user);
        
        return in_array($role, ['administrator', 'mp_admin']);
    }

    /**
     * Check if user is support agent
     */
    public static function isSupport($user): bool {
        $role = self::getUserRole($user);
        
        return $role === 'mp_support';
    }

    /**
     * Check if user is business owner
     */
    public static function isBusiness($user): bool {
        $role = self::getUserRole($user);
        
        return $role === 'mp_business';
    }

    /**
     * Check if user is reseller
     */
    public static function isReseller($user): bool {
        $role = self::getUserRole($user);
        
        return $role === 'mp_reseller';
    }

    /**
     * Check if user is individual/consumer
     */
    public static function isIndividual($user): bool {
        $role = self::getUserRole($user);
        
        return $role === 'mp_individual';
    }

    /**
     * Get all capabilities for a role
     */
    public static function getRoleCapabilities(string $role): array {
        switch ($role) {
            case 'mp_individual':
                return array_merge(
                    self::CAPABILITIES['core'],
                    self::CAPABILITIES['reviews'],
                    self::CAPABILITIES['profile']
                );
                
            case 'mp_business':
                return array_merge(
                    self::CAPABILITIES['core'],
                    self::CAPABILITIES['reviews'],
                    self::CAPABILITIES['business'],
                    self::CAPABILITIES['profile']
                );
                
            case 'mp_reseller':
                return array_merge(
                    self::CAPABILITIES['core'],
                    self::CAPABILITIES['reseller'],
                    self::CAPABILITIES['profile']
                );
                
            case 'mp_support':
                return array_merge(
                    self::CAPABILITIES['core'],
                    self::CAPABILITIES['support'],
                    self::CAPABILITIES['profile']
                );
                
            case 'mp_admin':
            case 'administrator':
                return array_merge(
                    self::CAPABILITIES['core'],
                    self::CAPABILITIES['admin'],
                    self::CAPABILITIES['support'],
                    self::CAPABILITIES['reseller'],
                    self::CAPABILITIES['business'],
                    self::CAPABILITIES['reviews'],
                    self::CAPABILITIES['profile']
                );
                
            default:
                return [];
        }
    }

    /**
     * Get all available capabilities
     */
    public static function getAllCapabilities(): array {
        return array_merge(
            self::CAPABILITIES['core'],
            self::CAPABILITIES['reviews'],
            self::CAPABILITIES['business'],
            self::CAPABILITIES['reseller'],
            self::CAPABILITIES['support'],
            self::CAPABILITIES['admin'],
            self::CAPABILITIES['profile']
        );
    }
}