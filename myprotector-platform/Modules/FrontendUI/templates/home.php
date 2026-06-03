<?php
/**
 * MyProtector Platform - Homepage Template
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$businesses = $this->getMockData('businesses');
$categories = $this->getMockData('categories');
$stats = $this->getMockData('stats');
?>

<div class="mp-frontend-ui">
    <!-- Header -->
    <header class="mp-header">
        <div class="mp-container">
            <div class="mp-header-inner">
                <a href="#" class="mp-logo">
                    <div class="mp-logo-icon">MP</div>
                    <div class="mp-logo-text">My<span>Protector</span></div>
                </a>
                
                <nav class="mp-nav">
                    <a href="#businesses" class="mp-nav-link active">Businesses</a>
                    <a href="#how-it-works" class="mp-nav-link">How It Works</a>
                    <a href="#trust-signals" class="mp-nav-link">Trust Signals</a>
                    <a href="#dashboard" class="mp-nav-link">Dashboard</a>
                </nav>
                
                <div class="mp-header-actions">
                    <a href="#" class="mp-btn mp-btn-ghost">Log In</a>
                    <a href="#" class="mp-btn mp-btn-primary">Sign Up</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="mp-hero">
        <div class="mp-container mp-hero-content">
            <h1>Trust the Businesses<br>You Choose</h1>
            <p class="mp-hero-subtitle">
                MyProtector helps you make informed decisions with verified reviews 
                and our unique Traffic Light Trust System. Know exactly who you're 
                dealing with before you spend a single dollar.
            </p>
            
            <!-- Hero Search -->
            <div class="mp-hero-search">
                <form action="#" method="GET">
                    <div class="mp-search">
                        <span class="mp-search-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </span>
                        <input type="text" class="mp-form-input mp-search-input" placeholder="Search businesses by name or category...">
                        <button type="submit" class="mp-btn mp-btn-primary mp-search-btn">Search</button>
                    </div>
                </form>
            </div>
            
            <div class="mp-hero-actions">
                <a href="#businesses" class="mp-btn mp-btn-primary mp-btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Search Businesses
                </a>
                <a href="#" class="mp-btn mp-btn-outline mp-btn-lg" style="background: transparent; color: #fff; border-color: #fff;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                    Leave a Review
                </a>
                <a href="#" class="mp-btn mp-btn-secondary mp-btn-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Claim Your Business
                </a>
            </div>
        </div>
    </section>

    <!-- Trust Signals Explanation -->
    <section class="mp-section" id="trust-signals">
        <div class="mp-container">
            <div class="mp-text-center" style="max-width: 700px; margin: 0 auto var(--mp-spacing-2xl);">
                <h2>Our Traffic Light Trust System</h2>
                <p style="color: var(--mp-gray-600); font-size: var(--mp-font-size-lg);">
                    We verify businesses against 5 key trust criteria. 
                    See instantly how trustworthy a business is before you engage.
                </p>
            </div>
            
            <div class="mp-grid mp-grid-3" style="max-width: 900px; margin: 0 auto;">
                <!-- Green - Shopping Safe -->
                <div class="mp-card" style="border-top: 4px solid var(--mp-green);">
                    <div class="mp-trust-signal" style="margin-bottom: var(--mp-spacing-lg);">
                        <div class="mp-trust-light mp-trust-light-green" style="width: 80px; height: 80px;">
                            <span class="mp-trust-icon">🛒</span>
                        </div>
                    </div>
                    <h3 style="color: var(--mp-green);">Shopping Safe</h3>
                    <p style="color: var(--mp-gray-600); margin-bottom: var(--mp-spacing-md);">
                        4-5 trust criteria met. This business has demonstrated high transparency 
                        and commitment to customer trust.
                    </p>
                    <ul style="color: var(--mp-gray-700); font-size: var(--mp-font-size-sm); list-style: none; padding: 0;">
                        <li>✓ Insurance verified</li>
                        <li>✓ Terms & conditions posted</li>
                        <li>✓ Promise pledge made</li>
                        <li>✓ Business verified</li>
                    </ul>
                </div>
                
                <!-- Amber - Walking Safe -->
                <div class="mp-card" style="border-top: 4px solid var(--mp-amber);">
                    <div class="mp-trust-signal" style="margin-bottom: var(--mp-spacing-lg);">
                        <div class="mp-trust-light mp-trust-light-amber" style="width: 80px; height: 80px;">
                            <span class="mp-trust-icon">🚶</span>
                        </div>
                    </div>
                    <h3 style="color: var(--mp-amber);">Walking Safe</h3>
                    <p style="color: var(--mp-gray-600); margin-bottom: var(--mp-spacing-md);">
                        2-3 trust criteria met. Exercise normal caution. 
                        Request additional verification if needed.
                    </p>
                    <ul style="color: var(--mp-gray-700); font-size: var(--mp-font-size-sm); list-style: none; padding: 0;">
                        <li>✓ Partial verification</li>
                        <li>✓ Some transparency</li>
                        <li>⚠ May need more info</li>
                        <li>⚠ Standard caution advised</li>
                    </ul>
                </div>
                
                <!-- Red - Caution -->
                <div class="mp-card" style="border-top: 4px solid var(--mp-red);">
                    <div class="mp-trust-signal" style="margin-bottom: var(--mp-spacing-lg);">
                        <div class="mp-trust-light mp-trust-light-red" style="width: 80px; height: 80px;">
                            <span class="mp-trust-icon">⚠️</span>
                        </div>
                    </div>
                    <h3 style="color: var(--mp-red);">Caution</h3>
                    <p style="color: var(--mp-gray-600); margin-bottom: var(--mp-spacing-md);">
                        0-1 trust criteria met. We recommend extreme caution. 
                        Do your own research before engaging.
                    </p>
                    <ul style="color: var(--mp-gray-700); font-size: var(--mp-font-size-sm); list-style: none; padding: 0;">
                        <li>⚠ Limited verification</li>
                        <li>⚠ Low transparency</li>
                        <li>⚠ High risk indicator</li>
                        <li>⚠ Proceed with care</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="mp-section mp-section-dark">
        <div class="mp-container">
            <div class="mp-stats">
                <div class="mp-stat-card">
                    <div class="mp-stat-value"><?php echo number_format($stats['total_businesses']); ?>+</div>
                    <div class="mp-stat-label">Verified Businesses</div>
                </div>
                <div class="mp-stat-card">
                    <div class="mp-stat-value"><?php echo number_format($stats['total_reviews']); ?>+</div>
                    <div class="mp-stat-label">Customer Reviews</div>
                </div>
                <div class="mp-stat-card">
                    <div class="mp-stat-value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                    <div class="mp-stat-label">Average Rating</div>
                </div>
                <div class="mp-stat-card">
                    <div class="mp-stat-value"><?php echo $stats['trust_score']; ?>%</div>
                    <div class="mp-stat-label">Trust Score</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Businesses -->
    <section class="mp-section" id="businesses">
        <div class="mp-container">
            <div class="mp-flex mp-items-center mp-justify-between" style="margin-bottom: var(--mp-spacing-xl);">
                <div>
                    <h2 style="margin-bottom: var(--mp-spacing-xs);">Featured Businesses</h2>
                    <p style="color: var(--mp-gray-600); margin: 0;">Discover highly-rated and trusted businesses</p>
                </div>
                <a href="#" class="mp-btn mp-btn-outline">View All Businesses</a>
            </div>
            
            <div class="mp-grid mp-grid-3">
                <?php foreach (array_slice($businesses, 0, 6) as $business): ?>
                <div class="mp-card mp-business-card mp-card-clickable">
                    <div class="mp-card-body">
                        <img src="<?php echo esc_attr($business['logo']); ?>" alt="<?php echo esc_attr($business['name']); ?>" class="mp-business-logo">
                        <h3 class="mp-business-name"><?php echo esc_html($business['name']); ?></h3>
                        <div class="mp-business-category"><?php echo esc_html($business['category']); ?></div>
                        <div class="mp-business-rating">
                            <?php echo $this->getTemplatePart('components/stars', ['rating' => $business['rating']]); ?>
                            <span class="mp-rating-value"><?php echo esc_html($business['rating']); ?></span>
                            <span class="mp-business-reviews">(<?php echo number_format($business['total_reviews']); ?> reviews)</span>
                        </div>
                        <div class="mp-flex mp-items-center mp-justify-between" style="margin-top: var(--mp-spacing-md);">
                            <?php echo $this->getTemplatePart('components/trust-badge', ['status' => $business['trust_status']]); ?>
                            <span class="mp-badge"><?php echo esc_html($business['location']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Recent Reviews -->
    <section class="mp-section mp-section-gray">
        <div class="mp-container">
            <div class="mp-flex mp-items-center mp-justify-between" style="margin-bottom: var(--mp-spacing-xl);">
                <div>
                    <h2 style="margin-bottom: var(--mp-spacing-xs);">Recent Reviews</h2>
                    <p style="color: var(--mp-gray-600); margin: 0;">Latest feedback from verified customers</p>
                </div>
                <a href="#" class="mp-btn mp-btn-outline">See All Reviews</a>
            </div>
            
            <div class="mp-grid mp-grid-2">
                <?php 
                $reviews = array_slice($this->getMockData('reviews'), 0, 4);
                foreach ($reviews as $review): 
                    $business = null;
                    foreach ($businesses as $b) {
                        if ($b['id'] == $review['business_id']) {
                            $business = $b;
                            break;
                        }
                    }
                ?>
                <div class="mp-card">
                    <?php if ($business): ?>
                    <div class="mp-flex mp-items-center mp-gap-md" style="margin-bottom: var(--mp-spacing-md);">
                        <img src="<?php echo esc_attr($business['logo']); ?>" alt="" style="width: 40px; height: 40px; border-radius: var(--mp-radius-md); object-fit: contain;">
                        <div>
                            <div style="font-weight: 600; color: var(--mp-dark-navy);"><?php echo esc_html($business['name']); ?></div>
                            <div class="mp-trust-badge mp-trust-badge-<?php echo esc_attr($business['trust_status']); ?>">
                                <?php 
                                $trust_icons = ['green' => '🛒', 'amber' => '🚶', 'red' => '⚠️'];
                                $trust_labels = ['green' => 'Shopping Safe', 'amber' => 'Walking Safe', 'red' => 'Caution'];
                                ?>
                                <span><?php echo $trust_icons[$business['trust_status']]; ?></span>
                                <?php echo $trust_labels[$business['trust_status']]; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mp-review-card" style="padding: 0; border: none;">
                        <div class="mp-review-header" style="margin-bottom: var(--mp-spacing-sm);">
                            <img src="<?php echo esc_attr($review['reviewer_avatar']); ?>" alt="" class="mp-review-avatar" style="width: 40px; height: 40px;">
                            <div class="mp-review-meta">
                                <div class="mp-review-reviewer">
                                    <?php echo esc_html($review['reviewer']); ?>
                                    <?php if ($review['verified']): ?>
                                    <span class="mp-review-verified">✓ Verified</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mp-review-date"><?php echo date_i18n('F j, Y', strtotime($review['date'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="mp-business-rating" style="margin-bottom: var(--mp-spacing-sm);">
                            <?php echo $this->getTemplatePart('components/stars', ['rating' => $review['rating']]); ?>
                        </div>
                        
                        <h4 class="mp-review-title"><?php echo esc_html($review['title']); ?></h4>
                        <p class="mp-review-content" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo esc_html($review['content']); ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="mp-section" id="how-it-works">
        <div class="mp-container">
            <div class="mp-text-center" style="max-width: 700px; margin: 0 auto var(--mp-spacing-2xl);">
                <h2>How MyProtector Works</h2>
                <p style="color: var(--mp-gray-600); font-size: var(--mp-font-size-lg);">
                    We've created a transparent ecosystem where businesses can prove their 
                    trustworthiness and customers can make confident decisions.
                </p>
            </div>
            
            <div class="mp-grid mp-grid-3" style="max-width: 1000px; margin: 0 auto;">
                <!-- For Customers -->
                <div class="mp-card">
                    <div style="width: 60px; height: 60px; background: var(--mp-green-bg); border-radius: var(--mp-radius-xl); display: flex; align-items: center; justify-content: center; margin-bottom: var(--mp-spacing-lg);">
                        <span style="font-size: 28px;">👤</span>
                    </div>
                    <h3 style="margin-bottom: var(--mp-spacing-md);">For Customers</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; color: var(--mp-gray-700);">
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Search</strong> - Find businesses by name or category
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Check Trust</strong> - See Traffic Light rating at a glance
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Read Reviews</strong> - Browse authentic customer feedback
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0;">
                            <strong>Make Decisions</strong> - Choose with confidence
                        </li>
                    </ul>
                </div>
                
                <!-- For Businesses -->
                <div class="mp-card">
                    <div style="width: 60px; height: 60px; background: var(--mp-amber-bg); border-radius: var(--mp-radius-xl); display: flex; align-items: center; justify-content: center; margin-bottom: var(--mp-spacing-lg);">
                        <span style="font-size: 28px;">🏢</span>
                    </div>
                    <h3 style="margin-bottom: var(--mp-spacing-md);">For Businesses</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; color: var(--mp-gray-700);">
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Claim Profile</strong> - Verify your business ownership
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Add Trust Signals</strong> - Insurance, terms, promises
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Respond</strong> - Engage with customer reviews
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0;">
                            <strong>Build Reputation</strong> - Earn trust badges
                        </li>
                    </ul>
                </div>
                
                <!-- Trust Verification -->
                <div class="mp-card">
                    <div style="width: 60px; height: 60px; background: var(--mp-red-bg); border-radius: var(--mp-radius-xl); display: flex; align-items: center; justify-content: center; margin-bottom: var(--mp-spacing-lg);">
                        <span style="font-size: 28px;">🛡️</span>
                    </div>
                    <h3 style="margin-bottom: var(--mp-spacing-md);">Trust Verification</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; color: var(--mp-gray-700);">
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Insurance</strong> - Verify coverage exists
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Terms</strong> - Confirm transparent policies
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                            <strong>Promises</strong> - Track commitments made
                        </li>
                        <li style="padding: var(--mp-spacing-sm) 0;">
                            <strong>Identity</strong> - Verify business legitimacy
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="mp-section mp-section-dark" style="padding: var(--mp-spacing-4xl) 0;">
        <div class="mp-container mp-text-center">
            <h2 style="margin-bottom: var(--mp-spacing-md);">Ready to Get Started?</h2>
            <p style="color: var(--mp-gray-300); font-size: var(--mp-font-size-lg); margin-bottom: var(--mp-spacing-xl); max-width: 600px; margin-left: auto; margin-right: auto;">
                Join thousands of businesses and customers who trust MyProtector 
                for transparent, verified business reviews.
            </p>
            <div class="mp-flex mp-justify-center mp-gap-md" style="flex-wrap: wrap;">
                <a href="#" class="mp-btn mp-btn-primary mp-btn-lg">Create Free Account</a>
                <a href="#" class="mp-btn mp-btn-secondary mp-btn-lg" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mp-footer">
        <div class="mp-container">
            <div class="mp-footer-grid">
                <div class="mp-footer-brand">
                    <a href="#" class="mp-logo">
                        <div class="mp-logo-icon">MP</div>
                        <div class="mp-logo-text">My<span>Protector</span></div>
                    </a>
                    <p class="mp-footer-desc">
                        Building trust between businesses and customers through 
                        transparent verification and authentic reviews.
                    </p>
                    <div class="mp-footer-social">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="mp-footer-title">Platform</h4>
                    <ul class="mp-footer-links">
                        <li><a href="#">Search Businesses</a></li>
                        <li><a href="#">Write a Review</a></li>
                        <li><a href="#">Trust Signals</a></li>
                        <li><a href="#">Dashboard</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="mp-footer-title">For Business</h4>
                    <ul class="mp-footer-links">
                        <li><a href="#">Claim Your Business</a></li>
                        <li><a href="#">Business Dashboard</a></li>
                        <li><a href="#">Verification</a></li>
                        <li><a href="#">Widgets</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="mp-footer-title">Company</h4>
                    <ul class="mp-footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">How It Works</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Careers</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mp-footer-bottom">
                <p class="mp-footer-copyright">
                    &copy; <?php echo date('Y'); ?> MyProtector. All rights reserved.
                </p>
                <div class="mp-footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Review Modal (Template) -->
    <?php include $this->getPath('templates/components/review-modal.php'); ?>
</div>
