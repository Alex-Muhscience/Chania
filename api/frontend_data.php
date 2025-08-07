<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../shared/Core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';

function sendResponse($data, $success = true, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

switch ($action) {
    case 'testimonials':
        getTestimonials($db);
        break;
    case 'team_members':
        getTeamMembers($db);
        break;
    case 'programs':
        getPrograms($db);
        break;
    case 'featured_programs':
        getFeaturedPrograms($db);
        break;
    case 'home_data':
        getHomeData($db);
        break;
    default:
        http_response_code(400);
        sendResponse(null, false, 'Invalid action parameter');
}

function getTestimonials($db) {
    try {
        // Try with is_active first, fall back to status
        $query = "SELECT id, name, content, position, company, program_title, image, video_url, 
                         rating, is_featured, created_at 
                  FROM testimonials 
                  WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL 
                  ORDER BY created_at DESC 
                  LIMIT 12";
        
        $testimonials = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        // If no results with is_active, try with status
        if (empty($testimonials)) {
            $query = "SELECT id, name, content, position, company, program_title, image, video_url, 
                             rating, is_featured, created_at 
                      FROM testimonials 
                      WHERE status = 'active' AND is_featured = 1 AND deleted_at IS NULL 
                      ORDER BY created_at DESC 
                      LIMIT 12";
            $testimonials = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Process testimonials for better frontend consumption
        foreach ($testimonials as &$testimonial) {
            // Ensure image URL is complete
            if (!empty($testimonial['image'])) {
                $testimonial['image_url'] = (strpos($testimonial['image'], 'http') === 0) 
                    ? $testimonial['image'] 
                    : "/chania/uploads/testimonials/" . $testimonial['image'];
            } else {
                $testimonial['image_url'] = null;
            }
            
            // Format video URL if present
            if (!empty($testimonial['video_url'])) {
                $testimonial['has_video'] = true;
                // Convert YouTube URLs to embeddable format
                if (strpos($testimonial['video_url'], 'youtube.com/watch') !== false) {
                    $testimonial['embed_url'] = str_replace('watch?v=', 'embed/', $testimonial['video_url']);
                } elseif (strpos($testimonial['video_url'], 'youtu.be/') !== false) {
                    $testimonial['embed_url'] = str_replace('youtu.be/', 'youtube.com/embed/', $testimonial['video_url']);
                } else {
                    $testimonial['embed_url'] = $testimonial['video_url'];
                }
            } else {
                $testimonial['has_video'] = false;
                $testimonial['embed_url'] = null;
            }
            
            // Ensure rating is numeric
            $testimonial['rating'] = (int)($testimonial['rating'] ?? 5);
            
            // Format date
            $testimonial['formatted_date'] = date('M Y', strtotime($testimonial['created_at']));
        }
        
        sendResponse($testimonials, true, 'Testimonials retrieved successfully');
        
    } catch (PDOException $e) {
        sendResponse(null, false, 'Failed to retrieve testimonials: ' . $e->getMessage());
    }
}

function getTeamMembers($db) {
    try {
        $query = "SELECT id, name, position, bio, email, phone, image, social_links, 
                         is_active, created_at 
                  FROM team_members 
                  WHERE is_active = 1 AND deleted_at IS NULL 
                  ORDER BY sort_order ASC, created_at ASC";
        
        $team_members = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Process team members for better frontend consumption
        foreach ($team_members as &$member) {
            // Ensure image URL is complete
            if (!empty($member['image'])) {
                $member['image_url'] = (strpos($member['image'], 'http') === 0) 
                    ? $member['image'] 
                    : "/chania/uploads/team/" . $member['image'];
            } else {
                $member['image_url'] = null;
            }
            
            // Parse social links JSON
            if (!empty($member['social_links'])) {
                $member['social_links_parsed'] = json_decode($member['social_links'], true) ?: [];
            } else {
                $member['social_links_parsed'] = [];
            }
            
            // Generate initials for fallback display
            $member['initials'] = strtoupper(substr($member['name'], 0, 1));
            if (strpos($member['name'], ' ') !== false) {
                $names = explode(' ', $member['name']);
                $member['initials'] = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));
            }
        }
        
        sendResponse($team_members, true, 'Team members retrieved successfully');
        
    } catch (PDOException $e) {
        sendResponse(null, false, 'Failed to retrieve team members: ' . $e->getMessage());
    }
}

