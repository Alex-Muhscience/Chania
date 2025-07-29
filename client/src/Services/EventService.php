<?php
require_once __DIR__ . '/../Models/EventModel.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class EventService {
    private $model;

    public function __construct($db) {
        $this->model = new EventModel($db);
    }

    public function getPaginatedEvents($page = 1, $perPage = 6) {
        $offset = ($page - 1) * $perPage;
        $events = $this->model->getAllEvents($perPage, $offset);
        $total = $this->model->countEvents();
        $totalPages = ceil($total / $perPage);

        return [
            'events' => $events,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ];
    }

    public function getUpcomingEvents($limit = 3) {
        return $this->model->getUpcomingEvents($limit);
    }

    public function getEventDetails($id) {
        return $this->model->getEventById($id);
    }

    public function registerForEvent($eventId, $data) {
$result = $this->model->registerForEvent(
            $eventId,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['organization'] ?? null,
            isset($data['newsletter'])
        );

        // Log activity
        if ($result) {
            Utilities::logActivity([
                'user_id' => null, // or replace with actual user ID if available
                'action' => 'Register for Event',
                'entity_type' => 'Event Registration',
                'entity_id' => $eventId,
                'details' => json_encode($data),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            ]);
        }

        return $result;
    }
}
?>