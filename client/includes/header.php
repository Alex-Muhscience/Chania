<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php
    // Include config if not already included
    if (!defined('SITE_NAME')) {
        require_once __DIR__ . '/config.php';
    }
    
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
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo getFaviconUrl(); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Bootstrap 5 Styling -->
    <style>
        :root {
            --bs-primary: #DA2525;
            --bs-primary-rgb: 218, 37, 37;
            --bs-secondary: #2C3E50;
            --bs-font-sans-serif: 'Inter', system-ui, -apple-system, sans-serif;
            --primary-gradient: linear-gradient(135deg, #DA2525 0%, #B31E1E 100%);
        }
        
        body {
            font-family: var(--bs-font-sans-serif);
        }
        
        .btn-primary {
            --bs-btn-bg: var(--bs-primary);
            --bs-btn-border-color: var(--bs-primary);
            --bs-btn-hover-bg: #B31E1E;
            --bs-btn-hover-border-color: #B31E1E;
            --bs-btn-active-bg: #B31E1E;
            --bs-btn-active-border-color: #B31E1E;
            font-weight: 500;
        }
        
        .bg-primary {
            background-color: var(--bs-primary) !important;
        }
        
        .text-primary {
            color: var(--bs-primary) !important;
        }
        
        .border-primary {
            border-color: var(--bs-primary) !important;
        }
        
        /* Premium Top Bar */
        .premium-topbar {
            background: var(--primary-gradient);
            color: white;
            font-size: 0.875rem;
            padding: 0.75rem 0;
            position: relative;
            overflow: hidden;
        }
        
        .premium-topbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .topbar-item {
            display: flex;
            align-items: center;
            margin-right: 2rem;
        }
        
        .topbar-item i {
            width: 18px;
            text-align: center;
            margin-right: 0.5rem;
            opacity: 0.9;
        }
        
        .topbar-item span {
            font-size: 0.85rem;
            font-weight: 400;
        }
        
        .social-links-top {
            display: flex;
            gap: 0.75rem;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .social-link:hover {
            background: rgba(255,255,255,0.25);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Premium Navigation */
        .premium-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        
        .premium-navbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 20px rgba(0,0,0,0.15);
            padding: 0.75rem 0;
        }
        
        .navbar-brand-premium {
            font-weight: 700;
            font-size: 1.75rem;
            color: var(--bs-primary) !important;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .navbar-brand-premium:hover {
            color: #B31E1E !important;
            transform: scale(1.02);
        }
        
        .brand-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(218, 37, 37, 0.3);
        }
        
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--bs-primary);
        }
        
        .brand-tagline {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .navbar-nav-premium {
            gap: 0.5rem;
        }
        
        .nav-link-premium {
            font-weight: 500;
            color: #495057 !important;
            padding: 0.75rem 1.25rem !important;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none;
        }
        
        .nav-link-premium:hover {
            color: var(--bs-primary) !important;
            background: rgba(218, 37, 37, 0.08);
            transform: translateY(-1px);
        }
        
        .nav-link-premium.active {
            color: var(--bs-primary) !important;
            background: rgba(218, 37, 37, 0.12);
            font-weight: 600;
        }
        
        .dropdown-menu-premium {
            border: none;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 0.5rem;
            backdrop-filter: blur(20px);
            background: rgba(255,255,255,0.98);
        }
        
        .dropdown-item-premium {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .dropdown-item-premium:hover {
            background: rgba(218, 37, 37, 0.08);
            color: var(--bs-primary);
            transform: translateX(5px);
        }
        
        .dropdown-icon {
            width: 36px;
            height: 36px;
            background: rgba(218, 37, 37, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-primary);
        }
        
        .cta-btn {
            background: var(--primary-gradient);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(218, 37, 37, 0.3);
        }
        
        .cta-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(218, 37, 37, 0.4);
        }
        
        .navbar-toggler-premium {
            border: none;
            background: none;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .navbar-toggler-premium:hover {
            background: rgba(218, 37, 37, 0.1);
        }
        
        .hamburger-line {
            width: 24px;
            height: 2px;
            background: var(--bs-primary);
            margin: 4px 0;
            transition: 0.3s;
            border-radius: 2px;
        }
        
        /* New Topbar Styles */
        .topbar {
            background: var(--primary-gradient);
            color: white;
            font-size: 0.875rem;
            padding: 0.5rem 0;
            position: relative;
        }
        
        .topbar-item {
            display: flex;
            align-items: center;
            margin-right: 1.5rem;
            font-size: 0.85rem;
        }
        
        .topbar-item i {
            margin-right: 0.5rem;
            width: 16px;
            text-align: center;
        }
        
        .topbar-social {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .topbar-social a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
        }
        
        .topbar-social a:hover {
            color: white;
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }
        
        .topbar-quick-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .topbar-quick-links a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        
        .topbar-quick-links a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        
        .topbar-language {
            position: relative;
        }
        
        .language-selector {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .language-selector:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .language-selector:focus {
            outline: none;
            background: rgba(255,255,255,0.2);
        }
        
        .language-selector option {
            background: white;
            color: #333;
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        
        .language-selector option:hover {
            background: #f8f9fa;
            color: var(--bs-primary);
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .topbar {
                font-size: 0.8rem;
                padding: 0.4rem 0;
            }
            
            .topbar-item {
                margin-right: 1rem;
                font-size: 0.8rem;
            }
            
            .topbar-quick-links {
                display: none;
            }
            
            .topbar-social {
                gap: 0.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .topbar-item:not(:first-child) {
                display: none;
            }
            
            .topbar-social a {
                width: 24px;
                height: 24px;
                font-size: 0.8rem;
            }
        }
    </style>
    
    <script>
        function changeLanguage(lang) {
            // Get current URL and add language parameter
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('lang', lang);
            window.location.href = currentUrl.toString();
        }
    </script>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="topbar-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo CONTACT_PHONE; ?></span>
                        </div>
                        <div class="topbar-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo CONTACT_EMAIL; ?></span>
                        </div>
                        <div class="topbar-item d-none d-lg-flex">
                            <i class="fas fa-clock"></i>
                            <span>Mon - Fri: 8AM - 6PM EAT</span>
                        </div>
                        <div class="topbar-quick-links d-none d-xl-flex">
                            <a href="<?php echo BASE_URL; ?>apply_online.php">Apply Online</a>
                            <a href="<?php echo BASE_URL; ?>newsletter_subscribe.php">Newsletter</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="topbar-social">
                            <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        </div>
                        <div class="topbar-language ms-3">
                            <select class="language-selector" onchange="changeLanguage(this.value)">
                                <?php 
                                global $supported_languages, $current_lang;
                                foreach ($supported_languages as $lang_code): 
                                    $info = getLanguageInfo($lang_code);
                                    $selected = ($current_lang === $lang_code) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $lang_code; ?>" <?php echo $selected; ?>>
                                        <?php echo $info['flag'] . ' ' . strtoupper($lang_code); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Header -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <?php 
                    $logo_url = getLogoUrl();
                    if (!empty($logo_url) && $logo_url !== ASSETS_URL . 'images/logo.png'):
                    ?>
                        <img src="<?php echo $logo_url; ?>" alt="<?php echo SITE_NAME; ?>" style="height: 40px; width: auto; margin-right: 0.5rem;">
                    <?php else: ?>
                        <i class="fas fa-graduation-cap me-2 text-primary"></i>
                    <?php endif; ?>
                    <span class="text-primary fw-bold"><?php echo SITE_NAME; ?></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>"><?php echo lang('home'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>about.php"><?php echo lang('about'); ?></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php echo basename($_SERVER['PHP_SELF']) == 'programs.php' ? 'active' : ''; ?>" href="#" id="programsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo lang('programs'); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="programsDropdown">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php"><?php echo lang('all_programs'); ?></a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=technology"><?php echo lang('technology'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=business"><?php echo lang('business'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=agriculture"><?php echo lang('agriculture'); ?></a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>programs.php?category=healthcare"><?php echo lang('healthcare'); ?></a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'training-fields.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>training-fields.php"><?php echo lang('trainings'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['events.php', 'event-details.php']) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>events.php"><?php echo lang('events'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>contact.php"><?php echo lang('contact'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="<?php echo BASE_URL; ?>programs.php"><?php echo lang('browse_courses'); ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

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
