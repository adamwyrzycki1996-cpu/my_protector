<?php
/**
 * MyProtector Platform - SEO Module
 * 
 * Integration with Yoast SEO and custom SEO features:
 * - Editable metadata
 * - Business schema
 * - Review schema
 * - XML sitemap
 * - HTML sitemap
 * 
 * @package MyProtector\Modules\SEO
 * @version 1.0.0
 */

namespace MyProtector\Modules\SEO;

use MyProtector\Core\Module;

class SEO extends Module {
    protected $name = 'seo';
    protected $dependencies = ['business-profiles', 'reviews'];

    protected function getModuleDirectory(): string {
        return 'SEO';
    }

    public function boot(): void {
        $this->registerHooks();
        $this->setupSitemaps();
    }

    public function registerHooks(): void {
        // Schema markup
        $this->addAction('wp_head', [$this, 'outputSchemaMarkup'], 1);
        
        // Yoast SEO integration
        $this->addFilter('wpseo_schema_webpage', [$this, 'modifyWebpageSchema']);
        $this->addFilter('wpseo_schema_organization', [$this, 'modifyOrganizationSchema']);
        
        // Custom meta
        $this->addAction('wp_head', [$this, 'outputCustomMeta'], 5);
        
        // Admin menu
        $this->addAction('admin_menu', [$this, 'addAdminMenu']);
        
        // AJAX
        $this->addAction('wp_ajax_mp_save_seo_settings', [$this, 'ajaxSaveSEOSettings']);
    }

    public function outputSchemaMarkup(): void {
        if (is_singular('mp_business') || is_post_type_archive('mp_business')) {
            $this->outputBusinessSchema();
        }
    }

    public function outputBusinessSchema(): void {
        $business_id = get_queried_object_id();
        $business = $this->getBusiness($business_id);
        
        if (!$business) {
            return;
        }
        
        $schema = $this->generateBusinessSchema($business);
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
        
        // Review schema
        $review_schema = $this->generateReviewSchema($business_id);
        if ($review_schema) {
            echo '<script type="application/ld+json">' . json_encode($review_schema, JSON_UNESCAPED_SLASHES) . '</script>';
        }
    }

