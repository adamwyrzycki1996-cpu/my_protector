<?php
/**
 * MyProtector Platform - Company Document Model
 * 
 * Represents a document associated with a company
 * 
 * @package MyProtector\Modules\BusinessProfiles\Models
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Models;

class CompanyDocument {
    /**
     * Document ID
     * 
     * @var int
     */
    public $document_id;

    /**
     * Company ID
     * 
     * @var int
     */
    public $company_id;

    /**
     * Document type
     * Options: insurance_certificate, business_license, incorporation, other
     * 
     * @var string
     */
    public $document_type;

    /**
     * Document name
     * 
     * @var string
     */
    public $document_name;

    /**
     * Document file URL
     * 
     * @var string
     */
    public $document_url;

    /**
     * Document file path
     * 
     * @var string
     */
    public $document_path;

    /**
     * Document mime type
     * 
     * @var string
     */
    public $mime_type;

    /**
     * File size in bytes
     * 
     * @var int
     */
    public $file_size;

    /**
     * Is verified by admin
     * 
     * @var bool
     */
    public $is_verified;

    /**
     * Verified by admin user ID
     * 
     * @var int
     */
    public $verified_by;

    /**
     * Verified at timestamp
     * 
     * @var int
     */
    public $verified_at;

    /**
     * Rejection reason
     * 
     * @var string
     */
    public $rejection_reason;

    /**
     * Uploaded by user ID
     * 
     * @var int
     */
    public $uploaded_by;

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
     * Document types
     */
    const TYPE_INSURANCE_CERTIFICATE = 'insurance_certificate';
    const TYPE_BUSINESS_LICENSE = 'business_license';
    const TYPE_INCORPORATION = 'incorporation';
    const TYPE_OTHER = 'other';

    /**
     * Get document type options
     * 
     * @return array
     */
    public static function getTypes(): array {
        return [
            self::TYPE_INSURANCE_CERTIFICATE => __('Insurance Certificate', 'myprotector-platform'),
            self::TYPE_BUSINESS_LICENSE => __('Business License', 'myprotector-platform'),
            self::TYPE_INCORPORATION => __('Incorporation Document', 'myprotector-platform'),
            self::TYPE_OTHER => __('Other Document', 'myprotector-platform'),
        ];
    }

    /**
     * Get type label
     * 
     * @param string $type
     * @return string
     */
    public static function getTypeLabel(string $type): string {
        $types = self::getTypes();
        return $types[$type] ?? $type;
    }

    /**
     * Get allowed mime types
     * 
     * @return array
     */
    public static function getAllowedMimeTypes(): array {
        return [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    /**
     * Get max file size (in bytes)
     * Default: 5MB
     * 
     * @return int
     */
    public static function getMaxFileSize(): int {
        return 5 * 1024 * 1024; // 5MB
    }

    /**
     * Create from database row
     * 
     * @param object $row
     * @return self
     */
    public static function fromRow(object $row): self {
        $doc = new self();
        
        $doc->document_id = (int) $row->document_id;
        $doc->company_id = (int) $row->company_id;
        $doc->document_type = $row->document_type ?? self::TYPE_OTHER;
        $doc->document_name = $row->document_name ?? '';
        $doc->document_url = $row->document_url ?? '';
        $doc->document_path = $row->document_path ?? '';
        $doc->mime_type = $row->mime_type ?? '';
        $doc->file_size = (int) ($row->file_size ?? 0);
        $doc->is_verified = (bool) ($row->is_verified ?? false);
        $doc->verified_by = (int) ($row->verified_by ?? 0);
        $doc->verified_at = $row->verified_at ? strtotime($row->verified_at) : 0;
        $doc->rejection_reason = $row->rejection_reason ?? '';
        $doc->uploaded_by = (int) ($row->uploaded_by ?? 0);
        $doc->created_at = $row->created_at ? strtotime($row->created_at) : 0;
        $doc->updated_at = $row->updated_at ? strtotime($row->updated_at) : 0;
        
        return $doc;
    }

    /**
     * Convert to array
     * 
     * @return array
     */
    public function toArray(): array {
        return [
            'document_id' => $this->document_id,
            'company_id' => $this->company_id,
            'document_type' => $this->document_type,
            'document_type_label' => self::getTypeLabel($this->document_type),
            'document_name' => $this->document_name,
            'document_url' => $this->document_url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->formatFileSize($this->file_size),
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at ? date_i18n('Y-m-d H:i:s', $this->verified_at) : null,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at ? date_i18n('Y-m-d H:i:s', $this->created_at) : null,
        ];
    }

    /**
     * Format file size for display
     * 
     * @param int $bytes
     * @return string
     */
    public static function formatFileSize(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }

    /**
     * Check if file type is allowed
     * 
     * @param string $mime_type
     * @return bool
     */
    public static function isAllowedType(string $mime_type): bool {
        return in_array($mime_type, self::getAllowedMimeTypes());
    }

    /**
     * Get file extension from mime type
     * 
     * @param string $mime_type
     * @return string
     */
    public static function getExtensionFromMime(string $mime_type): string {
        $map = array_flip(self::getAllowedMimeTypes());
        return $map[$mime_type] ?? '';
    }
}