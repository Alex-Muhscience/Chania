<?php
/**
 * Contacts API Controller
 * Handles all contact-related API operations for both client and admin
 */

require_once __DIR__ . '/BaseApiController.php';

class ContactsController extends BaseApiController {
    
    /**
     * Create new contact (PUBLIC - Client form submissions)
     * POST /api/v1/contacts
     */
    public function create($params = []) {
        // Validate required fields
        $this->validateRequired(['name', 'email', 'subject', 'message']);
        
        $data = $this->request['body'];
        
        // Validate email
        $this->validateEmail($data['email']);
        
        // Sanitize input
        $name = $this->sanitize($data['name']);
        $email = $this->sanitize($data['email']);
        $phone = $this->sanitize($data['phone'] ?? '');
        $subject = $this->sanitize($data['subject']);
        $message = $this->sanitize($data['message']);
        $category = $this->sanitize($data['category'] ?? 'general');
        $ipAddress = $this->getClientIp();
        
        try {
            // Insert contact
            $stmt = $this->db->prepare("
                INSERT INTO contacts (
                    name, email, phone, subject, message, category, 
                    status, ip_address, submitted_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'new', ?, NOW())
            ");
            
            $stmt->execute([
                $name, $email, $phone, $subject, $message, $category, $ipAddress
            ]);
            
            $contactId = $this->db->lastInsertId();
            
            // Log activity
            $this->logActivity(
                'CONTACT_SUBMIT',
                'contacts',
                $contactId,
                "New contact inquiry from {$name} ({$email})"
            );
            
            $this->sendSuccess([
                'id' => $contactId,
                'message' => 'Thank you for your inquiry. We will get back to you soon!'
            ], 'Contact submitted successfully', 201);
            
        } catch (Exception $e) {
            error_log('Contact creation error: ' . $e->getMessage());
            $this->sendError('Unable to submit contact. Please try again later.', 500);
        }
    }
    
    /**
     * Get contacts list (ADMIN ONLY)
     * GET /api/v1/contacts
     */
    public function index($params = []) {
        // Require authentication with admin or manager role
        $this->requireAuth();
        
        $page = $this->request['query']['page'] ?? 1;
        $limit = $this->request['query']['limit'] ?? 20;
        $status = $this->request['query']['status'] ?? '';
        $category = $this->request['query']['category'] ?? '';
        $search = $this->request['query']['search'] ?? '';
        
        // Build query
        $whereConditions = ['deleted_at IS NULL'];
        $queryParams = [];
        
        if ($status) {
            $whereConditions[] = 'status = ?';
            $queryParams[] = $status;
        }
        
        if ($category) {
            $whereConditions[] = 'category = ?';
            $queryParams[] = $category;
        }
        
        if ($search) {
            $whereConditions[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ?)';
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        
        $query = "
            SELECT id, name, email, phone, subject, message, category, 
                   status, priority, submitted_at, is_read
            FROM contacts 
            {$whereClause}
            ORDER BY submitted_at DESC
        ";
        
        try {
            $result = $this->paginate($query, $queryParams, $page, $limit);
            $this->sendSuccess($result);
            
        } catch (Exception $e) {
            error_log('Contacts fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch contacts', 500);
        }
    }
    
    /**
     * Get single contact (ADMIN ONLY)
     * GET /api/v1/contacts/:id
     */
    public function show($params = []) {
        $this->requireAuth();
        
        $contactId = $params['id'] ?? null;
        
        if (!$contactId) {
            $this->sendError('Contact ID is required', 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM contacts 
                WHERE id = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch();
            
            if (!$contact) {
                $this->sendError('Contact not found', 404);
            }
            
            // Mark as read if not already
            if (!$contact['is_read']) {
                $updateStmt = $this->db->prepare("
                    UPDATE contacts SET is_read = 1 WHERE id = ?
                ");
                $updateStmt->execute([$contactId]);
                $contact['is_read'] = 1;
            }
            
            $this->sendSuccess($contact);
            
        } catch (Exception $e) {
            error_log('Contact fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch contact', 500);
        }
    }
    
    /**
     * Update contact (ADMIN ONLY)
     * PUT /api/v1/contacts/:id
     */
    public function update($params = []) {
        $this->requireAuth();
        
        $contactId = $params['id'] ?? null;
        
        if (!$contactId) {
            $this->sendError('Contact ID is required', 400);
        }
        
        $data = $this->request['body'];
        $allowedFields = ['status', 'priority', 'response', 'assigned_to'];
        
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
        
        // Add response timestamp if response is being added
        if (isset($data['response']) && !empty($data['response'])) {
            $updates[] = "responded_at = NOW()";
        }
        
        $values[] = $contactId;
        
        try {
            $stmt = $this->db->prepare("
                UPDATE contacts 
                SET " . implode(', ', $updates) . ", updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL
            ");
            
            $stmt->execute($values);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Contact not found or no changes made', 404);
            }
            
            // Log activity
            $this->logActivity(
                'CONTACT_UPDATE',
                'contacts',
                $contactId,
                'Contact updated with fields: ' . implode(', ', array_keys($data))
            );
            
            $this->sendSuccess(null, 'Contact updated successfully');
            
        } catch (Exception $e) {
            error_log('Contact update error: ' . $e->getMessage());
            $this->sendError('Unable to update contact', 500);
        }
    }
    
    /**
     * Delete contact (ADMIN ONLY)
     * DELETE /api/v1/contacts/:id
     */
    public function delete($params = []) {
        $this->requireAuth('admin'); // Only admins can delete
        
        $contactId = $params['id'] ?? null;
        
        if (!$contactId) {
            $this->sendError('Contact ID is required', 400);
        }
        
        try {
            // Soft delete
            $stmt = $this->db->prepare("
                UPDATE contacts 
                SET deleted_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL
            ");
            
            $stmt->execute([$contactId]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Contact not found', 404);
            }
            
            // Log activity
            $this->logActivity(
                'CONTACT_DELETE',
                'contacts',
                $contactId,
                'Contact deleted'
            );
            
            $this->sendSuccess(null, 'Contact deleted successfully');
            
        } catch (Exception $e) {
            error_log('Contact delete error: ' . $e->getMessage());
            $this->sendError('Unable to delete contact', 500);
        }
    }
}
?>
