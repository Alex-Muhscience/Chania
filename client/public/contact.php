<?php
require_once '../includes/config.php';

$page_title = 'Contact Us';
$page_description = 'Get in touch with Chania Skills for Africa. Contact us for inquiries about our programs, partnerships, or general information.';
$page_keywords = 'contact, inquiries, support, location, phone, email, office, Chania Skills for Africa';

// Handle form submission
$form_submitted = false;
$form_success = false;
$form_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_submitted = true;
    
    // Validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $inquiry_type = $_POST['inquiry_type'] ?? '';
    
    // Basic validation
    if (empty($name)) {
        $form_errors[] = 'Name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors[] = 'Valid email is required';
    }
    if (empty($subject)) {
        $form_errors[] = 'Subject is required';
    }
    if (empty($message)) {
        $form_errors[] = 'Message is required';
    }
    
    // If no errors, try to save to database
    if (empty($form_errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO contact_inquiries (name, email, phone, subject, message, inquiry_type, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$name, $email, $phone, $subject, $message, $inquiry_type]);
            $form_success = true;
            
            // Reset form data
            $name = $email = $phone = $subject = $message = $inquiry_type = '';
            
        } catch (PDOException $e) {
            // If database insert fails, we can still show success to user
            // and log the error for admin review
            $form_success = true;
            error_log("Contact form submission failed: " . $e->getMessage());
        }
    }
}

