<?php
$pageTitle = "Contact Us - Chania Skills for Africa";
$pageDescription = "Get in touch with Chania Skills for Africa. We're here to help with your skills development journey.";
$activePage = "contact";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/ClientActivityLogger.php';
require_once __DIR__ . '/../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Utilities::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
        Utilities::redirect(BASE_URL . '/client/public/contact.php');
    }

    // Validate required fields
    $requiredFields = ['name', 'email', 'subject', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            Utilities::redirect(BASE_URL . '/client/public/contact.php');
        }
    }

    // Sanitize inputs
    $data = Database::sanitize($_POST);

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Please provide a valid email address.";
        Utilities::redirect(BASE_URL . '/client/public/contact.php');
    }

    try {
        // Log client activity in client_activities and admin_logs
        $logger = new ClientActivityLogger($db);
        $logger->logContactSubmission($data['name'], $data['email'], $data['subject'], $data['message']);

        $_SESSION['success_message'] = "Thank you for your message! We'll get back to you soon.";
        Utilities::redirect(BASE_URL . '/client/public/contact.php');
    } catch (PDOException $e) {
        error_log("Contact Form Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to send your message. Please try again later.";
        Utilities::redirect(BASE_URL . '/client/public/contact.php');
    }
}
?>

<!-- Hero Section -->
<section class="hero-gradient py-5 position-relative overflow-hidden">
    <div class="hero-pattern"></div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 mx-auto text-center">
                <div class="hero-content fade-in-up">
                    <div class="hero-badge mb-4">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill fs-6">
                            <i class="fas fa-comments me-2"></i>Get In Touch
                        </span>
                    </div>
                    <h1 class="display-3 fw-bold text-white mb-4">
                        Contact <span class="text-gradient">Chania Skills</span>
                    </h1>
                    <p class="lead text-white-75 mb-5 mx-auto" style="max-width: 600px;">
                        Ready to transform your future? We're here to guide you on your skills development journey. 
                        Let's connect and explore the possibilities together.
                    </p>
                    <div class="hero-stats row g-4 justify-content-center">
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number text-white fw-bold h3 mb-1">24/7</div>
                                <div class="stat-label text-white-75 small">Support Available</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="stat-item">
                                <div class="stat-number text-white fw-bold h3 mb-1">&lt;24h</div>
                                <div class="stat-label text-white-75 small">Response Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5 position-relative">
    <div class="container">
        <div class="row g-5 align-items-stretch">
            <!-- Contact Form -->
            <div class="col-lg-7">
                <div class="contact-form-wrapper">
                    <div class="mb-5">
                        <h2 class="h1 fw-bold mb-3">Send us a Message</h2>
                        <p class="text-muted fs-5">Fill out the form below and we'll get back to you as soon as possible.</p>
                    </div>

                    <!-- Status Messages -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    <?php unset($_SESSION['success_message']); endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <strong>Error!</strong> <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_message']); endif; ?>

                    <form method="post" class="contact-form needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo Utilities::generateCsrfToken(); ?>">
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="floating-label-group">
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder=" " required>
                                    <label for="name" class="floating-label">
                                        <i class="fas fa-user me-2"></i>Full Name *
                                    </label>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>Please provide your full name.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="floating-label-group">
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder=" " required>
                                    <label for="email" class="floating-label">
                                        <i class="fas fa-envelope me-2"></i>Email Address *
                                    </label>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>Please provide a valid email address.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="floating-label-group">
                                    <select class="form-select form-control-lg" id="subject" name="subject" required>
                                        <option value="" selected disabled>Choose your inquiry type</option>
                                        <option value="General Inquiry">💬 General Inquiry</option>
                                        <option value="Program Information">📚 Program Information</option>
                                        <option value="Partnership">🤝 Partnership Opportunities</option>
                                        <option value="Technical Support">🛠️ Technical Support</option>
                                        <option value="Feedback">⭐ Feedback & Suggestions</option>
                                        <option value="Media Inquiry">📰 Media & Press</option>
                                        <option value="Other">❓ Other</option>
                                    </select>
                                    <label for="subject" class="floating-label select-label">
                                        <i class="fas fa-tag me-2"></i>Subject *
                                    </label>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>Please select a subject for your inquiry.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="floating-label-group">
                                    <textarea class="form-control form-control-lg" id="message" name="message" rows="6" placeholder=" " required></textarea>
                                    <label for="message" class="floating-label">
                                        <i class="fas fa-comment-alt me-2"></i>Your Message *
                                    </label>
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-circle me-2"></i>Please share your message with us.
                                    </div>
                                    <div class="form-text">Minimum 10 characters required</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 fw-semibold position-relative overflow-hidden">
                                <span class="btn-text">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </span>
                                <span class="btn-loading d-none">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="col-lg-5">
                <div class="contact-info-wrapper h-100">
                    <div class="contact-info-card h-100 p-4 p-lg-5">
                        <div class="mb-5">
                            <h3 class="h2 fw-bold mb-3">Let's Connect</h3>
                            <p class="text-muted fs-6">Choose the best way to reach us. We're always here to help!</p>
                        </div>
                        
                        <div class="contact-methods">
                            <div class="contact-method mb-4 p-4 rounded-4 border hover-lift">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="contact-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                                            <i class="fas fa-map-marker-alt fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ms-4 flex-grow-1">
                                        <h4 class="h6 fw-bold mb-2 text-dark">Visit Our Office</h4>
                                        <p class="text-muted mb-2">123 Skill Street<br>Nairobi, Kenya</p>
                                        <a href="#map" class="text-primary text-decoration-none small fw-medium">
                                            <i class="fas fa-directions me-1"></i>Get Directions
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-method mb-4 p-4 rounded-4 border hover-lift">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="contact-icon bg-success bg-opacity-10 text-success rounded-3 p-3">
                                            <i class="fas fa-phone-alt fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ms-4 flex-grow-1">
                                        <h4 class="h6 fw-bold mb-2 text-dark">Call Us Direct</h4>
                                        <p class="text-muted mb-2">+254 700 123 456</p>
                                        <a href="tel:+254700123456" class="text-success text-decoration-none small fw-medium">
                                            <i class="fas fa-phone me-1"></i>Call Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-method mb-4 p-4 rounded-4 border hover-lift">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="contact-icon bg-info bg-opacity-10 text-info rounded-3 p-3">
                                            <i class="fas fa-envelope fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ms-4 flex-grow-1">
                                        <h4 class="h6 fw-bold mb-2 text-dark">Email Support</h4>
                                        <p class="text-muted mb-2">chania.skills@gmail.com</p>
                                        <a href="mailto:chania.skills@gmail.com" class="text-info text-decoration-none small fw-medium">
                                            <i class="fas fa-envelope me-1"></i>Send Email
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="contact-method mb-4 p-4 rounded-4 border hover-lift">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="contact-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                                            <i class="fas fa-clock fa-lg"></i>
                                        </div>
                                    </div>
                                    <div class="ms-4 flex-grow-1">
                                        <h4 class="h6 fw-bold mb-2 text-dark">Working Hours</h4>
                                        <div class="text-muted small">
                                            <div class="mb-1"><strong>Mon - Fri:</strong> 8:00 AM - 5:00 PM</div>
                                            <div class="mb-1"><strong>Saturday:</strong> 9:00 AM - 1:00 PM</div>
                                            <div><strong>Sunday:</strong> Closed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="social-connect mt-5 pt-4 border-top">
                            <h5 class="fw-bold mb-3">Follow Us</h5>
                            <div class="d-flex gap-3">
                                <a href="#" class="social-link btn btn-outline-primary btn-sm rounded-circle p-2">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-link btn btn-outline-info btn-sm rounded-circle p-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link btn btn-outline-primary btn-sm rounded-circle p-2">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="#" class="social-link btn btn-outline-danger btn-sm rounded-circle p-2">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <h2 class="display-6 fw-bold mb-3">Frequently Asked Questions</h2>
                <p class="lead text-muted">Find quick answers to common questions</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How quickly will I receive a response?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What programs do you offer?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer various skills development programs including technology, business, healthcare, and vocational training. Visit our Programs page for detailed information.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How can I apply for a program?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can apply online through our application form, visit our office, or contact us for guidance on the application process.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do you offer financial assistance?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we offer various financial assistance options including scholarships, payment plans, and partnerships with funding organizations. Contact us to learn more.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section id="map" class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="display-6 fw-bold mb-3">Find Us Here</h2>
                <p class="lead text-muted">Located in the heart of Nairobi, easily accessible by public transport</p>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="map-container rounded-4 overflow-hidden shadow-lg position-relative">
                    <div class="ratio ratio-16x9">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.854476489534!2d36.82121431475391!3d-1.268385899079145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f173c0a1f9de7%3A0xad2c84df1f7f2ec8!2s123%20Skill%20Street%2C%20Nairobi!5e0!3m2!1sen!2ske!4v1620000000000!5m2!1sen!2ske"
                            style="border:0; filter: grayscale(20%);"
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="map-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.1); opacity: 0; transition: opacity 0.3s;">
                        <button class="btn btn-light btn-lg rounded-pill shadow">
                            <i class="fas fa-expand-arrows-alt me-2"></i>View Larger Map
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Enhanced Contact Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form validation and enhancement
    const form = document.querySelector('.contact-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    // Floating labels
    const floatingInputs = document.querySelectorAll('.floating-label-group input, .floating-label-group textarea');
    floatingInputs.forEach(input => {
        // Check if input has value on load
        if (input.value.trim() !== '') {
            input.classList.add('has-value');
        }
        
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
        
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
    
    // Form submission handling
    form.addEventListener('submit', function(e) {
        // Custom validation
        const messageField = document.getElementById('message');
        if (messageField.value.trim().length < 10) {
            e.preventDefault();
            messageField.setCustomValidity('Message must be at least 10 characters long.');
        } else {
            messageField.setCustomValidity('');
        }
        
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Shake animation for invalid form
            form.classList.add('shake-animation');
            setTimeout(() => form.classList.remove('shake-animation'), 600);
        } else {
            // Show loading state
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            submitBtn.disabled = true;
        }
        
        form.classList.add('was-validated');
    });
    
    // Map interaction
    const mapContainer = document.querySelector('.map-container');
    const mapOverlay = mapContainer.querySelector('.map-overlay');
    
    mapContainer.addEventListener('mouseenter', function() {
        mapOverlay.style.opacity = '1';
    });
    
    mapContainer.addEventListener('mouseleave', function() {
        mapOverlay.style.opacity = '0';
    });
    
    // Contact method hover effects
    const contactMethods = document.querySelectorAll('.contact-method');
    contactMethods.forEach(method => {
        method.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.1)';
        });
        
        method.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Smooth scroll to map
    const mapLinks = document.querySelectorAll('a[href="#map"]');
    mapLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('map').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Hero scroll indicator
    const scrollIndicator = document.querySelector('.hero-scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            const nextSection = document.querySelector('.hero-gradient').nextElementSibling;
            if (nextSection) {
                nextSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
    
    // Social link hover effects
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0deg)';
        });
    });
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    const animateElements = document.querySelectorAll('.contact-method, .accordion-item');
    animateElements.forEach(el => observer.observe(el));
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>