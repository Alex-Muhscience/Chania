<?php
/**
 * Real-time Notifications using Server-Sent Events (SSE)
 * Provides live updates to admin panel for new submissions
 */

require_once __DIR__ . '/../../shared/Core/Environment.php';
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/ApiConfig.php';

Environment::load();

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Set execution time limit
set_time_limit(ApiConfig::SSE_MAX_EXECUTION_TIME);

try {
    $db = (new Database())->connect();
    $lastCheck = $_GET['lastCheck'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));
    
    // Function to send SSE data
    function sendSSEData($eventType, $data) {
        echo "event: {$eventType}\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }
    
    // Function to send heartbeat
    function sendHeartbeat() {
        echo "event: heartbeat\n";
        echo "data: " . json_encode(['timestamp' => date('Y-m-d H:i:s')]) . "\n\n";
        ob_flush();
        flush();
    }
    
    $heartbeatCounter = 0;
    
    while (true) {
        // Send heartbeat every few iterations
        if ($heartbeatCounter % ApiConfig::SSE_HEARTBEAT_INTERVAL === 0) {
            sendHeartbeat();
        }
        
        // Check for new contacts
        $stmt = $db->prepare("
            SELECT id, name, email, subject, submitted_at, status 
            FROM contacts 
            WHERE submitted_at > ? AND deleted_at IS NULL 
            ORDER BY submitted_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$lastCheck]);
        $newContacts = $stmt->fetchAll();
        
        foreach ($newContacts as $contact) {
            $notification = ApiConfig::createNotification(
                ApiConfig::NOTIFICATION_CONTACT,
                $contact['id'],
                "New contact from {$contact['name']}: {$contact['subject']}",
                $contact['email'],
                $contact['submitted_at'],
                [
                    'url' => '/admin/contacts.php?id=' . $contact['id'],
                    'status' => $contact['status']
                ]
            );
            sendSSEData('new_contact', $notification);
        }
        
        // Check for new applications
        $stmt = $db->prepare("
            SELECT a.id, a.first_name, a.last_name, a.email, a.submitted_at, a.status, p.title as program_title
            FROM applications a 
            LEFT JOIN programs p ON a.program_id = p.id
            WHERE a.submitted_at > ? AND a.deleted_at IS NULL 
            ORDER BY a.submitted_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$lastCheck]);
        $newApplications = $stmt->fetchAll();
        
        foreach ($newApplications as $application) {
            $notification = ApiConfig::createNotification(
                ApiConfig::NOTIFICATION_APPLICATION,
                $application['id'],
                "New application from {$application['first_name']} {$application['last_name']} for {$application['program_title']}",
                $application['email'],
                $application['submitted_at'],
                [
                    'url' => '/admin/applications.php?id=' . $application['id'],
                    'status' => $application['status'],
                    'program' => $application['program_title']
                ]
            );
            sendSSEData('new_application', $notification);
        }
        
        // Check for new event registrations
        $stmt = $db->prepare("
            SELECT er.id, er.first_name, er.last_name, er.email, er.registered_at, er.status, e.title as event_title
            FROM event_registrations er 
            LEFT JOIN events e ON er.event_id = e.id
            WHERE er.registered_at > ? 
            ORDER BY er.registered_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$lastCheck]);
        $newRegistrations = $stmt->fetchAll();
        
        foreach ($newRegistrations as $registration) {
            $notification = ApiConfig::createNotification(
                ApiConfig::NOTIFICATION_EVENT,
                $registration['id'],
                "New event registration from {$registration['first_name']} {$registration['last_name']} for {$registration['event_title']}",
                $registration['email'],
                $registration['registered_at'],
                [
                    'url' => '/admin/public/event_registrations.php',
                    'status' => $registration['status'],
                    'event' => $registration['event_title']
                ]
            );
            sendSSEData('new_event_registration', $notification);
        }
        
        // Check for new newsletter subscriptions
        $stmt = $db->prepare("
            SELECT id, name, email, subscribed_at, status 
            FROM newsletter_subscribers 
            WHERE subscribed_at > ? 
            ORDER BY subscribed_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$lastCheck]);
        $newSubscriptions = $stmt->fetchAll();
        
        foreach ($newSubscriptions as $subscription) {
            $notification = ApiConfig::createNotification(
                ApiConfig::NOTIFICATION_NEWSLETTER,
                $subscription['id'],
                "New newsletter subscription from " . ($subscription['name'] ?: $subscription['email']),
                $subscription['email'],
                $subscription['subscribed_at'],
                [
                    'status' => $subscription['status']
                ]
            );
            sendSSEData('new_newsletter_subscription', $notification);
        }
        
        // Update last check time
        $lastCheck = date('Y-m-d H:i:s');
        
        // Send updated statistics
        $stats = [];
        
        // Get dashboard stats
        $stmt = $db->query("
            SELECT 
                (SELECT COUNT(*) FROM contacts WHERE status = 'new' AND deleted_at IS NULL) as new_contacts,
                (SELECT COUNT(*) FROM applications WHERE status = 'pending' AND deleted_at IS NULL) as pending_applications,
                (SELECT COUNT(*) FROM event_registrations WHERE status = 'registered') as new_registrations,
                (SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'subscribed') as active_subscribers
        ");
        $stats = $stmt->fetch();
        
        sendSSEData('dashboard_stats', $stats);
        
        $heartbeatCounter++;
        sleep(1); // Check every second
        
        // Break if connection is closed
        if (connection_aborted()) {
            break;
        }
    }
    
} catch (Exception $e) {
    error_log('SSE Error: ' . $e->getMessage());
    sendSSEData('error', ['message' => 'Connection error occurred']);
}
?>
