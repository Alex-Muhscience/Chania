<?php
class EventModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllEvents($limit = null, $offset = null) {
        $sql = "SELECT * FROM events ORDER BY event_date DESC";
        $params = [];

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;

            if ($offset !== null) {
                $sql .= " OFFSET ?";
                $params[] = $offset;
            }
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getUpcomingEvents($limit = 3) {
        $stmt = $this->db->prepare("
            SELECT * FROM events 
            WHERE event_date >= NOW() 
            ORDER BY event_date ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function countEvents() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM events");
        return $stmt->fetchColumn();
    }

    public function registerForEvent($eventId, $name, $email, $phone, $organization = null, $subscribe = false) {
        $stmt = $this->db->prepare("
            INSERT INTO event_registrations (
                event_id, name, email, phone, organization, 
                subscribe_newsletter, ip_address
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $eventId,
            $name,
            $email,
            $phone,
            $organization,
            $subscribe ? 1 : 0,
            $_SERVER['REMOTE_ADDR']
        ]);
    }
}
?>