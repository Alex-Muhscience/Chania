/**
 * Contact form submission handler
 */
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    if (!contactForm) return;

    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(contactForm);
        const data = Object.fromEntries(formData.entries());
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Basic validation
        const errors = [];
        if (!data.name?.trim()) errors.push('Name is required');
        if (!data.email?.trim() || !isValidEmail(data.email)) errors.push('Valid email is required');
        if (!data.subject?.trim()) errors.push('Subject is required');
        if (!data.message?.trim()) errors.push('Message is required');
        
        if (errors.length > 0) {
            showAlert(errors.join('<br>'), 'danger');
            return;
        }
        
        // Display Loading State
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        submitBtn.disabled = true;

        try {
            const contactUrl = '/chania/api/contact.php';
            const response = await fetch(contactUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                showAlert(result.message || 'Thank you! Your message has been sent successfully.', 'success');
                this.reset();
            } else {
                showAlert(result.message || 'An error occurred. Please try again.', 'danger');
            }
        } catch (error) {
            console.error('Contact form error:', error);
            showAlert('An error occurred. Please try again later.', 'danger');
        } finally {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// Add to main initialization
document.addEventListener('DOMContentLoaded', function() {
    initContactForm();
});

// Show alert at the top of the page
function showAlert(message, type = 'info', duration = 5000) {
    // Create alert container if it doesn't exist
    let alertContainer = document.getElementById('alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alert-container';
        alertContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(alertContainer);
    }

    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-dismiss after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, duration);
}

// Get appropriate icon for alert type
function getAlertIcon(type) {
    const icons = {
        success: 'check-circle',
        danger: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Email validation function
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
