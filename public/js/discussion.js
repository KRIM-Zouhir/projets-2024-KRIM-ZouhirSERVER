document.addEventListener('DOMContentLoaded', function() {
    // Enhanced form validation with black and white theme
    const form = document.querySelector('form');
    const submitButtons = document.querySelectorAll('button[type="submit"]');

    // Add dynamic validation feedback
    function validateForm() {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;

        inputs.forEach(input => {
            // Remove previous validation states
            input.classList.remove('is-invalid', 'is-valid');

            // Basic validation
            if (input.hasAttribute('required') && !input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.add('is-valid');
            }
        });

        return isValid;
    }

    // Add validation on input
    form.querySelectorAll('input, textarea, select').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid', 'is-valid');
            
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('is-invalid');
            } else {
                this.classList.add('is-valid');
            }
        });
    });

    // Prevent form submission if invalid
    submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                
                // Highlight first invalid input
                const firstInvalidInput = form.querySelector('.is-invalid');
                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                }
            }
        });
    });

    // Draft saving functionality
    const saveDraftButton = document.querySelector('button[name="save_draft"]');
    if (saveDraftButton) {
        saveDraftButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Simple local storage draft save mechanism
            const formData = new FormData(form);
            const draftData = {};
            
            for (let [key, value] of formData.entries()) {
                draftData[key] = value;
            }
            
            try {
                localStorage.setItem('discussionDraft', JSON.stringify(draftData));
                alert('Draft saved successfully in black and white style!');
            } catch (error) {
                console.error('Draft saving failed:', error);
                alert('Unable to save draft. Please check browser settings.');
            }
        });
    }

    // Optional: Restore draft functionality
    window.addEventListener('load', function() {
        try {
            const savedDraft = localStorage.getItem('discussionDraft');
            if (savedDraft) {
                const draftData = JSON.parse(savedDraft);
                
                // Populate form fields
                for (let [key, value] of Object.entries(draftData)) {
                    const field = form.querySelector(`[name*="${key}"]`);
                    if (field) {
                        field.value = value;
                    }
                }
            }
        } catch (error) {
            console.error('Draft restoration failed:', error);
        }
    });
});