function getPrograms($db) {
    try {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $limit = (int)($_GET['limit'] ?? 50);
        $featured_only = $_GET['featured_only'] ?? false;
        
        $query = "SELECT id, title, description, category, duration, fee, max_participants, 
                         start_date, end_date, image, is_featured, is_active, created_at,
                         difficulty_level, instructor_name, location, tags 
                  FROM programs 
                  WHERE is_active = 1 AND deleted_at IS NULL";
        
        $params = [];
        
        if ($featured_only) {
            $query .= " AND is_featured = 1";
        }
        
        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR description LIKE ? OR tags LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category)) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        
        $query .= " ORDER BY is_featured DESC, created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process programs for better frontend consumption
        foreach ($programs as &$program) {
            // Ensure image URL is complete
            if (!empty($program['image'])) {
                $program['image_url'] = (strpos($program['image'], 'http') === 0) 
                    ? $program['image'] 
                    : "/chania/uploads/programs/" . $program['image'];
            } else {
                $program['image_url'] = null;
            }
            
            // Format fee
            $program['fee'] = (float)($program['fee'] ?? 0);
            $program['is_free'] = $program['fee'] <= 0;
            $program['formatted_fee'] = $program['is_free'] ? 'Free' : '$' . number_format($program['fee'], 0);
            
            // Parse tags
            if (!empty($program['tags'])) {
                $program['tags_array'] = array_map('trim', explode(',', $program['tags']));
            } else {
                $program['tags_array'] = [];
            }
            
            // Format dates
            if (!empty($program['start_date'])) {
                $program['formatted_start_date'] = date('M j, Y', strtotime($program['start_date']));
            }
            if (!empty($program['end_date'])) {
                $program['formatted_end_date'] = date('M j, Y', strtotime($program['end_date']));
            }
            
            // Create short description
            $program['short_description'] = strlen($program['description']) > 150 
                ? substr($program['description'], 0, 150) . '...' 
                : $program['description'];
        }
        
        sendResponse($programs, true, 'Programs retrieved successfully');
        
    } catch (PDOException $e) {
        sendResponse(null, false, 'Failed to retrieve programs: ' . $e->getMessage());
    }
}

function getFeaturedPrograms($db) {
    try {
        $limit = (int)($_GET['limit'] ?? 6);
        
        $query = "SELECT id, title, description, category, duration, fee, image, 
                         difficulty_level, instructor_name, tags, created_at
                  FROM programs 
                  WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL 
                  ORDER BY created_at DESC 
                  LIMIT ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$limit]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process programs
        foreach ($programs as &$program) {
            // Ensure image URL is complete
            if (!empty($program['image'])) {
                $program['image_url'] = (strpos($program['image'], 'http') === 0) 
                    ? $program['image'] 
                    : "/chania/uploads/programs/" . $program['image'];
            } else {
                $program['image_url'] = null;
            }
            
            // Format fee
            $program['fee'] = (float)($program['fee'] ?? 0);
            $program['is_free'] = $program['fee'] <= 0;
            $program['formatted_fee'] = $program['is_free'] ? 'Free' : '$' . number_format($program['fee'], 0);
            
            // Create short description
            $program['short_description'] = strlen($program['description']) > 120 
                ? substr($program['description'], 0, 120) . '...' 
                : $program['description'];
        }
        
        sendResponse($programs, true, 'Featured programs retrieved successfully');
        
    } catch (PDOException $e) {
        sendResponse(null, false, 'Failed to retrieve featured programs: ' . $e->getMessage());
    }
}

function getHomeData($db) {
    try {
        // Get all necessary data for homepage in one API call
        $data = [];
        
        // Get testimonials
        $testimonials_query = "SELECT id, name, content, position, company, program_title, image, 
                                     rating, created_at 
                              FROM testimonials 
                              WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL 
                              ORDER BY created_at DESC 
                              LIMIT 6";
        
        $testimonials = $db->query($testimonials_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Process testimonials
        foreach ($testimonials as &$testimonial) {
            if (!empty($testimonial['image'])) {
                $testimonial['image_url'] = (strpos($testimonial['image'], 'http') === 0) 
                    ? $testimonial['image'] 
                    : "/chania/uploads/testimonials/" . $testimonial['image'];
            } else {
                $testimonial['image_url'] = null;
            }
            $testimonial['rating'] = (int)($testimonial['rating'] ?? 5);
        }
        
        // Get featured programs
        $programs_query = "SELECT id, title, description, category, duration, fee, image, 
                                 difficulty_level, instructor_name 
                          FROM programs 
                          WHERE is_active = 1 AND is_featured = 1 AND deleted_at IS NULL 
                          ORDER BY created_at DESC 
                          LIMIT 6";
        
        $programs = $db->query($programs_query)->fetchAll(PDO::FETCH_ASSOC);
        
        // Process programs
        foreach ($programs as &$program) {
            if (!empty($program['image'])) {
                $program['image_url'] = (strpos($program['image'], 'http') === 0) 
                    ? $program['image'] 
                    : "/chania/uploads/programs/" . $program['image'];
            } else {
                $program['image_url'] = null;
            }
            
            $program['fee'] = (float)($program['fee'] ?? 0);
            $program['is_free'] = $program['fee'] <= 0;
            $program['formatted_fee'] = $program['is_free'] ? 'Free' : '$' . number_format($program['fee'], 0);
            $program['short_description'] = strlen($program['description']) > 120 
                ? substr($program['description'], 0, 120) . '...' 
                : $program['description'];
        }
        
        // Get stats
        $stats_query = "SELECT 
                           (SELECT COUNT(*) FROM programs WHERE is_active = 1 AND deleted_at IS NULL) as total_programs,
                           (SELECT COUNT(*) FROM applications WHERE deleted_at IS NULL) as total_applications,
                           (SELECT COUNT(*) FROM testimonials WHERE is_active = 1 AND deleted_at IS NULL) as total_testimonials,
                           (SELECT COUNT(*) FROM team_members WHERE is_active = 1 AND deleted_at IS NULL) as total_team_members";
        
        $stats = $db->query($stats_query)->fetch(PDO::FETCH_ASSOC);
        
        $data = [
            'testimonials' => $testimonials,
            'featured_programs' => $programs,
            'stats' => $stats
        ];
        
        sendResponse($data, true, 'Home page data retrieved successfully');
        
    } catch (PDOException $e) {
        sendResponse(null, false, 'Failed to retrieve home data: ' . $e->getMessage());
    }
}
?>
