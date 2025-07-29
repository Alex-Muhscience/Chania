<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Only handle AJAX requests
if (!isset($_GET['ajax']) || $_GET['ajax'] !== '1') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../src/Services/ProgramService.php';

try {
    // Get search and filter parameters
    $categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $difficultyFilter = $_GET['difficulty'] ?? '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 9; // Show 9 programs per page (3x3 grid)
    $offset = ($page - 1) * $limit;

    // Build dynamic SQL with filters
    $sql = "SELECT p.*, 
                   COALESCE(pc.name, p.category) as category_name, 
                   pc.color as category_color, 
                   pc.icon as category_icon,
                   pc.category_id as category_id_actual
            FROM programs p 
            LEFT JOIN program_categories pc ON (
                (p.category = pc.name) OR 
                (FIND_IN_SET(pc.name, p.category) > 0)
            )
            WHERE p.is_active = 1 AND p.deleted_at IS NULL";
    
    $countSql = "SELECT COUNT(DISTINCT p.id) 
                 FROM programs p 
                 LEFT JOIN program_categories pc ON (
                     (p.category = pc.name) OR 
                     (FIND_IN_SET(pc.name, p.category) > 0)
                 )
                 WHERE p.is_active = 1 AND p.deleted_at IS NULL";
    
    $params = [];
    $countParams = [];
    
    // Add category filter
    if ($categoryFilter) {
        $sql .= " AND pc.category_id = ?";
        $countSql .= " AND pc.category_id = ?";
        $params[] = $categoryFilter;
        $countParams[] = $categoryFilter;
    }
    
    // Add search filter
    if ($searchQuery) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $countSql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        $countParams = array_merge($countParams, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    // Add difficulty filter
    if ($difficultyFilter) {
        $sql .= " AND p.difficulty_level = ?";
        $countSql .= " AND p.difficulty_level = ?";
        $params[] = $difficultyFilter;
        $countParams[] = $difficultyFilter;
    }
    
    // Add ordering and pagination  
    $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC LIMIT $limit OFFSET $offset";
    
    // Get programs
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $programs = $stmt->fetchAll();
    
    // Get total count
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($countParams);
    $totalPrograms = $countStmt->fetchColumn();
    $totalPages = ceil($totalPrograms / $limit);
    
    // Prepare response
    $response = [
        'success' => true,
        'programs' => $programs,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => (int)$totalPrograms,
            'per_page' => $limit,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'showing_start' => ($offset + 1),
            'showing_end' => min($offset + $limit, $totalPrograms)
        ],
        'filters' => [
            'category' => $categoryFilter,
            'search' => $searchQuery,
            'difficulty' => $difficultyFilter
        ]
    ];
    
    // Clean up the programs data for JSON output
    foreach ($response['programs'] as &$program) {
        // Ensure all fields are properly formatted
        $program['is_featured'] = (bool)$program['is_featured'];
        $program['is_active'] = (bool)$program['is_active'];
        $program['is_online'] = (bool)$program['is_online'];
        $program['certification_available'] = (bool)$program['certification_available'];
        
        // Ensure image path is not null
        if (empty($program['image_path'])) {
            $program['image_path'] = 'default-program.jpg';
        }
        
        // Ensure descriptions are not null
        $program['short_description'] = $program['short_description'] ?: '';
        $program['description'] = $program['description'] ?: '';
        
        // Format duration
        $program['duration'] = $program['duration'] ?: 'Duration not specified';
        
        // Ensure category info is present
        $program['category_name'] = $program['category_name'] ?: $program['category'] ?: 'Uncategorized';
        $program['category_color'] = $program['category_color'] ?: '#6c757d';
        $program['category_icon'] = $program['category_icon'] ?: 'fas fa-graduation-cap';
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("Programs API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while loading programs. Please try again.',
        'programs' => [],
        'pagination' => [
            'current_page' => 1,
            'total_pages' => 0,
            'total_items' => 0,
            'per_page' => $limit ?? 9,
            'has_previous' => false,
            'has_next' => false
        ]
    ]);
}
?>
