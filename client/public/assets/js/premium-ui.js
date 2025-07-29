/**
 * CHANIA SKILLS FOR AFRICA - PREMIUM UI INTERACTIONS
 * Immersive JavaScript for Enhanced User Experience
 */

class PremiumUI {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initScrollAnimations();
        this.initNavbarEffects();
        this.initButtonEffects();
        this.initTopbarInteractions();
        this.initPageTransitions();
        this.initTouchEffects();
    }

    setupEventListeners() {
        // DOM Content Loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.onDOMReady();
            });
        } else {
            this.onDOMReady();
        }

        // Window events
        window.addEventListener('scroll', this.throttle(this.onScroll.bind(this), 16));
        window.addEventListener('resize', this.throttle(this.onResize.bind(this), 100));
        window.addEventListener('load', this.onWindowLoad.bind(this));
    }

    onDOMReady() {
        // Page entrance animation
        document.body.classList.add('page-enter-active');
        
        // Initialize tooltips
        this.initTooltips();
        
        // Initialize scroll reveal
        this.revealElementsInView();
    }

    onWindowLoad() {
        // Hide loading states, initialize lazy loading, etc.
        this.hidePreloader();
    }

    onScroll() {
        this.updateNavbarOnScroll();
        this.revealElementsInView();
        this.updateScrollProgress();
    }

    onResize() {
        this.updateLayout();
    }

    // Navbar scroll effects
    initNavbarEffects() {
        const navbar = document.getElementById('main-navbar');
        if (!navbar) return;

        let lastScrollY = window.scrollY;
        let scrollDirection = 'up';

        window.addEventListener('scroll', this.throttle(() => {
            const currentScrollY = window.scrollY;
            scrollDirection = currentScrollY > lastScrollY ? 'down' : 'up';
            lastScrollY = currentScrollY;

            if (currentScrollY > 100) {
                navbar.classList.add('scrolled');
                if (scrollDirection === 'down' && currentScrollY > 300) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
            } else {
                navbar.classList.remove('scrolled');
                navbar.style.transform = 'translateY(0)';
            }
        }, 16));
    }

    updateNavbarOnScroll() {
        const navbar = document.getElementById('main-navbar');
        if (!navbar) return;

        const scrolled = window.scrollY > 50;
        navbar.classList.toggle('navbar-scrolled', scrolled);
    }

    // Scroll animations
    initScrollAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        this.scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, observerOptions);

        // Observe all scroll-reveal elements
        document.querySelectorAll('.scroll-reveal').forEach(el => {
            this.scrollObserver.observe(el);
        });
    }

    revealElementsInView() {
        const elements = document.querySelectorAll('.scroll-reveal:not(.revealed)');
        elements.forEach(el => {
            if (this.isElementInViewport(el)) {
                el.classList.add('revealed');
            }
        });
    }

    isElementInViewport(el, offset = 100) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top <= (window.innerHeight - offset) &&
            rect.bottom >= 0 &&
            rect.left >= 0 &&
            rect.right <= window.innerWidth
        );
    }

    // Button interactions
    initButtonEffects() {
        document.querySelectorAll('.btn-modern, .ripple-effect').forEach(button => {
            button.addEventListener('click', this.createRippleEffect.bind(this));
        });

        // Hover effects for interactive elements
        document.querySelectorAll('.hover-lift').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'translateY(-4px)';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'translateY(0)';
            });
        });

        document.querySelectorAll('.hover-scale').forEach(el => {
            el.addEventListener('mouseenter', () => {
                el.style.transform = 'scale(1.05)';
            });
            el.addEventListener('mouseleave', () => {
                el.style.transform = 'scale(1)';
            });
        });
    }

    createRippleEffect(e) {
        const button = e.currentTarget;
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        // Remove existing ripples
        const existingRipple = button.querySelector('.ripple');
        if (existingRipple) {
            existingRipple.remove();
        }

        button.appendChild(ripple);

        // Remove ripple after animation
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.remove();
            }
        }, 600);
    }

    // Topbar interactions
    initTopbarInteractions() {
        const topbarClose = document.getElementById('topbarClose');
        const topbar = document.getElementById('topbar');

        if (topbarClose && topbar) {
            topbarClose.addEventListener('click', () => {
                topbar.style.transform = 'translateY(-100%)';
                topbar.style.opacity = '0';
                setTimeout(() => {
                    topbar.style.display = 'none';
                }, 300);
            });
        }

        // Auto-hide topbar on mobile scroll
        if (window.innerWidth <= 768) {
            let lastScrollY = window.scrollY;
            window.addEventListener('scroll', this.throttle(() => {
                const currentScrollY = window.scrollY;
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    if (topbar) {
                        topbar.style.transform = 'translateY(-100%)';
                    }
                } else {
                    if (topbar) {
                        topbar.style.transform = 'translateY(0)';
                    }
                }
                lastScrollY = currentScrollY;
            }, 16));
        }
    }

    // Page transitions
    initPageTransitions() {
        // Smooth page transitions for internal links
        document.querySelectorAll('a[href^="' + window.location.origin + '"], a[href^="/"], a[href^="./"], a[href^="../"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && !href.startsWith('#') && !link.hasAttribute('target')) {
                    e.preventDefault();
                    this.navigateWithTransition(href);
                }
            });
        });
    }

    navigateWithTransition(url) {
        document.body.classList.add('page-exit');
        setTimeout(() => {
            window.location.href = url;
        }, 300);
    }

    // Touch effects for mobile
    initTouchEffects() {
        if ('ontouchstart' in window) {
            document.querySelectorAll('.card-modern, .btn-modern').forEach(el => {
                el.addEventListener('touchstart', () => {
                    el.classList.add('touch-active');
                });
                el.addEventListener('touchend', () => {
                    setTimeout(() => {
                        el.classList.remove('touch-active');
                    }, 150);
                });
            });
        }
    }

    // Tooltips
    initTooltips() {
        document.querySelectorAll('[title], [data-tooltip]').forEach(el => {
            const tooltipText = el.getAttribute('title') || el.getAttribute('data-tooltip');
            if (tooltipText) {
                el.setAttribute('title', ''); // Remove default tooltip
                
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = tooltipText;
                document.body.appendChild(tooltip);

                el.addEventListener('mouseenter', (e) => {
                    const rect = el.getBoundingClientRect();
                    tooltip.style.left = rect.left + rect.width / 2 + 'px';
                    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                    tooltip.classList.add('show');
                });

                el.addEventListener('mouseleave', () => {
                    tooltip.classList.remove('show');
                });
            }
        });
    }

    // Scroll progress indicator
    updateScrollProgress() {
        const scrollProgress = document.querySelector('.scroll-progress');
        if (scrollProgress) {
            const scrolled = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            scrollProgress.style.width = scrolled + '%';
        }
    }

    // Preloader
    hidePreloader() {
        const preloader = document.querySelector('.preloader');
        if (preloader) {
            preloader.classList.add('fade-out');
            setTimeout(() => {
                preloader.remove();
            }, 500);
        }
    }

    // Layout updates
    updateLayout() {
        // Recalculate any layout-dependent elements
        this.revealElementsInView();
    }

    // Utility functions
    throttle(func, delay) {
        let timeoutId;
        let lastExecTime = 0;
        return function (...args) {
            const currentTime = Date.now();
            
            if (currentTime - lastExecTime > delay) {
                func.apply(this, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    }

    debounce(func, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }
}

// Initialize the premium UI
document.addEventListener('DOMContentLoaded', () => {
    new PremiumUI();
});

// Additional interactive features
class InteractiveFeatures {
    constructor() {
        this.initParallax();
        this.initAnimatedCounters();
        this.initSmoothScroll();
    }

    initParallax() {
        const parallaxElements = document.querySelectorAll('.parallax');
        if (parallaxElements.length === 0) return;

        window.addEventListener('scroll', this.throttle(() => {
            const scrolled = window.pageYOffset;
            parallaxElements.forEach(el => {
                const rate = scrolled * -0.5;
                el.style.transform = `translateY(${rate}px)`;
            });
        }, 16));
    }

    initAnimatedCounters() {
        const counters = document.querySelectorAll('.stat-number');
        const observerOptions = {
            threshold: 0.7
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    this.animateCounter(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }

    animateCounter(element) {
        element.classList.add('counted');
        const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
        let current = 0;
        const increment = target / 60; // 60 frames for 1 second at 60fps
        const suffix = element.textContent.replace(/[0-9]/g, '');

        const updateCounter = () => {
            current += increment;
            if (current < target) {
                element.textContent = Math.floor(current) + suffix;
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + suffix;
            }
        };

        updateCounter();
    }

    initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    throttle(func, delay) {
        let timeoutId;
        let lastExecTime = 0;
        return function (...args) {
            const currentTime = Date.now();
            
            if (currentTime - lastExecTime > delay) {
                func.apply(this, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    func.apply(this, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    }
}

// Initialize interactive features
document.addEventListener('DOMContentLoaded', () => {
    new InteractiveFeatures();
});

// Export for use in other files
window.PremiumUI = PremiumUI;
window.InteractiveFeatures = InteractiveFeatures;
