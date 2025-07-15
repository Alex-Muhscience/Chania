<?php
require_once __DIR__ . '/../Models/EventModel.php';

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
        return $this->model->registerForEvent(
            $eventId,
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['organization'] ?? null,
            isset($data['newsletter'])
        );
    }
}
?>