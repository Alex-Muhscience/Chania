<?php

require_once('config.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Skills for Africa'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription ?? 'Empowering African youth with skills for the digital economy'); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <link href="<?php echo BASE_URL; ?>/client/public/assets/css/styles.css" rel="stylesheet">
    
    <!-- Premium UI JavaScript -->
    <script src="<?php echo BASE_URL; ?>/client/public/assets/js/premium-ui.js" defer></script>
</head>
<body class="page-enter">
    <!-- Skip to the main content for accessibility -->
    <a href="#main-content" class="skip-to-main sr-only">Skip to main content</a>
    
    <!-- Premium Topbar -->
    <div class="topbar" id="topbar">
        <div class="container">
            <div class="row align-items-center py-2">
                <div class="col-md-8">
                    <div class="topbar-info d-flex flex-wrap align-items-center gap-3 gap-md-4">
                        <!-- Email -->
                        <div class="topbar-item">
                            <i class="fas fa-envelope topbar-icon"></i>
                            <a href="mailto:info@skillsforafrica.org" class="topbar-link">
                                <span class="d-none d-sm-inline">info@skillsforafrica.org</span>
                                <span class="d-sm-none">Email</span>
                            </a>
                        </div>
                        
                        <!-- Phone -->
                        <div class="topbar-item">
                            <i class="fas fa-phone topbar-icon"></i>
                            <a href="tel:+254700000000" class="topbar-link">
                                <span class="d-none d-sm-inline">+254 700 000 000</span>
                                <span class="d-sm-none">Call</span>
                            </a>
                        </div>
                        
                        <!-- Location -->
                        <div class="topbar-item d-none d-lg-flex">
                            <i class="fas fa-map-marker-alt topbar-icon"></i>
                            <span class="topbar-text">Nairobi, Kenya</span>
                        </div>
                        
                        <!-- Working Hours -->
                        <div class="topbar-item d-none d-xl-flex">
                            <i class="fas fa-clock topbar-icon"></i>
                            <span class="topbar-text">Mon-Fri: 8AM-6PM</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="topbar-social">
                        <!-- Social Media Links -->
                        <div class="social-links">
                            <a href="https://facebook.com/skillsforafrica" class="topbar-social-link" 
                               title="Follow us on Facebook" target="_blank" rel="noopener">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/skillsforafrica" class="topbar-social-link" 
                               title="Follow us on Twitter" target="_blank" rel="noopener">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://linkedin.com/company/skillsforafrica" class="topbar-social-link" 
                               title="Connect on LinkedIn" target="_blank" rel="noopener">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://instagram.com/skillsforafrica" class="topbar-social-link" 
                               title="Follow us on Instagram" target="_blank" rel="noopener">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://youtube.com/skillsforafrica" class="topbar-social-link" 
                               title="Subscribe to our YouTube" target="_blank" rel="noopener">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                        
                        <!-- Language Selector -->
                        <div class="topbar-language d-none d-lg-block">
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button" 
                                        id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-globe me-1"></i> EN
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="languageDropdown">
                                    <li><a class="dropdown-item" href="#">🇬🇧 English</a></li>
                                    <li><a class="dropdown-item" href="#">🇰🇪 Kiswahili</a></li>
                                    <li><a class="dropdown-item" href="#">🇫🇷 Français</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Topbar Close Button (for mobile) -->
        <button class="topbar-close d-md-none" type="button" id="topbarClose">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <header class="navbar navbar-expand-lg navbar-light navbar-modern sticky-top" id="main-navbar">
        <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo BASE_URL; ?>/client/public/assets/images/logo.svg" alt="Skills for Africa" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <nav class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'home' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'about' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'programs' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/programs.php">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'program_categories' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/program_categories.php">Training Areas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'events' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modern<?php echo $activePage === 'contact' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/contact.php">Contact</a>
                    </li>
                </ul>
                <a href="<?php echo BASE_URL; ?>/client/public/apply.php" class="btn btn-modern btn-gradient-primary ms-lg-3">Apply Now</a>
            </nav>
        </div>
    </header>

    <main id="main-content">
