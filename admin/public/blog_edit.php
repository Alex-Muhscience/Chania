<?php
require_once '../includes/header.php';
require_once '../classes/Blog.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);
if (!$user->hasPermission($_SESSION['user_id'], 'blog') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to manage blog posts.');
}

$blog = new Blog($db);
$isEdit = false;
$blogData = [
    'title' => '',
    'slug' => '',
    'body' => '',
    'category' => 'General',
    'excerpt' => '',
    'image' => '',
    'is_featured' => 0,
    'status' => 'draft',
    'published_at' => '',
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $blogData = $blog->getById($_GET['id']);
    if (!$blogData) die('Blog post not found.');
}

$categories = $blog->getCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blogData = [
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'body' => $_POST['body'] ?? '',
        'category' => trim($_POST['category'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'status' => $_POST['status'] ?? 'draft',
        'published_at' => $_POST['status'] === 'published' ? (empty($_POST['published_at']) ? date('Y-m-d H:i:s') : $_POST['published_at']) : NULL,
        'image' => $blogData['image'] ?? '', // Default to old value if not changed
        'author_id' => $_SESSION['user_id'],
    ];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = '../../public/uploads/blog/';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('blog_', true) . '.' . $ext;
        $destPath = $uploadsDir . $fileName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
            $blogData['image'] = 'uploads/blog/' . $fileName;
        } else {
            $error = 'Failed to upload image.';
        }
    }

    // Generate slug
    if (empty($blogData['slug'])) {
        $blogData['slug'] = $blog->generateSlug($blogData['title'], $isEdit ? $_GET['id'] : null);
    }

    // Validate
    if (empty($blogData['title']) || empty($blogData['body'])) {
        $error = 'Title and body are required.';
    } elseif (empty($blogData['slug'])) {
        $error = 'Slug could not be generated.';
    }

    if (!$error) {
        if ($isEdit) {
            $ok = $blog->update($_GET['id'], $blogData);
            $_SESSION['success'] = $ok ? 'Blog post updated.' : 'Failed to update post.';
        } else {
            $ok = $blog->create($blogData);
            $_SESSION['success'] = $ok ? 'Blog post created.' : 'Failed to create post.';
        }
        header('Location: blog.php');
        exit;
    }
}

?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $isEdit ? 'Edit' : 'Add' ?> Blog Post</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
        <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?> Post</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-blog me-1"></i>
            <?= $isEdit ? 'Edit' : 'Add' ?> Blog Post
        </div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($blogData['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="slug" class="form-label">Slug</label>
                    <input type="text" class="form-control" id="slug" name="slug" value="<?= htmlspecialchars($blogData['slug']) ?>" placeholder="auto-generated if blank">
                </div>
                <div class="mb-3">
                    <label class="form-label">Body</label>
                    <textarea class="form-control" name="body" id="body" rows="10" required><?= htmlspecialchars($blogData['body']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Excerpt</label>
                    <textarea class="form-control" name="excerpt" rows="2"><?= htmlspecialchars($blogData['excerpt']) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($blogData['category']) ?>" required list="category-list">
                        <datalist id="category-list">
                        <?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat) ?>"><?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft" <?= $blogData['status']==='draft'? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $blogData['status']==='published'? 'selected' : '' ?>>Published</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Featured</label><br>
                        <input type="checkbox" name="is_featured" value="1" <?= $blogData['is_featured'] ? 'checked' : '' ?>>
                        <label>Show as featured</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <?php if ($blogData['image']): ?>
                        <div class="mb-2"><img src="/<?= htmlspecialchars($blogData['image']) ?>" alt="Blog image" style="max-height: 80px;"></div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control">
                </div>
                <?php if($isEdit && $blogData['status']==='published'): ?>
                <div class="mb-3">
                    <label for="published_at" class="form-label">Publish Date/Time</label>
                    <input type="datetime-local" name="published_at" id="published_at" class="form-control" value="<?= $blogData['published_at'] ? date('Y-m-d\TH:i', strtotime($blogData['published_at'])) : '' ?>">
                    <small class="text-muted">Leave blank for current date/time</small>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $isEdit ? 'Update' : 'Create' ?> Post</button>
                <a href="blog.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: '#body',
  menubar: false,
  plugins: 'link image lists preview code',
  toolbar: 'undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code preview',
});
</script>
<?php require_once '../includes/footer.php'; ?>

