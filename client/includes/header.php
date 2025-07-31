<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php
    $page_title = isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME;
    $page_description = isset($page_description) ? $page_description : SITE_DESCRIPTION;
    $page_keywords = isset($page_keywords) ? $page_keywords : 'skills training, education, Africa, programs, events';
    ?>
    
    <title><?php echo sanitizeOutput($page_title); ?></title>
    <meta name="description" content="<?php echo sanitizeOutput($page_description); ?>">
    <meta name="keywords" content="<?php echo sanitizeOutput($page_keywords); ?>">
    <meta name="author" content="<?php echo SITE_NAME; ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo sanitizeOutput($page_title); ?>">
    <meta property="og:description" content="<?php echo sanitizeOutput($page_description); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    
    <!-- Loading Spinner -->
    <div id="loading-spinner" class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="topbar" id="topbar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="topbar-left d-flex align-items-center">
                        <div class="topbar-item me-4">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span>Call Us: <?php echo CONTACT_PHONE; ?></span>
                        </div>
                        <div class="topbar-item me-4">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <span>Email: <?php echo CONTACT_EMAIL; ?></span>
                        </div>
                        <div class="topbar-item">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <span>Mon - Fri: 8:00 AM - 6:00 PM EAT</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="topbar-right d-flex align-items-center justify-content-end">
                        <div class="social-links me-3">
                            <a href="#" class="social-link" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="social-link" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                        <div class="language-selector">
                            <div class="dropdown">
                                <button class="btn btn-sm dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-globe me-1"></i>EN
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-flag me-2"></i>English</a></li>
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-flag me-2"></i>Swahili</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-premium fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <div class="brand-logo d-flex align-items-center">
                    <div class="brand-icon me-3">
                        <div class="logo-shape">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="brand-content">
                        <div class="brand-name"><?php echo SITE_NAME; ?></div>
                        <div class="brand-tagline">Empowering Africa Through Skills</div>
                    </div>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo in_array(basename($_SERVER['PHP_SELF']), ['programs.php', 'program.php', 'training-fields.php']) ? 'active' : ''; ?>" href="#" id="programsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-play-circle me-1"></i>Short Courses
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="programsDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php">All Short Courses</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>training-fields.php"><i class="fas fa-th-large me-2"></i>Training Fields</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=technology"><i class="fas fa-laptop-code me-2"></i>Technology</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=business"><i class="fas fa-chart-line me-2"></i>Business</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=agriculture"><i class="fas fa-seedling me-2"></i>Agriculture</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=healthcare"><i class="fas fa-heartbeat me-2"></i>Healthcare</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['events.php', 'event.php']) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>events.php">
                            <i class="fas fa-calendar-alt me-1"></i>Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>contact.php">
                            <i class="fas fa-envelope me-1"></i>Contact
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary nav-cta-btn" href="<?php echo BASE_URL; ?>apply.php">
                            <i class="fas fa-rocket me-2"></i>Enroll Now
                            <span class="btn-shine"></span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    <div id="alert-container" class="fixed-top" style="top: 115px; z-index: 1045;">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
