<?php
/**
 * MyProtector Platform - Directory Template
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

$businesses = $this->getMockData('businesses');
$categories = $this->getMockData('categories');
?>

<div class="mp-frontend-ui">
    <!-- Header -->
    <?php include $this->getPath('templates/components/header.php'); ?>

    <!-- Page Header -->
    <section style="background: linear-gradient(135deg, var(--mp-dark-navy) 0%, var(--mp-dark-navy-light) 100%); padding: var(--mp-spacing-3xl) 0;">
        <div class="mp-container">
            <h1 style="color: #fff; margin-bottom: var(--mp-spacing-sm);">Business Directory</h1>
            <p style="color: var(--mp-gray-300); margin: 0;">Find trusted businesses with verified reviews and trust signals</p>
        </div>
    </section>

    <!-- Search & Filters -->
    <section class="mp-section" style="padding: var(--mp-spacing-xl) 0;">
        <div class="mp-container">
            <!-- Search Bar -->
            <div class="mp-search" style="max-width: 600px; margin: 0 auto var(--mp-spacing-xl);">
                <span class="mp-search-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </span>
                <input type="text" class="mp-form-input mp-search-input" placeholder="Search businesses by name or keyword...">
            </div>

            <!-- Filters -->
            <div class="mp-filters">
                <div class="mp-filter-group">
                    <label class="mp-filter-label">Category</label>
                    <select class="mp-form-input mp-form-select mp-filter-select mp-filter-category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mp-filter-group">
                    <label class="mp-filter-label">Rating</label>
                    <select class="mp-form-input mp-form-select mp-filter-select mp-filter-rating">
                        <option value="">All Ratings</option>
                        <option value="4">4+ Stars</option>
                        <option value="3">3+ Stars</option>
                        <option value="2">2+ Stars</option>
                    </select>
                </div>

                <div class="mp-filter-group">
                    <label class="mp-filter-label">Trust Level</label>
                    <div class="mp-flex mp-gap-sm" style="margin-top: var(--mp-spacing-xs);">
                        <button class="mp-btn mp-btn-sm mp-btn-outline mp-trust-filter-btn" data-trust="green">
                            🛒 Shopping Safe
                        </button>
                        <button class="mp-btn mp-btn-sm mp-btn-outline mp-trust-filter-btn" data-trust="amber">
                            🚶 Walking Safe
                        </button>
                        <button class="mp-btn mp-btn-sm mp-btn-outline mp-trust-filter-btn" data-trust="red">
                            ⚠️ Caution
                        </button>
                    </div>
                </div>

                <div class="mp-filter-group" style="margin-left: auto; align-self: flex-end;">
                    <button class="mp-btn mp-btn-ghost mp-btn-sm" id="mp-clear-filters">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="mp-flex mp-items-center mp-justify-between" style="margin-bottom: var(--mp-spacing-lg);">
                <p style="color: var(--mp-gray-600); margin: 0;">
                    Showing <strong class="mp-results-count"><?php echo count($businesses); ?></strong> businesses
                </p>
                <select class="mp-form-input mp-form-select" style="width: auto;">
                    <option>Sort by: Recommended</option>
                    <option>Highest Rated</option>
                    <option>Most Reviewed</option>
                    <option>Newest</option>
                </select>
            </div>

            <!-- Business Grid -->
            <div class="mp-grid mp-grid-3 mp-directory-results">
                <?php foreach ($businesses as $business): ?>
                <div class="mp-card mp-business-card mp-card-clickable" data-business-id="<?php echo esc_attr($business['id']); ?>">
                    <div class="mp-card-body">
                        <div class="mp-flex mp-items-center mp-gap-md">
                            <img src="<?php echo esc_attr($business['logo']); ?>" alt="<?php echo esc_attr($business['name']); ?>" class="mp-business-logo">
                            <div>
                                <h3 class="mp-business-name" style="margin-bottom: 0;"><?php echo esc_html($business['name']); ?></h3>
                                <div class="mp-business-category"><?php echo esc_html($business['category']); ?></div>
                            </div>
                        </div>
                        
                        <div class="mp-business-rating" style="margin-top: var(--mp-spacing-md);">
                            <?php echo $this->getTemplatePart('components/stars', ['rating' => $business['rating']]); ?>
                            <span class="mp-rating-value"><?php echo esc_html($business['rating']); ?></span>
                            <span class="mp-business-reviews">(<?php echo number_format($business['total_reviews']); ?>)</span>
                        </div>
                        
                        <p class="mp-business-location" style="margin-top: var(--mp-spacing-md);">
                            📍 <?php echo esc_html($business['location']); ?>
                        </p>

                        <div class="mp-flex mp-items-center mp-justify-between" style="margin-top: var(--mp-spacing-md); padding-top: var(--mp-spacing-md); border-top: 1px solid var(--mp-gray-100);">
                            <?php echo $this->getTemplatePart('components/trust-badge', ['status' => $business['trust_status']]); ?>
                            <?php if ($business['claimed']): ?>
                            <span class="mp-badge mp-badge-green">✓ Claimed</span>
                            <?php else: ?>
                            <span class="mp-badge">Unclaimed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <div class="mp-flex mp-justify-center" style="margin-top: var(--mp-spacing-2xl);">
                <nav class="mp-pagination">
                    <a href="#" class="mp-pagination-btn mp-pagination-prev">← Previous</a>
                    <a href="#" class="mp-pagination-btn active">1</a>
                    <a href="#" class="mp-pagination-btn">2</a>
                    <a href="#" class="mp-pagination-btn">3</a>
                    <span class="mp-pagination-ellipsis">...</span>
                    <a href="#" class="mp-pagination-btn">10</a>
                    <a href="#" class="mp-pagination-btn mp-pagination-next">Next →</a>
                </nav>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include $this->getPath('templates/components/footer.php'); ?>
</div>

<style>
/* Pagination Styles */
.mp-pagination {
    display: flex;
    align-items: center;
    gap: var(--mp-spacing-xs);
}

.mp-pagination-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 var(--mp-spacing-md);
    border-radius: var(--mp-radius-lg);
    font-size: var(--mp-font-size-sm);
    font-weight: 500;
    color: var(--mp-gray-600);
    background: var(--mp-white);
    border: 1px solid var(--mp-gray-200);
    text-decoration: none;
    transition: all var(--mp-transition-fast);
}

.mp-pagination-btn:hover {
    background: var(--mp-gray-50);
    border-color: var(--mp-gray-300);
}

.mp-pagination-btn.active {
    background: var(--mp-green);
    color: var(--mp-white);
    border-color: var(--mp-green);
}

.mp-pagination-ellipsis {
    color: var(--mp-gray-400);
    padding: 0 var(--mp-spacing-sm);
}
</style>
