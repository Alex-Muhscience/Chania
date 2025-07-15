<?php
$pageTitle = "Contact Us - Skills for Africa";
$pageDescription = "Get in touch with Skills for Africa team";
$activePage = "contact";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!Utilities::validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = "Invalid form submission. Please try again.";
        Utilities::redirect('/contact.php');
    }

    // Validate required fields
    $requiredFields = ['name', 'email', 'subject', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            Utilities::redirect('/contact.php');
        }
    }

    // Sanitize inputs
    $data = Database::sanitize($_POST);

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Please provide a valid email address.";
        Utilities::redirect('/contact.php');
    }

    try {
        // Save to database
        $stmt = $db->prepare("
            INSERT INTO contacts (name, email, subject, message, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['subject'],
            $data['message'],
            $_SERVER['REMOTE_ADDR']
        ]);

        // TODO: Send email notification

        $_SESSION['success_message'] = "Thank you for your message! We'll get back to you soon.";
        Utilities::redirect('/contact.php');
    } catch (PDOException $e) {
        error_log("Contact Form Error: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to send your message. Please try again later.";
        Utilities::redirect('/contact.php');
    }
}
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
                <p class="lead">Have questions or feedback? We'd love to hear from you.</p>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="pe-lg-5">
                    <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); endif; ?>

                    <form method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo Utilities::generateCsrfToken(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Please provide your name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" selected disabled>Select a subject</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Program Information">Program Information</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Feedback">Feedback</option>
                                <option value="Other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject.</div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            <div class="invalid-feedback">Please provide your message.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-4 p-lg-5">
                        <h3 class="h4 fw-bold mb-4">Get in Touch</h3>

                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-map-marker-alt text-primary fa-lg"></i>
                            </div>
                            <div class="ms-4">
                                <h4 class="h6 mb-1">Our Location</h4>
                                <p class="mb-0">123 Skill Street, Nairobi, Kenya</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-phone-alt text-primary fa-lg"></i>
                            </div>
                            <div class="ms-4">
                                <h4 class="h6 mb-1">Call Us</h4>
                                <p class="mb-0">+254 700 123 456</p>
                            </div>
                        </div>

                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-envelope text-primary fa-lg"></i>
                            </div>
                            <div class="ms-4">
                                <h4 class="h6 mb-1">Email Us</h4>
                                <p class="mb-0">info@skillsforafrica.org</p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-3 p-3">
                                <i class="fas fa-clock text-primary fa-lg"></i>
                            </div>
                            <div class="ms-4">
                                <h4 class="h6 mb-1">Working Hours</h4>
                                <p class="mb-0">Monday - Friday: 8:00 AM - 5:00 PM</p>
                                <p class="mb-0">Saturday: 9:00 AM - 1:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.854476489534!2d36.82121431475391!3d-1.268385899079145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f173c0a1f9de7%3A0xad2c84df1f7f2ec8!2s123%20Skill%20Street%2C%20Nairobi!5e0!3m2!1sen!2ske!4v1620000000000!5m2!1sen!2ske"
                            style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');

    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }

        form.classList.add('was-validated');
    }, false);
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>