document.addEventListener('DOMContentLoaded', () => {
    // Handle newsletter subscription
    const subscribeForm = document.getElementById('subscribeForm');
    if (subscribeForm) {
        subscribeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = subscribeForm.querySelector('input[name="email"]').value;
            const name = subscribeForm.querySelector('input[name="name"]').value;
            await postData('/api/subscriptions/newsletter', { email, name });
        });
    }

    // Handle course application
    const applyForm = document.getElementById('applyForm');
    if (applyForm) {
        applyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(applyForm);
            const formData = Object.fromEntries(data.entries());
            await postData('/api/applications/course', formData);
        });
    }

    // Handle contact submission
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(contactForm);
            const formData = Object.fromEntries(data.entries());
            await postData('/api/contact', formData);
        });
    }

    // Handle event registration
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const data = new FormData(registerForm);
            const formData = Object.fromEntries(data.entries());
            await postData('/api/registrations/event', formData);
        });
    }

    // Utility function to POST data to API
    async function postData(url = '', data = {}) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            console.log(result);
        } catch (error) {
            console.error('Error posting data:', error);
        }
    }
});

