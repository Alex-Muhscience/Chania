<?php
require_once 'shared/Core/Database.php';
require_once 'admin/classes/Blog.php';

if (!isset($_GET['slug'])) {
    header('Location: blog.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$blog = new Blog($db);

$post = $blog->getBySlug($_GET['slug']);

if (!$post || $post['status'] !== 'published') {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

$pageTitle = $post['title'];
$pageDescription = $post['excerpt'];
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <article class="blog-post">
                <!-- Post Header -->
                <header class="mb-4">
                    <h1 class="mb-3"><?= htmlspecialchars($post['title']) ?></h1>
                    <div class="text-muted mb-3">
                        <small>
                            By <strong><?= htmlspecialchars($post['author_username']) ?></strong> 
                            on <?= date('F j, Y', strtotime($post['published_at'])) ?>
                            <?php if ($post['category']): ?>
                                in <span class="badge bg-primary"><?= htmlspecialchars($post['category']) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                    <?php if ($post['is_featured']): ?>
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark">Featured Post</span>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Featured Image -->
                <?php if ($post['image']): ?>
                    <div class="mb-4">
                        <img src="<?= htmlspecialchars($post['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($post['title']) ?>">
                    </div>
                <?php endif; ?>

                <!-- Post Content -->
                <div class="post-content">
                    <?= $post['body'] ?>
                </div>

                <!-- Post Footer -->
                <footer class="mt-5 pt-4 border-top">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted mb-0">Category: <strong><?= htmlspecialchars($post['category']) ?></strong></p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="text-muted mb-0">Published: <?= date('F j, Y \a\t g:i A', strtotime($post['published_at'])) ?></p>
                        </div>
                    </div>
                </footer>
            </article>

            <!-- Navigation -->
            <div class="mt-5">
                <a href="blog.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Blog
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.post-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.25rem;
    margin: 1rem 0;
}

.post-content h1, .post-content h2, .post-content h3, 
.post-content h4, .post-content h5, .post-content h6 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.post-content p {
    margin-bottom: 1rem;
    line-height: 1.6;
}

.post-content blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    background-color: #f8f9fa;
    padding: 1rem 1rem 1rem 2rem;
}

.post-content ul, .post-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.post-content li {
    margin-bottom: 0.5rem;
}

.post-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
}

.post-content pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    overflow-x: auto;
}
</style>

<?php include 'includes/footer.php'; ?>
