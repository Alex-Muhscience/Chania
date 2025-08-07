<?php
// If this file is accessed directly, set up basic configuration
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/client/includes/config.php';
}

$page_title = 'Page Not Found';
$page_description = 'The page you are looking for could not be found.';
$page_keywords = '404, page not found, error';

// Check if we're being included from another file
$is_included = (basename($_SERVER['PHP_SELF']) !== '404.php');

if (!$is_included) {
    // Send 404 header only if accessed directly
    header('HTTP/1.0 404 Not Found');
}

// Include the header
include __DIR__ . '/client/includes/header.php';
?>

<!-- Custom Styles for 404 Page -->
<style>
    .error-container {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        position: relative;
        overflow: hidden;
    }
    
    .error-content {
        text-align: center;
        max-width: 700px;
        z-index: 2;
        position: relative;
    }
    
    .error-number {
        font-size: 10rem;
        font-weight: 800;
        color: var(--bs-primary);
        line-height: 1;
        margin-bottom: 1rem;
        text-shadow: 0 4px 20px rgba(218, 37, 37, 0.3);
        background: linear-gradient(135deg, var(--bs-primary), #B31E1E);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .error-title {
        font-size: 3rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-family: 'Poppins', sans-serif;
    }
    
    .error-description {
        font-size: 1.3rem;
        color: #6c757d;
        margin-bottom: 3rem;
        line-height: 1.6;
    }
    
    .error-actions {
        margin: 3rem 0;
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-custom {
        padding: 15px 35px;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 1.1rem;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, var(--bs-primary), #B31E1E);
        color: white;
        border: none;
        box-shadow: 0 4px 15px rgba(218, 37, 37, 0.3);
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(218, 37, 37, 0.4);
        color: white;
    }
    
    .btn-outline-custom {
        border: 2px solid var(--bs-primary);
        color: var(--bs-primary);
        background: white;
    }
    
    .btn-outline-custom:hover {
        background: var(--bs-primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(218, 37, 37, 0.3);
    }
    
    .search-box {
        max-width: 500px;
        margin: 2rem auto 3rem;
    }
    
    .search-input {
        border-radius: 50px;
        padding: 15px 25px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        font-size: 1rem;
    }
    
    .search-input:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.2rem rgba(218, 37, 37, 0.25);
    }
    
    .search-box .btn {
        border-radius: 50px;
        padding: 15px 25px;
    }
    
    .helpful-links {
        margin-top: 4rem;
    }
    
    .helpful-links h5 {
        color: #2c3e50;
        margin-bottom: 2rem;
        font-weight: 600;
    }
    
    .link-item {
        display: inline-block;
        margin: 0.75rem;
        padding: 12px 24px;
        background: white;
        border-radius: 50px;
        text-decoration: none;
        color: var(--bs-primary);
        border: 2px solid #f8f9fa;
        transition: all 0.3s ease;
        font-weight: 500;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .link-item:hover {
        background: var(--bs-primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(218, 37, 37, 0.3);
        border-color: var(--bs-primary);
    }
    
    .floating-shapes {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }
    
    .shape {
        position: absolute;
        opacity: 0.08;
        animation: float 8s ease-in-out infinite;
    }
    
    .shape:nth-child(1) {
        top: 15%;
        left: 10%;
        animation-delay: 0s;
        color: var(--bs-primary);
    }
    
    .shape:nth-child(2) {
        top: 50%;
        right: 15%;
        animation-delay: 2s;
        color: #28a745;
    }
    
    .shape:nth-child(3) {
        bottom: 25%;
        left: 15%;
        animation-delay: 4s;
        color: #ffc107;
    }
    
    .shape:nth-child(4) {
        top: 30%;
        right: 40%;
        animation-delay: 1s;
        color: #17a2b8;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0px) rotate(0deg);
        }
        25% {
            transform: translateY(-20px) rotate(5deg);
        }
        50% {
            transform: translateY(-10px) rotate(-5deg);
        }
        75% {
            transform: translateY(-25px) rotate(3deg);
        }
    }
    
    @media (max-width: 768px) {
        .error-number {
            font-size: 8rem;
        }
        
        .error-title {
            font-size: 2.5rem;
        }
        
        .error-description {
            font-size: 1.2rem;
        }
        
        .error-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-custom {
            width: 100%;
            max-width: 300px;
        }
        
        .link-item {
            margin: 0.5rem;
        }
    }
</style>

<!-- Error Content -->
<section class="error-container">
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-graduation-cap fa-4x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-book fa-3x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-lightbulb fa-2x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-users fa-3x"></i>
        </div>
    </div>
    
    <div class="container">
        <div class="error-content" data-aos="fade-up">
            <div class="error-number" data-aos="zoom-in" data-aos-delay="100">404</div>
            <h1 class="error-title" data-aos="fade-up" data-aos-delay="200">Page Not Found</h1>
            <p class="error-description" data-aos="fade-up" data-aos-delay="300">
                Oops! The page you're looking for seems to have wandered off into the digital wilderness. 
                It might have been moved, deleted, or you may have entered the wrong URL.
                Don't worry - let's get you back on your learning journey!
            </p>
            
            <!-- Search Box -->
            <div class="search-box" data-aos="fade-up" data-aos-delay="400">
                <form action="<?= BASE_URL ?>client/public/programs.php" method="get">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control search-input" 
                               name="search" 
                               placeholder="Search for programs, events, or pages..." 
                               aria-label="Search">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Action Buttons -->
            <div class="error-actions" data-aos="fade-up" data-aos-delay="500">
                <a href="<?= BASE_URL ?>" class="btn-custom btn-primary-custom">
                    <i class="fas fa-home me-2"></i>Go Home
                </a>
                <a href="javascript:history.back()" class="btn-custom btn-outline-custom">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
            </div>
            
            <!-- Helpful Links -->
            <div class="helpful-links" data-aos="fade-up" data-aos-delay="600">
                <h5>Explore these popular sections:</h5>
                <div class="d-flex flex-wrap justify-content-center">
                    <a href="<?= BASE_URL ?>client/public/about.php" class="link-item">
                        <i class="fas fa-info-circle me-2"></i>About Us
                    </a>
                    <a href="<?= BASE_URL ?>client/public/programs.php" class="link-item">
                        <i class="fas fa-graduation-cap me-2"></i>Our Programs
                    </a>
                    <a href="<?= BASE_URL ?>client/public/events.php" class="link-item">
                        <i class="fas fa-calendar me-2"></i>Events
                    </a>
                    <a href="<?= BASE_URL ?>client/public/contact.php" class="link-item">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                    <a href="<?= BASE_URL ?>client/public/training-fields.php" class="link-item">
                        <i class="fas fa-tools me-2"></i>Training Fields
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Focus on search input after page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }, 1500);
        
        // Log 404 error for analytics
        console.log('404 Error:', {
            url: window.location.href,
            referrer: document.referrer,
            timestamp: new Date().toISOString()
        });
    });
</script>

<?php
// Include the footer
include __DIR__ . '/client/includes/footer.php';
?>