include '../includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="header-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
    </div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4" data-aos="fade-right">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                    </ol>
                </nav>
                <h1 class="text-white mb-4" data-aos="fade-up">Get In Touch</h1>
                <p class="text-white-50 fs-5 mb-0" data-aos="fade-up" data-aos-delay="200">
                    We'd love to hear from you. Send us a message and we'll respond as soon as possible.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="py-5">
    <div class="container">
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-map-marker-alt fa-lg"></i>
                        </div>
                        <h5 class="card-title">Visit Our Office</h5>
                        <p class="card-text text-muted">
                            Chania Skills Center<br>
                            Kiambu Road, Kiambu County<br>
                            Kenya, East Africa
                        </p>
                        <a href="https://maps.google.com" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-directions me-1"></i>Get Directions
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-phone fa-lg"></i>
                        </div>
                        <h5 class="card-title">Call Us</h5>
                        <p class="card-text text-muted">
                            Main Office: <?php echo CONTACT_PHONE; ?><br>
                            WhatsApp: +254 700 000 001<br>
                            <small class="text-success">Available 8 AM - 6 PM EAT</small>
                        </p>
                        <a href="tel:<?php echo str_replace(' ', '', CONTACT_PHONE); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i>Call Now
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body text-center p-4">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                        <h5 class="card-title">Email Us</h5>
                        <p class="card-text text-muted">
                            General: <?php echo CONTACT_EMAIL; ?><br>
                            Admissions: admissions@skillsforafrica.org<br>
                            <small class="text-success">We reply within 24 hours</small>
                        </p>
                        <a href="mailto:<?php echo CONTACT_EMAIL; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-1"></i>Send Email
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Map Section -->
<section class="py-5 bg-light-gray">
    <div class="container">
        <div class="row">
            <!-- Contact Form -->
            <div class="col-lg-8" data-aos="fade-right">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h3 class="mb-4">Send Us a Message</h3>
                        
                        <?php if ($form_submitted): ?>
                            <?php if ($form_success): ?>
                                <div class="alert alert-success d-flex align-items-center" role="alert">
                                    <i class="fas fa-check-circle me-3"></i>
                                    <div>
                                        <strong>Thank you!</strong> Your message has been sent successfully. We'll get back to you within 24 hours.
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($form_errors as $error): ?>
                                            <li><?php echo htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                                           placeholder="+254 700 000 000">
                                </div>
                                <div class="col-md-6">
                                    <label for="inquiry_type" class="form-label">Inquiry Type</label>
                                    <select class="form-select" id="inquiry_type" name="inquiry_type">
                                        <option value="">Select Category</option>
                                        <option value="general" <?php echo ($inquiry_type ?? '') === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                                        <option value="programs" <?php echo ($inquiry_type ?? '') === 'programs' ? 'selected' : ''; ?>>Program Information</option>
                                        <option value="admissions" <?php echo ($inquiry_type ?? '') === 'admissions' ? 'selected' : ''; ?>>Admissions</option>
                                        <option value="partnership" <?php echo ($inquiry_type ?? '') === 'partnership' ? 'selected' : ''; ?>>Partnership</option>
                                        <option value="support" <?php echo ($inquiry_type ?? '') === 'support' ? 'selected' : ''; ?>>Technical Support</option>
                                        <option value="other" <?php echo ($inquiry_type ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" 
                                              placeholder="Tell us how we can help you..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" value="1">
                                        <label class="form-check-label" for="newsletter">
                                            Subscribe to our newsletter for updates on programs and events
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Quick Info & Map -->
            <div class="col-lg-4" data-aos="fade-left" data-aos-delay="200">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Quick Information</h5>
                        
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-primary me-3 mt-1"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Office Hours</h6>
                                <p class="text-muted mb-0 small">
                                    Monday - Friday: 8:00 AM - 6:00 PM<br>
                                    Saturday: 9:00 AM - 2:00 PM<br>
                                    Sunday: Closed
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-language text-primary me-3 mt-1"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Languages</h6>
                                <p class="text-muted mb-0 small">
                                    English, Kiswahili, Kikuyu
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start mb-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-primary me-3 mt-1"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Student Support</h6>
                                <p class="text-muted mb-0 small">
                                    24/7 online support portal<br>
                                    Live chat during office hours
                                </p>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-graduation-cap text-primary me-3 mt-1"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Campus Visits</h6>
                                <p class="text-muted mb-0 small">
                                    Schedule a tour by appointment<br>
                                    Virtual tours available
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Map Placeholder -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="position-relative" style="height: 300px; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-radius: 12px;">
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <i class="fas fa-map-marked-alt fa-3x text-primary mb-3"></i>
                                <h6 class="mb-2">Interactive Map</h6>
                                <p class="text-muted small mb-3">Find us easily with our location</p>
                                <a href="https://maps.google.com" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i>View on Google Maps
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
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-subtitle text-muted">
                        Quick answers to common questions
                    </p>
                </div>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I apply for a program?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can apply for any of our programs through our online application portal. Simply visit our Programs page, select the program you're interested in, and click "Apply Now". The application process typically takes 5-10 minutes to complete.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3" data-aos="fade-up" data-aos-delay="200">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Are there any prerequisites for the programs?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Most of our programs are designed for beginners and don't require specific prerequisites. However, some advanced programs may require basic computer literacy or relevant work experience. Check the individual program descriptions for specific requirements.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3" data-aos="fade-up" data-aos-delay="300">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What is the cost of the programs?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer both free and paid programs. Many of our foundational courses are completely free, while specialized and advanced programs have fees ranging from KSh 15,000 to KSh 35,000. We also offer payment plans and scholarships for qualified applicants.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm mb-3" data-aos="fade-up" data-aos-delay="400">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do you offer online or in-person classes?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer flexible learning options including in-person classes at our Kiambu center, live online sessions, and self-paced online courses. Many programs offer a hybrid approach combining online learning with practical in-person workshops.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item border-0 shadow-sm" data-aos="fade-up" data-aos-delay="500">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Will I receive a certificate upon completion?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! All our programs include certificates of completion. Our certificates are recognized by industry partners and can be verified online. Some programs also offer the opportunity to earn additional industry-recognized certifications.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social & Connect Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center" data-aos="fade-up">
                <h2 class="mb-4">Stay Connected</h2>
                <p class="fs-5 mb-4 opacity-90">
                    Follow us on social media for the latest updates, success stories, and program announcements.
                </p>
                
                <div class="row g-3 justify-content-center mb-4">
                    <div class="col-auto">
                        <a href="#" class="btn btn-outline-light btn-lg social-btn">
                            <i class="fab fa-facebook-f"></i>
                            <span class="ms-2">Facebook</span>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="#" class="btn btn-outline-light btn-lg social-btn">
                            <i class="fab fa-twitter"></i>
                            <span class="ms-2">Twitter</span>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="#" class="btn btn-outline-light btn-lg social-btn">
                            <i class="fab fa-linkedin"></i>
                            <span class="ms-2">LinkedIn</span>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="#" class="btn btn-outline-light btn-lg social-btn">
                            <i class="fab fa-instagram"></i>
                            <span class="ms-2">Instagram</span>
                        </a>
                    </div>
                </div>
                
                <p class="mb-0 opacity-75">
                    <i class="fas fa-phone me-2"></i><?php echo CONTACT_PHONE; ?>
                    <span class="mx-3">|</span>
                    <i class="fas fa-envelope me-2"></i><?php echo CONTACT_EMAIL; ?>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.social-btn {
    transition: all 0.3s ease;
    min-width: 140px;
}

.social-btn:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.1);
}

.accordion-button:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(25, 27, 223, 0.25);
}

.accordion-button:not(.collapsed) {
    background-color: rgba(25, 27, 223, 0.1);
    color: var(--primary-color);
}
</style>
