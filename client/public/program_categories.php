<?php
$pageTitle = "Program Categories - Skills for Africa";
$pageDescription = "Explore our diverse range of program categories designed to equip African youth with essential digital skills";
$activePage = "programs";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

try {
    // Get all program categories with program counts
    $sql = "SELECT pc.*, 
                   COUNT(DISTINCT p.id) as program_count,
                   AVG(CASE WHEN p.difficulty_level = 'beginner' THEN 1 
                           WHEN p.difficulty_level = 'intermediate' THEN 2 
                           WHEN p.difficulty_level = 'advanced' THEN 3 
                           ELSE 2 END) as avg_difficulty
            FROM program_categories pc 
            LEFT JOIN programs p ON (
                (p.category = pc.name) OR 
                (FIND_IN_SET(pc.name, p.category) > 0)
            ) AND p.is_active = 1 AND p.deleted_at IS NULL
            WHERE pc.is_active = 1 AND pc.deleted_at IS NULL
            GROUP BY pc.category_id, pc.sort_order, pc.name
            ORDER BY pc.sort_order ASC, pc.name ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Display the error temporarily for debugging
    echo "<div class='alert alert-danger'>Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    error_log("Program categories page error: " . $e->getMessage());
    $categories = [];
}
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="hero-background"></div>
    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-8 mx-auto text-center">
                <div class="hero-content animate-fade-in">
                    <div class="hero-badge mb-4">
                        <span class="badge-icon">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <span class="badge-text"><?= count($categories) ?> Specialized Categories</span>
                    </div>
                    <h1 class="hero-title mb-4">
                        Program <span class="text-accent">Categories</span>
                    </h1>
                    <p class="hero-subtitle mb-5">
                        Discover comprehensive training pathways designed to equip African youth with cutting-edge digital skills for tomorrow's economy.
                    </p>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($categories) ?></div>
                            <div class="stat-label">Categories</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">
                                <?php
                                $totalPrograms = array_sum(array_column($categories, 'program_count'));
                                echo $totalPrograms;
                                ?>
                            </div>
                            <div class="stat-label">Programs</div>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <div class="stat-number">100%</div>
                            <div class="stat-label">Digital Focus</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (empty($categories)): ?>
<!-- Empty State -->
<section class="py-5">
    <div class="container">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-folder-open"></i>
            </div>
            <h3 class="empty-state-title">No Categories Available</h3>
            <p class="empty-state-text">Program categories are currently being set up. Check back soon!</p>
            <a href="<?php echo BASE_URL; ?>/client/public/programs.php" class="btn btn-primary-custom">
                <i class="fas fa-eye me-2"></i>View All Programs
            </a>
        </div>
    </div>
</section>
<?php else: ?>

