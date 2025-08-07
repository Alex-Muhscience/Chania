document.addEventListener('DOMContentLoaded', () => {
    // API base URL - adjust based on current location
    const API_BASE = '/chania/api/v1';
    
    // Handle newsletter subscription
    const subscribeForm = document.getElementById('subscribeForm');
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = subscribeForm.querySelector('input[name="email"]').value;
            const name = subscribeForm.querySelector('input[name="name"]').value;
            await postData(`${API_BASE}/newsletter/subscribe`, { name, email });
        });
    }

    // Handle course application
    const applyForm = document.getElementById('applyForm');
    if (applyForm) {
        applyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(applyForm);
            const formData = Object.fromEntries(data.entries());
            await postData(`${API_BASE}/applications`, formData);
        });
    }

    // Handle contact submission
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(contactForm);
            const formData = Object.fromEntries(data.entries());
            await postData(`${API_BASE}/contacts`, formData);
        });
    }

    // Handle event registration (both registerForm and eventRegistrationForm)
    const registerForm = document.getElementById('registerForm') || document.getElementById('eventRegistrationForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(registerForm);
            const formData = Object.fromEntries(data.entries());
            const eventId = formData.event_id;
            if (!eventId) {
                showMessage('Event ID is missing', 'error');
                return;
            }
            // Split full_name into first_name and last_name if needed
            if (formData.full_name) {
                const nameParts = formData.full_name.trim().split(' ', 2);
                formData.first_name = nameParts[0];
                formData.last_name = nameParts[1] || '';
                delete formData.full_name;
            }
            await postData(`${API_BASE}/events/${eventId}/register`, formData, registerForm);
        });
    }

    // Utility function to POST data to API
    async function postData(url = '', data = {}, formElement = null) {
        let submitButton = null;
        let originalButtonContent = '';
        
        if (formElement) {
            submitButton = formElement.querySelector('button[type="submit"]');
            if (submitButton) {
                originalButtonContent = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            }
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok && result.status === 'success') {
                showMessage(result.message || 'Success!', 'success');
                if (formElement) formElement.reset();
            } else {
                showMessage(result.message || 'An error occurred', 'error');
            }
            
        } catch (error) {
            console.error('Error posting data:', error);
            showMessage('Network error. Please try again.', 'error');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonContent || '<i class="fas fa-ticket-alt me-2"></i>Register Now';
            }
        }
    }

    // Function to show user messages
    function showMessage(message, type = 'info') {
        // Create message element if it doesn't exist
        let messageContainer = document.getElementById('api-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'api-message';
            messageContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: bold;
                z-index: 10000;
                max-width: 400px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(messageContainer);
        }

        // Set message content and style
        messageContainer.textContent = message;
        
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            info: '#17a2b8',
            warning: '#ffc107'
        };
        
        messageContainer.style.backgroundColor = colors[type] || colors.info;
        messageContainer.style.display = 'block';

        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageContainer.style.display = 'none';
        }, 5000);
    }
});

