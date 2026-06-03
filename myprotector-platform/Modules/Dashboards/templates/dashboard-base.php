<?php
/**
 * MyProtector Platform - Dashboard Base Template
 * 
 * @package MyProtector\Modules\Dashboards\templates
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
$dashboard_type = get_query_var('mp_dashboard_type', 'individual');
$current_page = get_query_var('mp_dashboard_page', 'overview');

// Get dashboard service based on type
$service_map = [
    'individual' => 'dashboards.individual',
    'business' => 'dashboards.business',
    'reseller' => 'dashboards.reseller',
    'support' => 'dashboards.support',
];

$service = $module->getService($service_map[$dashboard_type] ?? 'dashboards.individual');
$stats = $service->getStats($user->ID) ?? [];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html($page_titles[$current_page] ?? 'Dashboard'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body>
    <div class="mp-dashboard mp-dash-<?php echo esc_attr($dashboard_type); ?>">
        <!-- Sidebar -->
        <aside class="mp-dash-sidebar">
            <div class="mp-dash-logo">
                <h1><?php bloginfo('name'); ?></h1>
            </div>
            
            <nav class="mp-dash-nav">
                <div class="mp-dash-nav-section">
                    <a href="<?php echo esc_url($module->getDashboardUrl()); ?>" class="mp-dash-nav-item <?php echo $current_page === 'overview' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span><?php _e('Overview', 'myprotector-platform'); ?></span>
                    </a>
                    
                    <?php if ($dashboard_type === 'individual'): ?>
                    <a href="<?php echo esc_url(home_url('/dashboard/reviews/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'reviews' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i>
                        <span><?php _e('My Reviews', 'myprotector-platform'); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($dashboard_type === 'business'): ?>
                    <a href="<?php echo esc_url(home_url('/business-dashboard/reviews/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'reviews' ? 'active' : ''; ?>">
                        <i class="fas fa-star"></i>
                        <span><?php _e('Reviews', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/business-dashboard/responses/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'responses' ? 'active' : ''; ?>">
                        <i class="fas fa-reply"></i>
                        <span><?php _e('Responses', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/business-dashboard/profile/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i>
                        <span><?php _e('Business Profile', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/business-dashboard/widgets/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'widgets' ? 'active' : ''; ?>">
                        <i class="fas fa-code"></i>
                        <span><?php _e('Widgets', 'myprotector-platform'); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($dashboard_type === 'reseller'): ?>
                    <a href="<?php echo esc_url(home_url('/reseller-dashboard/referrals/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'referrals' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span><?php _e('Referrals', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/reseller-dashboard/commissions/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'commissions' ? 'active' : ''; ?>">
                        <i class="fas fa-dollar-sign"></i>
                        <span><?php _e('Commissions', 'myprotector-platform'); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($dashboard_type === 'support'): ?>
                    <a href="<?php echo esc_url(home_url('/support-dashboard/tickets/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'tickets' ? 'active' : ''; ?>">
                        <i class="fas fa-ticket-alt"></i>
                        <span><?php _e('Tickets', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/support-dashboard/users/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'users' ? 'active' : ''; ?>">
                        <i class="fas fa-search"></i>
                        <span><?php _e('User Lookup', 'myprotector-platform'); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                
                <div class="mp-dash-nav-section">
                    <span class="mp-dash-nav-title"><?php _e('Settings', 'myprotector-platform'); ?></span>
                    <a href="<?php echo esc_url(home_url('/dashboard/profile/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i>
                        <span><?php _e('Profile', 'myprotector-platform'); ?></span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/dashboard/settings/')); ?>" class="mp-dash-nav-item <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i>
                        <span><?php _e('Settings', 'myprotector-platform'); ?></span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="mp-dash-main">
            <!-- Header -->
            <header class="mp-dash-header">
                <div class="mp-dash-breadcrumb">
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'myprotector-platform'); ?></a>
                    <span>/</span>
                    <span><?php echo esc_html($page_titles[$current_page] ?? 'Dashboard'); ?></span>
                </div>
                
                <div class="mp-dash-header-actions">
                    <div class="mp-dash-notifications" id="mp-notifications-btn">
                        <i class="fas fa-bell"></i>
                        <?php if (!empty($stats['unread_notifications'])): ?>
                        <span class="badge"><?php echo esc_html($stats['unread_notifications']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mp-dash-user">
                        <img src="<?php echo esc_url(get_avatar_url($user->ID)); ?>" alt="" class="mp-dash-avatar">
                        <span><?php echo esc_html($user->display_name); ?></span>
                        <a href="<?php echo esc_url(wp_logout_url()); ?>" class="mp-dash-btn mp-dash-btn-secondary">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="mp-dash-content">
                <?php 
                // Load specific page content based on current_page
                $page_file = $module->getPath('templates/partials/dashboard-' . $current_page . '.php');
                if (file_exists($page_file)) {
                    include $page_file;
                } else {
                    // Default overview
                    include $module->getPath('templates/partials/dashboard-overview.php');
                }
                ?>
            </div>
        </main>
    </div>

    <?php wp_footer(); ?>
    <script>
    jQuery(document).ready(function($) {
        // Dashboard interactions
        $('.mp-dash-nav-item').on('click', function() {
            $('.mp-dash-nav-item').removeClass('active');
            $(this).addClass('active');
        });
        
        // Mobile menu toggle
        $('.mp-dash-sidebar').addClass('open');
    });
    </script>
</body>
</html>