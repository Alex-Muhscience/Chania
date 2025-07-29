<?php
$pageTitle = "Apply for a Program";
$activePage = "apply";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Fetch all programs for dropdown
$programs = $db->query("SELECT id, title FROM programs ORDER BY title")->fetchAll();
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="text-center mb-4">Program Application Form</h2>
                        <p class="text-center text-muted mb-5">Fill out the form below to apply for one of our programs. We'll review your application and get back to you shortly.</p>

                        <form id="applicationForm" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo Utilities::generateCsrfToken(); ?>">

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" name="first_name" required>
                                    <div class="invalid-feedback">Please provide your first name.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" name="last_name" required>
                                    <div class="invalid-feedback">Please provide your last name.</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                    <div class="invalid-feedback">Please provide your phone number.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="address" class="form-label">Full Address *</label>
                                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
                                <div class="invalid-feedback">Please provide your address.</div>
                            </div>

                            <div class="mb-4">
                                <label for="program" class="form-label">Select Program *</label>
                                <select class="form-select" id="program" name="program_id" required>
                                    <option value="" selected disabled>Choose a program...</option>
                                    <?php foreach ($programs as $program): ?>
                                    <option value="<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a program.</div>
                            </div>

                            <div class="mb-4">
                                <label for="education" class="form-label">Education Background *</label>
                                <textarea class="form-control" id="education" name="education" rows="3" required></textarea>
                                <div class="invalid-feedback">Please provide your education background.</div>
                            </div>

                            <div class="mb-4">
                                <label for="experience" class="form-label">Work Experience (if any)</label>
                                <textarea class="form-control" id="experience" name="experience" rows="3"></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="motivation" class="form-label">Motivation Letter *</label>
                                <textarea class="form-control" id="motivation" name="motivation" rows="5" required></textarea>
                                <div class="invalid-feedback">Please explain why you want to join this program.</div>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="agreeTerms" name="agree_terms" required>
                                <label class="form-check-label" for="agreeTerms">I agree to the <a href="<?php echo BASE_URL; ?>/client/public/terms.php">Terms and Conditions</a> and <a href="<?php echo BASE_URL; ?>/client/public/privacy.php">Privacy Policy</a> *</label>
                                <div class="invalid-feedback">You must agree to the terms before submitting.</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <span id="submitText">Submit Application</span>
                                    <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </form>

                        <div id="successMessage" class="alert alert-success mt-4 d-none">
                            <h4 class="alert-heading">Application Submitted Successfully!</h4>
                            <p>Thank you for applying to our program. We've received your application and will review it shortly. You'll receive a confirmation email with further details.</p>
                            <hr>
                            <p class="mb-0">Reference ID: <strong id="referenceId"></strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('applicationForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const submitText = document.getElementById('submitText');
    const spinner = document.getElementById('spinner');
    const successMessage = document.getElementById('successMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Show loading state
        submitText.textContent = 'Submitting...';
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;

        // Prepare form data
        const formData = new FormData(form);

        // Submit via AJAX
        fetch('<?php echo BASE_URL; ?>/api/submit-application.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show the success message
                form.classList.add('d-none');
                successMessage.classList.remove('d-none');
                document.getElementById('referenceId').textContent = data.referenceId;
            } else {
                // Show the error message
                alert('Error: ' + data.message);
                submitText.textContent = 'Submit Application';
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitText.textContent = 'Submit Application';
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        });
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>