<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <!-- Section Header -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <div class="section-badge mb-3">
                    <i class="fas fa-graduation-cap me-2"></i>
                    <span>Skill Development Paths</span>
                </div>
                <h2 class="section-title">Choose Your Learning Journey</h2>
                <p class="section-subtitle">
                    Each category represents a comprehensive curriculum designed by industry experts to prepare you for real-world challenges.
                </p>
            </div>
        </div>

        <!-- Categories Grid -->
        <div class="categories-grid">
            <?php foreach ($categories as $index => $category): ?>
            <div class="category-card-wrapper" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="category-card">
                    <div class="card-shine"></div>

                    <!-- Card Header -->
                    <div class="card-header-section">
                        <div class="category-icon-container">
                            <div class="icon-background" style="background: linear-gradient(135deg, <?= htmlspecialchars($category['color'] ?? '#D8973C') ?>, <?= htmlspecialchars($category['color'] ?? '#D8973C') ?>cc);">
                                <?php if ($category['icon']): ?>
                                    <i class="<?= htmlspecialchars($category['icon']) ?>"></i>
                                <?php else: ?>
                                    <i class="fas fa-graduation-cap"></i>
                                <?php endif; ?>
                            </div>
                            <div class="icon-glow" style="background: <?= htmlspecialchars($category['color'] ?? '#D8973C') ?>33;"></div>
                        </div>

                        <div class="category-header">
                            <h3 class="category-title"><?= htmlspecialchars($category['name']) ?></h3>
                            <div class="category-meta">
                                <span class="program-count">
                                    <i class="fas fa-book-open me-1"></i>
                                    <?= $category['program_count'] ?> Program<?= $category['program_count'] != 1 ? 's' : '' ?>
                                </span>
                                <span class="difficulty-badge">
                                    <?php
                                    $avgDiff = floatval($category['avg_difficulty']);
                                    if ($avgDiff <= 1.5) {
                                        $diffLevel = 'Beginner';
                                        $diffClass = 'beginner';
                                    } elseif ($avgDiff <= 2.5) {
                                        $diffLevel = 'Intermediate';
                                        $diffClass = 'intermediate';
                                    } else {
                                        $diffLevel = 'Advanced';
                                        $diffClass = 'advanced';
                                    }
                                    ?>
                                    <span class="difficulty-dot <?= $diffClass ?>"></span>
                                    <?= $diffLevel ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Content -->
                    <div class="card-content">
                        <p class="category-description">
                            <?= nl2br(htmlspecialchars($category['description'] ?? 'Comprehensive training in ' . $category['name'] . ' skills designed to prepare you for the digital economy.')) ?>
                        </p>

                        <!-- Key Skills -->
                        <?php if (!empty($category['key_skills'])): ?>
                        <div class="skills-section">
                            <h6 class="skills-title">Key Skills You'll Learn</h6>
                            <div class="skills-tags">
                                <?php
                                $skills = explode(',', $category['key_skills']);
                                foreach (array_slice($skills, 0, 4) as $skill):
                                ?>
                                <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                                <?php endforeach; ?>
                                <?php if (count($skills) > 4): ?>
                                <span class="skill-tag more-skills">+<?= count($skills) - 4 ?> more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Card Actions -->
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/client/public/programs.php?category=<?= $category['category_id'] ?>"
                           class="btn btn-primary-custom">
                            <span class="btn-text">
                                <i class="fas fa-eye me-2"></i>Explore Programs
                            </span>
                            <span class="btn-icon">
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                        <?php if ($category['program_count'] > 0): ?>
                        <a href="<?= BASE_URL ?>/client/public/programs.php?category=<?= $category['category_id'] ?>&difficulty=beginner"
                           class="btn btn-secondary-custom">
                            <i class="fas fa-play me-2"></i>Start Learning
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-card">
            <div class="cta-background"></div>
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="cta-content">
                        <div class="cta-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3 class="cta-title">Ready to Transform Your Future?</h3>
                        <p class="cta-text">
                            Join thousands of African youth who are already building the skills that matter.
                            Choose your path and start your journey toward digital excellence today.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="cta-actions">
                        <a href="<?= BASE_URL ?>/client/public/programs.php" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-search me-2"></i>Browse All Programs
                        </a>
                        <a href="<?= BASE_URL ?>/client/public/apply.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Premium Styles -->
<style>
:root {
    --primary-color: #D8C99B;
    --secondary-color: #273E47;
    --accent-color: #D8973C;
    --text-dark: #1a1a1a;
    --text-light: #6b7280;
    --white: #ffffff;
    --light-bg: #f8fafc;
    --border-color: #e5e7eb;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    line-height: 1.6;
    color: var(--text-dark);
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--secondary-color) 0%, #1e2a30 100%);
    color: var(--white);
    padding: 120px 0 80px;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23D8C99B" opacity="0.1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.min-vh-75 {
    min-height: 75vh;
}

.animate-fade-in {
    animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(216, 201, 155, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(216, 201, 155, 0.2);
    border-radius: 50px;
    padding: 12px 24px;
    margin-bottom: 2rem;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.badge-icon {
    color: var(--primary-color);
    margin-right: 8px;
    font-size: 1.1rem;
}

.badge-text {
    color: var(--primary-color);
    font-weight: 600;
    font-size: 0.95rem;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 2rem;
}

.text-accent {
    color: var(--accent-color);
    position: relative;
}

.text-accent::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent-color), transparent);
    border-radius: 2px;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.8);
    max-width: 600px;
    margin: 0 auto 3rem;
    line-height: 1.6;
}

.hero-stats {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.stat-divider {
    width: 1px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
}

/* Categories Section */
.categories-section {
    background: var(--light-bg);
    padding: 100px 0;
}

.section-badge {
    display: inline-flex;
    align-items: center;
    background: var(--white);
    color: var(--secondary-color);
    padding: 8px 20px;
    border-radius: 50px;
    box-shadow: var(--shadow-sm);
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.section-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 2rem;
    margin-top: 4rem;
}

.category-card-wrapper {
    perspective: 1000px;
}

.category-card {
    background: var(--white);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: var(--shadow-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid var(--border-color);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.category-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: var(--shadow-xl);
    border-color: var(--primary-color);
}

.card-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(216, 201, 155, 0.1), transparent);
    transition: left 0.6s;
}

.category-card:hover .card-shine {
    left: 100%;
}

