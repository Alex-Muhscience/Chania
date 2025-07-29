// Enhanced Homepage JavaScript for Chania Skills for Africa
// Professional UI/UX with Modern Interactive Features v2.0

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initializeScrollReveal();
    initializeScrollIndicator();
    initializeButtonTracking();
    initializeSocialSharing();
    initializeAdvancedAnimations();
    initializeProgressiveEnhancement();
    initializeAccessibility();
    initializePerformanceOptimizations();
    
    // Professional UI enhancements
    initializeProfessionalInteractions();
    initializeMicroAnimations();
    initializeRippleEffects();
    initializeLoadingStates();
    initializeSmartNavigation();
    initializeImageLazyLoading();
    initializeTooltipSystem();
    initializeParallaxEffects();
    initializeTopbar();
});

// Scroll Reveal Animation System
function initializeScrollReveal() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                // Animate numbers if present
                if (entry.target.classList.contains('stat-number')) {
                    animateNumber(entry.target);
                }
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all scroll-reveal elements
    document.querySelectorAll('.scroll-reveal').forEach(el => {
        observer.observe(el);
    });

    // Observe stat numbers for animation
    document.querySelectorAll('.stat-number').forEach(el => {
        observer.observe(el);
    });
}

// Scroll Indicator Functionality
function initializeScrollIndicator() {
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', () => {
            const targetSection = document.querySelector('.hero-modern').nextElementSibling;
            if (targetSection) {
                targetSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        // Hide indicator after scrolling
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            if (window.scrollY > 100) {
                scrollIndicator.style.opacity = '0';
            } else {
                scrollIndicator.style.opacity = '1';
            }
        });
    }
}

// Button Click Tracking and Analytics
function initializeButtonTracking() {
    // Track hero buttons
    const applyBtn = document.getElementById('applyNowBtn');
    const exploreBtn = document.getElementById('exploreBtn');
    const ctaApplyBtn = document.getElementById('ctaApplyBtn');

    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            trackEvent('Hero', 'Apply Now Click', 'Primary CTA');
            // Add loading state
            addLoadingState(applyBtn);
        });
    }

    if (exploreBtn) {
        exploreBtn.addEventListener('click', () => {
            trackEvent('Hero', 'Explore Programs Click', 'Secondary CTA');
            addLoadingState(exploreBtn);
        });
    }

    if (ctaApplyBtn) {
        ctaApplyBtn.addEventListener('click', () => {
            trackEvent('CTA', 'Apply Now Click', 'Bottom CTA');
            addLoadingState(ctaApplyBtn);
        });
    }

    // Track program card interactions
    document.querySelectorAll('.program-card-modern').forEach((card, index) => {
        card.addEventListener('mouseenter', () => {
            trackEvent('Programs', 'Card Hover', `Program ${index + 1}`);
        });

        const viewBtn = card.querySelector('.btn-view-details');
        if (viewBtn) {
            viewBtn.addEventListener('click', () => {
                trackEvent('Programs', 'View Details Click', `Program ${index + 1}`);
            });
        }
    });
}

// Social Sharing Functionality
function initializeSocialSharing() {
    // Add share functionality to social links
    document.querySelectorAll('.social-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const platform = link.getAttribute('title').toLowerCase();
            shareOnPlatform(platform);
        });
    });
}

// Advanced Animation Effects
function initializeAdvancedAnimations() {
    // Parallax effect for hero decorations
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.hero-decoration-1, .hero-decoration-2');
        
        parallaxElements.forEach(element => {
            const speed = element.classList.contains('hero-decoration-1') ? 0.3 : 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Enhanced hover effects for cards
    document.querySelectorAll('.card-modern').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Floating elements animation sync
    const floatingElements = document.querySelectorAll('.floating-card');
    floatingElements.forEach((element, index) => {
        element.style.animationDelay = `${-2 * (index + 1)}s`;
    });
}

// Progressive Enhancement Features
function initializeProgressiveEnhancement() {
    // Enhanced testimonial navigation
    const testimonialNavBtns = document.querySelectorAll('.testimonial-nav-btn');
    testimonialNavBtns.forEach((btn, index) => {
        btn.addEventListener('mouseenter', () => {
            // Preview testimonial on hover
            previewTestimonial(index);
        });
    });

    // Smart loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('skeleton');
                    }
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            img.classList.add('skeleton');
            imageObserver.observe(img);
        });
    }

    // Enhanced form validation
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', handleNewsletterSubmit);
        
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', debounce(validateEmailRealTime, 300));
        }
    }
}

