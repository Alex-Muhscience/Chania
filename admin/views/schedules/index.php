<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Schedule Management</h1>
        <a href="schedule_add.php" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Schedule
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Schedules</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Schedules</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Upcoming</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['upcoming']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Open for Registration</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['open_registration']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Schedules</h6>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($filters['search']); ?>" 
                           placeholder="Schedule title, program title, or location...">
                </div>
                <div class="col-md-3">
                    <label for="program" class="form-label">Program</label>
                    <select class="form-select" id="program" name="program">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['id']; ?>" <?php echo $filters['program'] == $program['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $filters['status'] === 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $filters['status'] === 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Program Schedules</h6>
        </div>
        <div class="card-body">
            <?php if (empty($schedules)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No schedules found</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Program</th>
                                <th>Schedule Title</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Delivery Mode</th>
                                <th>Location</th>
                                <th>Fees</th>
                                <th>Status</th>
                                <th>Registration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?= $schedule['id'] ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($schedule['program_title']) ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($schedule['title']) ?></td>
                                    <td><?= date('M j, Y', strtotime($schedule['start_date'])) ?></td>
                                    <td><?= $schedule['end_date'] ? date('M j, Y', strtotime($schedule['end_date'])) : 'N/A' ?></td>
                                    <td>
                                        <span class="badge bg-<?= $schedule['delivery_mode'] === 'online' ? 'primary' : 'info' ?>">
                                            <?= ucfirst($schedule['delivery_mode']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($schedule['location']) ?></td>
                                    <td>
                                        <small>
                                            Online: $<?= number_format($schedule['online_fee'], 2) ?><br>
                                            Physical: $<?= number_format($schedule['physical_fee'], 2) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                        $isExpired = $schedule['end_date'] && strtotime($schedule['end_date']) < time();
                                        $badgeClass = 'secondary';
                                        $statusText = 'Inactive';
                                        
                                        if ($isExpired) {
                                            $badgeClass = 'danger';
                                            $statusText = 'Expired';
                                        } elseif ($schedule['is_active']) {
                                            $badgeClass = 'success';
                                            $statusText = 'Active';
                                        }
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $schedule['is_open_for_registration'] ? 'success' : 'warning' ?>">
                                            <?= $schedule['is_open_for_registration'] ? 'Open' : 'Closed' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="schedule_edit.php?id=<?= $schedule['id'] ?>"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteSchedule(<?= $schedule['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav aria-label="Schedules pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($filters['search']) ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search']) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($filters['search']) ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        // You can implement AJAX deletion or form submission here
        window.location.href = 'schedule_delete.php?id=' + id;
    }
}
</script>
