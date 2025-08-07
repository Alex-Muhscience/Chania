<?php
require_once __DIR__ . '/../../shared/Core/Database.php';
require_once __DIR__ . '/../../shared/Core/Utilities.php';

class AchievementsController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function index() {
        $pageTitle = 'Achievements & Statistics';
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Achievements & Statistics']
        ];

        try {
            $stmt = $this->db->query("
                SELECT * FROM achievements 
                WHERE deleted_at IS NULL 
                ORDER BY display_order ASC, created_at DESC
            ");
            $achievements = $stmt->fetchAll();

            require_once __DIR__ . '/../views/achievements/index.php';
            
        } catch (PDOException $e) {
            error_log("Error fetching achievements: " . $e->getMessage());
            $error = "Failed to load achievements.";
            require_once __DIR__ . '/../views/achievements/index.php';
        }
    }

    public function add() {
        $pageTitle = 'Add Achievement';
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Achievements', 'url' => BASE_URL . '/admin/public/achievements.php'],
            ['title' => 'Add Achievement']
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO achievements (
                        title, description, stat_value, stat_unit, icon, 
                        display_order, is_active, is_featured, category,
                        created_by, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                $display_order = $_POST['display_order'] ?? 0;
                if (empty($display_order)) {
                    // Get next display order
                    $orderStmt = $this->db->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM achievements");
                    $display_order = $orderStmt->fetch()['next_order'];
                }

                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['stat_value'],
                    $_POST['stat_unit'] ?? '',
                    $_POST['icon'] ?? 'fas fa-trophy',
                    $display_order,
                    isset($_POST['is_active']) ? 1 : 0,
                    isset($_POST['is_featured']) ? 1 : 0,
                    $_POST['category'] ?? 'general',
                    $_SESSION['user_id']
                ]);

                header('Location: ' . BASE_URL . '/admin/public/achievements.php?added=1');
                exit;

            } catch (PDOException $e) {
                error_log("Error adding achievement: " . $e->getMessage());
                $error = "Failed to add achievement.";
            }
        }

        require_once __DIR__ . '/../views/achievements/add.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Invalid achievement ID');
            exit;
        }

        $pageTitle = 'Edit Achievement';
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => BASE_URL . '/admin/'],
            ['title' => 'Achievements', 'url' => BASE_URL . '/admin/public/achievements.php'],
            ['title' => 'Edit Achievement']
        ];

        try {
            // Get achievement
            $stmt = $this->db->prepare("SELECT * FROM achievements WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$id]);
            $achievement = $stmt->fetch();

            if (!$achievement) {
                header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Achievement not found');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $this->db->prepare("
                    UPDATE achievements SET
                        title = ?, description = ?, stat_value = ?, stat_unit = ?, icon = ?,
                        display_order = ?, is_active = ?, is_featured = ?, category = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['stat_value'],
                    $_POST['stat_unit'] ?? '',
                    $_POST['icon'] ?? 'fas fa-trophy',
                    $_POST['display_order'] ?? $achievement['display_order'],
                    isset($_POST['is_active']) ? 1 : 0,
                    isset($_POST['is_featured']) ? 1 : 0,
                    $_POST['category'] ?? 'general',
                    $id
                ]);

                header('Location: ' . BASE_URL . '/admin/public/achievements.php?updated=1');
                exit;
            }

        } catch (PDOException $e) {
            error_log("Error editing achievement: " . $e->getMessage());
            $error = "Failed to load/update achievement.";
        }

        require_once __DIR__ . '/../views/achievements/edit.php';
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Invalid achievement ID');
            exit;
        }

        try {
            // Soft delete
            $stmt = $this->db->prepare("UPDATE achievements SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);

            header('Location: ' . BASE_URL . '/admin/public/achievements.php?deleted=1');
            exit;

        } catch (PDOException $e) {
            error_log("Error deleting achievement: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Failed to delete achievement');
            exit;
        }
    }

    public function toggleStatus() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Invalid achievement ID');
            exit;
        }

        try {
            // Toggle active status
            $stmt = $this->db->prepare("
                UPDATE achievements 
                SET is_active = NOT is_active, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            header('Location: ' . BASE_URL . '/admin/public/achievements.php?status_updated=1');
            exit;

        } catch (PDOException $e) {
            error_log("Error toggling achievement status: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Failed to update status');
            exit;
        }
    }

    public function toggleFeatured() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Invalid achievement ID');
            exit;
        }

        try {
            // Toggle featured status
            $stmt = $this->db->prepare("
                UPDATE achievements 
                SET is_featured = NOT is_featured, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            header('Location: ' . BASE_URL . '/admin/public/achievements.php?featured_updated=1');
            exit;

        } catch (PDOException $e) {
            error_log("Error toggling achievement featured status: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/admin/public/achievements.php?error=Failed to update featured status');
            exit;
        }
    }
}
?>
