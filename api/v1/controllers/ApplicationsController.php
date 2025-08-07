<?php
/**
 * Applications API Controller
 * Handles all application-related API operations for both client and admin
 */

require_once __DIR__ . '/BaseApiController.php';

class ApplicationsController extends BaseApiController {
    
    /**
     * Create new application (PUBLIC - Client form submissions)
     * POST /api/v1/applications
     */
    public function create($params = []) {
        // Validate required fields
        $this->validateRequired([
            'program_id', 'first_name', 'last_name', 'email', 'phone', 
            'address', 'education_details', 'motivation'
        ]);
        
        $data = $this->request['body'];
        
        // Validate email
        $this->validateEmail($data['email']);
        
        // Validate program exists
        $this->validateProgramExists($data['program_id']);
        
        // Sanitize input
        $sanitizedData = $this->sanitizeApplicationData($data);
        
        try {
            // Generate application number
            $applicationNumber = $this->generateApplicationNumber();
            
            // Insert application
            $stmt = $this->db->prepare("
                INSERT INTO applications (
                    program_id, application_number, first_name, last_name, email, phone,
                    date_of_birth, gender, nationality, id_number, address, city, postal_code, country,
                    emergency_contact_name, emergency_contact_phone, emergency_contact_relationship,
                    education_level, education_details, work_experience, skills, motivation, expectations,
                    how_did_you_hear, special_needs, status, ip_address, submitted_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW()
                )
            ");
            
            $stmt->execute([
                $sanitizedData['program_id'],
                $applicationNumber,
                $sanitizedData['first_name'],
                $sanitizedData['last_name'],
                $sanitizedData['email'],
                $sanitizedData['phone'],
                $sanitizedData['date_of_birth'] ?? null,
                $sanitizedData['gender'] ?? null,
                $sanitizedData['nationality'] ?? null,
                $sanitizedData['id_number'] ?? null,
                $sanitizedData['address'],
                $sanitizedData['city'] ?? null,
                $sanitizedData['postal_code'] ?? null,
                $sanitizedData['country'] ?? 'Kenya',
                $sanitizedData['emergency_contact_name'] ?? null,
                $sanitizedData['emergency_contact_phone'] ?? null,
                $sanitizedData['emergency_contact_relationship'] ?? null,
                $sanitizedData['education_level'] ?? null,
                $sanitizedData['education_details'],
                $sanitizedData['work_experience'] ?? null,
                $sanitizedData['skills'] ?? null,
                $sanitizedData['motivation'],
                $sanitizedData['expectations'] ?? null,
                $sanitizedData['how_did_you_hear'] ?? null,
                $sanitizedData['special_needs'] ?? null,
                $this->getClientIp()
            ]);
            
            $applicationId = $this->db->lastInsertId();
            
            // Log activity
            $this->logActivity(
                'APPLICATION_SUBMIT',
                'applications',
                $applicationId,
                "New application from {$sanitizedData['first_name']} {$sanitizedData['last_name']} ({$sanitizedData['email']})"
            );
            
            $this->sendSuccess([
                'id' => $applicationId,
                'application_number' => $applicationNumber,
                'message' => 'Your application has been submitted successfully! You will receive a confirmation email shortly.'
            ], 'Application submitted successfully', 201);
            
        } catch (Exception $e) {
            error_log('Application creation error: ' . $e->getMessage());
            $this->sendError('Unable to submit application. Please try again later.', 500);
        }
    }
    
    /**
     * Get applications list (ADMIN ONLY)
     * GET /api/v1/applications
     */
    public function index($params = []) {
        // $this->requireAuth();
        
        $page = $this->request['query']['page'] ?? 1;
        $limit = $this->request['query']['limit'] ?? 20;
        $status = $this->request['query']['status'] ?? '';
        $program_id = $this->request['query']['program_id'] ?? '';
        $search = $this->request['query']['search'] ?? '';
        
        // Build query
        $whereConditions = ['a.deleted_at IS NULL'];
        $queryParams = [];
        
        if ($status) {
            $whereConditions[] = 'a.status = ?';
            $queryParams[] = $status;
        }
        
        if ($program_id) {
            $whereConditions[] = 'a.program_id = ?';
            $queryParams[] = $program_id;
        }
        
        if ($search) {
            $whereConditions[] = '(CONCAT(a.first_name, " ", a.last_name) LIKE ? OR a.email LIKE ? OR a.application_number LIKE ?)';
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $query = "
            SELECT 
                a.id, a.application_number, a.first_name, a.last_name, a.email, a.phone,
                a.status, a.priority, a.submitted_at, a.reviewed_at, a.payment_status,
                a.preferred_delivery_mode, a.schedule_id,
                p.title as program_title, p.category as program_category,
                s.title as schedule_title, s.start_date as schedule_start_date, s.end_date as schedule_end_date,
                CONCAT(a.first_name, ' ', a.last_name) as full_name
            FROM applications a
            LEFT JOIN programs p ON a.program_id = p.id
            LEFT JOIN program_schedules s ON a.schedule_id = s.id
            {$whereClause}
            ORDER BY a.submitted_at DESC
        ";
        
        try {
            $result = $this->paginate($query, $queryParams, $page, $limit);
            $this->sendSuccess($result);
            
        } catch (Exception $e) {
            error_log('Applications fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch applications', 500);
        }
    }
    
    /**
     * Get single application (ADMIN ONLY)
     * GET /api/v1/applications/:id
     */
    public function show($params = []) {
        // $this->requireAuth();
        
        $applicationId = $params['id'] ?? null;
        
        if (!$applicationId) {
            $this->sendError('Application ID is required', 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    a.*, 
                    p.title as program_title, 
                    p.category as program_category, 
                    p.duration as program_duration,
                    p.fee as program_fee,
                    CONCAT(a.first_name, ' ', a.last_name) as full_name
                FROM applications a
                LEFT JOIN programs p ON a.program_id = p.id
                WHERE a.id = ? AND a.deleted_at IS NULL
            ");
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch();
            
            if (!$application) {
                $this->sendError('Application not found', 404);
            }
            
            // Parse JSON fields if they exist
            if ($application['documents']) {
                $application['documents'] = json_decode($application['documents'], true);
            }
            
            $this->sendSuccess($application);
            
        } catch (Exception $e) {
            error_log('Application fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch application', 500);
        }
    }
    
    /**
     * Update application (ADMIN ONLY)
     * PUT /api/v1/applications/:id
     */
    public function update($params = []) {
        // $this->requireAuth();
        
        $applicationId = $params['id'] ?? null;
        
        if (!$applicationId) {
            $this->sendError('Application ID is required', 400);
        }
        
        $data = $this->request['body'];
        $allowedFields = [
            'status', 'status_reason', 'priority', 'notes', 'reviewed_by',
            'interview_scheduled', 'interview_date', 'interview_notes',
            'payment_status', 'payment_amount', 'payment_reference',
            'schedule_id', 'preferred_delivery_mode'
        ];
        
        // Filter only allowed fields
        $updates = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "{$field} = ?";
                $values[] = $this->sanitize($data[$field]);
            }
        }
        
        if (empty($updates)) {
            $this->sendError('No valid fields to update', 400);
        }
        
        // Add review timestamp if status is being changed
        if (isset($data['status'])) {
            $updates[] = "reviewed_at = NOW()";
        }
        
        $values[] = $applicationId;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Application not found or no changes made', 404);
            }
            
            // Log activity
            $this->logActivity(
                'APPLICATION_UPDATE',
                'applications',
                $applicationId,
                'Application updated with fields: ' . implode(', ', array_keys($data))
            );
            
            $this->sendSuccess(null, 'Application updated successfully');
            
        } catch (Exception $e) {
            error_log('Application update error: ' . $e->getMessage());
            $this->sendError('Unable to update application', 500);
        }
    }
    
    /**
     * Delete application (ADMIN ONLY)
     * DELETE /api/v1/applications/:id
     */
    public function delete($params = []) {
        // $this->requireAuth();
        
        $applicationId = $params['id'] ?? null;
        
        if (!$applicationId) {
            $this->sendError('Application ID is required', 400);
        }
        
        try {
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET deleted_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL
            ");
            
            $stmt->execute([$applicationId]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Application not found', 404);
            }
            
            // Log activity
            $this->logActivity(
                'APPLICATION_DELETE',
                'applications',
                $applicationId,
                'Application deleted'
            );
            
            $this->sendSuccess(null, 'Application deleted successfully');
            
        } catch (Exception $e) {
            error_log('Application delete error: ' . $e->getMessage());
            $this->sendError('Unable to delete application', 500);
        }
    }
    
    /**
     * Helper: Validate program exists
     */
    private function validateProgramExists($programId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM programs 
                WHERE id = ? AND is_active = 1 AND deleted_at IS NULL
            ");
            $stmt->execute([$programId]);
            
            if (!$stmt->fetch()) {
                $this->sendError('Invalid or inactive program selected', 400, 'INVALID_PROGRAM');
            }
            
        } catch (Exception $e) {
            error_log('Program validation error: ' . $e->getMessage());
            $this->sendError('Unable to validate program', 500);
        }
    }
    
    /**
     * Helper: Sanitize application data
     */
    private function sanitizeApplicationData($data) {
        $sanitized = [];
        
        $fields = [
            'program_id', 'first_name', 'last_name', 'email', 'phone',
            'date_of_birth', 'gender', 'nationality', 'id_number', 'address',
            'city', 'postal_code', 'country', 'emergency_contact_name',
            'emergency_contact_phone', 'emergency_contact_relationship',
            'education_level', 'education_details', 'work_experience',
            'skills', 'motivation', 'expectations', 'how_did_you_hear', 'special_needs'
        ];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $this->sanitize($data[$field]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Helper: Generate unique application number
     */
    private function generateApplicationNumber() {
        $year = date('Y');
        $prefix = "APP{$year}";
        
        // Get the next sequential number for this year
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM applications 
            WHERE application_number LIKE ?
        ");
        $stmt->execute(["{$prefix}%"]);
        $count = $stmt->fetch()['count'];
        
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$sequence}";
    }
}
?>
