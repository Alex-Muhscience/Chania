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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/client/public/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>/client/public/assets/images/logo.png" alt="Skills for Africa" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <nav class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link<?php echo $activePage === 'home' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo $activePage === 'about' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/about.php">About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle<?php echo in_array($activePage, ['programs', 'courses']) ? ' active' : ''; ?>" href="#" id="programsDropdown" role="button" data-bs-toggle="dropdown">
                            Programs
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/client/public/programs.php">All Programs</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            // Fetch program categories
                            $stmt = $db->query("SELECT DISTINCT category FROM programs ORDER BY category");
                            while ($row = $stmt->fetch()) {
                                echo '<li><a class="dropdown-item" href="' . BASE_URL . '/programs.php?category=' . urlencode($row['category']) . '">' . htmlspecialchars($row['category']) . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo $activePage === 'events' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo $activePage === 'contact' ? ' active' : ''; ?>" href="<?php echo BASE_URL; ?>/client/public/contact.php">Contact</a>
                    </li>
                </ul>
                <a href="<?php echo BASE_URL; ?>/client/public/apply.php" class="btn btn-primary ms-lg-3">Apply Now</a>
            </nav>
        </div>
    </header>

    <main>