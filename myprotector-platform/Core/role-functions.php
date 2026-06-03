<?php
/**
 * MyProtector Platform - Role Installer
 * 
 * Standalone role registration for easy integration
 * 
 * @package MyProtector\Core
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Install Roles
 * Call this function on plugin activation
 */
function mp_install_roles(): void {
    require_once __DIR__ . '/RoleManager.php';
    \MyProtector\Core\RoleManager::registerRoles();
}

/**
 * Uninstall Roles
 * Call this function on plugin uninstall (if option selected)
 */
function mp_uninstall_roles(): void {
    require_once __DIR__ . '/RoleManager.php';
    \MyProtector\Core\RoleManager::removeRoles();
}

/**
 * Check User Role
 * 
 * @param int|null $user_id User ID (defaults to current user)
 * @return string|null Role slug or null
 */
function mp_get_user_role(?int $user_id = null): ?string {
    $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::getUserRole($user);
}

/**
 * Check if Current User is Admin
 */
function mp_is_admin(): bool {
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::isAdmin(wp_get_current_user());
}

/**
 * Check if Current User is Support
 */
function mp_is_support(): bool {
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::isSupport(wp_get_current_user());
}

/**
 * Check if Current User is Business
 */
function mp_is_business(): bool {
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::isBusiness(wp_get_current_user());
}

/**
 * Check if Current User is Reseller
 */
function mp_is_reseller(): bool {
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::isReseller(wp_get_current_user());
}

/**
 * Check if Current User is Individual
 */
function mp_is_individual(): bool {
    require_once __DIR__ . '/RoleManager.php';
    return \MyProtector\Core\RoleManager::isIndividual(wp_get_current_user());
}

/**
 * Check if User Has Capability
 */
function mp_user_can(string $capability, ?int $user_id = null): bool {
    if ($user_id) {
        return user_can($user_id, $capability);
    }
    return current_user_can($capability);
}

/**
 * Get Dashboard URL based on role
 */
function mp_get_dashboard_url(): string {
    $user = wp_get_current_user();
    
    if (mp_is_admin() || mp_is_support()) {
        return admin_url('admin.php?page=myprotector');
    }
    
    if (mp_is_business()) {
        return home_url('/dashboard/business');
    }
    
    if (mp_is_reseller()) {
        return home_url('/dashboard/reseller');
    }
    
    return home_url('/dashboard');
}

/**
 * Assign Role to User
 */
function mp_assign_role(int $user_id, string $role): bool {
    $user = get_user_by('id', $user_id);
    
    if (!$user) {
        return false;
    }
    
    $valid_roles = ['mp_individual', 'mp_business', 'mp_reseller', 'mp_support', 'mp_admin'];
    
    if (!in_array($role, $valid_roles)) {
        return false;
    }
    
    // Remove existing custom roles
    $custom_roles = ['mp_individual', 'mp_business', 'mp_reseller', 'mp_support', 'mp_admin'];
    foreach ($custom_roles as $r) {
        $user->remove_role($r);
    }
    
    // Add new role
    $user->add_role($role);
    
    return true;
}

/**
 * Get User by Role
 */
function mp_get_users_by_role(string $role, int $limit = -1): array {
    $args = [
        'role' => $role,
        'number' => $limit,
        'fields' => ['ID', 'user_email', 'display_name', 'user_registered'],
    ];
    
    return get_users($args);
}

/**
 * Get Role Display Name
 */
function mp_get_role_display_name(string $role): string {
    $names = [
        'administrator' => 'Administrator',
        'mp_admin' => 'MyProtector Admin',
        'mp_support' => 'Customer Support',
        'mp_business' => 'Business Owner',
        'mp_reseller' => 'Reseller',
        'mp_individual' => 'Individual',
    ];
    
    return $names[$role] ?? 'Unknown Role';
}

/**
 * Get Role Badge Color
 */
function mp_get_role_badge_color(string $role): string {
    $colors = [
        'administrator' => '#e91e63',
        'mp_admin' => '#e91e63',
        'mp_support' => '#2196f3',
        'mp_business' => '#4caf50',
        'mp_reseller' => '#ff9800',
        'mp_individual' => '#9e9e9e',
    ];
    
    return $colors[$role] ?? '#9e9e9e';
}

/**
 * Hook for capability checks in WordPress
 */
add_filter('map_meta_cap', function($caps, $cap, $user_id, $args) {
    // Handle custom capabilities
    if (strpos($cap, 'mp_') === 0) {
        // Check if user has the capability
        $user = get_user_by('id', $user_id);
        
        if ($user && $user->has_cap($cap)) {
            return ['read'];
        }
        
        return ['do_not_allow'];
    }
    
    return $caps;
}, 10, 4);