/**
 * Chania Skills for Africa - Main JavaScript
 * Modern ES6+ JavaScript with functionality for enhanced user experience
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initTopbar();
    initNavbar();
    initBackToTop();
    initNewsletterForm();
    initAnimations();
    initCounters();
    initLoadingSpinner();
});

/**
 * Premium Topbar functionality
 */
function initTopbar() {
    const topbar = document.getElementById('topbar');
    const navbar = document.getElementById('mainNav');
    if (!topbar || !navbar) return;

    let lastScrollY = window.scrollY;
    let topbarHeight = topbar.offsetHeight;

    function handleTopbarScroll() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            // Hide topbar when scrolling down
            topbar.style.transform = 'translateY(-100%)';
            topbar.style.opacity = '0';
            navbar.style.top = '0';
        } else {
            // Show topbar when at top
            topbar.style.transform = 'translateY(0)';
            topbar.style.opacity = '1';
            navbar.style.top = topbarHeight + 'px';
        }
        
        lastScrollY = currentScrollY;
    }

    // Add transition styles
    topbar.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
    navbar.style.transition = 'top 0.3s ease, padding 0.3s ease, box-shadow 0.3s ease';

    // Throttle scroll event for better performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                handleTopbarScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Social links hover effects
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.1)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Enhanced Navbar functionality
 */
function initNavbar() {
    const navbar = document.getElementById('mainNav');
    if (!navbar) return;

    // Add scrolled class on scroll
    function handleScroll() {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }

    // Throttle scroll event for better performance
    let ticking = false;
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(function() {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    });

    // Close mobile menu when clicking on links
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse.classList.contains('show')) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        });
    });

    // Enhanced nav link interactions
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(-1px)';
            }
        });
        
        link.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateY(0)';
            }
        });
    });

    // Premium CTA button effects
    const ctaBtn = document.querySelector('.nav-cta-btn');
    if (ctaBtn) {
        ctaBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });
        
        ctaBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    }
}

/**
 * Back to top button
 */
function initBackToTop() {
    const backToTop = document.getElementById('back-to-top');
    if (!backToTop) return;

    function toggleBackToTop() {
        if (window.scrollY > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    }

    // Show/hide on scroll
    window.addEventListener('scroll', toggleBackToTop);

    // Smooth scroll to top
    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * Newsletter form submission
 */
function initNewsletterForm() {
    const newsletterForm = document.getElementById('newsletter-form');
    if (!newsletterForm) return;

    newsletterForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = this.querySelector('input[type="email"]').value;
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Validate email
        if (!isValidEmail(email)) {
            showAlert('Please enter a valid email address.', 'danger');
            return;
        }

        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        submitBtn.disabled = true;

        try {
            // Call the actual newsletter subscription endpoint
            const formData = new FormData();
            formData.append('email', email);
            formData.append('source', 'website_footer');
            
            const response = await fetch('/chania/client/public/newsletter_subscribe.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.status === 'success' || result.status === 'info') {
                showAlert(result.message, result.status === 'success' ? 'success' : 'info');
                this.reset();
            } else {
                showAlert(result.message || 'An error occurred. Please try again later.', 'danger');
            }
        } catch (error) {
            console.error('Newsletter subscription error:', error);
            showAlert('Unable to connect to server. Please check your internet connection and try again.', 'danger');
        } finally {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

/**
 * Animation utilities
 */
function initAnimations() {
    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all fade-in elements
    document.querySelectorAll('[data-aos]').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Counter animations
 */
function initCounters() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    
    const counterObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
}

/**
 * Utility Functions
 */

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Show alert messages
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show mx-3" role="alert">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-dismiss after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
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

// Animate counter
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-count'));
    const duration = 2000; // 2 seconds
    const start = performance.now();
    const startValue = 0;

    function update(currentTime) {
        const elapsed = currentTime - start;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const currentValue = Math.floor(startValue + (target - startValue) * easeOut);
        
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = target.toLocaleString();
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Loading Spinner
 */
function initLoadingSpinner() {
    const spinner = document.getElementById('loading-spinner');
    if (!spinner) return;

    // Hide spinner after page load
    window.addEventListener('load', function() {
        spinner.style.opacity = '0';
        setTimeout(() => {
            spinner.style.display = 'none';
        }, 300);
    });

    // Fallback: hide spinner after 3 seconds
    setTimeout(() => {
        if (spinner.style.display !== 'none') {
            spinner.style.opacity = '0';
            setTimeout(() => {
                spinner.style.display = 'none';
            }, 300);
        }
    }, 3000);
}

/**
 * Enhanced Card Interactions
 */
function initCardInteractions() {
    const cards = document.querySelectorAll('.program-card-premium, .popular-course-card, .testimonial-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Enhanced Button Interactions
 */
function initButtonInteractions() {
    const buttons = document.querySelectorAll('.btn:not(.nav-cta-btn)');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            if (!this.disabled) {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
}

// Initialize enhanced interactions
document.addEventListener('DOMContentLoaded', function() {
    initCardInteractions();
    initButtonInteractions();
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        
        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const offsetTop = target.offsetTop - 100; // Account for fixed header
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// Simple form validation
document.querySelectorAll('input, textarea, select').forEach(input => {
    input.addEventListener('blur', validateField);
});

function validateField() {
    const field = this;
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    
    // Remove existing validation classes
    field.classList.remove('is-valid', 'is-invalid');
    
    // Skip validation if field is empty and not required
    if (!value && !required) return;
    
    let isValid = true;

    // Required field check
    if (required && !value) {
        isValid = false;
    }
    
    // Type-specific validation
    if (value && isValid) {
        switch (type) {
            case 'email':
                isValid = isValidEmail(value);
                break;
            case 'tel':
                isValid = /^[\+]?[0-9\s\-\(\)]+$/.test(value);
                break;
        }
    }

    // Apply validation classes
    field.classList.add(isValid ? 'is-valid' : 'is-invalid');
    
    return isValid;
}
