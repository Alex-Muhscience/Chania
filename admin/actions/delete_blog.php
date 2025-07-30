<?php
require_once '../../shared/Core/Database.php';
require_once '../classes/Blog.php';
require_once '../../shared/Core/User.php';

// Start session
session_start();

$database = new Database();
$db = $database->connect();
$user = new User($db);

if (!$user->hasPermission($_SESSION['user_id'], 'blog') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to delete blog posts.');
}

if (!isset($_GET['id'])) {
    header('Location: ../public/blog.php');
    exit;
}

$blog = new Blog($db);

// Get the blog post to check if it has an image to delete
$post = $blog->getById($_GET['id']);

if ($blog->delete($_GET['id'])) {
    // Delete associated image file if it exists
    if ($post && $post['image']) {
        $imagePath = '../../public/' . $post['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    $_SESSION['success'] = 'Blog post deleted successfully.';
} else {
    $_SESSION['error'] = 'Failed to delete blog post.';
}

header('Location: ../public/blog.php');
exit;
?>
