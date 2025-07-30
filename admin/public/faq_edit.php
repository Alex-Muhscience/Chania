<?php
require_once '../includes/header.php';
require_once '../classes/Faq.php';
require_once '../../shared/Core/User.php';

$database = new Database();
$db = $database->connect();
$user = new User($db);

if (!$user->hasPermission($_SESSION['user_id'], 'faqs') && !$user->hasPermission($_SESSION['user_id'], '*')) {
    die('Access denied. You do not have permission to manage FAQs.');
}

$faq = new Faq($db);
$isEdit = false;
$faqData = [
    'question' => '',
    'answer' => '',
    'category' => 'General',
    'is_active' => 1,
    'display_order' => 0
];

if (isset($_GET['id'])) {
    $isEdit = true;
    $faqData = $faq->getById($_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'question' => $_POST['question'],
        'answer' => $_POST['answer'],
        'category' => $_POST['category'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'display_order' => (int)$_POST['display_order'],
        'created_by' => $_SESSION['user_id']
    ];

    if ($isEdit) {
        $faq->update($_GET['id'], $data);
    } else {
        $faq->create($data);
    }

    header('Location: faqs.php');
    exit;
}

$categories = $faq->getCategories();

?>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $isEdit ? 'Edit' : 'Add' ?> FAQ</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="faqs.php">FAQs</a></li>
        <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?> FAQ</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-question-circle me-1"></i>
            <?= $isEdit ? 'Edit' : 'Add' ?> FAQ
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="question" class="form-label">Question</label>
                    <input type="text" class="form-control" id="question" name="question" value="<?= htmlspecialchars($faqData['question']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="answer" class="form-label">Answer</label>
                    <textarea class="form-control" id="answer" name="answer" rows="5" required><?= htmlspecialchars($faqData['answer']) ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" value="<?= htmlspecialchars($faqData['category']) ?>" required list="category-list">
                            <datalist id="category-list">
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?= htmlspecialchars($cat) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order" value="<?= (int)$faqData['display_order'] ?>">
                        </div>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" <?= $faqData['is_active'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Create' ?> FAQ</button>
                <a href="faqs.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

