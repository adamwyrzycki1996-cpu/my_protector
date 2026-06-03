<?php
/**
 * MyProtector Platform - Trust Signals Public Controller
 * 
 * Public-facing display for trust signals
 * 
 * @package MyProtector\Modules\TrustSignals\Public
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Public_;

use MyProtector\Modules\TrustSignals\TrustSignals;
use MyProtector\Modules\TrustSignals\Services\TrustSignalService;

class TrustSignalsPublicController {
    /**
     * Module reference
     * 
     * @var TrustSignals
     */
    protected $module;

    /**
     * Service instance
     * 
     * @var TrustSignalService
     */
    protected $service;

    /**
     * Constructor
     * 
     * @param TrustSignals $module
     */
    public function __construct(TrustSignals $module) {
        $this->module = $module;
        $this->service = new TrustSignalService();
        
        $this->registerShortcodes();
    }

    /**
     * Register shortcodes
     * 
     * @return void
     */
    protected function registerShortcodes(): void {
        add_shortcode('trust_signal', [$this, 'renderTrustSignalShortcode']);
        add_shortcode('trust_signal_badge', [$this, 'renderTrustSignalBadgeShortcode']);
    }

    /**
     * Render trust signal shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustSignalShortcode(array $atts = []): string {
        $atts = shortcode_atts([
            'company_id' => 0,
            'show_requirements' => 'true',
            'show_history' => 'false',
            'template' => 'default',
        ], $atts, 'trust_signal');

        $companyId = (int) $atts['company_id'];
        
        if (!$companyId) {
            return '<p class="mp-trust-signal-error">' . __('Company ID required.', 'myprotector-platform') . '</p>';
        }

        $signal = $this->service->getForCompany($companyId);
        $details = $this->service->getDetailsForCompany($companyId);

        if (!$signal) {
            return '<p class="mp-trust-signal-error">' . __('Trust signal not found.', 'myprotector-platform') . '</p>';
        }

        ob_start();
        include $this->module->getPath('templates/public/trust-signal-display.php');
        return ob_get_clean();
    }

    /**
     * Render trust signal badge shortcode
     * 
     * @param array $atts
     * @return string
     */
    public function renderTrustSignalBadgeShortcode(array $atts = []): string {
        $atts = shortcode_atts([
            'company_id' => 0,
            'size' => 'medium',
            'show_label' => 'true',
        ], $atts, 'trust_signal_badge');

        $companyId = (int) $atts['company_id'];
        
        if (!$companyId) {
            return '';
        }

        $signal = $this->service->getForCompany($companyId);

        if (!$signal) {
            return '';
        }

        $status = strtoupper($signal['status']);
        $color = $this->getStatusColor($signal['status']);
        $sizeClass = 'mp-badge-' . esc_attr($atts['size']);

        $html = '<div class="mp-trust-badge ' . esc_attr($sizeClass) . '" style="background-color: ' . esc_attr($color) . ';">';
        $html .= '<span class="mp-badge-status">' . esc_html($status) . '</span>';
        
        if ($signal['is_overridden'] && $atts['show_label'] === 'true') {
            $html .= '<span class="mp-badge-verified">' . __('Verified', 'myprotector-platform') . '</span>';
        }
        
        $html .= '</div>';

        return $html;
    }

    /**
     * Get status color
     * 
     * @param string $status
     * @return string
     */
    protected function getStatusColor(string $status): string {
        $colors = [
            'green' => '#28a745',
            'amber' => '#ffc107',
            'red' => '#dc3545',
        ];

        return $colors[$status] ?? $colors['red'];
    }
}