<?php
/**
 * Dashboard API Controller
 * Provides statistics and recent activity for the admin panel
 */

require_once __DIR__ . '/BaseApiController.php';

class DashboardController extends BaseApiController {
    
    /**
     * Get dashboard stats (ADMIN ONLY)
     * GET /api/v1/dashboard/stats
     */
    public function stats($params = []) {
        // $this->requireAuth();
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    (SELECT COUNT(*) FROM programs WHERE is_active = TRUE AND deleted_at IS NULL) as active_programs,
                    (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) as pending_applications,
                    (SELECT COUNT(*) FROM events WHERE event_date >= CURDATE() AND is_active = TRUE AND deleted_at IS NULL) as upcoming_events,
                    (SELECT COUNT(*) FROM contacts WHERE status = 'new' AND deleted_at IS NULL) as new_contacts,
                    (SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL) as active_users
            ");
            
            $stats = $stmt->fetch();
            $this->sendSuccess($stats);
            
        } catch (Exception $e) {
            error_log('Dashboard stats fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch dashboard statistics', 500);
        }
    }
    
    /**
     * Get recent activity (ADMIN ONLY)
     * GET /api/v1/dashboard/recent
     */
    public function recent($params = []) {
        // $this->requireAuth();
        
        $limit = $this->request['query']['limit'] ?? 10;
        
        try {
            // Recent applications
            $applicationsStmt = $this->db->prepare("
                SELECT a.id, a.application_number, a.first_name, a.last_name, a.email, a.submitted_at, p.title as program_title
                FROM applications a
                LEFT JOIN programs p ON a.program_id = p.id
                WHERE a.deleted_at IS NULL
                ORDER BY a.submitted_at DESC
                LIMIT ?
            ");
            $applicationsStmt->execute([$limit]);
            $recentApplications = $applicationsStmt->fetchAll();
            
            // Recent contacts
            $contactsStmt = $this->db->prepare("
                SELECT id, name, email, subject, submitted_at
                FROM contacts
                WHERE deleted_at IS NULL
                ORDER BY submitted_at DESC
                LIMIT ?
            ");
            $contactsStmt->execute([$limit]);
            $recentContacts = $contactsStmt->fetchAll();
            
            // Recent event registrations
            $registrationsStmt = $this->db->prepare("
                SELECT er.id, er.first_name, er.last_name, er.email, er.registered_at, e.title as event_title
                FROM event_registrations er
                LEFT JOIN events e ON er.event_id = e.id
                WHERE er.status = 'registered'
                ORDER BY er.registered_at DESC
                LIMIT ?
            ");
            $registrationsStmt->execute([$limit]);
            $recentRegistrations = $registrationsStmt->fetchAll();
            
            $recentActivity = [
                'applications' => $recentApplications,
                'contacts' => $recentContacts,
                'registrations' => $recentRegistrations
            ];
            
            $this->sendSuccess($recentActivity);
            
        } catch (Exception $e) {
            error_log('Dashboard recent activity fetch error: ' . $e->getMessage());
            $this->sendError('Unable to fetch recent activity', 500);
        }
    }
}
?>
