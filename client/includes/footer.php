    <!-- Footer -->
    <footer class="footer bg-dark text-light mt-5">
        <div class="container">
            <!-- Main Footer Content -->
            <div class="row py-5">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand mb-3">
                        <img src="<?php echo ASSETS_URL; ?>images/logo-white.png" alt="<?php echo SITE_NAME; ?>" height="40" class="mb-3">
                        <h5 class="text-white"><?php echo SITE_NAME; ?></h5>
                    </div>
                    <p class="text-light-gray mb-3"><?php echo SITE_DESCRIPTION; ?></p>
                    <div class="social-links">
                        <h6 class="text-white mb-3">Follow Us</h6>
                        <a href="#" class="social-link me-3" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link me-3" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link me-3" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link me-3" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link me-3" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="footer-links list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>about">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>programs">Programs</a></li>
                        <li><a href="<?php echo BASE_URL; ?>events">Events</a></li>
                        <li><a href="<?php echo BASE_URL; ?>contact">Contact</a></li>
                        <li><a href="<?php echo BASE_URL; ?>apply">Apply Now</a></li>
                    </ul>
                </div>

                <!-- Programs -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Programs</h6>
                    <ul class="footer-links list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>programs?category=technology">Technology</a></li>
                        <li><a href="<?php echo BASE_URL; ?>programs?category=business">Business</a></li>
                        <li><a href="<?php echo BASE_URL; ?>programs?category=agriculture">Agriculture</a></li>
                        <li><a href="<?php echo BASE_URL; ?>programs?category=healthcare">Healthcare</a></li>
                        <li><a href="<?php echo BASE_URL; ?>programs?featured=1">Featured Programs</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h6 class="text-white mb-3">Contact Information</h6>
                    <div class="contact-info">
                        <div class="contact-item mb-3">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                            <span>Chania, Kenya<br>P.O. Box 123-45678</span>
                        </div>
                        <div class="contact-item mb-3">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <a href="tel:<?php echo str_replace(' ', '', CONTACT_PHONE); ?>"><?php echo CONTACT_PHONE; ?></a>
                        </div>
                        <div class="contact-item mb-3">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <a href="mailto:<?php echo CONTACT_EMAIL; ?>"><?php echo CONTACT_EMAIL; ?></a>
                        </div>
                        <div class="contact-item mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <span>Mon - Fri: 8:00 AM - 5:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Newsletter Signup -->
            <div class="row py-4 border-top border-secondary">
                <div class="col-lg-8 col-md-7 mb-3">
                    <h6 class="text-white mb-2">Stay Updated</h6>
                    <p class="text-light-gray mb-0">Subscribe to our newsletter for the latest updates on programs and events.</p>
                </div>
                <div class="col-lg-4 col-md-5">
                    <form id="newsletter-form" class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Enter your email" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row py-3 border-top border-secondary">
                <div class="col-md-6">
                    <p class="text-light-gray mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="footer-legal-links">
                        <a href="<?php echo BASE_URL; ?>privacy" class="text-light-gray me-3">Privacy Policy</a>
                        <a href="<?php echo BASE_URL; ?>terms" class="text-light-gray">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="btn btn-primary btn-floating" title="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    
    <?php if (isset($additional_js)): ?>
        <?php foreach ($additional_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Hide loading spinner
        window.addEventListener('load', function() {
            const spinner = document.getElementById('loading-spinner');
            if (spinner) {
                spinner.style.display = 'none';
            }
        });
    </script>
</body>
</html>
