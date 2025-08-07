<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';

session_start();

// Require login
Utilities::requireLogin();

$eventId = intval($_GET['id'] ?? 0);
if (!$eventId) {
    $_SESSION['error'] = "Invalid event ID.";
    Utilities::redirect('/admin/public/events.php');
}

$pageTitle = "Edit Event";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/public/'],
    ['title' => 'Events', 'url' => BASE_URL . '/admin/public/events.php'],
    ['title' => 'Edit Event']
];

$errors = [];
$formData = [];

// Get event data
try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch();

    if (!$event) {
        $_SESSION['error'] = "Event not found.";
        Utilities::redirect('/admin/public/events.php');
    }

    $formData = [
        'title' => $event['title'],
        'description' => $event['description'],
        'event_date' => date('Y-m-d', strtotime($event['event_date'])),
        'start_time' => date('H:i', strtotime($event['event_date'])),
        'end_time' => $event['end_date'] ? date('H:i', strtotime($event['end_date'])) : '',
        'location' => $event['location'],
        'max_attendees' => $event['max_attendees'],
        'registration_deadline' => $event['registration_deadline'] ? date('Y-m-d\TH:i', strtotime($event['registration_deadline'])) : '',
        'is_active' => $event['is_active']
    ];

} catch (PDOException $e) {
    error_log("Event fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading event.";
    Utilities::redirect('/admin/public/events.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'event_date' => $_POST['event_date'] ?? '',
        'start_time' => $_POST['start_time'] ?? '',
        'end_time' => $_POST['end_time'] ?? '',
        'location' => trim($_POST['location'] ?? ''),
        'max_attendees' => intval($_POST['max_attendees'] ?? 0) ?: null,
        'registration_deadline' => $_POST['registration_deadline'] ?? '',
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    // Validation
    if (empty($formData['title'])) {
        $errors[] = "Event title is required.";
    }

    if (empty($formData['description'])) {
        $errors[] = "Event description is required.";
    }

    if (empty($formData['event_date'])) {
        $errors[] = "Event date is required.";
    }

    if (empty($formData['start_time'])) {
        $errors[] = "Start time is required.";
    }

    if (empty($formData['end_time'])) {
        $errors[] = "End time is required.";
    } elseif (!empty($formData['start_time']) && $formData['end_time'] <= $formData['start_time']) {
        $errors[] = "End time must be after start time.";
    }

    if (empty($formData['location'])) {
        $errors[] = "Location is required.";
    }

    if (!empty($formData['registration_deadline']) && strtotime($formData['registration_deadline']) > strtotime($formData['event_date'])) {
        $errors[] = "Registration deadline cannot be after event date.";
    }

    // Update event if no errors
    if (empty($errors)) {
        try {
            // Combine date and time for event_date and end_date
            $eventDateTime = $formData['event_date'] . ' ' . $formData['start_time'] . ':00';
            $endDateTime = !empty($formData['end_time']) ? $formData['event_date'] . ' ' . $formData['end_time'] . ':00' : null;
            
            $stmt = $db->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, end_date = ?, location = ?, max_attendees = ?, registration_deadline = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([
                $formData['title'],
                $formData['description'],
                $eventDateTime,
                $endDateTime,
                $formData['location'],
                $formData['max_attendees'],
                $formData['registration_deadline'] ?: null,
                $formData['is_active'],
                $eventId
            ]);

            $_SESSION['success'] = "Event updated successfully.";
            Utilities::redirect('/admin/public/events.php');

        } catch (PDOException $e) {
            error_log("Event update error: " . $e->getMessage());
            $errors[] = "Failed to update event. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Event</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($formData['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="event_date" class="form-label">Event Date *</label>
                                <input type="date" class="form-control" id="event_date" name="event_date"
                                       value="<?= htmlspecialchars($formData['event_date']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time"
                                       value="<?= htmlspecialchars($formData['start_time'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time"
                                       value="<?= htmlspecialchars($formData['end_time'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location *</label>
                        <input type="text" class="form-control" id="location" name="location"
                               value="<?= htmlspecialchars($formData['location']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_attendees" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_attendees" name="max_attendees"
                                       value="<?= $formData['max_attendees'] ?>" min="1">
                                <div class="form-text">Leave empty for unlimited participants</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                <input type="date" class="form-control" id="registration_deadline" name="registration_deadline"
                                       value="<?= htmlspecialchars($formData['registration_deadline'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                   <?= $formData['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/public/events.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Events
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>