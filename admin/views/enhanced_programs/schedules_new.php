<?php
/**
 * Enhanced Programs - Schedule Management View
 * Safely handles form data with proper validation and user experience
 * Created: 2025-08-07 16:29:00
 */

// Safe form data helper function
function getPostValue($key, $default = '') {
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

// Safe checkbox helper function  
function isPostChecked($key) {
    return isset($_POST[$key]) && $_POST[$key] == '1';
}
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Schedules: <?= htmlspecialchars($program['title']) ?></h1>
    <div class="d-none d-sm-inline-block">
        <a href="<?= BASE_URL ?>/admin/public/programs.php" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Programs
        </a>
    </div>
</div>

<!-- Success Alert -->
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><strong>✅ New Safe View:</strong> This page has been recreated with proper form data handling to prevent PHP warnings and improve user experience.
    <button type="button" class="close" data-dismiss="alert">
        <span>&times;</span>
    </button>
</div>

<!-- Flash Messages -->
<?php if (!empty($this->errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <ul class="mb-0">
            <?php foreach ($this->errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($this->success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($this->success) ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-md-12">

        <!-- Add New Schedule Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">Add New Schedule</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_schedule">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Schedule Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="e.g., March 2025 Online Cohort" required
                                       value="<?= getPostValue('title') ?>" maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_mode" class="form-label">Delivery Mode *</label>
                                <select class="form-control" id="delivery_mode" name="delivery_mode" required>
                                    <option value="">Select Mode</option>
                                    <option value="online" <?= getPostValue('delivery_mode') === 'online' ? 'selected' : '' ?>>Online</option>
                                    <option value="physical" <?= getPostValue('delivery_mode') === 'physical' ? 'selected' : '' ?>>Physical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control auto-resize" id="description" name="description" rows="2"
                                  placeholder="Brief description of this specific schedule"><?= getPostValue('description') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required
                                       value="<?= getPostValue('start_date') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required
                                       value="<?= getPostValue('end_date') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       value="<?= getPostValue('start_time', '09:00') ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" 
                                       value="<?= getPostValue('end_time', '17:00') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="e.g., Nairobi Campus, Zoom, etc." maxlength="255"
                                       value="<?= getPostValue('location') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-control" id="timezone" name="timezone">
                                    <option value="UTC" <?= getPostValue('timezone', 'Africa/Nairobi') === 'UTC' ? 'selected' : '' ?>>UTC</option>
                                    <option value="Africa/Nairobi" <?= getPostValue('timezone', 'Africa/Nairobi') === 'Africa/Nairobi' ? 'selected' : '' ?>>Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Johannesburg" <?= getPostValue('timezone', 'Africa/Nairobi') === 'Africa/Johannesburg' ? 'selected' : '' ?>>Africa/Johannesburg (SAST)</option>
                                    <option value="America/New_York" <?= getPostValue('timezone', 'Africa/Nairobi') === 'America/New_York' ? 'selected' : '' ?>>America/New_York (EST)</option>
                                    <option value="Europe/London" <?= getPostValue('timezone', 'Africa/Nairobi') === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1"
                                       value="<?= getPostValue('max_participants') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="venue_details" class="form-label">Venue Details</label>
                        <textarea class="form-control auto-resize" id="venue_details" name="venue_details" rows="2"
                                  placeholder="Additional venue information, directions, or online meeting details"><?= getPostValue('venue_details') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="online_fee" class="form-label">Online Fee *</label>
                                <input type="number" class="form-control" id="online_fee" name="online_fee" 
                                       min="0.01" step="0.01" required
                                       value="<?= getPostValue('online_fee') ?>">
                                <small class="form-text text-muted">All courses must be available online</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="physical_fee" class="form-label">Physical Fee *</label>
                                <input type="number" class="form-control" id="physical_fee" name="physical_fee" 
                                       min="0.01" step="0.01" required
                                       value="<?= getPostValue('physical_fee') ?>">
                                <small class="form-text text-muted">All courses must be available physically</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-control" id="currency" name="currency">
                                    <option value="USD" <?= getPostValue('currency', 'KES') === 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="KES" <?= getPostValue('currency', 'KES') === 'KES' ? 'selected' : '' ?>>KES</option>
                                    <option value="EUR" <?= getPostValue('currency', 'KES') === 'EUR' ? 'selected' : '' ?>>EUR</option>
                                    <option value="GBP" <?= getPostValue('currency', 'KES') === 'GBP' ? 'selected' : '' ?>>GBP</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                <input type="date" class="form-control" id="registration_deadline" name="registration_deadline"
                                       value="<?= getPostValue('registration_deadline') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_name" class="form-label">Instructor Name</label>
                                <input type="text" class="form-control" id="instructor_name" name="instructor_name" maxlength="255"
                                       value="<?= getPostValue('instructor_name') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_email" class="form-label">Instructor Email</label>
                                <input type="email" class="form-control" id="instructor_email" name="instructor_email" maxlength="255"
                                       value="<?= getPostValue('instructor_email') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meeting_link" class="form-label">Meeting Link (for online)</label>
                                <input type="url" class="form-control" id="meeting_link" name="meeting_link"
                                       placeholder="https://zoom.us/j/..." maxlength="500"
                                       value="<?= getPostValue('meeting_link') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meeting_password" class="form-label">Meeting Password</label>
                                <input type="text" class="form-control" id="meeting_password" name="meeting_password" maxlength="255"
                                       value="<?= getPostValue('meeting_password') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements</label>
                                <textarea class="form-control auto-resize" id="requirements" name="requirements" rows="2"
                                          placeholder="Specific requirements for this schedule"><?= getPostValue('requirements') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="materials_included" class="form-label">Materials Included</label>
                                <textarea class="form-control auto-resize" id="materials_included" name="materials_included" rows="2"
                                          placeholder="What materials or resources are included"><?= getPostValue('materials_included') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Schedule
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Schedules -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h5 class="m-0 font-weight-bold text-primary">Existing Schedules (<?= count($schedules) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($schedules)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No schedules found for this program.</p>
                        <p class="text-muted">Add your first schedule using the form above.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Mode</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Fee</th>
                                    <th>Max Participants</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($schedule['title']) ?></strong>
                                            <?php if (!empty($schedule['description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($schedule['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $schedule['delivery_mode'] === 'online' ? 'info' : 'success' ?>">
                                                <?= ucfirst(htmlspecialchars($schedule['delivery_mode'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= $schedule['start_date'] ? date('M j, Y', strtotime($schedule['start_date'])) : 'TBD' ?>
                                            <?php if (!empty($schedule['start_time'])): ?>
                                                <br><small class="text-muted"><?= date('g:i A', strtotime($schedule['start_time'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $schedule['end_date'] ? date('M j, Y', strtotime($schedule['end_date'])) : 'TBD' ?>
                                            <?php if (!empty($schedule['end_time'])): ?>
                                                <br><small class="text-muted"><?= date('g:i A', strtotime($schedule['end_time'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($schedule['location']) ?: '<span class="text-muted">Not specified</span>' ?>
                                            <?php if (!empty($schedule['timezone'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($schedule['timezone']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $fees = [];
                                            $currency = htmlspecialchars($schedule['currency'] ?? 'USD');
                                            if (!empty($schedule['online_fee'])) {
                                                $fees[] = 'Online: ' . $currency . ' ' . number_format($schedule['online_fee'], 2);
                                            }
                                            if (!empty($schedule['physical_fee'])) {
                                                $fees[] = 'Physical: ' . $currency . ' ' . number_format($schedule['physical_fee'], 2);
                                            }
                                            echo !empty($fees) ? implode('<br>', $fees) : '<span class="text-muted">Free</span>';
                                            ?>
                                        </td>
                                        <td><?= $schedule['max_participants'] ?: '<span class="text-muted">Unlimited</span>' ?></td>
                                        <td>
                                            <span class="badge bg-<?= !empty($schedule['is_active']) ? 'success' : 'secondary' ?>">
                                                <?= !empty($schedule['is_active']) ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                                                <input type="hidden" name="action" value="delete_schedule">
                                                <input type="hidden" name="schedule_id" value="<?= htmlspecialchars($schedule['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default timezone based on user's location (if available)
    const timezoneSelect = document.getElementById('timezone');
    if (timezoneSelect) {
        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        
        for (let option of timezoneSelect.options) {
            if (option.value === userTimezone) {
                option.selected = true;
                break;
            }
        }
    }

    // Auto-set registration deadline to one day before start date
    const startDateInput = document.getElementById('start_date');
    const deadlineInput = document.getElementById('registration_deadline');
    
    if (startDateInput && deadlineInput) {
        startDateInput.addEventListener('change', function() {
            if (this.value && !deadlineInput.value) {
                const startDate = new Date(this.value);
                startDate.setDate(startDate.getDate() - 1);
                deadlineInput.value = startDate.toISOString().slice(0, 10);
            }
        });
    }

    // Auto-resize textareas
    function autoResizeTextarea(textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight + 2) + 'px';
    }

    // Apply auto-resize to all textareas with the auto-resize class
    document.querySelectorAll('textarea.auto-resize').forEach(function(textarea) {
        // Set initial height
        autoResizeTextarea(textarea);
        
        // Add event listener for input changes
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    });

    // Form validation enhancement
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const onlineFee = document.getElementById('online_fee').value;
            const physicalFee = document.getElementById('physical_fee').value;
            
            if (!onlineFee || parseFloat(onlineFee) <= 0) {
                alert('Please enter a valid online fee greater than 0.');
                e.preventDefault();
                return false;
            }
            
            if (!physicalFee || parseFloat(physicalFee) <= 0) {
                alert('Please enter a valid physical fee greater than 0.');
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
