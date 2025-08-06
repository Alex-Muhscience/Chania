<?php
/**
 * Programs API Controller
 * Handles all program-related API operations for both client and admin
 */

require_once __DIR__ . '/BaseApiController.php';

class ProgramsController extends BaseApiController {
    
    /**
     * Get programs list (PUBLIC)
     * GET /api/v1/programs
     */
    public function index($params = []) {
        $page = $this->request['query']['page'] ?? 1;
        $limit = $this->request['query']['limit'] ?? 20;
        $category = $this->request['query']['category'] ?? '';
        $search = $this->request['query']['search'] ?? '';
        
        // Build query
        $whereConditions = ['p.deleted_at IS NULL', 'p.is_active = 1'];
        $queryParams = [];
        
        if ($category) {
            $whereConditions[] = 'p.category = ?';
            $queryParams[] = $category;
        }
        
        if ($search) {
            $whereConditions[] = '(p.title LIKE ? OR p.description LIKE ?)';
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $query = "
            SELECT 
                p.id, p.title, p.slug, p.short_description, p.duration, 
                p.difficulty_level, p.fee, p.start_date, p.end_date, 
                p.category, p.is_featured, p.view_count
            FROM programs p
            {$whereClause}
            ORDER BY p.start_date DESC
        ";
        
        try {
            $result = $this->paginate($query, $queryParams, $page, $limit);
            $this->sendSuccess($result);
            
        } catch (Exception $e) {
            error_log('Programs fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch programs', 500);
        }
    }
    
    /**
     * Get single program (PUBLIC)
     * GET /api/v1/programs/:id
     */
    public function show($params = []) {
        $programId = $params['id'] ?? null;
        
        if (!$programId) {
            $this->sendError('Program ID is required', 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*, 
                    (SELECT COUNT(*) FROM applications a WHERE a.program_id = p.id AND a.deleted_at IS NULL) as application_count
                FROM programs p
                WHERE p.id = ? AND p.deleted_at IS NULL
            ");
            $stmt->execute([$programId]);
            $program = $stmt->fetch();
            
            if (!$program) {
                $this->sendError('Program not found', 404);
            }
            
            // Parse JSON fields if they exist
            if ($program['gallery_images']) {
                $program['gallery_images'] = json_decode($program['gallery_images'], true);
            }
            if ($program['tags']) {
                $program['tags'] = json_decode($program['tags'], true);
            }
            
            $this->sendSuccess($program);
            
        } catch (Exception $e) {
            error_log('Program fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch program', 500);
        }
    }
    
    /**
     * Create program (ADMIN ONLY)
     * POST /api/v1/programs
     */
    public function create($params = []) {
        // $this->requireAuth();
        // Validate required fields
        $this->validateRequired(['title', 'slug', 'description', 'duration', 'category', 'fee']);
        
        $data = $this->request['body'];
        
        // Sanitize input
        $sanitizedData = $this->sanitizeProgramData($data);
        
        try {
            // Insert program
            $stmt = $this->db->prepare("
                INSERT INTO programs (
                    title, slug, description, short_description, duration, duration_type,
                    difficulty_level, fee, category, subcategory, requirements, benefits, curriculum,
                    prerequisites, certification_available, max_participants, min_participants,
                    start_date, end_date, registration_deadline, image_path, gallery_images,
                    video_url, brochure_path, instructor_name, instructor_bio, instructor_image,
                    location, is_featured, is_active, is_online, tags, meta_title, meta_description,
                    created_by, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $sanitizedData['title'],
                $sanitizedData['slug'],
                $sanitizedData['description'],
                $sanitizedData['short_description'],
                $sanitizedData['duration'],
                $sanitizedData['duration_type'],
                $sanitizedData['difficulty_level'],
                $sanitizedData['fee'],
                $sanitizedData['category'],
                $sanitizedData['subcategory'] ?? null,
                $sanitizedData['requirements'] ?? null,
                $sanitizedData['benefits'] ?? null,
                $sanitizedData['curriculum'] ?? null,
                $sanitizedData['prerequisites'] ?? null,
                $sanitizedData['certification_available'] ?? 0,
                $sanitizedData['max_participants'] ?? null,
                $sanitizedData['min_participants'] ?? 1,
                $sanitizedData['start_date'] ?? null,
                $sanitizedData['end_date'] ?? null,
                $sanitizedData['registration_deadline'] ?? null,
                $sanitizedData['image_path'] ?? null,
                $sanitizedData['gallery_images'] ? json_encode($sanitizedData['gallery_images']) : null,
                $sanitizedData['video_url'] ?? null,
                $sanitizedData['brochure_path'] ?? null,
                $sanitizedData['instructor_name'] ?? null,
                $sanitizedData['instructor_bio'] ?? null,
                $sanitizedData['instructor_image'] ?? null,
                $sanitizedData['location'] ?? null,
                $sanitizedData['is_featured'] ?? 0,
                $sanitizedData['is_online'] ?? 0,
                $sanitizedData['tags'] ? json_encode($sanitizedData['tags']) : null,
                $sanitizedData['meta_title'] ?? null,
                $sanitizedData['meta_description'] ?? null,
                $this->request['body']['created_by'] ?? null
            ]);
            
            $programId = $this->db->lastInsertId();
            
            // Log activity
            $this->logActivity(
                'PROGRAM_CREATE',
                'programs',
                $programId,
                "Program created: {$sanitizedData['title']}"
            );
            
            $this->sendSuccess(['id' => $programId], 'Program created successfully', 201);
            
        } catch (Exception $e) {
            error_log('Program creation error: ' . $e->getMessage());
            $this->sendError('Unable to create program', 500);
        }
    }
    
    /**
     * Update program (ADMIN ONLY)
     * PUT /api/v1/programs/:id
     */
    public function update($params = []) {
        // $this->requireAuth();
        $programId = $params['id'] ?? null;
        
        if (!$programId) {
            $this->sendError('Program ID is required', 400);
        }
        
        $data = $this->request['body'];
        
        // Sanitize input
        $sanitizedData = $this->sanitizeProgramData($data);
        
        // Filter only allowed fields
        $allowedFields = [
            'title', 'slug', 'description', 'short_description', 'duration', 'duration_type',
            'difficulty_level', 'fee', 'category', 'subcategory', 'requirements', 'benefits',
            'curriculum', 'prerequisites', 'certification_available', 'max_participants',
            'min_participants', 'start_date', 'end_date', 'registration_deadline', 'image_path',
            'gallery_images', 'video_url', 'brochure_path', 'instructor_name', 'instructor_bio',
            'instructor_image', 'location', 'is_featured', 'is_active', 'is_online', 'tags',
            'meta_title', 'meta_description', 'created_by'
        ];
        $updates = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($sanitizedData[$field])) {
                $updates[] = "{$field} = ?";
                $values[] = is_array($sanitizedData[$field]) ? json_encode($sanitizedData[$field]) : $sanitizedData[$field];
            }
        }
        
        if (empty($updates)) {
            $this->sendError('No valid fields to update', 400);
        }
        
        $values[] = $programId;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE programs 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Program not found or no changes made', 404);
            }
            
