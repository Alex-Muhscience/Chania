<?php
require_once 'shared/Core/Database.php';
require_once 'admin/classes/Blog.php';

$database = new Database();
$db = $database->connect();
$blog = new Blog($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$posts = $blog->getPublishedPosts($limit, $offset);
$totalPosts = $blog->getTotalCount(null, 'published', null);
$totalPages = ceil($totalPosts / $limit);

$pageTitle = "Blog";
include 'includes/header.php';
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Our Blog</h1>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info text-center">
            No blog posts have been published yet. Please check back later!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($posts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if ($post['image']): ?>
                            <a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>">
                                <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($post['title']) ?>" style="height: 200px; object-fit: cover;">
                            </a>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><a href="post.php?slug=<?= htmlspecialchars($post['slug']) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($post['title']) ?></a></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($post['excerpt']) ?></p>
                            <div class="mt-auto">
                                <small class="text-muted">By <?= htmlspecialchars($post['author_username']) ?> on <?= date('F j, Y', strtotime($post['published_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
