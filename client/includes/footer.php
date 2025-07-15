        </main>

        <footer class="bg-dark text-white py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <img src="<?php echo BASE_URL; ?>/client/public/assets/images/logo-white.png" alt="Skills for Africa" height="40">
                        <p class="mt-3">Empowering African youth with skills for the digital economy.</p>
                        <div class="social-icons">
                            <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 mb-4">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo BASE_URL; ?>" class="text-white">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/client/public/about.php" class="text-white">About Us</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/client/public/programs.php" class="text-white">Programs</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/client/public/events.php" class="text-white">Events</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/client/public/contact.php" class="text-white">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4">
                        <h5>Programs</h5>
                        <ul class="list-unstyled">
                            <?php
                            $stmt = $db->query("SELECT id, title FROM programs LIMIT 5");
                            while ($row = $stmt->fetch()) {
                                echo '<li><a href="' . BASE_URL . '/client/public/program.php?id=' . $row['id'] . '" class="text-white">' . htmlspecialchars($row['title']) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-4">
                        <h5>Contact Us</h5>
                        <address>
                            <p><i class="fas fa-map-marker-alt me-2"></i> 123 Skill Street, Nairobi, Kenya</p>
                            <p><i class="fas fa-phone me-2"></i> +254 700 123 456</p>
                            <p><i class="fas fa-envelope me-2"></i> info@skillsforafrica.org</p>
                        </address>
                    </div>
                </div>
                <hr class="my-4 bg-secondary">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; <?php echo date('Y'); ?> Skills for Africa. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <a href="<?php echo BASE_URL; ?>/client/public/privacy.php" class="text-white me-3">Privacy Policy</a>
                        <a href="<?php echo BASE_URL; ?>/client/public/terms.php" class="text-white">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bootstrap 5-JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Custom JS -->
        <script src="<?php echo BASE_URL; ?>/client/public/assets/js/main.js"></script>
