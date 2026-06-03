/**
 * MyProtector Platform - Business Public JavaScript
 * 
 * Public-facing JavaScript for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles
 */

(function($) {
    'use strict';

    var BusinessPublic = {
        /**
         * Configuration
         */
        config: {
            ajaxUrl: mpBusinessPublic.ajaxUrl,
            nonce: mpBusinessPublic.nonce,
            apiUrl: mpBusinessPublic.apiUrl,
            strings: mpBusinessPublic.strings
        },

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initListings();
            this.initShortcodes();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Logo upload
            this.bindLogoUpload();
            
            // Form submission
            this.bindFormSubmission();
            
            // Trust badge interactions
            this.bindTrustBadge();
        },

        /**
         * Bind logo upload events
         */
        bindLogoUpload: function() {
            var frame;
            
            $(document).on('click', '.mp-upload-logo-btn', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var $form = $btn.closest('form');
                
                if (frame) {
                    frame.open();
                    return;
                }
                
                frame = wp.media({
                    title: mpBusinessPublic.strings.selectLogo || 'Select Logo',
                    multiple: false,
                    library: { type: 'image' },
                    button: { text: mpBusinessPublic.strings.useAsLogo || 'Use as Logo' }
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    
                    $form.find('#mp-logo-url').val(attachment.url);
                    $form.find('#mp-logo-preview').html('<img src="' + attachment.url + '" alt="">');
                    $form.find('.mp-remove-logo-btn').show();
                    $btn.text(mpBusinessPublic.strings.changeLogo || 'Change Logo');
                });
                
                frame.open();
            });
            
            $(document).on('click', '.mp-remove-logo-btn', function(e) {
                e.preventDefault();
                
                var $form = $(this).closest('form');
                
                $form.find('#mp-logo-url').val('');
                $form.find('#mp-logo-preview').html(
                    '<div class="mp-logo-placeholder">' + 
                    (mpBusinessPublic.strings.noLogo || 'No logo uploaded') + 
                    '</div>'
                );
                $(this).hide();
                $form.find('.mp-upload-logo-btn').text(mpBusinessPublic.strings.uploadLogo || 'Upload Logo');
            });
        },

        /**
         * Bind form submission
         */
        bindFormSubmission: function() {
            $(document).on('submit', '.mp-business-form', function(e) {
                e.preventDefault();
                
                var $form = $(this);
                var $submitBtn = $form.find('#mp-submit-btn');
                var $messageDiv = $form.find('#mp-form-message');
                var isEdit = $form.find('input[name="company_id"]').length > 0;
                
                // Validate
                if (!BusinessPublic.validateForm($form)) {
                    return;
                }
                
                // Disable button
                $submitBtn.prop('disabled', true).text(mpBusinessPublic.strings.submitting || 'Submitting...');
                $messageDiv.removeClass('mp-success mp-error').html('');
                
                // Collect form data
                var formData = {
                    action: isEdit ? 'mp_update_business_profile' : 'mp_submit_business_profile',
                    nonce: $form.find('#mp_form_nonce').val(),
                    company_id: $form.find('input[name="company_id"]').val() || 0,
                    company_name: $form.find('#company_name').val(),
                    company_description: $form.find('#company_description').val(),
                    company_logo: $form.find('#mp-logo-url').val(),
                    company_website: $form.find('#company_website').val(),
                    company_phone: $form.find('#company_phone').val(),
                    company_email: $form.find('#company_email').val(),
                    company_address: $form.find('#company_address').val(),
                    insurance_name: $form.find('#insurance_name').val(),
                    insurance_url: $form.find('#insurance_url').val(),
                    terms_url: $form.find('#terms_url').val(),
                    promise_page_url: $form.find('#promise_page_url').val(),
                    promise_page_title: $form.find('#promise_page_title').val()
                };
                
                // Submit
                $.ajax({
                    url: mpBusinessPublic.ajaxUrl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $messageDiv.addClass('mp-success').html(
                                response.data.message || mpBusinessPublic.strings.success || 'Success!'
                            );
                            
                            // Reset form if creating new
                            if (!isEdit) {
                                $form[0].reset();
                                $form.find('#mp-logo-url').val('');
                                $form.find('#mp-logo-preview').html(
                                    '<div class="mp-logo-placeholder">' + 
                                    (mpBusinessPublic.strings.noLogo || 'No logo uploaded') + 
                                    '</div>'
                                );
                            }
                        } else {
                            $messageDiv.addClass('mp-error').html(
                                response.data.message || mpBusinessPublic.strings.error || 'An error occurred.'
                            );
                        }
                    },
                    error: function() {
                        $messageDiv.addClass('mp-error').html(
                            mpBusinessPublic.strings.error || 'An error occurred. Please try again.'
                        );
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text(
                            isEdit 
                                ? (mpBusinessPublic.strings.updateProfile || 'Update Profile')
                                : (mpBusinessPublic.strings.submitForReview || 'Submit for Review')
                        );
                    }
                });
            });
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            var valid = true;
            var errors = [];
            
            // Company name required
            var $name = $form.find('#company_name');
            if (!$name.val().trim()) {
                valid = false;
                errors.push(mpBusinessPublic.strings.nameRequired || 'Company name is required.');
                $name.addClass('mp-field-error');
            } else {
                $name.removeClass('mp-field-error');
            }
            
            // Website URL validation
            var $website = $form.find('#company_website');
            if ($website.val() && !BusinessPublic.isValidUrl($website.val())) {
                valid = false;
                errors.push(mpBusinessPublic.strings.invalidWebsite || 'Please enter a valid website URL.');
                $website.addClass('mp-field-error');
            } else {
                $website.removeClass('mp-field-error');
            }
            
            // Email validation
            var $email = $form.find('#company_email');
            if ($email.val() && !BusinessPublic.isValidEmail($email.val())) {
                valid = false;
                errors.push(mpBusinessPublic.strings.invalidEmail || 'Please enter a valid email address.');
                $email.addClass('mp-field-error');
            } else {
                $email.removeClass('mp-field-error');
            }
            
            // Trust URLs validation
            var trustFields = ['#insurance_url', '#terms_url', '#promise_page_url'];
            trustFields.forEach(function(selector) {
                var $field = $form.find(selector);
                if ($field.val() && !BusinessPublic.isValidUrl($field.val())) {
                    valid = false;
                    errors.push('Please enter a valid URL for ' + $field.prev('label').text());
                    $field.addClass('mp-field-error');
                } else {
                    $field.removeClass('mp-field-error');
                }
            });
            
            if (!valid && errors.length > 0) {
                var $messageDiv = $form.find('#mp-form-message');
                $messageDiv.addClass('mp-error').html(errors.join('<br>'));
            }
            
            return valid;
        },

        /**
         * Bind trust badge interactions
         */
        bindTrustBadge: function() {
            $(document).on('click', '.mp-trust-badge', function(e) {
                e.preventDefault();
                
                var $badge = $(this);
                var $card = $badge.closest('.mp-business-card, .mp-business-profile');
                
                if ($card.length) {
                    $('html, body').animate({
                        scrollTop: $card.find('.mp-profile-trust-info, .mp-profile-trust').offset().top - 100
                    }, 500);
                }
            });
        },

        /**
         * Initialize listings
         */
        initListings: function() {
            var $listings = $('.mp-business-listing');
            
            $listings.each(function() {
                var $listing = $(this);
                var $search = $listing.find('#mp-business-search');
                var $grid = $listing.find('.mp-businesses-grid');
                var $cards = $grid.find('.mp-business-card');
                
                // Search functionality
                if ($search.length) {
                    $search.on('input', function() {
                        var query = $(this).val().toLowerCase().trim();
                        
                        $cards.each(function() {
                            var $card = $(this);
                            var name = $card.find('.mp-card-name, .mp-profile-name').text().toLowerCase();
                            
                            if (query === '' || name.indexOf(query) !== -1) {
                                $card.show();
                            } else {
                                $card.hide();
                            }
                        });
                        
                        // Show no results message
                        var visibleCards = $grid.find('.mp-business-card:visible').length;
                        if (visibleCards === 0 && query !== '') {
                            if (!$listing.find('.mp-no-results').length) {
                                $grid.after('<p class="mp-no-results" style="text-align:center;padding:20px;color:#6b7280;">' + 
                                    (mpBusinessPublic.strings.noResults || 'No businesses match your search.') + '</p>');
                            }
                        } else {
                            $listing.find('.mp-no-results').remove();
                        }
                    });
                }
            });
        },

        /**
         * Initialize shortcodes
         */
        initShortcodes: function() {
            // Any shortcode-specific initialization can go here
        },

        /**
         * Check if URL is valid
         */
        isValidUrl: function(url) {
            if (!url) return true;
            return /^https?:\/\//.test(url) && /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/.test(url);
        },

        /**
         * Check if email is valid
         */
        isValidEmail: function(email) {
            if (!email) return true;
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        /**
         * Show toast notification
         */
        showToast: function(message, type) {
            type = type || 'info';
            
            var $toast = $('<div class="mp-toast mp-toast-' + type + '">' + message + '</div>');
            $('body').append($toast);
            
            setTimeout(function() {
                $toast.addClass('mp-toast-show');
            }, 100);
            
            setTimeout(function() {
                $toast.removeClass('mp-toast-show').fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BusinessPublic.init();
    });

    // Expose globally
    window.mpBusinessPublic = BusinessPublic;

})(jQuery);