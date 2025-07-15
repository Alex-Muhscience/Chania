<?php
$pageTitle = "Program Details - Skills for Africa";
$activePage = "programs";

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/programs.php");
    exit;
}

$programId = (int)$_GET['id'];
$program = getProgramById($db, $programId);

if (!$program) {
    header("Location: " . BASE_URL . "/programs.php");
    exit;
}

$pageTitle = $program['title'] . " - Skills for Africa";
$pageDescription = $program['short_description'];

// Get related programs
$relatedPrograms = getRelatedPrograms($db, $programId, $program['category']);
?>

<section class="py-5">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/programs.php">Programs</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($program['title']); ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-8">
                <div class="mb-4">
                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($program['category']); ?></span>
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="d-flex align-items-center text-muted small">
                            <i class="fas fa-clock me-2"></i>
                            <span><?php echo htmlspecialchars($program['duration']); ?></span>
                        </div>
                        <?php if ($program['is_featured']): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-star me-1"></i> Featured
                        </span>
                        <?php endif; ?>
                    </div>

                    <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path']); ?>"
                         alt="<?php echo htmlspecialchars($program['title']); ?>"
                         class="img-fluid rounded-3 mb-4 w-100" style="max-height: 400px; object-fit: cover;">

                    <div class="mb-5">
                        <h3 class="h4 fw-bold mb-3">About This Program</h3>
                        <div class="program-content">
                            <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($program['requirements'])): ?>
                    <div class="mb-5">
                        <h3 class="h4 fw-bold mb-3">Requirements</h3>
                        <div class="program-content">
                            <?php echo nl2br(htmlspecialchars($program['requirements'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($program['benefits'])): ?>
                    <div class="mb-5">
                        <h3 class="h4 fw-bold mb-3">What You'll Learn</h3>
                        <div class="program-content">
                            <?php echo nl2br(htmlspecialchars($program['benefits'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="text-center text-lg-start">
                    <a href="<?php echo BASE_URL; ?>/apply.php?program=<?php echo $programId; ?>" class="btn btn-primary btn-lg px-5">Apply Now</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h3 class="h5 fw-bold mb-4">Program Details</h3>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">Category</span>
                                <span><?php echo htmlspecialchars($program['category']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">Duration</span>
                                <span><?php echo htmlspecialchars($program['duration']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">Format</span>
                                <span>Online & In-Person</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-muted">Certification</span>
                                <span>Yes</span>
                            </li>
                        </ul>

                        <div class="d-grid">
                            <a href="<?php echo BASE_URL; ?>/apply.php?program=<?php echo $programId; ?>" class="btn btn-primary">Apply Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($relatedPrograms)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Related Programs</h2>
            <p class="lead text-muted">You might also be interested in these programs</p>
        </div>

        <div class="row g-4">
            <?php foreach ($relatedPrograms as $program): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-top overflow-hidden" style="height: 180px;">
                        <img src="<?php echo BASE_URL . '/uploads/' . htmlspecialchars($program['image_path']); ?>"
                             alt="<?php echo htmlspecialchars($program['title']); ?>"
                             class="img-fluid w-100 h-100 object-fit-cover">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($program['title']); ?></h5>
                        <p class="card-text text-muted small"><?php echo htmlspecialchars($program['short_description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($program['category']); ?></span>
                            <span class="text-muted small"><?php echo htmlspecialchars($program['duration']); ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="d-grid">
                            <a href="<?php echo BASE_URL; ?>/program.php?id=<?php echo $program['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>