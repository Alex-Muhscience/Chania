<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Manage Schedules: <?= htmlspecialchars($program['title']) ?></h3>
            <a href="<?= BASE_URL ?>/admin/enhanced_programs.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Programs
            </a>
        </div>

        <!-- Add New Schedule Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Add New Schedule</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($this->errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($this->errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="add_schedule">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Schedule Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="e.g., March 2025 Online Cohort" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="delivery_mode" class="form-label">Delivery Mode *</label>
                                <select class="form-control" id="delivery_mode" name="delivery_mode" required>
                                    <option value="">Select Mode</option>
                                    <option value="online">Online</option>
                                    <option value="physical">Physical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"
                                  placeholder="Brief description of this specific schedule"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" value="09:00">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" value="17:00">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       placeholder="e.g., Nairobi Campus, Zoom, etc.">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-control" id="timezone" name="timezone">
                                    <option value="UTC">UTC</option>
                                    <option value="Africa/Nairobi" selected>Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Johannesburg">Africa/Johannesburg (SAST)</option>
                                    <option value="America/New_York">America/New_York (EST)</option>
                                    <option value="Europe/London">Europe/London (GMT)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="max_participants" class="form-label">Max Participants</label>
                                <input type="number" class="form-control" id="max_participants" name="max_participants" min="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="venue_details" class="form-label">Venue Details</label>
                        <textarea class="form-control" id="venue_details" name="venue_details" rows="2"
                                  placeholder="Additional venue information, directions, or online meeting details"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="online_fee" class="form-label">Online Fee</label>
                                <input type="number" class="form-control" id="online_fee" name="online_fee" 
                                       min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="physical_fee" class="form-label">Physical Fee</label>
                                <input type="number" class="form-control" id="physical_fee" name="physical_fee" 
                                       min="0" step="0.01" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-control" id="currency" name="currency">
                                    <option value="USD">USD</option>
                                    <option value="KES" selected>KES</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="registration_deadline" class="form-label">Registration Deadline</label>
                                <input type="date" class="form-control" id="registration_deadline" name="registration_deadline">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_name" class="form-label">Instructor Name</label>
                                <input type="text" class="form-control" id="instructor_name" name="instructor_name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="instructor_email" class="form-label">Instructor Email</label>
                                <input type="email" class="form-control" id="instructor_email" name="instructor_email">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meeting_link" class="form-label">Meeting Link (for online)</label>
                                <input type="url" class="form-control" id="meeting_link" name="meeting_link"
                                       placeholder="https://zoom.us/j/...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="meeting_password" class="form-label">Meeting Password</label>
                                <input type="text" class="form-control" id="meeting_password" name="meeting_password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="2"
                                          placeholder="Specific requirements for this schedule"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="materials_included" class="form-label">Materials Included</label>
                                <textarea class="form-control" id="materials_included" name="materials_included" rows="2"
                                          placeholder="What materials or resources are included"></textarea>
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
        <div class="card">
            <div class="card-header">
                <h5>Existing Schedules (<?= count($schedules) ?>)</h5>
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
                                            <span class="badge bg-<?= $schedule['delivery_mode'] === 'online' ? 'info' : 'success' ?>">
                                                <?= ucfirst($schedule['delivery_mode']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i A', strtotime($schedule['start_date'])) ?></td>
                                        <td><?= $schedule['end_date'] ? date('M j, Y g:i A', strtotime($schedule['end_date'])) : 'TBD' ?></td>
                                        <td><?= htmlspecialchars($schedule['location']) ?: 'Not specified' ?></td>
                                        <td>
                                            <?php 
                                            $fees = [];
                                            if ($schedule['online_fee']) {
                                                $fees[] = 'Online: ' . ($schedule['currency'] ?? 'USD') . ' ' . number_format($schedule['online_fee'], 2);
                                            }
                                            if ($schedule['physical_fee']) {
                                                $fees[] = 'Physical: ' . ($schedule['currency'] ?? 'USD') . ' ' . number_format($schedule['physical_fee'], 2);
                                            }
                                            echo !empty($fees) ? implode('<br>', $fees) : 'Free';
                                            ?>
                                        </td>
                                        <td><?= $schedule['max_participants'] ?: 'Unlimited' ?></td>
                                        <td>
                                            <span class="badge bg-<?= $schedule['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $schedule['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_schedule">
                                                <input type="hidden" name="schedule_id" value="<?= $schedule['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this schedule?')">
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
    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    
    for (let option of timezoneSelect.options) {
        if (option.value === userTimezone) {
            option.selected = true;
            break;
        }
    }

    // Auto-set registration deadline to one day before start date
    const startDateInput = document.getElementById('start_date');
    const deadlineInput = document.getElementById('registration_deadline');
    
    startDateInput.addEventListener('change', function() {
        if (this.value && !deadlineInput.value) {
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() - 1);
            deadlineInput.value = startDate.toISOString().slice(0, 16);
        }
    });
});
</script>
