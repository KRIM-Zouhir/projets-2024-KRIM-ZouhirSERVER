// auth.js
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form');
    const inputs = document.querySelectorAll('.form-control');

    // Add floating label effect
    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', () => {
            if (!input.value) {
                input.parentElement.classList.remove('focused');
            }
        });
    });

    // Form validation
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const inputs = form.querySelectorAll('.form-control[required]');

            inputs.forEach(input => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    });

    // Password strength meter (for registration)
    const passwordInput = document.querySelector('input[type="password"]');
    if (passwordInput) {
        const strengthMeter = createStrengthMeter();
        passwordInput.parentElement.appendChild(strengthMeter);

        passwordInput.addEventListener('input', () => {
            updateStrengthMeter(passwordInput.value, strengthMeter);
        });
    }
});

function validateInput(input) {
    removeError(input);

    if (!input.value.trim()) {
        showError(input, 'This field is required');
        return false;
    }

    if (input.type === 'email' && !validateEmail(input.value)) {
        showError(input, 'Please enter a valid email address');
        return false;
    }

    return true;
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(input, message) {
    input.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentElement.appendChild(errorDiv);
}

function removeError(input) {
    input.classList.remove('is-invalid');
    const errorMessage = input.parentElement.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function createStrengthMeter() {
    const meter = document.createElement('div');
    meter.className = 'password-strength-meter';
    meter.innerHTML = `
        <div class="strength-bar"></div>
        <span class="strength-text"></span>
    `;
    return meter;
}

function updateStrengthMeter(password, meterElement) {
    const strength = calculatePasswordStrength(password);
    const bar = meterElement.querySelector('.strength-bar');
    const text = meterElement.querySelector('.strength-text');

    bar.style.width = `${strength.score}%`;
    bar.style.backgroundColor = strength.color;
    text.textContent = strength.message;
}

function calculatePasswordStrength(password) {
    let score = 0;
    let message = '';
    let color = '#dc3545';

    if (password.length >= 8) score += 25;
    if (password.match(/[A-Z]/)) score += 25;
    if (password.match(/[0-9]/)) score += 25;
    if (password.match(/[^A-Za-z0-9]/)) score += 25;

    if (score === 100) {
        message = 'Strong';
        color = '#28a745';
    } else if (score >= 50) {
        message = 'Medium';
        color = '#ffc107';
    } else {
        message = 'Weak';
    }

    return { score, message, color };
}