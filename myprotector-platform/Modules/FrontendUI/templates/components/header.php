<?php
/**
 * MyProtector Platform - Header Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;
?>

<header class="mp-header">
    <div class="mp-container">
        <div class="mp-header-inner">
            <a href="#" class="mp-logo">
                <div class="mp-logo-icon">MP</div>
                <div class="mp-logo-text">My<span>Protector</span></div>
            </a>
            
            <nav class="mp-nav">
                <a href="#" class="mp-nav-link">Home</a>
                <a href="#" class="mp-nav-link">Businesses</a>
                <a href="#" class="mp-nav-link">How It Works</a>
                <a href="#" class="mp-nav-link">Dashboard</a>
            </nav>
            
            <div class="mp-header-actions">
                <a href="#" class="mp-btn mp-btn-ghost">Log In</a>
                <a href="#" class="mp-btn mp-btn-primary">Sign Up</a>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button class="mp-btn mp-btn-icon mp-btn-ghost mp-mobile-menu-toggle" style="display: none;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</header>
