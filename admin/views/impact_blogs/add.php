<div class="container-fluid px-4">
    <h1 class="mt-4"><?= htmlspecialchars($pageTitle) ?></h1>
    <ol class="breadcrumb mb-4">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php if (isset($breadcrumb['url'])): ?>
                <li class="breadcrumb-item"><a href="<?= htmlspecialchars($breadcrumb['url']) ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($breadcrumb['title']) ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>Add New Impact Story
        </div>
        <div class="card-body">
            <?php 
                $formAction = "impact_blog_add.php";
                $blog = null; 
            ?>
            <?php include __DIR__ . '/_form.php'; ?>
        </div>
    </div>
</div>

