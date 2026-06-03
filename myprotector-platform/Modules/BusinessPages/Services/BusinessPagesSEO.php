<?php
/**
 * MyProtector Platform - Business Pages SEO Service
 * 
 * Handles SEO optimization for business pages including:
 * - Schema markup (Business and Review schemas)
 * - Meta tags
 * - Open Graph tags
 * 
 * @package MyProtector\Modules\BusinessPages\Services
 * @version 1.0.0
 */

namespace MyProtector\Modules\BusinessPages\Services;

class BusinessPagesSEO {
    /**
     * Generate business schema markup
     * 
     * @param object $business
     * @return string
     */
    public function generateBusinessSchema(object $business): string {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            '@id' => home_url('/business/' . $business->business_slug . '/'),
            'name' => $business->business_name,
            'description' => $business->business_description ?: '',
            'url' => $business->business_website ?: home_url('/business/' . $business->business_slug . '/'),
            'telephone' => $business->business_phone ?: '',
            'email' => $business->business_email ?: '',
            'address' => $this->formatAddress($business),
            'aggregateRating' => $this->generateAggregateRating($business),
            'review' => $this->generateReviewReference($business),
        ];
        
        // Add logo if available
        if (!empty($business->logo_url)) {
            $schema['logo'] = $business->logo_url;
            $schema['image'] = $business->logo_url;
        }
        
        // Add opening hours if available
        if (!empty($business->business_hours)) {
            $schema['openingHours'] = $business->business_hours;
        }
        
        // Add geo coordinates if available
        if (!empty($business->latitude) && !empty($business->longitude)) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $business->latitude,
                'longitude' => $business->longitude,
            ];
        }
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Generate review schema markup
     * 
     * @param object $business
     * @return string
     */
    public function generateReviewSchema(object $business): string {
        $reviews_table = $this->getReviewsForSchema($business->business_id);
        
        if (empty($reviews_table)) {
            return '';
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'itemListElement' => [],
        ];
        
        foreach ($reviews_table as $index => $review) {
            $schema['itemListElement'][] = [
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
                        'name' => $review['reviewer_name'],
                    ],
                    'reviewBody' => $review['review_content'],
                    'datePublished' => $review['published_at'],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => get_bloginfo('name'),
                    ],
                ],
            ];
        }
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Get reviews for schema
     * 
     * @param int $business_id
     * @return array
     */
    protected function getReviewsForSchema(int $business_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT review_rating, review_title, review_content, review_author_name, published_at 
                FROM {$table} 
                WHERE business_id = %d AND review_status = 'approved' 
                ORDER BY published_at DESC LIMIT 10",
                $business_id
            ),
            ARRAY_A
        ) ?: [];
    }

    /**
     * Generate aggregate rating
     * 
     * @param object $business
     * @return array|null
     */
    protected function generateAggregateRating(object $business): ?array {
        if ($business->total_reviews < 1) {
            return null;
        }
        
        return [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format((float)$business->avg_rating, 2),
            'reviewCount' => $business->total_reviews,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /**
     * Generate review reference
     * 
     * @param object $business
     * @return array|null
     */
    protected function generateReviewReference(object $business): ?array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mp_reviews';
        
        $latest_review = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT review_rating, review_title, review_content, review_author_name, published_at 
                FROM {$table} 
                WHERE business_id = %d AND review_status = 'approved' 
                ORDER BY published_at DESC LIMIT 1",
                $business->business_id
            ),
            ARRAY_A
        );
        
        if (!$latest_review) {
            return null;
        }
        
        return [
            '@type' => 'Review',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $latest_review['review_rating'],
                'bestRating' => 5,
                'worstRating' => 1,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $latest_review['review_author_name'] ?: 'Verified Customer',
            ],
            'reviewBody' => $latest_review['review_content'],
            'datePublished' => $latest_review['published_at'],
        ];
    }

    /**
     * Format address
     * 
     * @param object $business
     * @return array
     */
    protected function formatAddress(object $business): array {
        $address = [
            '@type' => 'PostalAddress',
        ];
        
        if (!empty($business->address_line1)) {
            $address['streetAddress'] = $business->address_line1;
        }
        
        if (!empty($business->address_line2)) {
            $address['streetAddress'] .= ', ' . $business->address_line2;
        }
        
        if (!empty($business->city)) {
            $address['addressLocality'] = $business->city;
        }
        
        if (!empty($business->state)) {
            $address['addressRegion'] = $business->state;
        }
        
        if (!empty($business->postal_code)) {
            $address['postalCode'] = $business->postal_code;
        }
        
        if (!empty($business->country)) {
            $address['addressCountry'] = $business->country;
        }
        
        return $address;
    }

    /**
     * Generate meta description
     * 
     * @param object $business
     * @return string
     */
    public function generateMetaDescription(object $business): string {
        $rating_text = '';
        if ($business->total_reviews > 0) {
            $rating_text = sprintf(
                _n(
                    'Rated %.1f out of 5 stars based on %d review.',
                    'Rated %.1f out of 5 stars based on %d reviews.',
                    $business->total_reviews,
                    'myprotector-platform'
                ),
                $business->avg_rating,
                $business->total_reviews
            );
        }
        
        $description = sprintf(
            '%s %s %s',
            $business->business_name,
            $business->business_tagline ?: '',
            $rating_text
        );
        
        return trim($description);
    }

    /**
     * Generate Open Graph tags
     * 
     * @param object $business
     * @return string
     */
    public function generateOpenGraphTags(object $business): string {
        $title = sprintf('%s Reviews & Ratings - %s', 
            $business->business_name,
            get_bloginfo('name')
        );
        
        $description = $this->generateMetaDescription($business);
        $url = home_url('/business/' . $business->business_slug . '/');
        $image = $business->logo_url ?: '';
        
        $tags = [];
        
        // Basic OG tags
        $tags[] = '<meta property="og:type" content="website" />';
        $tags[] = '<meta property="og:title" content="' . esc_attr($title) . '" />';
        $tags[] = '<meta property="og:description" content="' . esc_attr($description) . '" />';
        $tags[] = '<meta property="og:url" content="' . esc_url($url) . '" />';
        $tags[] = '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />';
        
        // Image
        if (!empty($image)) {
            $tags[] = '<meta property="og:image" content="' . esc_url($image) . '" />';
            $tags[] = '<meta property="og:image:secure_url" content="' . esc_url($image) . '" />';
        }
        
        // Twitter Card
        $tags[] = '<meta name="twitter:card" content="summary_large_image" />';
        $tags[] = '<meta name="twitter:title" content="' . esc_attr($title) . '" />';
        $tags[] = '<meta name="twitter:description" content="' . esc_attr($description) . '" />';
        
        if (!empty($image)) {
            $tags[] = '<meta name="twitter:image" content="' . esc_url($image) . '" />';
        }
        
        return implode("\n", $tags);
    }

    /**
     * Generate canonical URL
     * 
     * @param object $business
     * @return string
     */
    public function generateCanonicalUrl(object $business): string {
        return home_url('/business/' . $business->business_slug . '/');
    }

    /**
     * Generate breadcrumb schema
     * 
     * @param object $business
     * @return string
     */
    public function generateBreadcrumbSchema(object $business): string {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => get_bloginfo('name'),
                    'item' => home_url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => __('Businesses', 'myprotector-platform'),
                    'item' => home_url('/businesses/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $business->business_name,
                    'item' => home_url('/business/' . $business->business_slug . '/'),
                ],
            ],
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}