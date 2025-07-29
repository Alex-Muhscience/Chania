<?php
/**
 * Client Activity Logger
 * 
 * Logs activities from the client-side (public website) to admin notifications
 */

require_once __DIR__ . '/../../shared/Core/Database.php';

class ClientActivityLogger
{
    private $db;
    private $ipAddress;
    private $userAgent;
    private $referrer;
    
    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->ipAddress = $this->getClientIpAddress();
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $this->referrer = $_SERVER['HTTP_REFERER'] ?? '';
    }
    
    /**
     * Log an application submission
     */
    public function logApplicationSubmission($applicationId, $userEmail, $programTitle)
    {
        return $this->logActivity(
            'application_submit',
            'application',
            $applicationId,
            $userEmail,
            [
                'program_title' => $programTitle,
                'submission_time' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log an event registration
     */
    public function logEventRegistration($registrationId, $userEmail, $eventTitle)
    {
        return $this->logActivity(
            'event_register',
            'event_registration',
            $registrationId,
            $userEmail,
            [
                'event_title' => $eventTitle,
                'registration_time' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log a contact form submission
     */
    public function logContactSubmission($contactId, $userEmail, $subject)
    {
        return $this->logActivity(
            'contact_submit',
            'contact',
            $contactId,
            $userEmail,
            [
                'subject' => $subject,
                'submission_time' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log newsletter subscription
     */
    public function logNewsletterSubscription($subscriberId, $userEmail)
    {
        return $this->logActivity(
            'newsletter_subscribe',
            'newsletter_subscriber',
            $subscriberId,
            $userEmail,
            [
                'subscription_time' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Log page view for important pages
     */
    public function logPageView($page, $userIdentifier = null)
    {
        return $this->logActivity(
            'page_view',
            'page',
            null,
            $userIdentifier ?: $this->ipAddress,
            [
                'page' => $page,
                'view_time' => date('Y-m-d H:i:s')
            ]
        );
    }
    
    /**
     * Generic activity logging method
     */
    private function logActivity($activityType, $entityType, $entityId, $userIdentifier, $activityData)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO client_activities (
                    activity_type, entity_type, entity_id, user_identifier, 
                    activity_data, ip_address, user_agent, referrer, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $activityType,
                $entityType,
                $entityId,
                $userIdentifier,
                json_encode($activityData),
                $this->ipAddress,
                $this->userAgent,
                $this->referrer
            ]);
            
        } catch (Exception $e) {
            error_log("ClientActivityLogger: Failed to log activity - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Also log to admin_logs for immediate admin visibility
     */
    public function logToAdminLogs($action, $entityType, $entityId, $details, $userIdentifier)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO admin_logs (
                    user_id, action, entity_type, entity_id, details, 
                    ip_address, user_agent, created_at, source
                ) VALUES (NULL, ?, ?, ?, ?, ?, ?, NOW(), 'client')
            ");
            
            return $stmt->execute([
                $action,
                $entityType,
                $entityId,
                $details . " (by: {$userIdentifier})",
                $this->ipAddress,
                $this->userAgent
            ]);
            
        } catch (Exception $e) {
            error_log("ClientActivityLogger: Failed to log to admin_logs - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIpAddress()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get recent client activities for admin dashboard
     */
    public static function getRecentActivities($limit = 10, $days = 7)
    {
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("
                SELECT 
                    ca.*,
                    CASE 
                        WHEN ca.activity_type = 'application_submit' THEN 
                            CONCAT('New application submitted for: ', JSON_UNQUOTE(JSON_EXTRACT(ca.activity_data, '$.program_title')))
                        WHEN ca.activity_type = 'event_register' THEN 
                            CONCAT('New registration for: ', JSON_UNQUOTE(JSON_EXTRACT(ca.activity_data, '$.event_title')))
                        WHEN ca.activity_type = 'contact_submit' THEN 
                            CONCAT('New contact message: ', JSON_UNQUOTE(JSON_EXTRACT(ca.activity_data, '$.subject')))
                        WHEN ca.activity_type = 'newsletter_subscribe' THEN 
                            'New newsletter subscription'
                        ELSE 'Website activity'
                    END as activity_description
                FROM client_activities ca
                WHERE ca.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY ca.created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$days, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("ClientActivityLogger: Failed to get recent activities - " . $e->getMessage());
            return [];
        }
    }
}
?>
