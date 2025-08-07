<?php
require_once '../includes/config.php';

// Set JSON response headers
header('Content-Type: application/json');

// Get blog ID from GET parameter
$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($blog_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid blog ID'
    ]);
    exit;
}

try {
    // Fetch blog details
    $query = "
        SELECT id, title, slug, excerpt, content, category, featured_image, video_url, 
               video_embed_code, stats_data, tags, author_name, published_at, view_count
        FROM impact_blogs 
        WHERE id = :id AND is_active = 1 AND deleted_at IS NULL
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$blog) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Blog not found'
        ]);
        exit;
    }
    
    // Decode JSON fields
    if ($blog['stats_data']) {
        $blog['stats_data'] = json_decode($blog['stats_data'], true);
    }
    if ($blog['tags']) {
        $blog['tags'] = json_decode($blog['tags'], true);
    }
    
    // Update view count
    $updateQuery = "UPDATE impact_blogs SET view_count = view_count + 1 WHERE id = :id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':id', $blog_id, PDO::PARAM_INT);
    $updateStmt->execute();
    
    // Return blog data
    echo json_encode([
        'success' => true,
        'blog' => $blog
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>
