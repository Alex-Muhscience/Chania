<?php
/**
 * Newsletter API Controller
 * Handles newsletter subscription and management
 */

require_once __DIR__ . '/BaseApiController.php';

class NewsletterController extends BaseApiController {
    
    /**
     * Subscribe to newsletter (PUBLIC)
     * POST /api/v1/newsletter/subscribe
     */
    public function subscribe($params = []) {
        // Validate required fields
        $this->validateRequired(['email']);
        
        $data = $this->request['body'];
        
        // Validate email
        $this->validateEmail($data['email']);
        
        // Sanitize input
        $email = $this->sanitize($data['email']);
        $name = $this->sanitize($data['name'] ?? '');
        
        try {
            // Check if email already exists
            $stmt = $this->db->prepare("
                SELECT id, status FROM newsletter_subscribers 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($existing['status'] === 'subscribed') {
                    $this->sendError('Email is already subscribed to our newsletter', 400, 'ALREADY_SUBSCRIBED');
                } else {
                    // Reactivate subscription
                    $stmt = $this->db->prepare("
                        UPDATE newsletter_subscribers 
                        SET status = 'subscribed', subscribed_at = NOW(), name = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $existing['id']]);
                    
                    $this->sendSuccess([
                        'message' => 'Welcome back! Your newsletter subscription has been reactivated.'
                    ], 'Subscription reactivated successfully');
                    return;
                }
            }
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Insert new subscription
            $stmt = $this->db->prepare("
                INSERT INTO newsletter_subscribers (
                    email, name, status, verification_token, subscribed_at, ip_address
                ) VALUES (?, ?, 'subscribed', ?, NOW(), ?)
            ");
            
            $stmt->execute([
                $email,
                $name,
                $verificationToken,
                $this->getClientIp()
            ]);
            
            $subscriberId = $this->db->lastInsertId();
            
            // Log activity
            $this->logActivity(
                'NEWSLETTER_SUBSCRIBE',
                'newsletter_subscribers',
                $subscriberId,
                "New newsletter subscription: {$email}"
            );
            
            $this->sendSuccess([
                'id' => $subscriberId,
                'message' => 'Thank you for subscribing to our newsletter!'
            ], 'Subscription successful', 201);
            
        } catch (Exception $e) {
            error_log('Newsletter subscription error: ' . $e->getMessage());
            $this->sendError('Unable to process subscription. Please try again later.', 500);
        }
    }
    
    /**
     * Get newsletter subscribers (ADMIN ONLY)
     * GET /api/v1/newsletter/subscribers
     */
    public function index($params = []) {
        // $this->requireAuth();
        
        $page = $this->request['query']['page'] ?? 1;
        $limit = $this->request['query']['limit'] ?? 20;
        $status = $this->request['query']['status'] ?? '';
        $search = $this->request['query']['search'] ?? '';
        
        // Build query
        $whereConditions = [];
        $queryParams = [];
        
        if ($status) {
            $whereConditions[] = 'status = ?';
            $queryParams[] = $status;
        }
        
        if ($search) {
            $whereConditions[] = '(email LIKE ? OR name LIKE ?)';
            $searchTerm = "%{$search}%";
            $queryParams[] = $searchTerm;
            $queryParams[] = $searchTerm;
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $query = "
            SELECT id, email, name, status, subscribed_at, unsubscribed_at, verified_at
            FROM newsletter_subscribers 
            {$whereClause}
            ORDER BY subscribed_at DESC
        ";
        
        try {
            $result = $this->paginate($query, $queryParams, $page, $limit);
            $this->sendSuccess($result);
            
        } catch (Exception $e) {
            error_log('Newsletter subscribers fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch subscribers', 500);
        }
    }
    
    /**
     * Unsubscribe from newsletter (ADMIN ONLY)
     * DELETE /api/v1/newsletter/subscribers/:id
     */
    public function unsubscribe($params = []) {
        // $this->requireAuth();
        
        $subscriberId = $params['id'] ?? null;
        
        if (!$subscriberId) {
            $this->sendError('Subscriber ID is required', 400);
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'unsubscribed', unsubscribed_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([$subscriberId]);
            
            if ($stmt->rowCount() === 0) {
                $this->sendError('Subscriber not found', 404);
            }
            
            // Log activity
            $this->logActivity(
                'NEWSLETTER_UNSUBSCRIBE',
                'newsletter_subscribers',
                $subscriberId,
                'Subscriber unsubscribed (admin action)'
            );
            
            $this->sendSuccess(null, 'Subscriber unsubscribed successfully');
            
        } catch (Exception $e) {
            error_log('Newsletter unsubscribe error: ' . $e->getMessage());
            $this->sendError('Unable to unsubscribe', 500);
        }
    }
}
?>
