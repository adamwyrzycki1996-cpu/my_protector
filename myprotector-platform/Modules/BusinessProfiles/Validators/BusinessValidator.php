<?php
/**
 * MyProtector Platform - Business Validator
 * 
 * Form validation for business profiles
 * 
 * @package MyProtector\Modules\BusinessProfiles\Validators
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessProfiles\Validators;

class BusinessValidator {
    /**
     * Validation rules for create
     * 
     * @var array
     */
    protected $create_rules = [
        'company_name' => 'required|string|max:255',
        'company_description' => 'string|max:5000',
        'company_website' => 'url|max:500',
        'company_logo' => 'url|max:500',
        'company_phone' => 'phone|max:50',
        'company_email' => 'email|max:255',
        'company_address' => 'string|max:1000',
        'insurance_name' => 'string|max:255',
        'insurance_url' => 'url|max:500',
        'terms_url' => 'url|max:500',
        'promise_page_url' => 'url|max:500',
        'promise_page_title' => 'string|max:255',
    ];

    /**
     * Validation rules for update
     * 
     * @var array
     */
    protected $update_rules = [
        'id' => 'required|integer',
        'company_name' => 'required|string|max:255',
        'company_description' => 'string|max:5000',
        'company_website' => 'url|max:500',
        'company_logo' => 'url|max:500',
        'company_phone' => 'phone|max:50',
        'company_email' => 'email|max:255',
        'company_address' => 'string|max:1000',
        'insurance_name' => 'string|max:255',
        'insurance_url' => 'url|max:500',
        'terms_url' => 'url|max:500',
        'promise_page_url' => 'url|max:500',
        'promise_page_title' => 'string|max:255',
    ];

    /**
     * Error messages
     * 
     * @var array
     */
    protected $messages = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->messages = [
            'required' => __('This field is required.', 'myprotector-platform'),
            'string' => __('This field must be a valid text.', 'myprotector-platform'),
            'integer' => __('This field must be a valid number.', 'myprotector-platform'),
            'email' => __('Please enter a valid email address.', 'myprotector-platform'),
            'url' => __('Please enter a valid URL (must start with http:// or https://).', 'myprotector-platform'),
            'phone' => __('Please enter a valid phone number.', 'myprotector-platform'),
            'max' => __('This field exceeds the maximum allowed length.', 'myprotector-platform'),
        ];
    }

    /**
     * Validate create data
     * 
     * @param array $data
     * @return bool|\WP_Error
     */
    public function validateCreate(array $data): bool {
        return $this->validate($data, $this->create_rules);
    }

    /**
     * Validate update data
     * 
     * @param array $data
     * @return bool|\WP_Error
     */
    public function validateUpdate(array $data): bool {
        return $this->validate($data, $this->update_rules);
    }

    /**
     * Validate data against rules
     * 
     * @param array $data
     * @param array $rules
     * @return bool|\WP_Error
     */
    protected function validate(array $data, array $rules): bool {
        $errors = [];

        foreach ($rules as $field => $rule_string) {
            $field_rules = explode('|', $rule_string);
            $value = $data[$field] ?? null;

            foreach ($field_rules as $rule) {
                $rule_result = $this->applyRule($field, $value, $rule);
                
                if (is_wp_error($rule_result)) {
                    $errors[$field] = $rule_result->get_error_message();
                    break;
                }
            }
        }

        if (!empty($errors)) {
            $first_error = reset($errors);
            return new \WP_Error('validation_failed', $first_error, $errors);
        }

        return true;
    }

    /**
     * Apply a single validation rule
     * 
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return bool|\WP_Error
     */
    protected function applyRule(string $field, $value, string $rule): bool {
        // Parse rule and parameters
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $param_str] = explode(':', $rule, 2);
            $params = explode(',', $param_str);
        }

        switch ($rule) {
            case 'required':
                if ($this->isEmpty($value)) {
                    return new \WP_Error('required', $this->getMessage('required', $field));
                }
                break;

            case 'string':
                if (!$this->isEmpty($value) && !is_string($value)) {
                    return new \WP_Error('string', $this->getMessage('string', $field));
                }
                break;

            case 'integer':
                if (!$this->isEmpty($value) && !is_numeric($value)) {
                    return new \WP_Error('integer', $this->getMessage('integer', $field));
                }
                break;

            case 'email':
                if (!$this->isEmpty($value) && !is_email($value)) {
                    return new \WP_Error('email', $this->getMessage('email', $field));
                }
                break;

            case 'url':
                if (!$this->isEmpty($value) && !$this->isValidUrl($value)) {
                    return new \WP_Error('url', $this->getMessage('url', $field));
                }
                break;

            case 'phone':
                if (!$this->isEmpty($value) && !$this->isValidPhone($value)) {
                    return new \WP_Error('phone', $this->getMessage('phone', $field));
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 255);
                if (!$this->isEmpty($value) && strlen($value) > $max) {
                    return new \WP_Error('max', $this->getMessage('max', $field));
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (!is_numeric($value) || $value < $min) {
                    return new \WP_Error('min', sprintf(
                        __('This field must be at least %d characters.', 'myprotector-platform'),
                        $min
                    ));
                }
                break;

            case 'in':
                $allowed = $params;
                if (!in_array($value, $allowed)) {
                    return new \WP_Error('in', __('Please select a valid option.', 'myprotector-platform'));
                }
                break;
        }

        return true;
    }

    /**
     * Check if value is empty
     * 
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value): bool {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }

    /**
     * Validate URL
     * 
     * @param string $url
     * @return bool
     */
    protected function isValidUrl(string $url): bool {
        // Must start with http:// or https://
        if (!preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        // Must be a valid URL
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate phone number
     * 
     * @param string $phone
     * @return bool
     */
    protected function isValidPhone(string $phone): bool {
        // Remove common formatting characters
        $clean = preg_replace('/[\s\-\(\)\+\.]/', '', $phone);
        
        // Must contain only digits and be reasonable length
        if (!preg_match('/^\d{7,15}$/', $clean)) {
            return false;
        }

        return true;
    }

    /**
     * Get error message
     * 
     * @param string $rule
     * @param string $field
     * @return string
     */
    protected function getMessage(string $rule, string $field): string {
        $field_label = $this->getFieldLabel($field);
        $message = $this->messages[$rule] ?? __('Invalid value.', 'myprotector-platform');
        
        return sprintf($message, $field_label);
    }

    /**
     * Get human-readable field label
     * 
     * @param string $field
     * @return string
     */
    protected function getFieldLabel(string $field): string {
        $labels = [
            'company_name' => __('Company name', 'myprotector-platform'),
            'company_description' => __('Description', 'myprotector-platform'),
            'company_website' => __('Website URL', 'myprotector-platform'),
            'company_logo' => __('Logo', 'myprotector-platform'),
            'company_phone' => __('Phone number', 'myprotector-platform'),
            'company_email' => __('Email address', 'myprotector-platform'),
            'company_address' => __('Address', 'myprotector-platform'),
            'insurance_name' => __('Insurance provider name', 'myprotector-platform'),
            'insurance_url' => __('Insurance URL', 'myprotector-platform'),
            'terms_url' => __('Terms and conditions URL', 'myprotector-platform'),
            'promise_page_url' => __('Promise page URL', 'myprotector-platform'),
            'promise_page_title' => __('Promise page title', 'myprotector-platform'),
        ];

        return $labels[$field] ?? $field;
    }

    /**
     * Validate logo file
     * 
     * @param array $file
     * @return bool|\WP_Error
     */
    public function validateLogo(array $file): bool {
        if (empty($file) || !isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return new \WP_Error('no_file', __('No logo file provided.', 'myprotector-platform'));
        }

        // Check file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return new \WP_Error('file_too_large', __('Logo must be less than 2MB.', 'myprotector-platform'));
        }

        // Check mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime_type, $allowed_types)) {
            return new \WP_Error('invalid_type', __('Only JPG, PNG, GIF, and WebP images are allowed.', 'myprotector-platform'));
        }

        return true;
    }

    /**
     * Sanitize data for database
     * 
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function sanitize(array $data, array $rules = []): array {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } elseif (is_string($value)) {
                if (strpos($key, 'url') !== false) {
                    $sanitized[$key] = esc_url_raw($value);
                } elseif (strpos($key, 'email') !== false) {
                    $sanitized[$key] = sanitize_email($value);
                } elseif (strpos($key, 'description') !== false || strpos($key, 'address') !== false) {
                    $sanitized[$key] = sanitize_textarea_field($value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}