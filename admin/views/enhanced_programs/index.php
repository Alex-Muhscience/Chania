<h1>Enhanced Programs Management</h1>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Duration</th>
                <th>Fee</th>
                <th>Schedules</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($programs)): ?>
                <tr>
                    <td colspan="8" class="text-center">No programs found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($programs as $program): ?>
                    <tr>
                        <td><?= $program['id'] ?></td>
                        <td><?= htmlspecialchars($program['title']) ?></td>
                        <td><?= htmlspecialchars(substr($program['description'], 0, 50)) ?></td>
                        <td><?= htmlspecialchars($program['duration']) ?></td>
                        <td><?= $program['fee'] ?: 'Free' ?></td>
                        <td><?= $program['schedule_count'] ?></td>
                        <td><?= $program['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <td>
                            <a href="program_edit.php?id=<?= $program['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
