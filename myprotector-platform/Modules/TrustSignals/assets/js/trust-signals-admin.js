/**
 * Trust Signals Admin JavaScript
 */

(function($) {
    'use strict';

    const TrustSignalsAdmin = {
        initialized: false,

        init: function() {
            if (this.initialized) return;
            this.initialized = true;

            this.bindEvents();
        },

        bindEvents: function() {
            // Override button click
            $(document).on('click', '.mp-override-btn', this.handleOverrideClick.bind(this));

            // View details button click
            $(document).on('click', '.mp-view-details-btn', this.handleViewDetails.bind(this));

            // Modal close buttons
            $(document).on('click', '.mp-modal-close, .mp-modal-cancel', this.closeModals.bind(this));

            // Modal submit
            $(document).on('click', '.mp-modal-submit', this.handleOverrideSubmit.bind(this));

            // Modal overlay click (close on background click)
            $(document).on('click', '.mp-modal', this.handleModalBackgroundClick.bind(this));

            // Recalculate button
            $(document).on('click', '.mp-recalculate-btn', this.handleRecalculate.bind(this));

            // Clear override button
            $(document).on('click', '.mp-clear-override-btn', this.handleClearOverride.bind(this));

            // Form submission (edit page)
            $(document).on('submit', '#mp-manual-override-form', this.handleFormSubmit.bind(this));
        },

        handleOverrideClick: function(e) {
            const $btn = $(e.currentTarget);
            const companyId = $btn.data('company-id');

            $('#mp-override-company-id').val(companyId);
            $('#mp-override-reason').val('');

            this.showModal('#mp-override-modal');
        },

        handleViewDetails: function(e) {
            const $btn = $(e.currentTarget);
            const companyId = $btn.data('company-id');

            $('#mp-details-content').html('<div class="mp-loading"></div>');
            this.showModal('#mp-details-modal');

            this.fetchTrustSignalDetails(companyId);
        },

        fetchTrustSignalDetails: function(companyId) {
            const restUrl = mpTrustSignals.restUrl || window.location.origin + '/wp-json/myprotector/v1/';

            $.ajax({
                url: restUrl + 'trust-signals/' + companyId + '/details',
                type: 'GET',
                headers: {
                    'X-WP-Nonce': mpTrustSignals.nonce
                },
                success: function(response) {
                    if (response.success) {
                        this.renderDetailsModal(response.data);
                    } else {
                        $('#mp-details-content').html(
                            '<p class="mp-message-error">' + (response.message || 'Error loading details') + '</p>'
                        );
                    }
                }.bind(this),
                error: function() {
                    $('#mp-details-content').html(
                        '<p class="mp-message-error">An error occurred while loading details.</p>'
                    );
                }
            });
        },

        renderDetailsModal: function(data) {
            const signal = data.signal;
            const requirements = data.requirements || {};

            let html = '<div class="mp-details-company">';
            html += '<h3>' + (data.company?.company_name || 'Unknown Company') + '</h3>';

            // Status
            html += '<div class="mp-details-status">';
            html += '<strong>Current Status:</strong> ';
            html += '<span class="mp-status-badge mp-status-' + signal.status + '">' + signal.status.toUpperCase() + '</span>';

            if (signal.is_overridden) {
                html += ' <em>(Manually overridden)</em>';
            }
            html += '</div>';

            // Requirements
            html += '<h4>Requirements Checklist</h4>';
            html += '<ul class="mp-details-req-list">';

            const reqLabels = {
                insurance_page: 'Insurance Page',
                refund_history: 'Refund History',
                claims_page: 'Claims Page',
                terms_page: 'Terms Page',
                active_subscription: 'Active Subscription'
            };

            let metCount = 0;
            let totalCount = 0;

            for (const [key, req] of Object.entries(requirements)) {
                totalCount++;
                if (req.met) metCount++;

                const icon = req.met ? '✓' : '✗';
                const color = req.met ? '#28a745' : '#dc3545';

                html += '<li style="color:' + color + '">';
                html += '<span class="mp-req-icon">' + icon + '</span> ';
                html += '<span class="mp-req-label">' + (reqLabels[key] || key) + '</span>';
                html += ' - ' + (req.description || '');
                html += '</li>';
            }

            html += '</ul>';

            html += '<div class="mp-details-progress">';
            html += '<strong>Progress:</strong> ' + metCount + '/' + totalCount + ' requirements met';
            html += '</div>';

            html += '</div>';

            // Add styles
            html += '<style>';
            html += '.mp-details-company { padding: 10px; }';
            html += '.mp-details-status { margin: 15px 0; padding: 10px; background: #f5f5f5; border-radius: 4px; }';
            html += '.mp-details-req-list { list-style: none; padding: 0; margin: 10px 0; }';
            html += '.mp-details-req-list li { padding: 8px; border-bottom: 1px solid #eee; }';
            html += '.mp-details-req-list .mp-req-icon { margin-right: 8px; font-weight: bold; }';
            html += '.mp-details-progress { margin-top: 15px; padding: 10px; background: #e7f3ff; border-radius: 4px; }';
            html += '</style>';

            $('#mp-details-content').html(html);
        },

        handleOverrideSubmit: function() {
            const companyId = $('#mp-override-company-id').val();
            const status = $('#mp-override-status').val();
            const reason = $('#mp-override-reason').val().trim();

            if (reason.length < 10) {
                alert(mpTrustSignals.strings.confirmOverride || 'Please enter a reason (minimum 10 characters).');
                return;
            }

            const $btn = $('.mp-modal-submit');
            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: mpTrustSignals.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_override_trust_signal',
                    nonce: mpTrustSignals.nonce,
                    company_id: companyId,
                    status: status,
                    reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        alert(mpTrustSignals.strings.overrideSuccess || 'Trust signal overridden successfully.');
                        location.reload();
                    } else {
                        alert(response.data?.message || mpTrustSignals.strings.overrideError || 'An error occurred.');
                        $btn.prop('disabled', false).text('Apply Override');
                    }
                },
                error: function() {
                    alert(mpTrustSignals.strings.overrideError || 'An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Apply Override');
                }
            });
        },

        handleRecalculate: function(e) {
            const $btn = $(e.currentTarget);
            const companyId = $btn.data('company-id');

            $btn.prop('disabled', true).text('Recalculating...');

            $.ajax({
                url: mpTrustSignals.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mp_refresh_trust_signal',
                    nonce: mpTrustSignals.nonce,
                    company_id: companyId
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data?.message || 'An error occurred.');
                        $btn.prop('disabled', false).text('Recalculate Automatically');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Recalculate Automatically');
                }
            });
        },

        handleClearOverride: function(e) {
            const $btn = $(e.currentTarget);
            const companyId = $btn.data('company-id');

            if (!confirm('Are you sure you want to clear this override? The trust signal will be recalculated automatically.')) {
                return;
            }

            const restUrl = mpTrustSignals.restUrl || window.location.origin + '/wp-json/myprotector/v1/';

            $.ajax({
                url: restUrl + 'admin/trust-signals/' + companyId + '/clear-override',
                type: 'POST',
                headers: {
                    'X-WP-Nonce': mpTrustSignals.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'An error occurred.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        handleFormSubmit: function(e) {
            e.preventDefault();

            const $form = $(e.currentTarget);
            const formData = new FormData($form[0]);

            formData.append('nonce', mpTrustSignals.nonce);

            $.ajax({
                url: mpTrustSignals.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Trust signal overridden successfully.');
                        location.reload();
                    } else {
                        alert(response.data?.message || 'An error occurred.');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        handleModalBackgroundClick: function(e) {
            if ($(e.target).hasClass('mp-modal')) {
                this.closeModals();
            }
        },

        showModal: function(selector) {
            $(selector).addClass('show').show();
            $('body').css('overflow', 'hidden');
        },

        closeModals: function() {
            $('.mp-modal').removeClass('show').hide();
            $('body').css('overflow', '');
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if (typeof mpTrustSignals !== 'undefined') {
            TrustSignalsAdmin.init();
        }
    });

    // Expose to global scope for external access
    window.MPTrustSignalsAdmin = TrustSignalsAdmin;

})(jQuery);