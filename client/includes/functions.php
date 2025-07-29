<?php

use Random\RandomException;

/**
 * Get all distinct program categories from the database
 * @param PDO $db Database connection
 * @return array Array of category data
 */
function getProgramCategories($db) {
    try {
        // Simple, straightforward query that works with the current table structure
        $stmt = $db->query("SELECT id, name, description FROM program_categories WHERE status = 'active' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getProgramCategories error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get partners from the database
 * @param PDO $db Database connection
 * @param bool $featured Whether to get only featured partners
 * @return array Array of partner data
 */
function getPartners($db, $featured = false) {
    try {
        // First, check if the is_featured column exists
        $columnCheck = $db->query("SHOW COLUMNS FROM partners LIKE 'is_featured'");
        $hasFeaturedColumn = $columnCheck->fetch() !== false;
        
        $sql = "SELECT * FROM partners";
        $params = [];
        
        // Only filter by featured if the column exists and featured is requested
        if ($featured && $hasFeaturedColumn) {
            $sql .= " WHERE is_featured = ?";
            $params[] = 1;
        }
        
        // Order by display_order if it exists, otherwise by id
        $orderColumnCheck = $db->query("SHOW COLUMNS FROM partners LIKE 'display_order'");
        $hasOrderColumn = $orderColumnCheck->fetch() !== false;
        
        if ($hasOrderColumn) {
            $sql .= " ORDER BY display_order";
        } else {
            $sql .= " ORDER BY id";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Partners query error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get team members from the database
 * @param PDO $db Database connection
 * @return array Array of team member data
 */
function getTeamMembers($db) {
    $stmt = $db->prepare("SELECT * FROM team_members ORDER BY id");
    $stmt->execute();
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
 * Check if a user is logged in (basic implementation)
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Generate CSRF token
 * @return string CSRF token
 * @throws RandomException
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
function verifyCsrfToken($token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Output format (default: F j, Y)
 * @return string Formatted date
 * @throws DateMalformedStringException
 */
function formatDate(string $date, string $format = 'F j, Y'): string
{
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}
/**
 * Get all events from the database
 * @param PDO $db Database connection
 * @param string|null $slug Optional event slug to get specific event
 * @return array|false Array of event data or false if not found
 */
function getEvents($db, $slug = null) {
    if ($slug) {
        // Get specific event by slug
        $stmt = $db->prepare("SELECT * FROM events WHERE slug = ? AND is_active = 1 AND deleted_at IS NULL");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } else {
        // Get all active events
        $stmt = $db->query("SELECT * FROM events WHERE is_active = 1 AND deleted_at IS NULL ORDER BY event_date ASC");
        return $stmt->fetchAll();
    }
}

/**
 * Register a user for an event
 * @param PDO $db Database connection
 * @param int $event_id Event ID
 * @param string $name User's name
 * @param string $email User's email
 * @param string|null $phone User's phone (optional)
 * @return bool True if registration successful, false otherwise
 */
function registerForEvent($db, $event_id, $name, $email, $phone = null) {
    try {
        // Check if user is already registered
        $stmt = $db->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ?");
        $stmt->execute([$event_id, $email]);
        if ($stmt->fetch()) {
            return false; // Already registered
        }
        
        // Register the user
        $stmt = $db->prepare("
            INSERT INTO event_registrations (event_id, name, email, phone, registration_date) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([$event_id, $name, $email, $phone]);
    } catch (Exception $e) {
        error_log("Event registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if an event has available spots
 * @param PDO $db Database connection
 * @param int $event_id Event ID
 * @return bool True if spots available, false if full
 */
function hasAvailableSpots($db, $event_id) {
    $stmt = $db->prepare("
        SELECT e.max_participants, COUNT(r.id) as registered_count 
        FROM events e 
        LEFT JOIN event_registrations r ON e.id = r.event_id 
        WHERE e.id = ? 
        GROUP BY e.id, e.max_participants
    ");
    $stmt->execute([$event_id]);
    $result = $stmt->fetch();
    
    if (!$result) return false;
    
    return $result['registered_count'] < $result['max_participants'];
}

/**
 * Get registration count for an event
 * @param PDO $db Database connection
 * @param int $event_id Event ID
 * @return int Number of registrations
 */
function getRegistrationCount($db, $event_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Format time for display
 * @param string $time Time string
 * @param string $format Output format (default: g:i A)
 * @return string Formatted time
 * @throws DateMalformedStringException
 */
function formatTime(string $time, string $format = 'g:i A'): string
{
    $dateTime = new DateTime($time);
    return $dateTime->format($format);
}

/**
 * Check if an event is in the past
 * @param string $event_date Event date string
 * @param string|null $event_time Optional event time
 * @return bool True if event is past, false otherwise
 */
function isEventPast($event_date, $event_time = null): bool
{
    $eventDateTime = $event_date;
    if ($event_time) {
        $eventDateTime .= ' ' . $event_time;
    }
    
    try {
        $eventTimestamp = new DateTime($eventDateTime);
        $now = new DateTime();
        return $eventTimestamp < $now;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Sanitize input data
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid email, false otherwise
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
