<?php

class ProgramSchedule {
    private $conn;
    private $table = 'program_schedules';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all schedules with program information
    public function getAll($limit = null, $offset = 0, $search = '', $programId = null, $status = null) {
        $sql = "SELECT ps.*, p.title as program_title, p.description as program_description 
                FROM {$this->table} ps 
                LEFT JOIN programs p ON ps.program_id = p.id 
                WHERE ps.deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (ps.title LIKE ? OR p.title LIKE ? OR ps.location LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if ($programId !== null) {
            $sql .= " AND ps.program_id = ?";
            $params[] = $programId;
        }

        if ($status !== null) {
            $sql .= " AND ps.is_active = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ps.start_date DESC";

        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total count for pagination
    public function getTotalCount($search = '', $programId = null, $status = null) {
        $sql = "SELECT COUNT(*) FROM {$this->table} ps 
                LEFT JOIN programs p ON ps.program_id = p.id 
                WHERE ps.deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (ps.title LIKE ? OR p.title LIKE ? OR ps.location LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if ($programId !== null) {
            $sql .= " AND ps.program_id = ?";
            $params[] = $programId;
        }

        if ($status !== null) {
            $sql .= " AND ps.is_active = ?";
            $params[] = $status;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // Get a single schedule by ID
    public function getById($id) {
        $sql = "SELECT ps.*, p.title as program_title 
                FROM {$this->table} ps 
                LEFT JOIN programs p ON ps.program_id = p.id 
                WHERE ps.id = ? AND ps.deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get schedules by program ID
    public function getByProgram($programId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE program_id = ? AND deleted_at IS NULL 
                ORDER BY start_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$programId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new schedule
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (
                    program_id, title, start_date, end_date, location, 
                    delivery_mode, max_participants, online_fee, physical_fee, 
                    registration_deadline, instructor_name, session_notes, 
                    meeting_link, venue_address, is_active, is_open_for_registration
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['program_id'],
            $data['title'] ?? 'Session',
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['location'] ?? 'Online',
            $data['delivery_mode'] ?? 'online',
            $data['max_participants'] ?? null,
            $data['online_fee'] ?? 0.00,
            $data['physical_fee'] ?? 0.00,
            $data['registration_deadline'] ?? null,
            $data['instructor_name'] ?? null,
            $data['session_notes'] ?? null,
            $data['meeting_link'] ?? null,
            $data['venue_address'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            isset($data['is_open_for_registration']) ? (int)$data['is_open_for_registration'] : 1
        ]);
    }

    // Update a schedule
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET 
                    program_id = ?, title = ?, start_date = ?, end_date = ?, 
                    location = ?, delivery_mode = ?, max_participants = ?, 
                    online_fee = ?, physical_fee = ?, registration_deadline = ?, 
                    instructor_name = ?, session_notes = ?, meeting_link = ?, 
                    venue_address = ?, is_active = ?, is_open_for_registration = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['program_id'],
            $data['title'] ?? 'Session',
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['location'] ?? 'Online',
            $data['delivery_mode'] ?? 'online',
            $data['max_participants'] ?? null,
            $data['online_fee'] ?? 0.00,
            $data['physical_fee'] ?? 0.00,
            $data['registration_deadline'] ?? null,
            $data['instructor_name'] ?? null,
            $data['session_notes'] ?? null,
            $data['meeting_link'] ?? null,
            $data['venue_address'] ?? null,
            isset($data['is_active']) ? (int)$data['is_active'] : 1,
            isset($data['is_open_for_registration']) ? (int)$data['is_open_for_registration'] : 1,
            $id
        ]);
    }

    // Soft delete a schedule
    public function delete($id) {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Get schedule statistics
    public function getStatistics() {
        $stats = [];

        // Total schedules
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL");
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();

        // Active schedules
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE is_active = 1 AND deleted_at IS NULL");
        $stmt->execute();
        $stats['active'] = $stmt->fetchColumn();

        // Upcoming schedules
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE start_date > NOW() AND deleted_at IS NULL");
        $stmt->execute();
        $stats['upcoming'] = $stmt->fetchColumn();

        // Open for registration
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE is_open_for_registration = 1 AND deleted_at IS NULL");
        $stmt->execute();
        $stats['open_registration'] = $stmt->fetchColumn();

        return $stats;
    }

    // Toggle schedule status
    public function toggleStatus($id) {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Toggle registration status
    public function toggleRegistration($id) {
        $sql = "UPDATE {$this->table} SET is_open_for_registration = NOT is_open_for_registration WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Auto-archive expired schedules
    public function archiveExpiredSchedules() {
        $sql = "UPDATE {$this->table} SET 
                    is_active = 0, 
                    is_open_for_registration = 0, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE end_date < CURDATE() 
                AND is_active = 1 
                AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    // Auto-delete schedules that have been expired for a long time (optional)
    public function deleteOldExpiredSchedules($daysAfterExpiry = 90) {
        $sql = "UPDATE {$this->table} SET 
                    deleted_at = CURRENT_TIMESTAMP
                WHERE end_date < DATE_SUB(CURDATE(), INTERVAL ? DAY) 
                AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$daysAfterExpiry]);
    }

    // Get expired schedules count
    public function getExpiredSchedulesCount() {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE end_date < CURDATE() 
                AND is_active = 1 
                AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Get schedules expiring soon (within next 7 days)
    public function getExpiringSoonSchedules($days = 7) {
        $sql = "SELECT ps.*, p.title as program_title 
                FROM {$this->table} ps 
                LEFT JOIN programs p ON ps.program_id = p.id 
                WHERE ps.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND ps.is_active = 1 
                AND ps.deleted_at IS NULL
                ORDER BY ps.end_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Close registration for schedules where registration deadline has passed
    public function closeExpiredRegistrations() {
        $sql = "UPDATE {$this->table} SET 
                    is_open_for_registration = 0, 
                    updated_at = CURRENT_TIMESTAMP
                WHERE registration_deadline < CURDATE() 
                AND is_open_for_registration = 1 
                AND deleted_at IS NULL";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    // Run all automated maintenance tasks
    public function runAutomatedMaintenance() {
        $results = [];
        
        // Close expired registrations
        $results['closed_registrations'] = $this->closeExpiredRegistrations();
        
        // Archive expired schedules
        $results['archived_schedules'] = $this->archiveExpiredSchedules();
        
        // Optionally delete very old schedules (uncomment if needed)
        // $results['deleted_old_schedules'] = $this->deleteOldExpiredSchedules(90);
        
        return $results;
    }
}

?>
