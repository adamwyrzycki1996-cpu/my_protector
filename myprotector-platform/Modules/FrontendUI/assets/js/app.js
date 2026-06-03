/**
 * MyProtector Platform - Frontend UI JavaScript
 * 
 * Interactive components for the MyProtector Platform
 * 
 * @package MyProtector\Modules\FrontendUI
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Global state
    const state = {
        isLoading: false,
        currentBusiness: null,
        searchTimeout: null,
    };

    // DOM Ready
    $(document).ready(function() {
        initModals();
        initSearch();
        initStarRating();
        initFilters();
        initLazyLoad();
        initSmoothScroll();
        initDashboardNav();
        initReviewForm();
        initHelpfulButtons();
        initTooltips();
    });

    /**
     * Initialize Modal System
     */
    function initModals() {
        // Open modal buttons
        $(document).on('click', '[data-modal]', function(e) {
            e.preventDefault();
            const modalId = $(this).data('modal');
            openModal(modalId);
        });

        // Close modal buttons
        $(document).on('click', '.mp-modal-close, [data-modal-close]', function(e) {
            e.preventDefault();
            closeModal($(this).closest('.mp-modal-overlay'));
        });

        // Close on overlay click
        $(document).on('click', '.mp-modal-overlay', function(e) {
            if ($(e.target).hasClass('mp-modal-overlay')) {
                closeModal($(this));
            }
        });

        // Close on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal($('.mp-modal-overlay.active'));
            }
        });
    }

    /**
     * Open Modal
     */
    function openModal(modalId) {
        const $modal = $('#' + modalId);
        if ($modal.length) {
            $modal.addClass('active');
            $('body').css('overflow', 'hidden');
            
            // Focus first input
            setTimeout(() => {
                $modal.find('input, textarea, select').first().focus();
            }, 100);
        }
    }

    /**
     * Close Modal
     */
    function closeModal($modal) {
        $modal.removeClass('active');
        $('body').css('overflow', '');
        
        // Reset form if present
        const $form = $modal.find('form');
        if ($form.length) {
            $form[0].reset();
            $form.find('.mp-form-error').remove();
        }
    }

    /**
     * Initialize Search
     */
    function initSearch() {
        const $searchInput = $('.mp-search-input');
        
        if (!$searchInput.length) return;

        $searchInput.on('input', function() {
            const $this = $(this);
            const query = $this.val().trim();
            const $container = $this.closest('.mp-hero-search, .mp-filters') || $this.parent().parent();
            
            // Debounce
            clearTimeout(state.searchTimeout);
            
            state.searchTimeout = setTimeout(() => {
                if (query.length >= 2 || query.length === 0) {
                    performSearch(query);
                }
            }, 300);
        });

        // Search form submit
        $(document).on('submit', '.mp-hero-search form', function(e) {
            e.preventDefault();
            const query = $(this).find('.mp-search-input').val().trim();
            performSearch(query);
        });
    }

    /**
     * Perform Search (AJAX)
     */
    function performSearch(query) {
        const $resultsContainer = $('.mp-businesses-grid, .mp-directory-results');
        
        if (!$resultsContainer.length) return;

        // Get current filters
        const filters = {
            query: query,
            category: $('.mp-filter-category').val() || '',
            rating: $('.mp-filter-rating').val() || 0,
            trust: $('.mp-filter-trust').val() || '',
        };

        // Show loading
        $resultsContainer.addClass('mp-loading');
        
        // AJAX request
        $.ajax({
            url: mpFrontend.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mp_search_businesses',
                nonce: mpFrontend.nonce,
                ...filters
            },
            success: function(response) {
                if (response.success) {
                    $resultsContainer.html(response.data.html);
                    
                    // Update count
                    const $countEl = $('.mp-results-count');
                    if ($countEl.length) {
                        $countEl.text(response.data.count);
                    }
                }
            },
            error: function() {
                showToast(mpFrontend.strings.error, 'error');
            },
            complete: function() {
                $resultsContainer.removeClass('mp-loading');
            }
        });
    }

    /**
     * Initialize Star Rating Input
     */
    function initStarRating() {
        $('.mp-star-rating').each(function() {
            const $rating = $(this);
            const $stars = $rating.find('label');
            
            $stars.on('mouseenter', function() {
                const index = $(this).index();
                highlightStars($stars, index);
            });

            $stars.on('mouseleave', function() {
                const checkedIndex = $rating.find('input:checked').index();
                highlightStars($stars, checkedIndex >= 0 ? checkedIndex : -1);
            });

            $stars.on('click', function() {
                const index = $(this).index();
                $rating.find('input').eq(index).prop('checked', true).trigger('change');
            });
        });
    }

    /**
     * Highlight Stars
     */
    function highlightStars($stars, index) {
        $stars.each(function(i) {
            if (i <= index) {
                $(this).addClass('star-hover');
            } else {
                $(this).removeClass('star-hover');
            }
        });
    }

    /**
     * Initialize Filters
     */
    function initFilters() {
        $(document).on('change', '.mp-filter-select', function() {
            const query = $('.mp-search-input').val() || '';
            performSearch(query);
        });

        // Trust filter specific handling
        $(document).on('click', '.mp-trust-filter-btn', function() {
            const trust = $(this).data('trust');
            const $filters = $(this).closest('.mp-filters');
            
            $filters.find('.mp-filter-trust').val(trust).trigger('change');
            
            // Update active state
            $filters.find('.mp-trust-filter-btn').removeClass('active');
            $(this).addClass('active');
        });
    }

    /**
     * Initialize Lazy Loading
     */
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const $el = $(entry.target);
                        const src = $el.data('src');
                        
                        if (src) {
                            $el.attr('src', src).removeData('src');
                            $el.addClass('mp-loaded');
                        }
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });

            $('[data-src]').each(function() {
                observer.observe(this);
            });
        }
    }

    /**
     * Initialize Smooth Scroll
     */
    function initSmoothScroll() {
        $(document).on('click', 'a[href^="#"]', function(e) {
            const href = $(this).attr('href');
            
            if (href === '#') return;
            
            const $target = $(href);
            
            if ($target.length) {
                e.preventDefault();
                const offset = 80; // Account for sticky header
                const position = $target.offset().top - offset;
                
                $('html, body').animate({
                    scrollTop: position
                }, 300);
            }
        });
    }

    /**
     * Initialize Dashboard Navigation
     */
    function initDashboardNav() {
        $(document).on('click', '.mp-dashboard-nav-item', function(e) {
            const $this = $(this);
            const section = $this.data('section');
            
            if (!section) return;
            
            e.preventDefault();
            
            // Update active state
            $('.mp-dashboard-nav-item').removeClass('active');
            $this.addClass('active');
            
            // Show section
            $('.mp-dashboard-section').removeClass('active').hide();
            const $section = $('#' + section);
            if ($section.length) {
                $section.fadeIn(200).addClass('active');
            }
        });

        // Mobile menu toggle
        $(document).on('click', '.mp-dashboard-menu-toggle', function() {
            $('.mp-dashboard-sidebar').toggleClass('mp-open');
        });
    }

    /**
     * Initialize Review Form
     */
    function initReviewForm() {
        $(document).on('submit', '.mp-review-form', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            
            // Validate
            const rating = $form.find('input[name="rating"]:checked').val();
            const title = $form.find('input[name="title"]').val().trim();
            const content = $form.find('textarea[name="content"]').val().trim();
            
            // Clear previous errors
            $form.find('.mp-form-error').remove();
            
            let hasError = false;
            
            if (!rating) {
                showFieldError($form, 'rating', 'Please select a rating');
                hasError = true;
            }
            
            if (!title) {
                showFieldError($form, 'title', 'Please enter a review title');
                hasError = true;
            }
            
            if (!content || content.length < 20) {
                showFieldError($form, 'content', 'Please write at least 20 characters');
                hasError = true;
            }
            
            if (hasError) return;
            
            // Disable button
            $submitBtn.prop('disabled', true).html('<span class="mp-spinner"></span> Submitting...');
            
            // Simulate submission (UI only)
            setTimeout(() => {
                showToast('Review submitted successfully!', 'success');
                closeModal($('.mp-modal-overlay.active'));
                $submitBtn.prop('disabled', false).text('Submit Review');
                $form[0].reset();
            }, 1500);
        });
    }

    /**
     * Show Field Error
     */
    function showFieldError($form, name, message) {
        const $field = $form.find(`[name="${name}"]`);
        $field.after(`<div class="mp-form-error">${message}</div>`);
        $field.addClass('mp-form-input-error');
    }

    /**
     * Initialize Helpful Buttons
     */
    function initHelpfulButtons() {
        $(document).on('click', '.mp-review-helpful-btn', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const $count = $btn.find('.mp-helpful-count');
            const reviewId = $btn.closest('.mp-review-card').data('review-id');
            
            // Check if already clicked
            if ($btn.hasClass('mp-active')) {
                return;
            }
            
            // Simulate helpful count
            let count = parseInt($count.text()) || 0;
            count++;
            $count.text(count);
            $btn.addClass('mp-active');
            
            showToast('Thank you for your feedback!', 'success');
        });
    }

    /**
     * Initialize Tooltips
     */
    function initTooltips() {
        $('[data-tooltip]').each(function() {
            const $el = $(this);
            const text = $el.data('tooltip');
            
            $el.on('mouseenter', function() {
                const $tooltip = $('<div class="mp-tooltip"></div>').text(text);
                $el.append($tooltip);
                
                // Position tooltip
                setTimeout(() => {
                    const tooltipOffset = $tooltip.outerHeight() + 10;
                    $tooltip.css('bottom', `calc(100% + ${tooltipOffset}px)`);
                }, 0);
            });

            $el.on('mouseleave', function() {
                $el.find('.mp-tooltip').remove();
            });
        });
    }

    /**
     * Show Toast Notification
     */
    function showToast(message, type) {
        type = type || 'info';
        
        const $toast = $(`
            <div class="mp-toast mp-toast-${type}">
                <span class="mp-toast-icon">${getToastIcon(type)}</span>
                <span class="mp-toast-message">${message}</span>
            </div>
        `);
        
        $('#mp-toast-container').append($toast);
        
        setTimeout(() => $toast.addClass('mp-visible'), 10);
        
        setTimeout(() => {
            $toast.removeClass('mp-visible');
            setTimeout(() => $toast.remove(), 300);
        }, 4000);
    }

    /**
     * Get Toast Icon
     */
    function getToastIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    /**
     * Render Business Card (for dynamic content)
     */
    function renderBusinessCard(business) {
        const trustLabels = {
            green: 'Shopping Safe',
            amber: 'Walking Safe',
            red: 'Caution'
        };

        const trustIcons = {
            green: '🛒',
            amber: '🚶',
            red: '⚠️'
        };

        return `
            <div class="mp-card mp-business-card mp-card-clickable" data-business-id="${business.id}">
                <div class="mp-card-body">
                    <img src="${business.logo}" alt="${business.name}" class="mp-business-logo">
                    <h3 class="mp-business-name">${business.name}</h3>
                    <div class="mp-business-category">${business.category}</div>
                    <div class="mp-business-rating">
                        ${renderStars(business.rating)}
                        <span class="mp-rating-value">${business.rating}</span>
                        <span class="mp-business-reviews">(${business.total_reviews} reviews)</span>
                    </div>
                    <div class="mp-flex mp-items-center mp-justify-between">
                        <span class="mp-trust-badge mp-trust-badge-${business.trust_status}">
                            <span class="mp-trust-badge-icon">${trustIcons[business.trust_status]}</span>
                            ${trustLabels[business.trust_status]}
                        </span>
                        <span class="mp-badge">${business.location}</span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Render Stars HTML
     */
    function renderStars(rating) {
        let html = '<div class="mp-rating-stars">';
        const fullStars = Math.floor(rating);
        const hasHalf = rating % 1 >= 0.5;
        
        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                html += '<span class="mp-rating-star mp-rating-star-filled">★</span>';
            } else if (i === fullStars && hasHalf) {
                html += '<span class="mp-rating-star mp-rating-star-half">★</span>';
            } else {
                html += '<span class="mp-rating-star">★</span>';
            }
        }
        
        html += '</div>';
        return html;
    }

    /**
     * Render Review Card HTML
     */
    function renderReviewCard(review) {
        return `
            <div class="mp-review-card" data-review-id="${review.id}">
                <div class="mp-review-header">
                    <img src="${review.reviewer_avatar}" alt="${review.reviewer}" class="mp-review-avatar">
                    <div class="mp-review-meta">
                        <div class="mp-review-reviewer">
                            ${review.reviewer}
                            ${review.verified ? '<span class="mp-review-verified">✓ Verified</span>' : ''}
                        </div>
                        <div class="mp-review-date">${formatDate(review.date)}</div>
                    </div>
                    <div class="mp-rating">
                        ${renderStars(review.rating)}
                    </div>
                </div>
                <h4 class="mp-review-title">${review.title}</h4>
                <p class="mp-review-content">${review.content}</p>
                ${review.images && review.images.length ? `
                    <div class="mp-review-images">
                        ${review.images.map(img => `<img src="${img}" alt="Review image" class="mp-review-image">`).join('')}
                    </div>
                ` : ''}
                <div class="mp-review-footer">
                    <div class="mp-review-helpful">
                        <button class="mp-review-helpful-btn">
                            <span>👍</span>
                            <span>Helpful</span>
                            <span class="mp-helpful-count">${review.helpful}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Format Date
     */
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
    }

    /**
     * Image Upload Preview
     */
    $(document).on('change', '.mp-upload-input', function(e) {
        const $input = $(this);
        const $preview = $input.siblings('.mp-upload-preview') || $input.parent().find('.mp-upload-preview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                let previewHtml;
                
                if ($preview.length) {
                    $preview.html(`<img src="${e.target.result}" alt="Preview">`).show();
                } else {
                    $input.parent().find('.mp-upload-icon').hide();
                    $input.parent().append(`<div class="mp-upload-preview"><img src="${e.target.result}" alt="Preview"></div>`);
                }
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });

    /**
     * Create Toast Container
     */
    $('body').append('<div id="mp-toast-container"></div>');

    // Expose to global
    window.mpFrontend = {
        openModal,
        closeModal,
        showToast,
        renderBusinessCard,
        renderReviewCard,
        performSearch
    };

})(jQuery);