<?php
/**
 * MyProtector Platform - Dashboard Template
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$dashboard_type = isset($atts['type']) ? $atts['type'] : 'individual';

// Mock user data
$user = [
    'name' => 'John Anderson',
    'email' => 'john.anderson@example.com',
    'avatar' => 'https://ui-avatars.com/api/?name=JA&background=0A1F44&color=fff&size=128',
    'type' => $dashboard_type,
];

// Mock stats
$stats = [
    'reviews_written' => 12,
    'helpful_votes' => 45,
    'avg_rating_given' => 4.2,
];

$business_stats = [
    'total_reviews' => 247,
    'avg_rating' => 4.8,
    'trust_score' => 100,
    'responses' => 38,
];

$reseller_stats = [
    'referrals' => 45,
    'earnings' => '$1,245.00',
    'pending' => '$125.00',
    'commissions' => '10%',
];
?>

<div class="mp-frontend-ui">
    <!-- Header -->
    <?php include $this->getPath('templates/components/header.php'); ?>

    <div class="mp-dashboard">
        <!-- Sidebar -->
        <aside class="mp-dashboard-sidebar">
            <div class="mp-dashboard-user" style="padding-bottom: var(--mp-spacing-lg); border-bottom: 1px solid var(--mp-gray-200); margin-bottom: var(--mp-spacing-lg);">
                <img src="<?php echo esc_attr($user['avatar']); ?>" alt="" style="width: 64px; height: 64px; border-radius: var(--mp-radius-full); margin-bottom: var(--mp-spacing-sm);">
                <div style="font-weight: 600; color: var(--mp-dark-navy);"><?php echo esc_html($user['name']); ?></div>
                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);"><?php echo esc_html($user['type']); ?></div>
            </div>

            <nav class="mp-dashboard-nav">
                <?php if ($dashboard_type === 'individual'): ?>
                <a href="#" class="mp-dashboard-nav-item active" data-section="overview">
                    <span class="mp-dashboard-nav-icon">📊</span>
                    Overview
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="my-reviews">
                    <span class="mp-dashboard-nav-icon">⭐</span>
                    My Reviews
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="saved">
                    <span class="mp-dashboard-nav-icon">🔖</span>
                    Saved Businesses
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="settings">
                    <span class="mp-dashboard-nav-icon">⚙️</span>
                    Settings
                </a>
                
                <?php elseif ($dashboard_type === 'business'): ?>
                <a href="#" class="mp-dashboard-nav-item active" data-section="overview">
                    <span class="mp-dashboard-nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="reviews">
                    <span class="mp-dashboard-nav-icon">⭐</span>
                    Reviews
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="analytics">
                    <span class="mp-dashboard-nav-icon">📈</span>
                    Analytics
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="profile">
                    <span class="mp-dashboard-nav-icon">🏢</span>
                    Business Profile
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="widgets">
                    <span class="mp-dashboard-nav-icon">📱</span>
                    Widgets
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="settings">
                    <span class="mp-dashboard-nav-icon">⚙️</span>
                    Settings
                </a>
                
                <?php else: // reseller ?>
                <a href="#" class="mp-dashboard-nav-item active" data-section="overview">
                    <span class="mp-dashboard-nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="referrals">
                    <span class="mp-dashboard-nav-icon">👥</span>
                    Referrals
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="earnings">
                    <span class="mp-dashboard-nav-icon">💰</span>
                    Earnings
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="tools">
                    <span class="mp-dashboard-nav-icon">🛠️</span>
                    Marketing Tools
                </a>
                <a href="#" class="mp-dashboard-nav-item" data-section="settings">
                    <span class="mp-dashboard-nav-icon">⚙️</span>
                    Settings
                </a>
                <?php endif; ?>
            </nav>

            <div style="margin-top: auto; padding-top: var(--mp-spacing-lg);">
                <a href="#" class="mp-btn mp-btn-ghost mp-w-full">
                    <span>🚪</span> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="mp-dashboard-content">
            <?php if ($dashboard_type === 'individual'): ?>
            
            <!-- Individual Dashboard Sections -->
            <section id="overview" class="mp-dashboard-section active">
                <div class="mp-dashboard-header">
                    <h1 class="mp-dashboard-title">Welcome back, <?php echo esc_html(explode(' ', $user['name'])[0]); ?>!</h1>
                    <p class="mp-dashboard-subtitle">Here's an overview of your activity</p>
                </div>

                <div class="mp-dashboard-cards">
                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-green-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">⭐</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($stats['reviews_written']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Reviews Written</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-amber-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">👍</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($stats['helpful_votes']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Helpful Votes</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-info); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">📊</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($stats['avg_rating_given']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Avg Rating Given</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mp-card" style="margin-top: var(--mp-spacing-xl);">
                    <h3 style="margin-bottom: var(--mp-spacing-lg);">Recent Activity</h3>
                    <div style="display: flex; flex-direction: column; gap: var(--mp-spacing-md);">
                        <div class="mp-flex mp-items-center mp-gap-md" style="padding: var(--mp-spacing-md); background: var(--mp-gray-50); border-radius: var(--mp-radius-lg);">
                            <span style="font-size: 24px;">⭐</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 600;">You reviewed TechVentures Solutions</div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">2 days ago</div>
                            </div>
                            <span class="mp-badge mp-badge-green">5 Stars</span>
                        </div>
                        <div class="mp-flex mp-items-center mp-gap-md" style="padding: var(--mp-spacing-md); background: var(--mp-gray-50); border-radius: var(--mp-radius-lg);">
                            <span style="font-size: 24px;">🔖</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 600;">You saved HealthFirst Medical Group</div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">1 week ago</div>
                            </div>
                        </div>
                        <div class="mp-flex mp-items-center mp-gap-md" style="padding: var(--mp-spacing-md); background: var(--mp-gray-50); border-radius: var(--mp-radius-lg);">
                            <span style="font-size: 24px;">👍</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 600;">Your review was marked helpful</div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">2 weeks ago</div>
                            </div>
                            <span class="mp-badge">+5 points</span>
                        </div>
                    </div>
                </div>
            </section>

            <?php elseif ($dashboard_type === 'business'): ?>
            
            <!-- Business Dashboard Sections -->
            <section id="overview" class="mp-dashboard-section active">
                <div class="mp-dashboard-header">
                    <h1 class="mp-dashboard-title">Business Dashboard</h1>
                    <p class="mp-dashboard-subtitle">Manage your business presence on MyProtector</p>
                </div>

                <div class="mp-dashboard-cards">
                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-green-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">⭐</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($business_stats['total_reviews']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Total Reviews</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-amber-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">📊</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($business_stats['avg_rating']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Average Rating</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-green); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">🛒</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($business_stats['trust_score']); ?>%</div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Trust Score</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-info); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">💬</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($business_stats['responses']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Responses</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trust Status Card -->
                <div class="mp-card" style="margin-top: var(--mp-spacing-xl);">
                    <h3 style="margin-bottom: var(--mp-spacing-lg);">Trust Status</h3>
                    <div class="mp-flex mp-items-center mp-gap-xl">
                        <div class="mp-trust-signal">
                            <div class="mp-trust-light mp-trust-light-green" style="width: 80px; height: 80px;">
                                <span class="mp-trust-icon">🛒</span>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <div class="mp-trust-checklist">
                                <div class="mp-trust-checklist-item">
                                    <span class="mp-trust-check-icon mp-trust-check-pass">✓</span>
                                    <span>Insurance verified</span>
                                </div>
                                <div class="mp-trust-checklist-item">
                                    <span class="mp-trust-check-icon mp-trust-check-pass">✓</span>
                                    <span>Terms & conditions posted</span>
                                </div>
                                <div class="mp-trust-checklist-item">
                                    <span class="mp-trust-check-icon mp-trust-check-pass">✓</span>
                                    <span>Promise pledge made</span>
                                </div>
                            </div>
                        </div>
                        <div class="mp-text-center">
                            <button class="mp-btn mp-btn-outline">Update Trust Info</button>
                        </div>
                    </div>
                </div>
            </section>

            <?php else: // reseller ?>
            
            <!-- Reseller Dashboard Sections -->
            <section id="overview" class="mp-dashboard-section active">
                <div class="mp-dashboard-header">
                    <h1 class="mp-dashboard-title">Reseller Dashboard</h1>
                    <p class="mp-dashboard-subtitle">Track your referrals and earnings</p>
                </div>

                <div class="mp-dashboard-cards">
                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-green-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">👥</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($reseller_stats['referrals']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Total Referrals</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-green); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">💰</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-green);"><?php echo esc_html($reseller_stats['earnings']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Total Earnings</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-amber-bg); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px;">⏳</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-amber);"><?php echo esc_html($reseller_stats['pending']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Pending Payout</div>
                            </div>
                        </div>
                    </div>

                    <div class="mp-card">
                        <div style="display: flex; align-items: center; gap: var(--mp-spacing-md);">
                            <div style="width: 48px; height: 48px; background: var(--mp-info); border-radius: var(--mp-radius-lg); display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">%</div>
                            <div>
                                <div style="font-size: var(--mp-font-size-2xl); font-weight: 700; color: var(--mp-dark-navy);"><?php echo esc_html($reseller_stats['commissions']); ?></div>
                                <div style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500);">Commission Rate</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Referral Link Card -->
                <div class="mp-card" style="margin-top: var(--mp-spacing-xl);">
                    <h3 style="margin-bottom: var(--mp-spacing-lg);">Your Referral Link</h3>
                    <div class="mp-flex mp-gap-md">
                        <input type="text" class="mp-form-input" value="https://myprotector.org/ref/john.anderson" readonly style="flex: 1;">
                        <button class="mp-btn mp-btn-primary">Copy Link</button>
                    </div>
                    <p style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500); margin-top: var(--mp-spacing-sm);">
                        Share this link to earn commissions on new sign-ups
                    </p>
                </div>
            </section>

            <?php endif; ?>
        </main>
    </div>
</div>