            // Log activity
            $this->logActivity(
                'PROGRAM_UPDATE',
                'programs',
                $programId,
                'Program updated'
            );
            
            $this->sendSuccess(null, 'Program updated successfully');
            
        } catch (Exception $e) {
            error_log('Program update error: ' . $e->getMessage());
            $this->sendError('Unable to update program', 500);
        }
    }
    
    /**
     * Delete program (ADMIN ONLY)
     * DELETE /api/v1/programs/:id
     */
    public function delete($params = []) {
        // $this->requireAuth();
        $programId = $params['id'] ?? null;
        
        if (!$programId) {
            $this->sendError('Program ID is required', 400);
        }
        
        try {
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE programs 
                SET deleted_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$programId]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Program not found', 404);
            }
            
            // Log activity
            $this->logActivity(
                'PROGRAM_DELETE',
                'programs',
                $programId,
                'Program deleted'
            );
            
            $this->sendSuccess(null, 'Program deleted successfully');
            
        } catch (Exception $e) {
            error_log('Program delete error: ' . $e->getMessage());
            $this->sendError('Unable to delete program', 500);
        }
    }
    
    /**
     * Helper: Sanitize program data
     */
    private function sanitizeProgramData($data) {
        $sanitized = [];
        
        $fields = [
            'title', 'slug', 'description', 'short_description', 'duration', 'duration_type',
            'difficulty_level', 'fee', 'category', 'subcategory', 'requirements', 'benefits',
            'curriculum', 'prerequisites', 'certification_available', 'max_participants',
            'min_participants', 'start_date', 'end_date', 'registration_deadline', 'image_path',
            'gallery_images', 'video_url', 'brochure_path', 'instructor_name', 'instructor_bio',
            'instructor_image', 'location', 'is_featured', 'is_active', 'is_online', 'tags',
            'meta_title', 'meta_description', 'created_by'
        ];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $this->sanitize($data[$field]);
            }
        }
        
        return $sanitized;
    }
}
?>
