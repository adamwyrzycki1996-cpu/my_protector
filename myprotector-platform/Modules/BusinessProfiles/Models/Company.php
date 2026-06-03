<?php
/**
 * MyProtector Platform - Company Model
 * 
 * Represents a business/company entity in the system
 * 
 * @package MyProtector\Modules\BusinessProfiles\Models
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Models;

class Company {
    /**
     * Company ID
     * 
     * @var int
     */
    public $company_id;

    /**
     * WordPress user ID (owner)
     * 
     * @var int
     */
    public $user_id;

    /**
     * Company name
     * 
     * @var string
     */
    public $company_name;

    /**
     * URL-friendly slug
     * 
     * @var string
     */
    public $company_slug;

    /**
     * Company description
     * 
     * @var string
     */
    public $company_description;

    /**
     * Company website URL
     * 
     * @var string
     */
    public $company_website;

    /**
     * Company logo URL
     * 
     * @var string
     */
    public $company_logo;

    /**
     * Company address
     * 
     * @var string
     */
    public $company_address;

    /**
     * Company phone
     * 
     * @var string
     */
    public $company_phone;

    /**
     * Company email
     * 
     * @var string
     */
    public $company_email;

    /**
     * Company category ID
     * 
     * @var int
     */
    public $company_category;

    /**
     * Insurance provider name
     * 
     * @var string
     */
    public $insurance_name;

    /**
     * Insurance URL
     * 
     * @var string
     */
    public $insurance_url;

    /**
     * Terms and conditions URL
     * 
     * @var string
     */
    public $terms_url;

    /**
     * Promise/ pledge page URL
     * 
     * @var string
     */
    public $promise_page_url;

    /**
     * Promise page title
     * 
     * @var string
     */
    public $promise_page_title;

    /**
     * Company status
     * Options: pending, approved, rejected, suspended
     * 
     * @var string
     */
    public $status;

    /**
     * Trust score (0.00 - 100.00)
     * 
     * @var float
     */
    public $trust_score;

    /**
     * Total number of reviews
     * 
     * @var int
     */
    public $total_reviews;

    /**
     * Average rating
     * 
     * @var float
     */
    public $avg_rating;

    /**
     * Is featured company
     * 
     * @var bool
     */
    public $is_featured;

    /**
     * Rejection reason (if rejected)
     * 
     * @var string
     */
    public $rejection_reason;

    /**
     * Approved by admin user ID
     * 
     * @var int
     */
    public $approved_by;

    /**
     * Approved at timestamp
     * 
     * @var int
     */
    public $approved_at;

    /**
     * Created timestamp
     * 
     * @var int
     */
    public $created_at;

    /**
     * Updated timestamp
     * 
     * @var int
     */
    public $updated_at;

    /**
     * Available statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Get all status options
     * 
     * @return array
     */
    public static function getStatuses(): array {
        return [
            self::STATUS_PENDING => __('Pending', 'myprotector-platform'),
            self::STATUS_APPROVED => __('Approved', 'myprotector-platform'),
            self::STATUS_REJECTED => __('Rejected', 'myprotector-platform'),
            self::STATUS_SUSPENDED => __('Suspended', 'myprotector-platform'),
        ];
    }

    /**
     * Get status label
     * 
     * @param string $status
     * @return string
     */
    public static function getStatusLabel(string $status): string {
        $statuses = self::getStatuses();
        return $statuses[$status] ?? $status;
    }

    /**
     * Get status color class for admin UI
     * 
     * @param string $status
     * @return string
     */
    public static function getStatusColorClass(string $status): string {
        $classes = [
            self::STATUS_PENDING => 'mp-status-pending',
            self::STATUS_APPROVED => 'mp-status-approved',
            self::STATUS_REJECTED => 'mp-status-rejected',
            self::STATUS_SUSPENDED => 'mp-status-suspended',
        ];
        return $classes[$status] ?? '';
    }

    /**
     * Check if company is approved
     * 
     * @return bool
     */
    public function isApproved(): bool {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if company is pending
     * 
     * @return bool
     */
    public function isPending(): bool {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if company is rejected
     * 
     * @return bool
     */
    public function isRejected(): bool {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if user is owner
     * 
     * @param int $user_id
     * @return bool
     */
    public function isOwner(int $user_id): bool {
        return $this->user_id === $user_id;
    }

    /**
     * Create Company from database row
     * 
     * @param object $row
     * @return self
     */
    public static function fromRow(object $row): self {
        $company = new self();
        
        $company->company_id = (int) $row->company_id;
        $company->user_id = (int) $row->user_id;
        $company->company_name = $row->company_name ?? '';
        $company->company_slug = $row->company_slug ?? '';
        $company->company_description = $row->company_description ?? '';
        $company->company_website = $row->company_website ?? '';
        $company->company_logo = $row->company_logo ?? '';
        $company->company_address = $row->company_address ?? '';
        $company->company_phone = $row->company_phone ?? '';
        $company->company_email = $row->company_email ?? '';
        $company->company_category = (int) ($row->company_category ?? 0);
        $company->insurance_name = $row->insurance_name ?? '';
        $company->insurance_url = $row->insurance_url ?? '';
        $company->terms_url = $row->terms_url ?? '';
        $company->promise_page_url = $row->promise_page_url ?? '';
        $company->promise_page_title = $row->promise_page_title ?? '';
        $company->status = $row->status ?? self::STATUS_PENDING;
        $company->trust_score = (float) ($row->trust_score ?? 0);
        $company->total_reviews = (int) ($row->total_reviews ?? 0);
        $company->avg_rating = (float) ($row->avg_rating ?? 0);
        $company->is_featured = (bool) ($row->is_featured ?? false);
        $company->rejection_reason = $row->rejection_reason ?? '';
        $company->approved_by = (int) ($row->approved_by ?? 0);
        $company->approved_at = $row->approved_at ? strtotime($row->approved_at) : 0;
        $company->created_at = $row->created_at ? strtotime($row->created_at) : 0;
        $company->updated_at = $row->updated_at ? strtotime($row->updated_at) : 0;
        
        return $company;
    }

    /**
     * Convert to array for API/display
     * 
     * @return array
     */
    public function toArray(): array {
        return [
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'company_name' => $this->company_name,
            'company_slug' => $this->company_slug,
            'company_description' => $this->company_description,
            'company_website' => $this->company_website,
            'company_logo' => $this->company_logo,
            'company_address' => $this->company_address,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'company_category' => $this->company_category,
            'insurance_name' => $this->insurance_name,
            'insurance_url' => $this->insurance_url,
            'terms_url' => $this->terms_url,
            'promise_page_url' => $this->promise_page_url,
            'promise_page_title' => $this->promise_page_title,
            'status' => $this->status,
            'status_label' => self::getStatusLabel($this->status),
            'trust_score' => $this->trust_score,
            'total_reviews' => $this->total_reviews,
            'avg_rating' => $this->avg_rating,
            'is_featured' => $this->is_featured,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at ? date_i18n('Y-m-d H:i:s', $this->created_at) : null,
            'updated_at' => $this->updated_at ? date_i18n('Y-m-d H:i:s', $this->updated_at) : null,
        ];
    }

    /**
     * Convert to short array (for listings)
     * 
     * @return array
     */
    public function toShortArray(): array {
        return [
            'company_id' => $this->company_id,
            'company_name' => $this->company_name,
            'company_slug' => $this->company_slug,
            'company_logo' => $this->company_logo,
            'company_website' => $this->company_website,
            'status' => $this->status,
            'status_label' => self::getStatusLabel($this->status),
            'trust_score' => $this->trust_score,
            'total_reviews' => $this->total_reviews,
            'avg_rating' => $this->avg_rating,
            'created_at' => $this->created_at ? date_i18n('Y-m-d H:i:s', $this->created_at) : null,
        ];
    }

    /**
     * Get company URL
     * 
     * @return string
     */
    public function getUrl(): string {
        return get_permalink($this->company_id);
    }

    /**
     * Get admin edit URL
     * 
     * @return string
     */
    public function getAdminUrl(): string {
        return admin_url('post.php?post=' . $this->company_id . '&action=edit');
    }

    /**
     * Get logo HTML
     * 
     * @param int $size
     * @return string
     */
    public function getLogoHtml(int $size = 150): string {
        if (empty($this->company_logo)) {
            return '<div class="mp-company-logo-placeholder" style="width:' . $size . 'px;height:' . $size . 'px;">' . substr($this->company_name, 0, 1) . '</div>';
        }
        
        return '<img src="' . esc_url($this->company_logo) . '" alt="' . esc_attr($this->company_name) . '" style="width:' . $size . 'px;height:' . $size . 'px;object-fit:contain;" />';
    }

    /**
     * Check if has required URLs for trust status
     * 
     * @return array
     */
    public function getTrustRequirementsStatus(): array {
        return [
            'insurance' => !empty($this->insurance_url),
            'terms' => !empty($this->terms_url),
            'promise' => !empty($this->promise_page_url),
        ];
    }

    /**
     * Get fill percentage for trust status
     * 
     * @return float
     */
    public function getTrustFillPercentage(): float {
        $requirements = $this->getTrustRequirementsStatus();
        $filled = array_sum($requirements);
        return ($filled / 3) * 100;
    }
}