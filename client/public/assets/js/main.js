document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    // Testimonial carousel
    const testimonialCarousel = document.getElementById('testimonialCarousel');
    if (testimonialCarousel) {
        const carousel = new bootstrap.Carousel(testimonialCarousel, {
            interval: 5000,
            pause: 'hover'
        });
    }

    // Back to top button
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopButton.className = 'btn btn-primary btn-back-to-top';
    backToTopButton.setAttribute('aria-label', 'Back to top');
    document.body.appendChild(backToTopButton);

    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });

    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Program filter
    const programFilter = document.getElementById('programFilter');
    if (programFilter) {
        programFilter.addEventListener('change', function() {
            const category = this.value;
            window.location.href = `programs.php${category ? '?category=' + encodeURIComponent(category) : ''}`;
        });
    }

    // Lazy loading images
    if ('loading' in HTMLImageElement.prototype) {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        lazyImages.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
            }
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/vanilla-lazyload@17.4.0/dist/lazyload.min.js';
        document.body.appendChild(script);

        script.onload = function() {
            new LazyLoad({
                elements_selector: '[loading="lazy"]'
            });
        };
    }

    // Accessibility improvements for back to top button
    backToTopButton.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            backToTopButton.click();
        }
    });

    // Add focus styles for back to top button
    backToTopButton.addEventListener('focus', function() {
        backToTopButton.classList.add('focus-visible');
    });
    backToTopButton.addEventListener('blur', function() {
        backToTopButton.classList.remove('focus-visible');
    });

    // Enhanced form handling for application forms
    const applicationForm = document.getElementById('applicationForm');
    if (applicationForm) {
        applicationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = applicationForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            
            // Create form data
            const formData = new FormData(applicationForm);
            
            // Submit form via AJAX
            fetch(applicationForm.action || 'submit_application.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Application submitted successfully!', 'success');
                    applicationForm.reset();
                    applicationForm.classList.remove('was-validated');
                } else {
                    showNotification(data.message || 'Failed to submit application. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }

    // Contact form handling
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            const formData = new FormData(contactForm);
            
            fetch('submit_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Message sent successfully!', 'success');
                    contactForm.reset();
                    contactForm.classList.remove('was-validated');
                } else {
                    showNotification(data.message || 'Failed to send message. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }

    // Newsletter subscription form handling
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = newsletterForm.querySelector('input[type="email"]');
            const submitButton = newsletterForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Basic email validation
            if (!emailInput.value || !emailInput.checkValidity()) {
                showNotification('Please enter a valid email address.', 'error');
                return;
            }
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subscribing...';
            
            const formData = new FormData(newsletterForm);
            
            fetch('api/newsletter_subscribe.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Successfully subscribed to newsletter!', 'success');
                    newsletterForm.reset();
                    newsletterForm.classList.remove('was-validated');
                } else {
                    showNotification(data.message || 'Failed to subscribe. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Newsletter subscription error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        });
    }

    // Search functionality enhancement
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification-toast`;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Create notification container if it doesn't exist
        let container = document.querySelector('.notification-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Search function for programs
    function performSearch(query) {
        if (!query.trim()) {
            // Reset to show all programs
            window.location.href = 'programs.php';
            return;
        }
        
        // Redirect to programs page with search query
        window.location.href = `programs.php?search=${encodeURIComponent(query)}`;
    }

    // Modal handling improvements
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            // Focus first input when modal opens
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });

    // Prevent modal backdrop click when forms are dirty
    modals.forEach(modal => {
        const form = modal.querySelector('form');
        if (form) {
            modal.addEventListener('hide.bs.modal', function(e) {
                const formData = new FormData(form);
                let isDirty = false;
                
                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        isDirty = true;
                        break;
                    }
                }
                
                if (isDirty && !confirm('You have unsaved changes. Are you sure you want to close?')) {
                    e.preventDefault();
                }
            });
        }
    });

});