.card-header-section {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.category-icon-container {
    position: relative;
    flex-shrink: 0;
}

.icon-background {
    width: 70px;
    height: 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.8rem;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.icon-glow {
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.category-card:hover .icon-glow {
    opacity: 1;
}

.category-card:hover .icon-background {
    transform: scale(1.1) rotate(5deg);
}

.category-header {
    flex: 1;
}

.category-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.category-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.program-count {
    font-size: 0.9rem;
    color: var(--text-light);
    font-weight: 500;
}

.difficulty-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-light);
}

.difficulty-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.difficulty-dot.beginner { background: #10b981; }
.difficulty-dot.intermediate { background: #f59e0b; }
.difficulty-dot.advanced { background: #ef4444; }

.card-content {
    flex: 1;
    margin-bottom: 2rem;
}

.category-description {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 1.5rem;
}

.skills-section {
    margin-top: 1.5rem;
}

.skills-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.skills-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.skill-tag {
    background: var(--light-bg);
    color: var(--text-dark);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
}

.skill-tag:hover {
    background: var(--primary-color);
    color: var(--secondary-color);
    border-color: var(--primary-color);
}

.skill-tag.more-skills {
    background: var(--accent-color);
    color: var(--white);
    border-color: var(--accent-color);
}

.card-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Buttons */
.btn {
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    min-height: 48px;
}

.btn-primary-custom {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
    color: var(--secondary-color);
    box-shadow: var(--shadow-sm);
    flex: 1;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
    color: var(--secondary-color);
}

.btn-secondary-custom {
    background: var(--white);
    color: var(--secondary-color);
    border: 2px solid var(--secondary-color);
}

.btn-secondary-custom:hover {
    background: var(--secondary-color);
    color: var(--white);
    transform: translateY(-2px);
}

.btn-text {
    transition: transform 0.3s ease;
}

.btn-icon {
    margin-left: 0.5rem;
    transition: transform 0.3s ease;
}

.btn-primary-custom:hover .btn-icon {
    transform: translateX(3px);
}

/* CTA Section */
.cta-section {
    background: linear-gradient(135deg, var(--secondary-color) 0%, #1e2a30 100%);
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.cta-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 3rem;
    position: relative;
    overflow: hidden;
}

.cta-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 30%, rgba(216, 201, 155, 0.1) 0%, transparent 50%);
}

.cta-content {
    position: relative;
    z-index: 2;
}

.cta-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--secondary-color);
    font-size: 2rem;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.cta-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 1rem;
}

.cta-text {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
    margin-bottom: 0;
}

.cta-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    position: relative;
    z-index: 2;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    min-height: 56px;
}

.btn-outline-light {
    background: transparent;
    color: var(--white);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-outline-light:hover {
    background: var(--white);
    color: var(--secondary-color);
    border-color: var(--white);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    max-width: 500px;
    margin: 0 auto;
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--text-light);
    margin-bottom: 2rem;
}

.empty-state-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 1rem;
}

.empty-state-text {
    color: var(--text-light);
    margin-bottom: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        padding: 80px 0 60px;
    }

    .hero-stats {
        gap: 1.5rem;
    }

    .stat-divider {
        display: none;
    }

    .categories-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        margin-top: 3rem;
    }

    .category-card {
        padding: 1.5rem;
    }

    .card-header-section {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
    }

    .category-meta {
        justify-content: center;
    }

    .card-actions {
        flex-direction: column;
    }

    .cta-card {
        padding: 2rem;
    }

    .cta-title {
        font-size: 2rem;
    }

    .cta-actions {
        margin-top: 2rem;
    }
}

@media (max-width: 480px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }

    .hero-title {
        font-size: 2.5rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
    }

    .section-title {
        font-size: 2rem;
    }
}

/* Animation delays for staggered effect */
[data-aos="fade-up"]:nth-child(1) { animation-delay: 0ms; }
[data-aos="fade-up"]:nth-child(2) { animation-delay: 100ms; }
[data-aos="fade-up"]:nth-child(3) { animation-delay: 200ms; }
[data-aos="fade-up"]:nth-child(4) { animation-delay: 300ms; }
[data-aos="fade-up"]:nth-child(5) { animation-delay: 400ms; }
[data-aos="fade-up"]:nth-child(6) { animation-delay: 500ms; }

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Focus states for accessibility */
.btn:focus,
.category-card:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

/* Loading states */
.category-card.loading {
    opacity: 0.7;
    pointer-events: none;
}

.category-card.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Print styles */
@media print {
    .hero-section,
    .cta-section {
        background: white !important;
        color: black !important;
    }

    .category-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ccc;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .category-card {
        border: 2px solid var(--secondary-color);
    }

    .btn-primary-custom {
        border: 2px solid var(--secondary-color);
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .category-card:hover {
        transform: none;
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    :root {
        --light-bg: #111827;
        --white: #1f2937;
        --text-dark: #f9fafb;
        --text-light: #d1d5db;
        --border-color: #374151;
    }
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>