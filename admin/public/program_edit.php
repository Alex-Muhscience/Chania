<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../classes/ProgramCategory.php';

session_start();

// Require login
Utilities::requireLogin();

$programId = intval($_GET['id'] ?? 0);
if (!$programId) {
    $_SESSION['error'] = "Invalid program ID.";
    Utilities::redirect('/admin/programs.php');
}

$pageTitle = "Edit Program";
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
    ['title' => 'Programs', 'url' => BASE_URL . '/admin/programs.php'],
    ['title' => 'Edit Program']
];

$errors = [];
$formData = [];

// Get program data
try {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$programId]);
    $program = $stmt->fetch();

    if (!$program) {
        $_SESSION['error'] = "Program not found.";
        Utilities::redirect('/admin/programs.php');
    }

    $formData = [
        'title' => $program['title'],
        'description' => $program['description'],
        'duration' => $program['duration'],
        'fee' => $program['fee'],
        'start_date' => $program['start_date'],
        'end_date' => $program['end_date'],
        'max_participants' => $program['max_participants'],
        'is_active' => $program['is_active'],
        'category_id' => $program['category_id'] ?? null
    ];

    // Get all categories
    $programCategory = new ProgramCategory($db);
    $categories = $programCategory->getAll();

} catch (PDOException $e) {
    error_log("Program fetch error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading program.";
    Utilities::redirect('/admin/programs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'duration' => trim($_POST['duration'] ?? ''),
        'fee' => floatval($_POST['fee'] ?? 0),
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'max_participants' => intval($_POST['max_participants'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'category_id' => $_POST['category_id'] ?: null
    ];

    // Validation
    if (empty($formData['title'])) {
        $errors[] = "Program title is required.";
    }

    if (empty($formData['description'])) {
        $errors[] = "Program description is required.";
    }

    if (empty($formData['duration'])) {
        $errors[] = "Duration is required.";
    }

    if ($formData['fee'] < 0) {
        $errors[] = "Fee cannot be negative.";
    }

    if (!empty($formData['start_date']) && !empty($formData['end_date'])) {
        if (strtotime($formData['end_date']) <= strtotime($formData['start_date'])) {
            $errors[] = "End date must be after start date.";
        }
    }

    // Update program if no errors
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("UPDATE programs SET title = ?, description = ?, duration = ?, fee = ?, start_date = ?, end_date = ?, max_participants = ?, is_active = ?, category_id = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([
                $formData['title'],
                $formData['description'],
                $formData['duration'],
                $formData['fee'],
                $formData['start_date'] ?: null,
                $formData['end_date'] ?: null,
                $formData['max_participants'] ?: null,
                $formData['is_active'],
                $formData['category_id'],
                $programId
            ]);

            $_SESSION['success'] = "Program updated successfully.";
            Utilities::redirect('/admin/programs.php');

        } catch (PDOException $e) {
            error_log("Program update error: " . $e->getMessage());
            $errors[] = "Failed to update program. Please try again.";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Program</h5>
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
                        <label for="title" class="form-label">Program Title *</label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= htmlspecialchars($formData['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id">
                            <option value="">Select a category (optional)</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $formData['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Assign this program to a category for better organization.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration *</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                       value="<?= htmlspecialchars($formData['duration']) ?>"
                                       placeholder="e.g., 6 weeks, 3 months" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fee" class="form-label">Fee ($)</label>
                                <input type="number" class="form-control" id="fee" name="fee"
                                       value="<?= $formData['fee'] ?>" min="0" step="0.01">
                                <div class="form-text">Enter 0 for free programs</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?= htmlspecialchars($formData['start_date']) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?= htmlspecialchars($formData['end_date']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_participants" class="form-label">Max Participants</label>
                        <input type="number" class="form-control" id="max_participants" name="max_participants"
                               value="<?= $formData['max_participants'] ?>" min="1">
                        <div class="form-text">Leave empty for unlimited participants</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                   <?= $formData['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/public/programs.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Programs
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>