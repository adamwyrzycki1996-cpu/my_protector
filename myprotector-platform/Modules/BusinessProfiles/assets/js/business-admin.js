/**
 * MyProtector Platform - Business Admin JavaScript
 * 
 * Admin-specific JavaScript for business profile management
 * 
 * @package MyProtector\Modules\BusinessProfiles
 */

(function($) {
    'use strict';

    var BusinessAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initLogoUpload();
            this.initFormValidation();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Status change confirmation
            $(document).on('click', '.mp-approve-btn', this.handleApprove.bind(this));
            $(document).on('click', '.mp-reject-btn', this.handleReject.bind(this));
            $(document).on('click', '.mp-suspend-btn', this.handleSuspend.bind(this));
            $(document).on('click', '.mp-delete-btn', this.handleDelete.bind(this));
            
            // Bulk actions
            $(document).on('change', '.mp-bulk-select', this.handleBulkSelect.bind(this));
            $(document).on('click', '.mp-bulk-action-btn', this.handleBulkAction.bind(this));
            
            // Search
            $(document).on('submit', '.mp-admin-search-form', this.handleSearch.bind(this));
        },

        /**
         * Initialize logo upload
         */
        initLogoUpload: function() {
            var frame;
            
            $(document).on('click', '.mp-upload-logo-btn', function(e) {
                e.preventDefault();
                
                if (frame) {
                    frame.open();
                    return;
                }
                
                frame = wp.media({
                    title: mpBusinessAdmin.strings.uploadLogo || 'Select Logo',
                    multiple: false,
                    library: { type: 'image' },
                    button: { text: 'Use as Logo' }
                });
                
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    
                    $('#mp-logo-url').val(attachment.url);
                    $('#mp-logo-preview').html('<img src="' + attachment.url + '" alt="">');
                    $('.mp-remove-logo-btn').show();
                    $('.mp-upload-logo-btn').text(mpBusinessAdmin.strings.changeLogo || 'Change Logo');
                });
                
                frame.open();
            });
            
            $(document).on('click', '.mp-remove-logo-btn', function(e) {
                e.preventDefault();
                
                $('#mp-logo-url').val('');
                $('#mp-logo-preview').html('<div class="mp-logo-placeholder">' + 
                    (mpBusinessAdmin.strings.noLogo || 'No logo uploaded') + '</div>');
                $(this).hide();
                $('.mp-upload-logo-btn').text(mpBusinessAdmin.strings.uploadLogo || 'Upload Logo');
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            $(document).on('submit', '#mp-business-form', function(e) {
                var $form = $(this);
                var $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
                
                // Validate required fields
                var $required = $form.find('[required]');
                var valid = true;
                
                $required.each(function() {
                    if (!$(this).val().trim()) {
                        valid = false;
                        $(this).addClass('mp-field-error');
                    } else {
                        $(this).removeClass('mp-field-error');
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert(mpBusinessAdmin.strings.fillRequired || 'Please fill all required fields.');
                    return false;
                }
                
                $submitBtn.prop('disabled', true).addClass('mp-loading');
            });
            
            // Clear error on input
            $(document).on('input', '.mp-field-error', function() {
                $(this).removeClass('mp-field-error');
            });
        },

        /**
         * Handle approve
         */
        handleApprove: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var companyId = $btn.data('company-id');
            
            if (!confirm(mpBusinessAdmin.strings.confirmApprove || 'Approve this business profile?')) {
                return;
            }
            
            $btn.prop('disabled', true).addClass('mp-loading');
            
            $.ajax({
                url: mpBusinessAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_approve_business',
                    nonce: $btn.data('nonce'),
                    company_id: companyId
                },
                success: function(response) {
                    if (response.success) {
                        BusinessAdmin.showNotice('success', mpBusinessAdmin.strings.approveSuccess || 'Business profile approved!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        BusinessAdmin.showNotice('error', response.data.message || mpBusinessAdmin.strings.error);
                        $btn.prop('disabled', false).removeClass('mp-loading');
                    }
                },
                error: function() {
                    BusinessAdmin.showNotice('error', mpBusinessAdmin.strings.error);
                    $btn.prop('disabled', false).removeClass('mp-loading');
                }
            });
        },

        /**
         * Handle reject
         */
        handleReject: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var companyId = $btn.data('company-id');
            var reason = prompt(mpBusinessAdmin.strings.rejectReason || 'Enter rejection reason:');
            
            if (reason === null) {
                return;
            }
            
            $btn.prop('disabled', true).addClass('mp-loading');
            
            $.ajax({
                url: mpBusinessAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_reject_business',
                    nonce: $btn.data('nonce'),
                    company_id: companyId,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        BusinessAdmin.showNotice('success', mpBusinessAdmin.strings.rejectSuccess || 'Business profile rejected.');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        BusinessAdmin.showNotice('error', response.data.message || mpBusinessAdmin.strings.error);
                        $btn.prop('disabled', false).removeClass('mp-loading');
                    }
                },
                error: function() {
                    BusinessAdmin.showNotice('error', mpBusinessAdmin.strings.error);
                    $btn.prop('disabled', false).removeClass('mp-loading');
                }
            });
        },

        /**
         * Handle suspend
         */
        handleSuspend: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var companyId = $btn.data('company-id');
            var reason = prompt(mpBusinessAdmin.strings.suspendReason || 'Enter suspension reason (optional):');
            
            $btn.prop('disabled', true).addClass('mp-loading');
            
            $.ajax({
                url: mpBusinessAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_suspend_business',
                    nonce: $btn.data('nonce'),
                    company_id: companyId,
                    reason: reason || ''
                },
                success: function(response) {
                    if (response.success) {
                        BusinessAdmin.showNotice('success', mpBusinessAdmin.strings.suspendSuccess || 'Business profile suspended.');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        BusinessAdmin.showNotice('error', response.data.message || mpBusinessAdmin.strings.error);
                        $btn.prop('disabled', false).removeClass('mp-loading');
                    }
                },
                error: function() {
                    BusinessAdmin.showNotice('error', mpBusinessAdmin.strings.error);
                    $btn.prop('disabled', false).removeClass('mp-loading');
                }
            });
        },

        /**
         * Handle delete
         */
        handleDelete: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var companyId = $btn.data('company-id');
            
            if (!confirm(mpBusinessAdmin.strings.confirmDelete || 'Delete this business profile?')) {
                return;
            }
            
            $btn.prop('disabled', true).addClass('mp-loading');
            
            $.ajax({
                url: mpBusinessAdmin.apiUrl + '/businesses/' + companyId,
                type: 'DELETE',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', mpBusinessAdmin.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        BusinessAdmin.showNotice('success', mpBusinessAdmin.strings.deleteSuccess || 'Business profile deleted.');
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        BusinessAdmin.showNotice('error', response.message || mpBusinessAdmin.strings.error);
                        $btn.prop('disabled', false).removeClass('mp-loading');
                    }
                },
                error: function() {
                    BusinessAdmin.showNotice('error', mpBusinessAdmin.strings.error);
                    $btn.prop('disabled', false).removeClass('mp-loading');
                }
            });
        },

        /**
         * Handle bulk select
         */
        handleBulkSelect: function(e) {
            var selected = $(e.currentTarget).val();
            if (selected) {
                $('.mp-bulk-action-btn').removeAttr('disabled');
            } else {
                $('.mp-bulk-action-btn').attr('disabled', true);
            }
        },

        /**
         * Handle bulk action
         */
        handleBulkAction: function(e) {
            e.preventDefault();
            
            var $btn = $(e.currentTarget);
            var action = $('.mp-bulk-select').val();
            var $checked = $('input[name="businesses[]"]:checked');
            
            if (!action || $checked.length === 0) {
                return;
            }
            
            if (!confirm('Apply "' + action + '" to ' + $checked.length + ' items?')) {
                return;
            }
            
            var ids = $checked.map(function() {
                return $(this).val();
            }).get();
            
            $btn.prop('disabled', true).addClass('mp-loading');
            
            $.ajax({
                url: mpBusinessAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_bulk_business_action',
                    nonce: $btn.data('nonce'),
                    action_type: action,
                    business_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        BusinessAdmin.showNotice('success', response.data.message || 'Bulk action completed.');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        BusinessAdmin.showNotice('error', response.data.message || mpBusinessAdmin.strings.error);
                        $btn.prop('disabled', false).removeClass('mp-loading');
                    }
                },
                error: function() {
                    BusinessAdmin.showNotice('error', mpBusinessAdmin.strings.error);
                    $btn.prop('disabled', false).removeClass('mp-loading');
                }
            });
        },

        /**
         * Handle search
         */
        handleSearch: function(e) {
            var $form = $(e.currentTarget);
            var $searchInput = $form.find('input[name="s"]');
            
            if ($searchInput.val().length < 3 && $form.serialize().indexOf('status') === -1) {
                e.preventDefault();
                return;
            }
            
            $form.find('input[type="submit"]').addClass('mp-loading');
        },

        /**
         * Show notice
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="mp-notice mp-notice-' + type + '">' + message + '</div>');
            
            $('.mp-admin-wrap').prepend($notice);
            
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        BusinessAdmin.init();
    });

})(jQuery);