    public function generateBusinessSchema(object $business): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => home_url('/business/' . $business->business_slug),
            'name' => $business->business_name,
            'description' => $business->business_description ?: '',
            'url' => $business->business_website ?: home_url('/business/' . $business->business_slug),
        ];
        
        if ($business->business_phone) {
            $schema['telephone'] = $business->business_phone;
        }
        
        if ($business->business_email) {
            $schema['email'] = $business->business_email;
        }
        
        if ($business->address_line1) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $business->address_line1 . ($business->address_line2 ? ', ' . $business->address_line2 : ''),
                'addressLocality' => $business->city ?: '',
                'addressRegion' => $business->state ?: '',
                'postalCode' => $business->postal_code ?: '',
                'addressCountry' => $business->country ?: 'US',
            ];
        }
        
        if ($business->logo_url) {
            $schema['logo'] = $business->logo_url;
            $schema['image'] = $business->logo_url;
        }
        
        if ($business->avg_rating > 0 && $business->total_reviews > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($business->avg_rating, 2),
                'reviewCount' => $business->total_reviews,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }
        
        if ($business->business_website) {
            $schema['sameAs'] = array_filter([
                $business->facebook_url,
                $business->twitter_url,
                $business->instagram_url,
                $business->linkedin_url,
            ]);
        }
        
        return $schema;
    }

    public function generateReviewSchema(int $business_id): array {
        global $wpdb;
        
        $reviews = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT review_rating, review_title, review_content, review_author_name, published_at 
                FROM {$wpdb->prefix}mp_reviews 
                WHERE business_id = %d AND review_status = 'approved' 
                ORDER BY published_at DESC LIMIT 10",
                $business_id
            ),
            ARRAY_A
        );
        
        if (empty($reviews)) {
            return null;
        }
        
        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => array_map(function($review, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Review',
                        'reviewRating' => [
                            '@type' => 'Rating',
                            'ratingValue' => $review['review_rating'],
                            'bestRating' => 5,
                            'worstRating' => 1,
                        ],
                        'author' => [
                            '@type' => 'Person',
                            'name' => $review['review_author_name'] ?: 'Verified Customer',
                        ],
                        'reviewBody' => $review['review_content'],
                        'datePublished' => $review['published_at'],
                    ],
                ];
            }, $reviews, array_keys($reviews)),
        ];
    }

    public function outputCustomMeta(): void {
        $post = get_queried_object();
        
        if (!$post) {
            return;
        }
        
        $custom_title = get_post_meta($post->ID, '_mp_seo_title', true);
        $custom_description = get_post_meta($post->ID, '_mp_seo_description', true);
        
        if ($custom_title) {
            echo '<title>' . esc_html($custom_title) . '</title>' . "\n";
        }
        
        if ($custom_description) {
            echo '<meta name="description" content="' . esc_attr($custom_description) . '">' . "\n";
        }
    }

    public function modifyWebpageSchema(array $schema): array {
        if (is_singular('mp_business')) {
            $schema['@type'] = 'WebPage';
            $schema['about'] = [
                '@type' => 'LocalBusiness',
                'name' => get_the_title(),
            ];
        }
        
        return $schema;
    }

    public function modifyOrganizationSchema(array $schema): array {
        $schema['name'] = get_bloginfo('name');
        $schema['url'] = home_url('/');
        
        return $schema;
    }

    protected function getBusiness(int $business_id): ?object {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mp_businesses WHERE business_id = %d",
                $business_id
            )
        );
    }

    public function setupSitemaps(): void {
        add_action('init', [$this, 'registerSitemapRewriteRules']);
        add_filter('query_vars', [$this, 'addSitemapQueryVars']);
    }

    public function registerSitemapRewriteRules(): void {
        add_rewrite_rule('sitemap\.xml$', 'index.php?mp_sitemap=xml', 'top');
        add_rewrite_rule('sitemap_index\.xml$', 'index.php?mp_sitemap=index', 'top');
    }

    public function addSitemapQueryVars(array $vars): array {
        $vars[] = 'mp_sitemap';
        return $vars;
    }

    public function generateXMLSitemap(): string {
        global $wpdb;
        
        $items = [];
        
        // Get businesses
        $businesses = $wpdb->get_results(
            "SELECT business_slug, updated_at FROM {$wpdb->prefix}mp_businesses WHERE business_status = 'active'",
            ARRAY_A
        );
        
        foreach ($businesses as $business) {
            $items[] = [
                'loc' => home_url('/business/' . $business['business_slug'] . '/'),
                'lastmod' => date('c', strtotime($business['updated_at'])),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }
        
        // Get reviews
        $reviews = $wpdb->get_results(
            "SELECT r.review_id, r.published_at, b.business_slug 
            FROM {$wpdb->prefix}mp_reviews r 
            JOIN {$wpdb->prefix}mp_businesses b ON r.business_id = b.business_id 
            WHERE r.review_status = 'approved'",
            ARRAY_A
        );
        
        foreach ($reviews as $review) {
            $items[] = [
                'loc' => home_url('/business/' . $review['business_slug'] . '/#review-' . $review['review_id']),
                'lastmod' => date('c', strtotime($review['published_at'])),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }
        
        return $this->buildSitemapXML($items);
    }

    protected function buildSitemapXML(array $items): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($items as $item) {
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($item['loc']) . '</loc>';
            $xml .= '<lastmod>' . esc_html($item['lastmod']) . '</lastmod>';
            $xml .= '<changefreq>' . esc_html($item['changefreq']) . '</changefreq>';
            $xml .= '<priority>' . esc_html($item['priority']) . '</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    public function addAdminMenu(): void {
        add_submenu_page(
            'myprotector',
            __('SEO Settings', 'myprotector-platform'),
            __('SEO', 'myprotector-platform'),
            'manage_myprotector',
            'mp-seo',
            [$this, 'renderSEOSettingsPage']
        );
    }

    public function renderSEOSettingsPage(): void {
        include $this->getPath('templates/admin/seo-settings.php');
    }

    public function ajaxSaveSEOSettings(): void {
        check_ajax_referer('mp_seo', 'nonce');
        
        if (!current_user_can('manage_myprotector')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'myprotector-platform')]);
        }
        
        $settings = [
            'default_title' => sanitize_text_field($_POST['default_title'] ?? ''),
            'default_description' => sanitize_textarea_field($_POST['default_description'] ?? ''),
            'og_image' => esc_url_raw($_POST['og_image'] ?? ''),
        ];
        
        foreach ($settings as $key => $value) {
            update_option('mp_seo_' . $key, $value);
        }
        
        wp_send_json_success(['message' => __('SEO settings saved.', 'myprotector-platform')]);
    }
}