// Accessibility Enhancements
function initializeAccessibility() {
    // Enhanced keyboard navigation
    document.querySelectorAll('.btn, .social-link, .testimonial-nav-btn').forEach(element => {
        element.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                element.click();
            }
        });
    });

    // Screen reader announcements
    const announceToScreenReader = (message) => {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        document.body.appendChild(announcement);
        setTimeout(() => document.body.removeChild(announcement), 1000);
    };

    // Focus management for modals and overlays
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const targetModal = document.querySelector(trigger.getAttribute('data-bs-target'));
            if (targetModal) {
                setTimeout(() => {
                    const firstFocusable = targetModal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
                    if (firstFocusable) firstFocusable.focus();
                }, 150);
            }
        });
    });
}

// Performance Optimizations
function initializePerformanceOptimizations() {
    // Throttle scroll events
    let scrollTimeout;
    const throttledScrollHandler = () => {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            updateScrollProgress();
        }, 16); // ~60fps
    };

    window.addEventListener('scroll', throttledScrollHandler, { passive: true });

    // Preload critical resources
    const preloadCriticalResources = () => {
        const criticalImages = [
            '/client/public/assets/images/hero-image.jpg',
            // Add other critical images
        ];

        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });
    };

    preloadCriticalResources();

    // Service Worker registration for caching
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(err => {
            console.log('Service worker registration failed:', err);
        });
    }
}

// Utility Functions

function animateNumber(element) {
    const target = parseInt(element.textContent.replace(/\D/g, ''));
    const duration = 2000;
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const updateNumber = () => {
        current += increment;
        if (current >= target) {
            element.textContent = element.textContent.replace(/\d+/, target);
            return;
        }
        element.textContent = element.textContent.replace(/\d+/, Math.floor(current));
        requestAnimationFrame(updateNumber);
    };

    requestAnimationFrame(updateNumber);
}

function trackEvent(category, action, label) {
    // Google Analytics 4 event tracking
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            event_category: category,
            event_label: label
        });
    }

    // Also log to console for development
    console.log(`Event: ${category} - ${action} - ${label}`);
}

function addLoadingState(button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
    
    // Remove loading state after navigation (fallback)
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    }, 3000);
}

function shareOnPlatform(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent('Skills for Africa - Digital Training Programs');
    
    let shareUrl;
    switch (platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            break;
        case 'instagram':
            // Instagram doesn't support direct URL sharing
            navigator.clipboard.writeText(window.location.href);
            showNotification('Link copied to clipboard! Share on Instagram.', 'info');
            return;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
        trackEvent('Social', 'Share', platform);
    }
}

function shareEvent(eventTitle) {
    if (navigator.share) {
        navigator.share({
            title: eventTitle,
            text: `Check out this event: ${eventTitle}`,
            url: window.location.href
        }).catch(err => console.log('Error sharing:', err));
    } else {
        // Fallback to clipboard
        navigator.clipboard.writeText(`Check out this event: ${eventTitle} - ${window.location.href}`);
        showNotification('Event details copied to clipboard!', 'success');
    }
    trackEvent('Events', 'Share', eventTitle);
}

function previewTestimonial(index) {
    // Add subtle preview effect
    const carousel = document.getElementById('testimonialCarousel');
    if (carousel) {
        const previewTimeout = setTimeout(() => {
            const bootstrapCarousel = bootstrap.Carousel.getInstance(carousel);
            if (bootstrapCarousel) {
                bootstrapCarousel.to(index);
            }
        }, 500);

        // Clear timeout if mouse leaves
        const navBtn = document.querySelectorAll('.testimonial-nav-btn')[index];
        navBtn.addEventListener('mouseleave', () => clearTimeout(previewTimeout), { once: true });
    }
}

function handleNewsletterSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const emailInput = form.querySelector('input[type="email"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (!validateEmail({ target: emailInput })) return;
    
    // Add loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Subscribing...';
    
    // Simulate API call
    fetch('/api/newsletter-subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email: emailInput.value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Successfully subscribed to newsletter!', 'success');
            form.reset();
            trackEvent('Newsletter', 'Subscribe', 'Homepage');
        } else {
            showNotification(data.message || 'Subscription failed. Please try again.', 'error');
        }
    })
    .catch(error => {
        console.error('Newsletter subscription error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function validateEmail(e) {
    const email = e.target.value;
    const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    
    if (email && !isValid) {
        e.target.classList.add('is-invalid');
        return false;
    } else if (isValid) {
        e.target.classList.remove('is-invalid');
        e.target.classList.add('is-valid');
        return true;
    }
    
    e.target.classList.remove('is-invalid', 'is-valid');
    return email === ''; // Empty is valid (not required to show error)
}

function validateEmailRealTime(e) {
    validateEmail(e);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show notification-toast`;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Create or get notification container
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            max-width: 350px;
        `;
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

function updateScrollProgress() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    
    // Update any scroll progress indicators
    const progressBars = document.querySelectorAll('.scroll-progress');
    progressBars.forEach(bar => {
        bar.style.width = scrolled + '%';
    });
}

// Enhanced error handling
window.addEventListener('error', (e) => {
    console.error('JavaScript error:', e.error);
    // Track errors for monitoring
    if (typeof gtag !== 'undefined') {
        gtag('event', 'exception', {
            description: e.error.toString(),
            fatal: false
        });
    }
});

// Performance monitoring
if (typeof PerformanceObserver !== 'undefined') {
    const observer = new PerformanceObserver((list) => {
        list.getEntries().forEach((entry) => {
            if (entry.entryType === 'navigation') {
                trackEvent('Performance', 'Page Load', Math.round(entry.loadEventEnd - entry.loadEventStart));
            }
        });
    });
    observer.observe({ entryTypes: ['navigation'] });
}

// Professional UI Enhancement Functions

// Professional Interactive Elements
function initializeProfessionalInteractions() {
    // Enhanced button interactions
    document.querySelectorAll('.btn-interactive, .btn-modern').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
            this.style.boxShadow = '0 15px 35px rgba(102, 126, 234, 0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = 'var(--shadow-lg)';
        });
        
        button.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(-1px) scale(0.98)';
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
    });
    
    // Enhanced card interactions with professional effects
    document.querySelectorAll('.card-modern, .program-card-modern, .event-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.willChange = 'transform';
            this.style.transform = 'translateY(-12px) rotateX(5deg)';
            this.style.transformStyle = 'preserve-3d';
            
            // Add glow effect
            this.style.boxShadow = '0 25px 60px rgba(102, 126, 234, 0.2), 0 0 30px rgba(102, 126, 234, 0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotateX(0deg)';
            this.style.boxShadow = 'var(--shadow-md)';
            this.style.willChange = 'auto';
        });
        
        // Add subtle tilt on mouse move
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            const deltaX = (e.clientX - centerX) / rect.width;
            const deltaY = (e.clientY - centerY) / rect.height;
            
            this.style.transform = `translateY(-12px) rotateY(${deltaX * 5}deg) rotateX(${-deltaY * 5}deg)`;
        });
    });
}

// Micro-animations for enhanced UX
function initializeMicroAnimations() {
    // Stagger animation for lists and grids
    const staggerElements = document.querySelectorAll('.program-card-modern, .event-card, .testimonial-modern');
    staggerElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
        element.classList.add('animate-fade-in-up');
    });
    
    // Floating badge animations
    document.querySelectorAll('.badge-featured, .badge-professional').forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.animation = 'none';
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.animation = 'pulse 2s infinite';
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Icon rotation and scale effects
    document.querySelectorAll('.feature-icon, .icon-professional').forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.15) rotate(10deg)';
            this.style.background = 'var(--gradient-secondary)';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
            this.style.background = 'var(--gradient-primary)';
        });
    });
    
    // Progressive number reveals
    document.querySelectorAll('.stat-number').forEach(stat => {
        stat.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.color = 'var(--color-primary)';
        });
        
        stat.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.color = 'var(--color-accent)';
        });
    });
}

