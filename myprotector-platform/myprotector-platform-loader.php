<?php
/**
 * MyProtector Platform - Autoloader
 * 
 * @package MyProtector
 * @version 1.0.0
 */

namespace MyProtector;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * PSR-4 Autoloader
 * 
 * Automatically loads classes based on namespace
 */
spl_autoload_register(function (string $class): void {
    // Project namespace prefix
    $prefix = 'MyProtector\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    // Add .php extension
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});