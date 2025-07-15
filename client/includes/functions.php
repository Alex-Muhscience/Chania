<?php
/**
 * Get all distinct program categories from the database
 * @param PDO $db Database connection
 * @return array Array of category names
 */
function getProgramCategories($db) {
    $stmt = $db->query("SELECT DISTINCT category FROM programs ORDER BY category");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get partners from the database
 * @param PDO $db Database connection
 * @param bool $featured Whether to get only featured partners
 * @return array Array of partner data
 */
function getPartners($db, $featured = true) {
    $sql = "SELECT * FROM partners";
    $params = [];
    
    if ($featured) {
        $sql .= " WHERE is_featured = ?";
        $params[] = 1;
    }
    
    $sql .= " ORDER BY display_order";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Redirect to another page
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in (basic implementation)
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format (default: F j, Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y') {
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}
?>