// Advanced ripple effects for buttons and interactive elements
function initializeRippleEffects() {
    document.querySelectorAll('.ripple-effect, .btn-modern, .btn-interactive').forEach(element => {
        element.addEventListener('click', function(e) {
            // Create ripple element
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: radial-gradient(circle, rgba(255,255,255,0.6) 0%, transparent 70%);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple-animation 0.6s ease-out;
                pointer-events: none;
                z-index: 1;
            `;
            
            this.appendChild(ripple);
            
            // Remove ripple after animation
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.remove();
                }
            }, 600);
        });
    });
    
    // Add ripple animation keyframes if not exists
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            @keyframes ripple-animation {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Professional loading states and transitions
function initializeLoadingStates() {
    // Form submission loading states
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                addProfessionalLoadingState(submitBtn);
            }
        });
    });
    
    // Link loading states for navigation
    document.querySelectorAll('a[href]:not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.target || this.target === '_self') {
                addNavigationLoadingState();
            }
        });
    });
    
    // AJAX request loading states
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        showGlobalLoadingIndicator();
        return originalFetch.apply(this, args)
            .finally(() => {
                hideGlobalLoadingIndicator();
            });
    };
}

// Smart navigation with preloading and smooth transitions
function initializeSmartNavigation() {
    // Preload pages on hover
    document.querySelectorAll('a[href]:not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(link => {
        let preloadTimeout;
        
        link.addEventListener('mouseenter', function() {
            preloadTimeout = setTimeout(() => {
                preloadPage(this.href);
            }, 200);
        });
        
        link.addEventListener('mouseleave', function() {
            clearTimeout(preloadTimeout);
        });
    });
    
    // Smooth page transitions
    window.addEventListener('beforeunload', function() {
        document.body.style.opacity = '0';
        document.body.style.transform = 'translateY(-20px)';
        document.body.style.transition = 'all 0.3s ease-out';
    });
}

// Enhanced image lazy loading with intersection observer
function initializeImageLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Add loading shimmer effect
                    img.classList.add('skeleton');
                    
                    if (img.dataset.src) {
                        const tempImg = new Image();
                        tempImg.onload = () => {
                            img.src = img.dataset.src;
                            img.classList.remove('skeleton');
                            img.classList.add('image-loaded');
                            
                            // Add entrance animation
                            setTimeout(() => {
                                img.style.opacity = '1';
                                img.style.transform = 'scale(1)';
                            }, 50);
                        };
                        tempImg.src = img.dataset.src;
                    }
                    
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });
        
        // Observe all lazy images
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.style.cssText += 'opacity: 0; transform: scale(0.95); transition: all 0.3s ease;';
            imageObserver.observe(img);
        });
    }
}

// Professional tooltip system
function initializeTooltipSystem() {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'professional-tooltip';
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        pointer-events: none;
        z-index: 10000;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    `;
    document.body.appendChild(tooltip);
    
    // Add tooltips to elements with data-tooltip attribute
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const text = this.getAttribute('data-tooltip');
            tooltip.textContent = text;
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            
            tooltip.style.opacity = '1';
            tooltip.style.transform = 'translateY(0)';
        });
        
        element.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
            tooltip.style.transform = 'translateY(10px)';
        });
    });
}

