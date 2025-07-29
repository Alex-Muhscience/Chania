<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class NewsletterService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Subscribe an email to the newsletter
     * @param string $email The email address to subscribe
     * @param string $name Optional name of the subscriber
     * @return array Response array with success status and message
     */
    public function subscribe($email, $name = '') {
        try {
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'Please provide a valid email address.'
                ];
            }
            
            // Check if email is already subscribed
            if ($this->isEmailSubscribed($email)) {
                return [
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.'
                ];
            }
            
            // Generate confirmation token
            $token = $this->generateToken();
            
            // Insert new subscriber
            $stmt = $this->db->prepare("
                INSERT INTO newsletter_subscribers (email, name, status, subscribed_at, token) 
                VALUES (?, ?, 'active', NOW(), ?)
            ");
            
            $result = $stmt->execute([
                $email,
                $name,
                $token
            ]);
            
            if ($result) {
                // Optional: Send welcome email (implement this later if needed)
                // $this->sendWelcomeEmail($email, $name);
                
                return [
                    'success' => true,
                    'message' => 'Thank you for subscribing! You\'ve been added to our newsletter.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to subscribe. Please try again later.'
                ];
            }
            
        } catch (Exception $e) {
            // Log the error (in production, you'd want proper logging)
            error_log("Newsletter subscription error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }
    
    /**
     * Check if an email is already subscribed
     * @param string $email The email to check
     * @return bool True if already subscribed, false otherwise
     */
    private function isEmailSubscribed($email) {
        $stmt = $this->db->prepare("
            SELECT id FROM newsletter_subscribers 
            WHERE email = ? AND status = 'active'
        ");
        $stmt->execute([$email]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Generate a random token for confirmation or unsubscribe purposes
     * @return string Random token
     */
    private function generateToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Unsubscribe an email from the newsletter
     * @param string $email The email to unsubscribe
     * @param string $token Optional token for verification
     * @return array Response array
     */
    public function unsubscribe($email, $token = null) {
        try {
            $sql = "UPDATE newsletter_subscribers SET status = 'unsubscribed', unsubscribed_at = NOW() WHERE email = ?";
            $params = [$email];
            
            // If token is provided, verify it
            if ($token) {
                $sql .= " AND token = ?";
                $params[] = $token;
            }
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'You have been successfully unsubscribed.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Unable to unsubscribe. Please check your email or contact support.'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Newsletter unsubscribe error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ];
        }
    }
    
    /**
     * Get subscriber count
     * @return int Number of active subscribers
     */
    public function getSubscriberCount() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM newsletter_subscribers 
                WHERE status = 'active'
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Newsletter subscriber count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Future: Send welcome email to new subscriber
     * @param string $email Subscriber email
     * @param string $name Subscriber name
     */
    private function sendWelcomeEmail($email, $name) {
        // This can be implemented later when email functionality is needed
        // For now, it's just a placeholder
    }
}
?>
