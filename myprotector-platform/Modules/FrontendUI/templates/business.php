<?php
/**
 * MyProtector Platform - Business Profile Template
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;
?>

<div class="mp-frontend-ui">
    <!-- Header -->
    <?php include $this->getPath('templates/components/header.php'); ?>

    <!-- Business Header -->
    <section class="mp-business-profile-header" style="background: linear-gradient(135deg, var(--mp-dark-navy) 0%, var(--mp-dark-navy-light) 100%); padding: var(--mp-spacing-3xl) 0; color: #fff;">
        <div class="mp-container">
            <div class="mp-flex mp-flex-wrap mp-gap-xl">
                <!-- Logo -->
                <div style="flex-shrink: 0;">
                    <img src="<?php echo esc_attr($business['logo']); ?>" alt="<?php echo esc_attr($business['name']); ?>" 
                         style="width: 140px; height: 140px; border-radius: var(--mp-radius-xl); object-fit: contain; background: #fff; padding: 10px;">
                </div>
                
                <!-- Business Info -->
                <div style="flex: 1; min-width: 300px;">
                    <div class="mp-flex mp-items-center mp-gap-md" style="margin-bottom: var(--mp-spacing-sm);">
                        <h1 style="color: #fff; margin: 0; font-size: var(--mp-font-size-3xl);"><?php echo esc_html($business['name']); ?></h1>
                        <?php if ($business['claimed']): ?>
                        <span class="mp-badge mp-badge-green" style="font-size: var(--mp-font-size-xs);">✓ Claimed</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mp-business-category" style="color: var(--mp-gray-300); margin-bottom: var(--mp-spacing-md);">
                        <?php echo esc_html($business['category']); ?> • <?php echo esc_html($business['location']); ?>
                    </div>
                    
                    <!-- Trust Signal -->
                    <?php echo $this->getTemplatePart('components/trust-signal', ['business' => $business, 'size' => 'large']); ?>
                    
                    <!-- Rating Summary -->
                    <div class="mp-flex mp-items-center mp-gap-lg" style="margin-top: var(--mp-spacing-lg);">
                        <div class="mp-business-rating">
                            <?php echo $this->getTemplatePart('components/stars', ['rating' => $business['rating']]); ?>
                            <span class="mp-rating-value" style="color: #fff;"><?php echo esc_html($business['rating']); ?></span>
                            <span class="mp-business-reviews" style="color: var(--mp-gray-300);">(<?php echo number_format($business['total_reviews']); ?> reviews)</span>
                        </div>
                        
                        <div class="mp-trust-checklist" style="margin: 0; padding: var(--mp-spacing-sm) var(--mp-spacing-md);">
                            <span style="font-size: var(--mp-font-size-sm);">
                                Trust Score: 
                                <strong style="color: <?php 
                                    echo $business['trust_status'] === 'green' ? 'var(--mp-green)' : 
                                        ($business['trust_status'] === 'amber' ? 'var(--mp-amber)' : 'var(--mp-red)'); 
                                ?>;"><?php echo esc_html($business['trust_score']); ?>%</strong>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div style="flex-shrink: 0;">
                    <button class="mp-btn mp-btn-primary mp-btn-lg mp-write-review-btn" data-modal="mp-review-modal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                        Write a Review
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="mp-section">
        <div class="mp-container">
            <div class="mp-grid" style="grid-template-columns: 1fr 380px; gap: var(--mp-spacing-2xl);">
                <!-- Reviews Column -->
                <div>
                    <!-- Filter/Sort Bar -->
                    <div class="mp-flex mp-items-center mp-justify-between" style="margin-bottom: var(--mp-spacing-xl);">
                        <h2 style="margin: 0;">Customer Reviews</h2>
                        <select class="mp-form-input mp-form-select" style="width: auto;">
                            <option>Sort by: Most Recent</option>
                            <option>Highest Rated</option>
                            <option>Lowest Rated</option>
                            <option>Most Helpful</option>
                        </select>
                    </div>

                    <!-- Reviews List -->
                    <div class="mp-reviews-list">
                        <?php foreach ($reviews as $review): ?>
                        <div class="mp-card" style="margin-bottom: var(--mp-spacing-lg);">
                            <div class="mp-review-card" style="padding: 0; border: none;">
                                <div class="mp-review-header">
                                    <img src="<?php echo esc_attr($review['reviewer_avatar']); ?>" alt="" class="mp-review-avatar">
                                    <div class="mp-review-meta">
                                        <div class="mp-review-reviewer">
                                            <?php echo esc_html($review['reviewer']); ?>
                                            <?php if ($review['verified']): ?>
                                            <span class="mp-review-verified">✓ Verified</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mp-review-date"><?php echo date_i18n('F j, Y', strtotime($review['date'])); ?></div>
                                    </div>
                                    <div class="mp-rating">
                                        <?php echo $this->getTemplatePart('components/stars', ['rating' => $review['rating']]); ?>
                                    </div>
                                </div>
                                
                                <h4 class="mp-review-title"><?php echo esc_html($review['title']); ?></h4>
                                <p class="mp-review-content"><?php echo esc_html($review['content']); ?></p>
                                
                                <?php if (!empty($review['images'])): ?>
                                <div class="mp-review-images">
                                    <?php foreach ($review['images'] as $image): ?>
                                    <img src="<?php echo esc_attr($image); ?>" alt="" class="mp-review-image">
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mp-review-footer">
                                    <div class="mp-review-helpful">
                                        <button class="mp-review-helpful-btn">
                                            <span>👍</span>
                                            <span>Helpful</span>
                                            <span class="mp-helpful-count">(<?php echo esc_html($review['helpful']); ?>)</span>
                                        </button>
                                        <button class="mp-review-helpful-btn">
                                            <span>⚑</span>
                                            <span>Report</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($reviews)): ?>
                        <div class="mp-no-results">
                            <div class="mp-no-results-icon">📝</div>
                            <h3>No reviews yet</h3>
                            <p>Be the first to leave a review for <?php echo esc_html($business['name']); ?>!</p>
                            <button class="mp-btn mp-btn-primary mp-write-review-btn" data-modal="mp-review-modal">
                                Write a Review
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Load More -->
                    <?php if (!empty($reviews)): ?>
                    <div class="mp-text-center" style="margin-top: var(--mp-spacing-xl);">
                        <button class="mp-btn mp-btn-secondary">Load More Reviews</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div>
                    <!-- Business Info Card -->
                    <div class="mp-card" style="margin-bottom: var(--mp-spacing-lg);">
                        <h3 style="margin-bottom: var(--mp-spacing-lg);">Business Information</h3>
                        
                        <?php if (!empty($business['website'])): ?>
                        <div class="mp-business-info-item" style="margin-bottom: var(--mp-spacing-md);">
                            <div class="mp-flex mp-items-center mp-gap-sm" style="color: var(--mp-gray-600); font-size: var(--mp-font-size-sm); margin-bottom: var(--mp-spacing-xs);">
                                <span>🌐</span> Website
                            </div>
                            <a href="<?php echo esc_url($business['website']); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--mp-info);">
                                <?php echo esc_html($business['website']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($business['location'])): ?>
                        <div class="mp-business-info-item" style="margin-bottom: var(--mp-spacing-md);">
                            <div class="mp-flex mp-items-center mp-gap-sm" style="color: var(--mp-gray-600); font-size: var(--mp-font-size-sm); margin-bottom: var(--mp-spacing-xs);">
                                <span>📍</span> Location
                            </div>
                            <span style="color: var(--mp-gray-800);"><?php echo esc_html($business['location']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($business['established'])): ?>
                        <div class="mp-business-info-item" style="margin-bottom: var(--mp-spacing-md);">
                            <div class="mp-flex mp-items-center mp-gap-sm" style="color: var(--mp-gray-600); font-size: var(--mp-font-size-sm); margin-bottom: var(--mp-spacing-xs);">
                                <span>📅</span> Established
                            </div>
                            <span style="color: var(--mp-gray-800);"><?php echo esc_html($business['established']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($business['description'])): ?>
                        <div style="padding-top: var(--mp-spacing-md); border-top: 1px solid var(--mp-gray-100);">
                            <div class="mp-flex mp-items-center mp-gap-sm" style="color: var(--mp-gray-600); font-size: var(--mp-font-size-sm); margin-bottom: var(--mp-spacing-sm);">
                                <span>📋</span> About
                            </div>
                            <p style="color: var(--mp-gray-700); font-size: var(--mp-font-size-sm); line-height: 1.7; margin: 0;">
                                <?php echo esc_html($business['description']); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Authority Links Card -->
                    <div class="mp-card" style="margin-bottom: var(--mp-spacing-lg);">
                        <h3 style="margin-bottom: var(--mp-spacing-lg);">Trust & Authority</h3>
                        
                        <div class="mp-authority-links">
                            <?php 
                            $links = [
                                [
                                    'icon' => '🛡️',
                                    'label' => 'Insurance',
                                    'url' => $business['insurance_url'],
                                    'status' => !empty($business['insurance_url']),
                                ],
                                [
                                    'icon' => '📄',
                                    'label' => 'Terms & Conditions',
                                    'url' => $business['terms_url'],
                                    'status' => !empty($business['terms_url']),
                                ],
                                [
                                    'icon' => '✅',
                                    'label' => 'Promise Page',
                                    'url' => $business['promise_url'],
                                    'status' => !empty($business['promise_url']),
                                ],
                            ];
                            
                            foreach ($links as $link): 
                            ?>
                            <div class="mp-authority-link-item" style="padding: var(--mp-spacing-sm) 0; border-bottom: 1px solid var(--mp-gray-100);">
                                <div class="mp-flex mp-items-center mp-justify-between">
                                    <div class="mp-flex mp-items-center mp-gap-sm">
                                        <span style="font-size: 18px;"><?php echo $link['icon']; ?></span>
                                        <span style="color: var(--mp-gray-700);"><?php echo esc_html($link['label']); ?></span>
                                    </div>
                                    <?php if ($link['status']): ?>
                                    <a href="<?php echo esc_url($link['url']); ?>" target="_blank" rel="noopener noreferrer" class="mp-btn mp-btn-sm mp-btn-ghost">
                                        View →
                                    </a>
                                    <?php else: ?>
                                    <span style="color: var(--mp-gray-400); font-size: var(--mp-font-size-sm);">Not provided</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Share Card -->
                    <div class="mp-card">
                        <h3 style="margin-bottom: var(--mp-spacing-md);">Share This Business</h3>
                        <div class="mp-flex mp-gap-sm">
                            <button class="mp-btn mp-btn-icon mp-btn-secondary" title="Share on Facebook">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </button>
                            <button class="mp-btn mp-btn-icon mp-btn-secondary" title="Share on Twitter">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </button>
                            <button class="mp-btn mp-btn-icon mp-btn-secondary" title="Copy Link">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                            </button>
                            <button class="mp-btn mp-btn-icon mp-btn-secondary" title="Email">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include $this->getPath('templates/components/footer.php'); ?>

    <!-- Review Modal -->
    <?php include $this->getPath('templates/components/review-modal.php'); ?>
</div>

<style>
@media (max-width: 1024px) {
    .mp-grid[style*="grid-template-columns: 1fr 380px"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
