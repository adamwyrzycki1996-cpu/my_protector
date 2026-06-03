<?php
/**
 * MyProtector Platform - Review Modal Component
 * 
 * @package MyProtector\Modules\FrontendUI
 */

if (!defined('ABSPATH')) exit;

// Get business name for modal title
$modal_business_name = isset($business) ? $business['name'] : 'TechVentures Solutions';
?>

<div class="mp-modal-overlay" id="mp-review-modal">
    <div class="mp-modal">
        <div class="mp-modal-header">
            <h3 class="mp-modal-title">Review <?php echo esc_html($modal_business_name); ?></h3>
            <button class="mp-modal-close" aria-label="Close">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        
        <div class="mp-modal-body">
            <form class="mp-review-form" action="#" method="POST">
                <!-- Star Rating -->
                <div class="mp-form-group">
                    <label class="mp-form-label">Your Rating *</label>
                    <div class="mp-star-rating" id="mp-review-rating">
                        <input type="radio" name="rating" id="rating-5" value="5">
                        <label for="rating-5" title="5 stars">★</label>
                        
                        <input type="radio" name="rating" id="rating-4" value="4">
                        <label for="rating-4" title="4 stars">★</label>
                        
                        <input type="radio" name="rating" id="rating-3" value="3">
                        <label for="rating-3" title="3 stars">★</label>
                        
                        <input type="radio" name="rating" id="rating-2" value="2">
                        <label for="rating-2" title="2 stars">★</label>
                        
                        <input type="radio" name="rating" id="rating-1" value="1">
                        <label for="rating-1" title="1 star">★</label>
                    </div>
                </div>
                
                <!-- Review Title -->
                <div class="mp-form-group">
                    <label for="mp-review-title" class="mp-form-label">Review Title *</label>
                    <input type="text" id="mp-review-title" name="title" class="mp-form-input" 
                           placeholder="Summarize your experience in a few words" required>
                </div>
                
                <!-- Review Content -->
                <div class="mp-form-group">
                    <label for="mp-review-content" class="mp-form-label">Your Review *</label>
                    <textarea id="mp-review-content" name="content" class="mp-form-input mp-form-textarea" 
                              rows="5" placeholder="Share your experience with this business..." required></textarea>
                    <p class="mp-form-hint" style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-500); margin-top: var(--mp-spacing-xs);">
                        Minimum 20 characters
                    </p>
                </div>
                
                <!-- Image Upload -->
                <div class="mp-form-group">
                    <label class="mp-form-label">Add Photos (Optional)</label>
                    <div class="mp-upload" id="mp-review-upload">
                        <input type="file" class="mp-upload-input" id="mp-review-images" name="images[]" accept="image/*" multiple hidden>
                        <div class="mp-upload-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <p class="mp-upload-text">Click to upload or drag and drop<br><small>PNG, JPG up to 5MB</small></p>
                        <div class="mp-upload-preview" style="display: none; margin-top: var(--mp-spacing-md);"></div>
                    </div>
                </div>
                
                <!-- Pros & Cons (Optional) -->
                <div class="mp-grid mp-grid-2">
                    <div class="mp-form-group">
                        <label for="mp-review-pros" class="mp-form-label">Pros (Optional)</label>
                        <textarea id="mp-review-pros" name="pros" class="mp-form-input" rows="2" 
                                  placeholder="What did you like?"></textarea>
                    </div>
                    <div class="mp-form-group">
                        <label for="mp-review-cons" class="mp-form-label">Cons (Optional)</label>
                        <textarea id="mp-review-cons" name="cons" class="mp-form-input" rows="2" 
                                  placeholder="What could be improved?"></textarea>
                    </div>
                </div>
                
                <!-- Terms -->
                <div class="mp-form-group">
                    <label class="mp-flex mp-items-center mp-gap-sm" style="cursor: pointer;">
                        <input type="checkbox" name="terms" required style="width: 18px; height: 18px;">
                        <span style="font-size: var(--mp-font-size-sm); color: var(--mp-gray-600);">
                            I verify this is my genuine experience with this business and agree to the 
                            <a href="#" style="color: var(--mp-info);">Terms of Service</a>.
                        </span>
                    </label>
                </div>
            </form>
        </div>
        
        <div class="mp-modal-footer">
            <button type="button" class="mp-btn mp-btn-secondary" data-modal-close>Cancel</button>
            <button type="submit" form="mp-review-form" class="mp-btn mp-btn-primary">
                Submit Review
            </button>
        </div>
    </div>
</div>

<style>
/* Review Modal Specific Styles */
.mp-form-hint {
    margin-bottom: 0;
}

.mp-upload {
    border: 2px dashed var(--mp-gray-300);
    border-radius: var(--mp-radius-lg);
    padding: var(--mp-spacing-xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--mp-transition-fast);
    background: var(--mp-gray-50);
}

.mp-upload:hover {
    border-color: var(--mp-green);
    background: var(--mp-green-bg);
}

.mp-upload-icon {
    color: var(--mp-gray-400);
    margin-bottom: var(--mp-spacing-sm);
}

.mp-upload-text {
    font-size: var(--mp-font-size-sm);
    color: var(--mp-gray-500);
    margin: 0;
}

.mp-upload-preview {
    display: flex;
    flex-wrap: wrap;
    gap: var(--mp-spacing-sm);
    justify-content: center;
}

.mp-upload-preview img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: var(--mp-radius-md);
}
</style>

<script>
// Initialize upload preview
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('mp-review-upload');
    const fileInput = document.getElementById('mp-review-images');
    const previewArea = uploadArea.querySelector('.mp-upload-preview');
    const uploadText = uploadArea.querySelector('.mp-upload-text');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', function() {
            fileInput.click();
        });
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--mp-green)';
            uploadArea.style.background = 'var(--mp-green-bg)';
        });
        
        uploadArea.addEventListener('dragleave', function() {
            uploadArea.style.borderColor = 'var(--mp-gray-300)';
            uploadArea.style.background = 'var(--mp-gray-50)';
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.style.borderColor = 'var(--mp-gray-300)';
            uploadArea.style.background = 'var(--mp-gray-50)';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFiles(e.dataTransfer.files);
            }
        });
        
        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });
        
        function handleFiles(files) {
            previewArea.style.display = 'flex';
            uploadText.innerHTML = '<small>Selected files:</small><br>' + files.length + ' file(s)';
            
            // Show preview for images
            for (let file of files) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewArea.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    }
});
</script>
