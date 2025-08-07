<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Applications Management</h1>
        <p class="text-muted mb-0">Manage and assign applications to cohorts</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>../../public/applications_dashboard.php" class="btn btn-outline-info">
            <i class="fas fa-chart-bar"></i> Dashboard
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download"></i> Export
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/applications_export.php?format=csv<?= http_build_query(($filters ?? []) ? ['&'] + ($filters ?? []) : []) ?>">CSV Export</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/public/applications_export.php?format=pdf<?= http_build_query(($filters ?? []) ? ['&'] + ($filters ?? []) : []) ?>">PDF Export</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Search and Advanced Filters -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Search & Filters</h5>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
            <i class="fas fa-filter"></i> Advanced Filters
        </button>
    </div>
    <div class="card-body">
        <form method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Name, email, or application number...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="under_review" <?= $status === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="submitted_at" <?= ($sort ?? 'submitted_at') === 'submitted_at' ? 'selected' : '' ?>>Date Submitted</option>
                        <option value="first_name" <?= ($sort ?? '') === 'first_name' ? 'selected' : '' ?>>Name</option>
                        <option value="status" <?= ($sort ?? '') === 'status' ? 'selected' : '' ?>>Status</option>
                        <option value="program_title" <?= ($sort ?? '') === 'program_title' ? 'selected' : '' ?>>Program</option>
                        <option value="schedule_title" <?= ($sort ?? '') === 'schedule_title' ? 'selected' : '' ?>>Cohort</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <select name="order" class="form-select">
                        <option value="DESC" <?= ($order ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>Newest First</option>
                        <option value="ASC" <?= ($order ?? '') === 'ASC' ? 'selected' : '' ?>>Oldest First</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="<?= BASE_URL ?>../../public/applications.php" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Filters -->
            <div class="collapse mt-3" id="advancedFilters">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select">
                            <option value="">All Programs</option>
                            <?php foreach (($programs ?? []) as $prog): ?>
                                <option value="<?= $prog['id'] ?>" <?= ($program ?? '') === (string)$prog['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prog['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cohort</label>
                        <select name="cohort" class="form-select">
                            <option value="">All Cohorts</option>
                            <option value="unassigned" <?= ($cohort ?? '') === 'unassigned' ? 'selected' : '' ?>>Not Assigned</option>
                            <?php foreach (($schedules ?? []) as $schedule): ?>
                                <option value="<?= $schedule['id'] ?>" <?= ($cohort ?? '') === (string)$schedule['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($schedule['title']) ?> (<?= htmlspecialchars($schedule['program_title']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Delivery Mode</label>
                        <select name="delivery_mode" class="form-select">
                            <option value="">All Modes</option>
                            <option value="online" <?= ($delivery_mode ?? '') === 'online' ? 'selected' : '' ?>>Online</option>
                            <option value="physical" <?= ($delivery_mode ?? '') === 'physical' ? 'selected' : '' ?>>Physical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Per Page</label>
                        <select name="limit" class="form-select">
                            <option value="10" <?= ($pagination['limit'] ?? 10) == 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= ($pagination['limit'] ?? 10) == 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= ($pagination['limit'] ?? 10) == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= ($pagination['limit'] ?? 10) == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($applications ?? [])): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No applications found</p>
            </div>
        <?php else: ?>
            <!-- Bulk Operations -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">Showing <?= count($applications ?? []) ?> of <?= number_format($pagination['totalApplications'] ?? 0) ?> applications</span>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-info" id="bulkAssignBtn" disabled>
                        <i class="fas fa-users"></i> Bulk Assign Cohort
                    </button>
                    <button class="btn btn-sm btn-outline-success" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check"></i> Bulk Approve
                    </button>
                    <button class="btn btn-sm btn-outline-warning" id="bulkRejectBtn" disabled>
                        <i class="fas fa-times"></i> Bulk Reject
                    </button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped no-datatables">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Program</th>
                            <th>Schedule/Cohort</th>
                            <th>Delivery Mode</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($applications ?? []) as $application): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_applications[]" value="<?= $application['id'] ?>" class="form-check-input application-checkbox">
                                </td>
                                <td><?= $application['id'] ?? 'N/A' ?></td>
                                <td><?= htmlspecialchars($application['full_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($application['email'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($application['program_title'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if ($application['schedule_title'] ?? false): ?>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-info mb-1"><?= htmlspecialchars($application['schedule_title']) ?></span>
                                            <small class="text-muted">
                                                <?= date('M j', strtotime($application['schedule_start_date'])) ?> - <?= date('M j, Y', strtotime($application['schedule_end_date'])) ?>
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $mode = $application['preferred_delivery_mode'] ?? 'online';
                                    $modeClass = [
                                        'online' => 'bg-primary',
                                        'physical' => 'bg-success'
                                    ][$mode] ?? 'bg-primary';
                                    ?>
                                    <span class="badge <?= $modeClass ?>"><?= ucfirst($mode) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($application['status'] ?? 'pending') === 'approved' ? 'success' : (($application['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($application['status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td><?= isset($application['submitted_at']) && $application['submitted_at'] ? date('M j, Y', strtotime($application['submitted_at'])) : 'N/A' ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASE_URL ?>\admin\public\application_view.php?id=<?= $application['id'] ?? '' ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle"
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="application_id" value="<?= $application['id'] ?? '' ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-check text-success"></i> Approve
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="update_status">
                                                        <input type="hidden" name="application_id" value="<?= $application['id'] ?? '' ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="fas fa-times text-danger"></i> Reject
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal" data-bs-target="#assignCohortModal" 
                                                data-app-id="<?= $application['id'] ?? '' ?>"
                                                data-app-name="<?= htmlspecialchars($application['full_name'] ?? '') ?>"
                                                data-program-id="<?= $application['program_id'] ?? '' ?>"
                                                title="Assign to Cohort">
                                            <i class="fas fa-users"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteApplication(<?= $application['id'] ?? '' ?>)">
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
            <?php if (($pagination['totalPages'] ?? 0) > 1): ?>
                <nav aria-label="Applications pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['page'] - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                            <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $pagination['page'] + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Cohort Modal -->
<div class="modal fade" id="assignCohortModal" tabindex="-1" aria-labelledby="assignCohortModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignCohortModalLabel">Assign to Cohort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignCohortForm">
                    <input type="hidden" id="applicationId" name="application_id">
                    <input type="hidden" id="programId" name="program_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Applicant:</label>
                        <p class="fw-bold" id="applicantName"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="scheduleSelect" class="form-label">Select Cohort/Schedule:</label>
                        <select class="form-select" id="scheduleSelect" name="schedule_id" required>
                            <option value="">Loading schedules...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deliveryModeSelect" class="form-label">Delivery Mode:</label>
                        <select class="form-select" id="deliveryModeSelect" name="preferred_delivery_mode">
                            <option value="online">Online</option>
                            <option value="physical">Physical</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAssignment">Assign to Cohort</button>
            </div>
        </div>
    </div>
</div>

<script>
// Success and Error message display functions
function showSuccessMessage(message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-success');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create success alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

function showErrorMessage(message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create error alert
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at top of page
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Handle cohort assignment modal
document.addEventListener('DOMContentLoaded', function() {
    const assignModal = document.getElementById('assignCohortModal');
    const assignForm = document.getElementById('assignCohortForm');
    const saveBtn = document.getElementById('saveAssignment');
    
    assignModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const appId = button.getAttribute('data-app-id');
        const appName = button.getAttribute('data-app-name');
        const programId = button.getAttribute('data-program-id');
        
        // Set form values
        document.getElementById('applicationId').value = appId;
        document.getElementById('programId').value = programId;
        document.getElementById('applicantName').textContent = appName;
        
        // Load schedules for this program
        loadSchedules(programId);
    });
    
    function loadSchedules(programId) {
        const scheduleSelect = document.getElementById('scheduleSelect');
        scheduleSelect.innerHTML = '<option value="">Loading schedules...</option>';
        
        fetch(`<?= BASE_URL ?>/admin/api/get_schedules.php?program_id=${programId}`)
            .then(response => response.json())
            .then(data => {
                scheduleSelect.innerHTML = '<option value="">Select a cohort...</option>';
                
                if (data.success && data.schedules) {
                    data.schedules.forEach(schedule => {
                        const option = document.createElement('option');
                        option.value = schedule.id;
                        option.textContent = `${schedule.title} (${schedule.start_date} - ${schedule.end_date})`;
                        scheduleSelect.appendChild(option);
                    });
                } else {
                    scheduleSelect.innerHTML = '<option value="">No schedules available</option>';
                }
            })
            .catch(error => {
                console.error('Error loading schedules:', error);
                scheduleSelect.innerHTML = '<option value="">Error loading schedules</option>';
            });
    }
    
    saveBtn.addEventListener('click', function() {
        const formData = new FormData(assignForm);
        formData.append('action', 'assign_cohort');
        
        // Debug logging
        console.log('Submitting cohort assignment:', {
            application_id: formData.get('application_id'),
            schedule_id: formData.get('schedule_id'),
            preferred_delivery_mode: formData.get('preferred_delivery_mode'),
            action: formData.get('action')
        });
        
        fetch('<?= BASE_URL ?>/admin/public/applications.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text(); // Get as text first to see what we're getting
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);
                
                if (data.success) {
                    showSuccessMessage('Application assigned to cohort successfully!');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Check for authentication error with redirect
                    if (data.redirect) {
                        showErrorMessage('Session expired. Redirecting to login page...');
                        setTimeout(() => window.location.href = data.redirect, 2000);
                    } else {
                        showErrorMessage('Error: ' + (data.message || 'An unknown error occurred.'));
                    }
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
                showErrorMessage('Invalid response from server. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showErrorMessage('Network error: ' + error.message);
        });
        
        const modal = bootstrap.Modal.getInstance(assignModal);
        modal.hide();
    });
    
    // Bulk Operations Functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const applicationCheckboxes = document.querySelectorAll('.application-checkbox');
    const bulkButtons = {
        assign: document.getElementById('bulkAssignBtn'),
        approve: document.getElementById('bulkApproveBtn'),
        reject: document.getElementById('bulkRejectBtn')
    };
    
    // Select All functionality
    selectAllCheckbox.addEventListener('change', function() {
        applicationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkButtonStates();
    });
    
    // Individual checkbox change
    applicationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtonStates);
    });
    
    function updateBulkButtonStates() {
        const checkedBoxes = document.querySelectorAll('.application-checkbox:checked');
        const hasSelection = checkedBoxes.length > 0;
        
        // Enable/disable bulk buttons
        Object.values(bulkButtons).forEach(button => {
            button.disabled = !hasSelection;
        });
        
        // Update select all checkbox state
        selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < applicationCheckboxes.length;
        selectAllCheckbox.checked = checkedBoxes.length === applicationCheckboxes.length && applicationCheckboxes.length > 0;
    }
    
    // Bulk approve
    bulkButtons.approve.addEventListener('click', function() {
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) return;
        
        if (confirm(`Are you sure you want to approve ${selectedIds.length} application(s)?`)) {
            bulkUpdateStatus(selectedIds, 'approved');
        }
    });
    
    // Bulk reject
    bulkButtons.reject.addEventListener('click', function() {
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) return;
        
        if (confirm(`Are you sure you want to reject ${selectedIds.length} application(s)?`)) {
            bulkUpdateStatus(selectedIds, 'rejected');
        }
    });
    
    // Bulk assign cohort
    bulkButtons.assign.addEventListener('click', function() {
        const selectedIds = getSelectedApplicationIds();
        if (selectedIds.length === 0) return;
        
        showBulkAssignModal(selectedIds);
    });
    
    function getSelectedApplicationIds() {
        return Array.from(document.querySelectorAll('.application-checkbox:checked'))
                   .map(checkbox => checkbox.value);
    }
    
    function bulkUpdateStatus(applicationIds, status) {
        // Show loading state
        const button = status === 'approved' ? bulkButtons.approve : bulkButtons.reject;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        button.disabled = true;
        
        // Use single request with bulk action
        const formData = new FormData();
        const actionName = status === 'approved' ? 'bulk_approve' : 'bulk_reject';
        formData.append('action', actionName);
        applicationIds.forEach(id => formData.append('ids[]', id));
        
        fetch(`<?= BASE_URL ?>/admin/public/applications.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message with nice formatting
                showSuccessMessage(data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Bulk update error:', error);
            showErrorMessage('An error occurred during bulk update');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    function showBulkAssignModal(applicationIds) {
        // Create and show bulk assign modal
        const modalHtml = `
            <div class="modal fade" id="bulkAssignModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk Assign to Cohort</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Assign <strong>${applicationIds.length}</strong> selected applications to a cohort.</p>
                            <div class="mb-3">
                                <label class="form-label">Select Schedule/Cohort:</label>
                                <select class="form-select" id="bulkScheduleSelect" required>
                                    <option value="">Select a cohort...</option>
                                    <?php foreach ($schedules as $schedule): ?>
                                        <option value="<?= $schedule['id'] ?>">
                                            <?= htmlspecialchars($schedule['title']) ?> (<?= htmlspecialchars($schedule['program_title']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Delivery Mode:</label>
                                <select class="form-select" id="bulkDeliveryModeSelect">
                                    <option value="online" selected>Online</option>
                                    <option value="physical">Physical</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="bulkAssignSave">Assign to Cohort</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('bulkAssignModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bulkAssignModal'));
        modal.show();
        
        // Handle save
        document.getElementById('bulkAssignSave').addEventListener('click', function() {
            const scheduleId = document.getElementById('bulkScheduleSelect').value;
            const deliveryMode = document.getElementById('bulkDeliveryModeSelect').value;
            
            if (!scheduleId) {
                alert('Please select a cohort');
                return;
            }
            
            // Show loading
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';
            this.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'bulk_assign');
            formData.append('schedule_id', scheduleId);
            formData.append('preferred_delivery_mode', deliveryMode);
            applicationIds.forEach(id => formData.append('ids[]', id));
            
            fetch(`<?= BASE_URL ?>/admin/public/applications.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Bulk assign error:', error);
                alert('An error occurred during bulk assignment');
            })
            .finally(() => {
                modal.hide();
            });
        });
        
        // Clean up modal when hidden
        document.getElementById('bulkAssignModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }
    
    // Delete application function
    window.deleteApplication = function(applicationId) {
        if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', applicationId);
        
        fetch(`<?= BASE_URL ?>/admin/public/applications.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showErrorMessage('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showErrorMessage('An error occurred while deleting the application');
        });
    };
});
</script>

