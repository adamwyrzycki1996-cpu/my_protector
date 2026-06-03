/**
 * MyProtector Platform - Business Pages JavaScript
 * 
 * @package MyProtector\Modules\BusinessPages
 */

(function($) {
    'use strict';

    const BusinessPages = {
        init: function() {
            this.bindEvents();
            this.initStarRating();
            this.initInfiniteScroll();
        },

        bindEvents: function() {
            // Mark as helpful
            $(document).on('click', '.mp-btn-helpful', this.handleMarkHelpful.bind(this));
            
            // Report review
            $(document).on('click', '.mp-btn-report', this.handleReportReview.bind(this));
            
            // Load more reviews
            $(document).on('click', '.mp-load-more-reviews', this.handleLoadMore.bind(this));
            
            // Filter reviews
            $(document).on('change', '.mp-review-filter', this.handleFilterChange.bind(this));
            
            // Sort reviews
            $(document).on('change', '.mp-review-sort', this.handleSortChange.bind(this));
            
            // Submit business response
            $(document).on('submit', '.mp-response-form', this.handleSubmitResponse.bind(this));
            
            // Search form
            $(document).on('submit', '.mp-business-search form', this.handleSearch.bind(this));
        },

        /**
         * Initialize star rating input
         */
        initStarRating: function() {
            $('.mp-star-rating-input').each(function() {
                const $container = $(this);
                const $input = $container.find('input[type="hidden"]');
                
                $container.find('.mp-star').on('click', function() {
                    const rating = $(this).data('rating');
                    $input.val(rating);
                    $container.find('.mp-star').removeClass('active');
                    $container.find('.mp-star').each(function() {
                        if ($(this).data('rating') <= rating) {
                            $(this).addClass('active');
                        }
                    });
                });
                
                // Hover effect
                $container.find('.mp-star').on('mouseenter', function() {
                    const rating = $(this).data('rating');
                    $container.find('.mp-star').each(function() {
                        if ($(this).data('rating') <= rating) {
                            $(this).addClass('hover');
                        }
                    });
                });
                
                $container.find('.mp-star').on('mouseleave', function() {
                    $container.find('.mp-star').removeClass('hover');
                });
            });
        },

        /**
         * Initialize infinite scroll for reviews
         */
        initInfiniteScroll: function() {
            if (!$('.mp-reviews-list').length) return;
            
            $(window).on('scroll', function() {
                if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
                    BusinessPages.loadMoreReviews();
                }
            });
        },

        /**
         * Handle mark helpful click
         */
        handleMarkHelpful: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const reviewId = $btn.data('review-id');
            
            $btn.prop('disabled', true);
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_mark_helpful',
                    nonce: mpBusinessPages.nonce,
                    review_id: reviewId
                },
                success: function(response) {
                    if (response.success) {
                        $btn.find('.mp-helpful-count').text('(' + response.data.count + ')');
                        $btn.addClass('mp-helpful-marked');
                    } else {
                        BusinessPages.showError(response.data.message);
                    }
                },
                error: function() {
                    BusinessPages.showError(mpBusinessPages.strings.error);
                }
            });
        },

        /**
         * Handle report review
         */
        handleReportReview: function(e) {
            e.preventDefault();
            
            const reviewId = $(e.currentTarget).data('review-id');
            const reason = prompt('Please provide a reason for reporting this review:');
            
            if (!reason) return;
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_report_review',
                    nonce: mpBusinessPages.nonce,
                    review_id: reviewId,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        BusinessPages.showSuccess(response.data.message);
                    } else {
                        BusinessPages.showError(response.data.message);
                    }
                },
                error: function() {
                    BusinessPages.showError(mpBusinessPages.strings.error);
                }
            });
        },

        /**
         * Handle load more reviews
         */
        handleLoadMore: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $container = $btn.closest('.mp-reviews-container');
            const businessId = $container.data('business-id');
            const currentPage = $container.data('page') || 1;
            
            $btn.prop('disabled', true).text(mpBusinessPages.strings.loading);
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_get_reviews',
                    nonce: mpBusinessPages.nonce,
                    business_id: businessId,
                    page: currentPage + 1
                },
                success: function(response) {
                    if (response.success && response.data.reviews.length > 0) {
                        $container.find('.mp-reviews-list').append(response.data.html);
                        $container.data('page', currentPage + 1);
                        
                        if (currentPage + 1 >= response.data.pages) {
                            $btn.hide();
                        }
                    } else {
                        $btn.hide();
                    }
                },
                error: function() {
                    BusinessPages.showError(mpBusinessPages.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Load More Reviews');
                }
            });
        },

        /**
         * Handle filter change
         */
        handleFilterChange: function(e) {
            const rating = $(e.currentTarget).val();
            const $container = $(e.currentTarget).closest('.mp-reviews-container');
            
            $container.find('.mp-reviews-list').html('<div class="mp-loading"></div>');
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_get_reviews',
                    nonce: mpBusinessPages.nonce,
                    business_id: $container.data('business-id'),
                    rating: rating,
                    page: 1
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.mp-reviews-list').html(response.data.html);
                    }
                }
            });
        },

        /**
         * Handle sort change
         */
        handleSortChange: function(e) {
            const sort = $(e.currentTarget).val();
            const $container = $(e.currentTarget).closest('.mp-reviews-container');
            
            $container.find('.mp-reviews-list').html('<div class="mp-loading"></div>');
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_get_reviews',
                    nonce: mpBusinessPages.nonce,
                    business_id: $container.data('business-id'),
                    sort: sort,
                    page: 1
                },
                success: function(response) {
                    if (response.success) {
                        $container.find('.mp-reviews-list').html(response.data.html);
                    }
                }
            });
        },

        /**
         * Handle submit response
         */
        handleSubmitResponse: function(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const $btn = $form.find('button[type="submit"]');
            const $message = $form.find('.mp-response-message');
            
            $btn.prop('disabled', true).text('Submitting...');
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_submit_response',
                    nonce: mpBusinessPages.nonce,
                    review_id: $form.find('input[name="review_id"]').val(),
                    content: $form.find('textarea[name="content"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $message.addClass('mp-success').text(response.data.message);
                        $form[0].reset();
                    } else {
                        $message.addClass('mp-error').text(response.data.message);
                    }
                },
                error: function() {
                    $message.addClass('mp-error').text(mpBusinessPages.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Submit Response');
                }
            });
        },

        /**
         * Handle search
         */
        handleSearch: function(e) {
            const $form = $(e.currentTarget);
            const $input = $form.find('input[name="search"]');
            
            if (!$input.val().trim()) {
                e.preventDefault();
            }
        },

        /**
         * Load more reviews (infinite scroll)
         */
        loadMoreReviews: function() {
            if (this.isLoading || this.allLoaded) return;
            
            const $container = $('.mp-reviews-container');
            const businessId = $container.data('business-id');
            const currentPage = $container.data('page') || 1;
            const maxPages = $container.data('max-pages') || 1;
            
            if (currentPage >= maxPages) {
                this.allLoaded = true;
                return;
            }
            
            this.isLoading = true;
            
            $.ajax({
                url: mpBusinessPages.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_get_reviews',
                    nonce: mpBusinessPages.nonce,
                    business_id: businessId,
                    page: currentPage + 1
                },
                success: function(response) {
                    if (response.success && response.data.reviews.length > 0) {
                        $container.find('.mp-reviews-list').append(response.data.html);
                        $container.data('page', currentPage + 1);
                    } else {
                        BusinessPages.allLoaded = true;
                    }
                },
                complete: function() {
                    BusinessPages.isLoading = false;
                }
            });
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            this.showMessage(message, 'success');
        },

        /**
         * Show error message
         */
        showError: function(message) {
            this.showMessage(message, 'error');
        },

        /**
         * Show message
         */
        showMessage: function(message, type) {
            const $message = $('<div class="mp-message mp-message-' + type + '">' + message + '</div>');
            
            $('body').append($message);
            
            $message.fadeIn().delay(3000).fadeOut(function() {
                $(this).remove();
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        BusinessPages.init();
    });

})(jQuery);