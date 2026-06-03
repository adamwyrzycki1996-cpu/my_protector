<?php
/**
 * MyProtector Platform - Trust Signals Admin Controller
 * 
 * Admin interface for managing trust signals
 * 
 * @package MyProtector\Modules\TrustSignals\Admin
 * @version 1.0.0
 */

namespace MyProtector\Modules\TrustSignals\Admin;

use MyProtector\Modules\TrustSignals\TrustSignals;
use MyProtector\Modules\TrustSignals\Services\TrustSignalService;
use MyProtector\Modules\TrustSignals\Services\TrustSignalAdminService;

class TrustSignalsAdminController {
    /**
     * Module reference
     * 
     * @var TrustSignals
     */
    protected $module;

    /**
     * Service instances
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Constructor
     * 
     * @param TrustSignals $module
     */
    public function __construct(TrustSignals $module) {
        $this->module = $module;
        $this->services['trust_signals'] = new TrustSignalService();
        $this->services['admin'] = new TrustSignalAdminService();
    }

    /**
     * Render the list page
     * 
     * @return void
     */
    public function renderListPage(): void {
        // Get status filter
        $statusFilter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $perPage = 20;

        // Get trust signals
        $args = [
            'status' => $statusFilter ?: null,
            'search' => $search ?: null,
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];

        $signals = $this->services['trust_signals']->list($args);
        $distribution = $this->services['trust_signals']->getStatusDistribution();

        // Include template
        include $this->module->getPath('templates/admin/trust-signals-list.php');
    }

    /**
     * Render the edit/override page for a single company
     * 
     * @return void
     */
    public function renderEditPage(): void {
        $companyId = isset($_GET['company_id']) ? (int) $_GET['company_id'] : 0;
        
        if (!$companyId) {
            wp_die(__('Invalid company ID.', 'myprotector-platform'));
        }

        $signal = $this->services['trust_signals']->getForCompany($companyId);
        $details = $this->services['trust_signals']->getDetailsForCompany($companyId);
        $history = $this->services['admin']->getOverrideHistory($companyId);
        $statusInfo = $this->services['admin']->getStatusDisplayInfo($signal['status']);

        include $this->module->getPath('templates/admin/trust-signals-edit.php');
    }

    /**
     * Render the dashboard overview
     * 
     * @return void
     */
    public function renderDashboard(): void {
        $distribution = $this->services['trust_signals']->getStatusDistribution();
        $attentionRequired = $this->services['admin']->getAttentionRequired();
        $overrideStats = $this->services['admin']->getOverrideStats();

        include $this->module->getPath('templates/admin/trust-signals-dashboard.php');
    }
}