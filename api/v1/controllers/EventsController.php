<?php
/**
 * Events API Controller
 * Handles all event-related API operations for both client and admin
 */

require_once __DIR__ . '/BaseApiController.php';

class EventsController extends BaseApiController {
    
    /**
     * Get events list (PUBLIC)
     * GET /api/v1/events
     */
    public function index($params = []) {
        $page = $this->request['query']['page'] ?? 1;
        $limit = $this->request['query']['limit'] ?? 20;
        $type = $this->request['query']['type'] ?? '';
        $search = $this->request['query']['search'] ?? '';
        $upcoming = $this->request['query']['upcoming'] ?? false;
        $admin = $this->request['query']['admin'] ?? false;
        
        // Build query
        $whereConditions = ['e.deleted_at IS NULL'];
        $queryParams = [];
        
        // For non-admin requests, only show active events
        if (!$admin) {
            $whereConditions[] = 'e.is_active = 1';
        }
        
        if ($type) {
            $whereConditions[] = 'e.event_type = ?';
            $queryParams[] = $type;
        }
        
        if ($upcoming) {
            $whereConditions[] = 'e.event_date >= CURDATE()';
        }
        
        if ($search) {
            $whereConditions[] = '(e.title LIKE ? OR e.description LIKE ?)';
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $query = "
            SELECT 
                e.id, e.title, e.slug, e.short_description, e.event_type, 
                e.event_date, e.end_date, e.location, e.max_attendees, 
                e.current_attendees, e.registration_fee, e.registration_deadline,
                e.is_featured, e.is_active, e.is_virtual, e.view_count, e.created_at
            FROM events e
            {$whereClause}
            ORDER BY e.created_at DESC
        ";
        
        try {
            $result = $this->paginate($query, $queryParams, $page, $limit);
            $this->sendSuccess($result);
            
        } catch (Exception $e) {
            error_log('Events fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch events', 500);
        }
    }
    
    /**
     * Get single event (PUBLIC)
     * GET /api/v1/events/:id
     */
    public function show($params = []) {
        $eventId = $params['id'] ?? null;
        
        if (!$eventId) {
            $this->sendError('Event ID is required', 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    e.*, 
                    (SELECT COUNT(*) FROM event_registrations er WHERE er.event_id = e.id) as registration_count
                FROM events e
                WHERE e.id = ? AND e.deleted_at IS NULL
            ");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                $this->sendError('Event not found', 404);
            }
            
            // Parse JSON fields if they exist
            if ($event['gallery_images']) {
                $event['gallery_images'] = json_decode($event['gallery_images'], true);
            }
            if ($event['tags']) {
                $event['tags'] = json_decode($event['tags'], true);
            }
            
            $this->sendSuccess($event);
            
        } catch (Exception $e) {
            error_log('Event fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch event', 500);
        }
    }
    
    /**
     * Create event registration (PUBLIC)
     * POST /api/v1/events/:id/register
     */
    public function register($params = []) {
        $eventId = $params['id'] ?? null;
        
        if (!$eventId) {
            $this->sendError('Event ID is required', 400);
        }
        
        $data = $this->request['body'];
        
        // Handle full_name split if needed
        if (isset($data['full_name']) && !isset($data['first_name'])) {
            $nameParts = explode(' ', trim($data['full_name']), 2);
            $data['first_name'] = $nameParts[0];
            $data['last_name'] = $nameParts[1] ?? '';
        }
        
        // Validate required fields
        $this->validateRequired(['first_name', 'email']);
        
        // Validate email
        $this->validateEmail($data['email']);
        
        // Validate event exists and is available for registration
        $this->validateEventAvailable($eventId);
        
        // Check for existing registration
        $this->checkDuplicateRegistration($eventId, $data['email']);
        
        // Sanitize input
        $sanitizedData = $this->sanitizeRegistrationData($data);
        
        try {
            // Insert event registration
            $stmt = $this->db->prepare("
                INSERT INTO event_registrations (
                    event_id, first_name, last_name, email, phone, organization, position,
                    dietary_requirements, accessibility_needs, status,
                    registered_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'registered', NOW(), NOW())
            ");
            
            $stmt->execute([
                $eventId,
                $sanitizedData['first_name'],
                $sanitizedData['last_name'] ?? '',
                $sanitizedData['email'],
                $sanitizedData['phone'] ?? null,
                $sanitizedData['organization'] ?? null,
                $sanitizedData['position'] ?? null,
                $sanitizedData['dietary_requirements'] ?? $sanitizedData['special_requirements'] ?? null,
                $sanitizedData['accessibility_needs'] ?? $sanitizedData['special_requirements'] ?? null
            ]);
            
            $registrationId = $this->db->lastInsertId();
            
            // Update event attendee count
            $this->updateEventAttendeeCount($eventId);
            
            // Log activity for admin notifications
            require_once __DIR__ . '/../../../shared/Core/Utilities.php';
            Utilities::logActivity(
                null, // No user ID for public registrations
                'EVENT_REGISTRATION',
                'event_registrations',
                $registrationId,
                "New event registration from {$sanitizedData['first_name']} {$sanitizedData['last_name']} ({$sanitizedData['email']}) for event ID {$eventId}"
            );
            
            $this->sendSuccess([
                'id' => $registrationId,
                'message' => 'Your registration has been submitted successfully! You will receive a confirmation email shortly.'
            ], 'Registration submitted successfully', 201);
            
        } catch (Exception $e) {
            error_log('Event registration error: ' . $e->getMessage());
            $this->sendError('Unable to submit registration. Please try again later.', 500);
        }
    }
    
    /**
     * Create event (ADMIN ONLY)
     * POST /api/v1/events
     */
    public function create($params = []) {
        // $this->requireAuth();
        
        // Validate required fields
        $this->validateRequired(['title', 'slug', 'description', 'event_date', 'location']);
        
        $data = $this->request['body'];
        
        // Sanitize input
        $sanitizedData = $this->sanitizeEventData($data);
        
        try {
            // Insert event
            $stmt = $this->db->prepare("
                INSERT INTO events (
                    title, slug, description, short_description, event_type, event_date, end_date,
                    location, venue_details, max_attendees, registration_fee, registration_deadline,
                    image_path, gallery_images, speaker_name, speaker_bio, speaker_image, agenda,
                    requirements, contact_info, external_link, is_featured, is_active, is_virtual,
                    meeting_link, tags, meta_title, meta_description, created_by, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $sanitizedData['title'],
                $sanitizedData['slug'],
                $sanitizedData['description'],
                $sanitizedData['short_description'],
                $sanitizedData['event_type'],
                $sanitizedData['event_date'],
                $sanitizedData['end_date'] ?? null,
                $sanitizedData['location'],
                $sanitizedData['venue_details'] ?? null,
                $sanitizedData['max_attendees'] ?? null,
                $sanitizedData['registration_fee'] ?? 0.00,
                $sanitizedData['registration_deadline'] ?? null,
                $sanitizedData['image_path'] ?? null,
                $sanitizedData['gallery_images'] ? json_encode($sanitizedData['gallery_images']) : null,
                $sanitizedData['speaker_name'] ?? null,
                $sanitizedData['speaker_bio'] ?? null,
                $sanitizedData['speaker_image'] ?? null,
                $sanitizedData['agenda'] ?? null,
                $sanitizedData['requirements'] ?? null,
                $sanitizedData['contact_info'] ?? null,
                $sanitizedData['external_link'] ?? null,
                $sanitizedData['is_featured'] ?? 0,
                $sanitizedData['is_virtual'] ?? 0,
                $sanitizedData['meeting_link'] ?? null,
                $sanitizedData['tags'] ? json_encode($sanitizedData['tags']) : null,
                $sanitizedData['meta_title'] ?? null,
                $sanitizedData['meta_description'] ?? null,
                $this->request['body']['created_by'] ?? null
            ]);
            
            $eventId = $this->db->lastInsertId();
            
            // Log activity
            $this->logActivity(
                'EVENT_CREATE',
                'events',
                $eventId,
                "Event created: {$sanitizedData['title']}"
            );
            
            $this->sendSuccess(['id' => $eventId], 'Event created successfully', 201);
            
        } catch (Exception $e) {
            error_log('Event creation error: ' . $e->getMessage());
            $this->sendError('Unable to create event', 500);
        }
    }
    
    /**
     * Update event (ADMIN ONLY)
     * PUT /api/v1/events/:id
     */
    public function update($params = []) {
        // $this->requireAuth();
        
        $eventId = $params['id'] ?? null;
        
        if (!$eventId) {
            $this->sendError('Event ID is required', 400);
        }
        
        $data = $this->request['body'];
        
        // Sanitize input
        $sanitizedData = $this->sanitizeEventData($data);
        
        // Filter only allowed fields
        $allowedFields = [
            'title', 'slug', 'description', 'short_description', 'event_type', 'event_date',
            'end_date', 'location', 'venue_details', 'max_attendees', 'registration_fee',
            'registration_deadline', 'image_path', 'gallery_images', 'speaker_name', 'speaker_bio',
            'speaker_image', 'agenda', 'requirements', 'contact_info', 'external_link',
            'is_featured', 'is_active', 'is_virtual', 'meeting_link', 'tags', 'meta_title',
            'meta_description'
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
        
        $values[] = $eventId;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE events 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Event not found or no changes made', 404);
            }
            
            // Log activity
            $this->logActivity(
                'EVENT_UPDATE',
                'events',
                $eventId,
                'Event updated'
            );
            
            $this->sendSuccess(null, 'Event updated successfully');
            
        } catch (Exception $e) {
            error_log('Event update error: ' . $e->getMessage());
            $this->sendError('Unable to update event', 500);
        }
    }
    
    /**
     * Delete event (ADMIN ONLY)
     * DELETE /api/v1/events/:id
     */
    public function delete($params = []) {
        // $this->requireAuth();
        
        $eventId = $params['id'] ?? null;
        
        if (!$eventId) {
            $this->sendError('Event ID is required', 400);
        }
        
        try {
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE events 
                SET deleted_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$eventId]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Event not found', 404);
            }
            
            // Log activity
            $this->logActivity(
                'EVENT_DELETE',
                'events',
                $eventId,
                'Event deleted'
            );
            
            $this->sendSuccess(null, 'Event deleted successfully');
            
        } catch (Exception $e) {
            error_log('Event delete error: ' . $e->getMessage());
            $this->sendError('Unable to delete event', 500);
        }
    }
    
    /**
     * Helper: Validate event is available for registration
     */
    private function validateEventAvailable($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, max_attendees, current_attendees, registration_deadline, event_date
                FROM events 
                WHERE id = ? AND is_active = 1 AND deleted_at IS NULL
            ");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            if (!$event) {
                $this->sendError('Event not found or inactive', 400, 'INVALID_EVENT');
            }
            
            // Check if registration is still open
            if ($event['registration_deadline'] && strtotime($event['registration_deadline']) < time()) {
                $this->sendError('Registration deadline has passed', 400, 'REGISTRATION_CLOSED');
            }
            
            // Check if event has already passed
            if (strtotime($event['event_date']) < time()) {
                $this->sendError('Cannot register for past events', 400, 'EVENT_PASSED');
            }
            
            // Check if event is full
            if ($event['max_attendees'] && $event['current_attendees'] >= $event['max_attendees']) {
                $this->sendError('Event is fully booked', 400, 'EVENT_FULL');
            }
            
        } catch (Exception $e) {
            error_log('Event validation error: ' . $e->getMessage());
            $this->sendError('Unable to validate event', 500);
        }
    }
    
    /**
     * Helper: Update event attendee count
     */
    private function updateEventAttendeeCount($eventId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE events 
                SET current_attendees = (
                    SELECT COUNT(*) FROM event_registrations 
                    WHERE event_id = ? AND status != 'cancelled'
                )
                WHERE id = ?
            ");
            $stmt->execute([$eventId, $eventId]);
            
        } catch (Exception $e) {
            error_log('Error updating attendee count: ' . $e->getMessage());
        }
    }
    
    /**
     * Helper: Sanitize event data
     */
    private function sanitizeEventData($data) {
        $sanitized = [];
        
        $fields = [
            'title', 'slug', 'description', 'short_description', 'event_type', 'event_date',
            'end_date', 'location', 'venue_details', 'max_attendees', 'registration_fee',
            'registration_deadline', 'image_path', 'gallery_images', 'speaker_name', 'speaker_bio',
            'speaker_image', 'agenda', 'requirements', 'contact_info', 'external_link',
            'is_featured', 'is_active', 'is_virtual', 'meeting_link', 'tags', 'meta_title',
            'meta_description'
        ];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $this->sanitize($data[$field]);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Helper: Check for duplicate registration
     */
    private function checkDuplicateRegistration($eventId, $email) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM event_registrations 
                WHERE event_id = ? AND email = ? AND status != 'cancelled'
            ");
            $stmt->execute([$eventId, $email]);
            
            if ($stmt->fetch()) {
                $this->sendError('You are already registered for this event', 409, 'DUPLICATE_REGISTRATION');
            }
            
        } catch (Exception $e) {
            error_log('Duplicate check error: ' . $e->getMessage());
            $this->sendError('Unable to validate registration', 500);
        }
    }
    
    /**
     * Helper: Sanitize registration data
     */
    private function sanitizeRegistrationData($data) {
        $sanitized = [];
        
        $fields = [
            'first_name', 'last_name', 'email', 'phone', 'organization',
            'position', 'dietary_requirements', 'accessibility_needs', 'special_requirements'
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