// Advanced parallax effects
function initializeParallaxEffects() {
    const parallaxElements = document.querySelectorAll('.parallax-element');
    
    if (parallaxElements.length > 0) {
        let ticking = false;
        
        function updateParallax() {
            const scrollY = window.pageYOffset;
            
            parallaxElements.forEach(element => {
                const speed = parseFloat(element.dataset.speed) || 0.5;
                const yPos = -(scrollY * speed);
                element.style.transform = `translate3d(0, ${yPos}px, 0)`;
            });
            
            ticking = false;
        }
        
        function requestParallaxUpdate() {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', requestParallaxUpdate, { passive: true });
    }
    
    // Mouse parallax for hero elements
    const heroSection = document.querySelector('.hero-modern');
    if (heroSection) {
        heroSection.addEventListener('mousemove', function(e) {
            const x = (e.clientX / window.innerWidth) - 0.5;
            const y = (e.clientY / window.innerHeight) - 0.5;
            
            const floatingCards = this.querySelectorAll('.floating-card');
            floatingCards.forEach((card, index) => {
                const intensity = (index + 1) * 10;
                card.style.transform = `translate(${x * intensity}px, ${y * intensity}px)`;
            });
        });
    }
}

// Utility functions for professional features

function addProfessionalLoadingState(button) {
    const originalContent = button.innerHTML;
    const originalWidth = button.offsetWidth;
    
    button.style.width = originalWidth + 'px';
    button.disabled = true;
    button.innerHTML = `
        <div class="d-flex align-items-center justify-content-center">
            <div class="spinner-professional me-2" style="width: 16px; height: 16px;"></div>
            Processing...
        </div>
    `;
    
    // Store original state for restoration
    button._originalContent = originalContent;
    button._originalWidth = originalWidth;
    
    return button;
}

function removeProfessionalLoadingState(button) {
    if (button._originalContent) {
        button.innerHTML = button._originalContent;
        button.disabled = false;
        button.style.width = 'auto';
        delete button._originalContent;
        delete button._originalWidth;
    }
}

function addNavigationLoadingState() {
    const overlay = document.createElement('div');
    overlay.className = 'navigation-loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner-professional';
    overlay.appendChild(spinner);
    
    document.body.appendChild(overlay);
    
    // Trigger fade in
    setTimeout(() => {
        overlay.style.opacity = '1';
    }, 10);
}

function showGlobalLoadingIndicator() {
    let indicator = document.querySelector('.global-loading-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.className = 'global-loading-indicator';
        indicator.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--gradient-primary);
            z-index: 10000;
            transform: scaleX(0);
            transform-origin: left;
            animation: loading-progress 2s ease-in-out infinite;
        `;
        document.body.appendChild(indicator);
        
        // Add animation keyframes
        if (!document.querySelector('#loading-progress-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-progress-styles';
            style.textContent = `
                @keyframes loading-progress {
                    0% { transform: scaleX(0); }
                    50% { transform: scaleX(0.7); }
                    100% { transform: scaleX(1); }
                }
            `;
            document.head.appendChild(style);
        }
    }
}

function hideGlobalLoadingIndicator() {
    const indicator = document.querySelector('.global-loading-indicator');
    if (indicator) {
        indicator.style.animation = 'none';
        indicator.style.transform = 'scaleX(1)';
        setTimeout(() => {
            indicator.remove();
        }, 300);
    }
}

function preloadPage(url) {
    if (!document.querySelector(`link[rel="prefetch"][href="${url}"]`)) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
        
        console.log(`Preloading: ${url}`);
    }
}

// Top bar functionality
function initializeTopbar() {
    const topbar = document.querySelector('.topbar');
    const closeBtn = document.querySelector('.topbar-close');
    
    if (topbar && closeBtn) {
        // Handle topbar close functionality
        closeBtn.addEventListener('click', function() {
            topbar.style.transform = 'translateY(-100%)';
            topbar.style.opacity = '0';
            
            setTimeout(() => {
                topbar.style.display = 'none';
                // Adjust main content to account for removed topbar
                const mainNav = document.querySelector('.navbar');
                if (mainNav) {
                    mainNav.style.top = '0';
                }
            }, 300);
            
            // Track topbar dismissal
            trackEvent('Topbar', 'Close', 'User Action');
        });
        
        // Add smooth animations for topbar links
        const topbarLinks = topbar.querySelectorAll('a');
        topbarLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.transition = 'transform 0.2s ease';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
            
            // Track topbar link clicks
            link.addEventListener('click', function() {
                const linkText = this.textContent.trim() || this.getAttribute('aria-label') || 'Unknown';
                trackEvent('Topbar', 'Link Click', linkText);
            });
        });
        
        // Make topbar responsive on smaller screens
        const handleTopbarResize = () => {
            if (window.innerWidth < 768) {
                topbar.classList.add('topbar-mobile');
            } else {
                topbar.classList.remove('topbar-mobile');
            }
        };
        
        window.addEventListener('resize', handleTopbarResize);
        handleTopbarResize(); // Initial check
        
        // Add subtle animation when page loads
        setTimeout(() => {
            topbar.style.transform = 'translateY(0)';
            topbar.style.opacity = '1';
        }, 500);
    }
    
    // Initialize social media tooltips for topbar
    const socialLinks = document.querySelectorAll('.topbar .social-link');
    socialLinks.forEach(link => {
        const platform = link.getAttribute('aria-label') || link.getAttribute('title');
        if (platform && !link.hasAttribute('data-tooltip')) {
            link.setAttribute('data-tooltip', `Follow us on ${platform}`);
        }
    });
}

// Export functions for global access
window.SkillsForAfrica = {
    trackEvent,
    showNotification,
    shareEvent,
    addLoadingState,
    addProfessionalLoadingState,
    removeProfessionalLoadingState,
    preloadPage,
    initializeTopbar
};
