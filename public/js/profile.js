document.addEventListener('DOMContentLoaded', () => {
    initializeTabNavigation();
    initializeProfilePictureUpload();
    initializePasswordValidation();
    initializeFormValidation();
    initializeAccountDeletion();
});

/**
 * Initializes tab navigation functionality
 * Handles switching between profile sections
 */
function initializeTabNavigation() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            
            // Reset active states
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Set new active states
            button.classList.add('active');
            document.getElementById(targetId).classList.add('active');

            // Update URL hash for direct linking
            history.pushState(null, null, `#${targetId}`);
        });
    });

    // Handle direct URL navigation
    if (location.hash) {
        const targetTab = document.querySelector(`[data-target="${location.hash.slice(1)}"]`);
        if (targetTab) {
            targetTab.click();
        }
    }
}

/**
 * Initializes profile picture upload functionality
 * Handles file selection, preview, and validation
 */
function initializeProfilePictureUpload() {
    const fileInput = document.querySelector('.file-input');
    const uploadLabel = document.querySelector('.upload-label');
    const previewImage = document.getElementById('profile-preview');
    
    if (!fileInput || !uploadLabel || !previewImage) return;

    // Add click handler for the label
    uploadLabel.addEventListener('click', (e) => {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            if (validateProfilePicture(file)) {
                displayProfilePicturePreview(file, previewImage);
            } else {
                fileInput.value = '';
            }
        }
    });
}
/**
 * Validates the selected profile picture file
 * @param {File} file - The selected image file
 * @returns {boolean} - Validation result
 */
function validateProfilePicture(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!allowedTypes.includes(file.type)) {
        showError('Please select a valid image file (JPEG, PNG, GIF, or WebP).');
        return false;
    }

    if (file.size > maxSize) {
        showError('Image size must be less than 5MB.');
        return false;
    }

    return true;
}

/**
 * Displays the selected image preview
 * @param {File} file - The selected image file
 * @param {HTMLImageElement} previewElement - The preview image element
 */
function displayProfilePicturePreview(file, previewElement) {
    const reader = new FileReader();
    reader.onload = (e) => {
        previewElement.src = e.target.result;
        previewElement.style.display = 'block';
    };
    reader.readAsDataURL(file);
}

/**
 * Handles the profile picture upload process
 * @param {HTMLFormElement} form - The upload form element
 * @param {HTMLElement} modal - The modal element
 */
async function handleProfilePictureUpload(form, modal) {
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    try {
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) throw new Error('Upload failed');

        const result = await response.json();
        
        if (result.success) {
            // Update all profile picture instances on the page
            document.querySelectorAll('.profile-avatar').forEach(img => {
                img.src = result.imageUrl;
            });
            
            // Close modal and show success message
            bootstrap.Modal.getInstance(modal).hide();
            showSuccess('Profile picture updated successfully');
        } else {
            throw new Error(result.message || 'Upload failed');
        }
    } catch (error) {
        showError(error.message);
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = 'Save Changes';
    }
}

/**
 * Initializes password validation functionality
 * Implements real-time password strength checking
 */
function initializePasswordValidation() {
    const passwordForm = document.querySelector('.password-change-form');
    if (!passwordForm) return;

    const newPasswordInput = passwordForm.querySelector('#new-password');
    const confirmPasswordInput = passwordForm.querySelector('#confirm-password');
    const strengthBar = passwordForm.querySelector('.strength-bar');
    const strengthText = passwordForm.querySelector('.password-strength-text');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', () => {
            const strength = checkPasswordStrength(newPasswordInput.value);
            updatePasswordStrengthIndicator(strength, strengthBar, strengthText);
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', () => {
            validatePasswordMatch(newPasswordInput.value, confirmPasswordInput.value);
        });
    }

    passwordForm.addEventListener('submit', (event) => {
        if (!validatePasswordForm(passwordForm)) {
            event.preventDefault();
        }
    });
}

/**
 * Checks password strength against security criteria
 * @param {string} password - The password to check
 * @returns {number} - Strength score (0-100)
 */
function checkPasswordStrength(password) {
    let score = 0;
    
    // Length check
    if (password.length >= 8) score += 25;
    if (password.length >= 12) score += 10;

    // Character variety checks
    if (/[A-Z]/.test(password)) score += 15;
    if (/[a-z]/.test(password)) score += 15;
    if (/[0-9]/.test(password)) score += 15;
    if (/[^A-Za-z0-9]/.test(password)) score += 20;

    return score;
}

/**
 * Updates the password strength indicator
 * @param {number} strength - Password strength score
 * @param {HTMLElement} bar - Strength bar element
 * @param {HTMLElement} text - Strength text element
 */
function updatePasswordStrengthIndicator(strength, bar, text) {
    bar.style.width = `${strength}%`;
    
    if (strength < 40) {
        bar.style.backgroundColor = '#dc3545';
        text.textContent = 'Weak';
    } else if (strength < 70) {
        bar.style.backgroundColor = '#ffc107';
        text.textContent = 'Moderate';
    } else {
        bar.style.backgroundColor = '#28a745';
        text.textContent = 'Strong';
    }
}

/**
 * Initializes form validation for the profile form
 */
function initializeFormValidation() {
    const profileForm = document.querySelector('.profile-form');
    if (!profileForm) return;

    profileForm.addEventListener('submit', (event) => {
        if (!validateProfileForm(profileForm)) {
            event.preventDefault();
        }
    });

    // Real-time username availability check
    const usernameInput = profileForm.querySelector('input[name="profile_edit[username]"]');
    if (usernameInput) {
        let debounceTimer;
        usernameInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => checkUsernameAvailability(usernameInput), 500);
        });
    }
}

/**
 * Checks username availability via API
 * @param {HTMLInputElement} input - Username input element
 */
async function checkUsernameAvailability(input) {
    const username = input.value.trim();
    if (!username) return;

    try {
        const response = await fetch('/profile/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ username })
        });

        const data = await response.json();
        
        const feedback = input.nextElementSibling;
        input.classList.remove('is-valid', 'is-invalid');
        
        if (data.available) {
            input.classList.add('is-valid');
            if (feedback) feedback.textContent = 'Username is available';
        } else {
            input.classList.add('is-invalid');
            if (feedback) feedback.textContent = 'Username is already taken';
        }
    } catch (error) {
        console.error('Error checking username:', error);
    }
}

/**
 * Initializes account deletion functionality
 */
function initializeAccountDeletion() {
    const deleteButton = document.querySelector('.btn-delete-account');
    if (!deleteButton) return;

    deleteButton.addEventListener('click', async (event) => {
        event.preventDefault();
        
        if (confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
            try {
                const response = await fetch('/profile/delete-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '/logout';
                } else {
                    showError(result.message || 'Failed to delete account');
                }
            } catch (error) {
                showError('An error occurred while deleting your account');
            }
        }
    });
}

/**
 * Shows a success message to the user
 * @param {string} message - Success message to display
 */
function showSuccess(message) {
    const alert = createAlert('success', message);
    document.querySelector('.profile-container').prepend(alert);
    setTimeout(() => alert.remove(), 5000);
}

/**
 * Shows an error message to the user
 * @param {string} message - Error message to display
 */
function showError(message) {
    const alert = createAlert('danger', message);
    document.querySelector('.profile-container').prepend(alert);
    setTimeout(() => alert.remove(), 5000);
}

/**
 * Creates an alert element
 * @param {string} type - Alert type (success/danger)
 * @param {string} message - Alert message
 * @returns {HTMLElement} - The created alert element
 */
function createAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    return alert;
}