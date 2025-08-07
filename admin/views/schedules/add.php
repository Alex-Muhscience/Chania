<?php include __DIR__ . '/../../includes/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Schedule</h1>
        <a href="schedules.php" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Schedules
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Schedule Details</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <!-- Program Selection -->
                    <div class="col-md-6 mb-3">
                        <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                        <select class="form-select" id="program_id" name="program_id" required>
                            <option value="">Select a Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['id']; ?>" <?php echo (isset($_POST['program_id']) && $_POST['program_id'] == $program['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($program['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Schedule Title -->
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Schedule Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                               placeholder="e.g., January 2025 Cohort" required>
                    </div>
                </div>

                <div class="row">
                    <!-- Start Date -->
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                    </div>

                    <!-- End Date -->
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row">
                    <!-- Start Time -->
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" 
                               value="<?php echo htmlspecialchars($_POST['start_time'] ?? '09:00'); ?>">
                    </div>

                    <!-- End Time -->
                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" 
                               value="<?php echo htmlspecialchars($_POST['end_time'] ?? '17:00'); ?>">
                    </div>
                </div>

                <div class="row">
                    <!-- Delivery Mode -->
                    <div class="col-md-6 mb-3">
                        <label for="delivery_mode" class="form-label">Primary Delivery Mode</label>
                        <select class="form-select" id="delivery_mode" name="delivery_mode">
                            <option value="hybrid" <?php echo (isset($_POST['delivery_mode']) && $_POST['delivery_mode'] == 'hybrid') ? 'selected' : 'selected'; ?>>
                                Both Online & Physical Available
                            </option>
                            <option value="online" <?php echo (isset($_POST['delivery_mode']) && $_POST['delivery_mode'] == 'online') ? 'selected' : ''; ?>>
                                Online Only
                            </option>
                            <option value="physical" <?php echo (isset($_POST['delivery_mode']) && $_POST['delivery_mode'] == 'physical') ? 'selected' : ''; ?>>
                                Physical Only
                            </option>
                        </select>
                        <small class="form-text text-muted">We recommend offering both modes to maximize accessibility.</small>
                    </div>

                    <!-- Location -->
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="<?php echo htmlspecialchars($_POST['location'] ?? 'Online'); ?>" 
                               placeholder="Online or Physical Location">
                    </div>
                </div>

                <div class="row">
                    <!-- Online Fee -->
                    <div class="col-md-4 mb-3">
                        <label for="online_fee" class="form-label">Online Fee (USD) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="online_fee" name="online_fee" 
                               value="<?php echo htmlspecialchars($_POST['online_fee'] ?? ''); ?>" 
                               placeholder="e.g., 50.00" required>
                        <small class="form-text text-muted">Fee in USD for online participants</small>
                    </div>

                    <!-- Physical Fee -->
                    <div class="col-md-4 mb-3">
                        <label for="physical_fee" class="form-label">Physical Fee (USD) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" id="physical_fee" name="physical_fee" 
                               value="<?php echo htmlspecialchars($_POST['physical_fee'] ?? ''); ?>" 
                               placeholder="e.g., 65.00" required>
                        <small class="form-text text-muted">Fee in USD for in-person participants</small>
                    </div>

                    <!-- Max Participants -->
                    <div class="col-md-4 mb-3">
                        <label for="max_participants" class="form-label">Max Participants</label>
                        <input type="number" class="form-control" id="max_participants" name="max_participants" 
                               value="<?php echo htmlspecialchars($_POST['max_participants'] ?? ''); ?>" 
                               placeholder="Leave empty for unlimited">
                    </div>
                </div>

                <div class="row">
                    <!-- Registration Deadline -->
                    <div class="col-md-6 mb-3">
                        <label for="registration_deadline" class="form-label">Registration Deadline</label>
                        <input type="date" class="form-control" id="registration_deadline" name="registration_deadline" 
                               value="<?php echo htmlspecialchars($_POST['registration_deadline'] ?? ''); ?>">
                    </div>

                    <!-- Instructor Name -->
                    <div class="col-md-6 mb-3">
                        <label for="instructor_name" class="form-label">Instructor Name</label>
                        <input type="text" class="form-control" id="instructor_name" name="instructor_name" 
                               value="<?php echo htmlspecialchars($_POST['instructor_name'] ?? ''); ?>" 
                               placeholder="Primary instructor name">
                    </div>
                </div>

                <div class="row">
                    <!-- Instructor Email -->
                    <div class="col-md-6 mb-3">
                        <label for="instructor_email" class="form-label">Instructor Email</label>
                        <input type="email" class="form-control" id="instructor_email" name="instructor_email" 
                               value="<?php echo htmlspecialchars($_POST['instructor_email'] ?? ''); ?>" 
                               placeholder="instructor@example.com">
                    </div>

                    <!-- Timezone -->
                    <div class="col-md-6 mb-3">
                        <label for="timezone" class="form-label">Timezone</label>
                        <select class="form-select" id="timezone" name="timezone">
                            <option value="Africa/Nairobi" <?php echo (isset($_POST['timezone']) && $_POST['timezone'] == 'Africa/Nairobi') ? 'selected' : ''; ?>>
                                Africa/Nairobi (EAT)
                            </option>
                            <option value="UTC" <?php echo (isset($_POST['timezone']) && $_POST['timezone'] == 'UTC') ? 'selected' : ''; ?>>
                                UTC
                            </option>
                            <option value="Africa/Lagos" <?php echo (isset($_POST['timezone']) && $_POST['timezone'] == 'Africa/Lagos') ? 'selected' : ''; ?>>
                                Africa/Lagos (WAT)
                            </option>
                            <option value="Africa/Johannesburg" <?php echo (isset($_POST['timezone']) && $_POST['timezone'] == 'Africa/Johannesburg') ? 'selected' : ''; ?>>
                                Africa/Johannesburg (SAST)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Meeting Link (for online sessions) -->
                <div class="mb-3">
                    <label for="meeting_link" class="form-label">Meeting Link</label>
                    <input type="url" class="form-control" id="meeting_link" name="meeting_link" 
                           value="<?php echo htmlspecialchars($_POST['meeting_link'] ?? ''); ?>" 
                           placeholder="https://zoom.us/j/... or other meeting platform link">
                </div>

                <!-- Meeting Password -->
                <div class="mb-3">
                    <label for="meeting_password" class="form-label">Meeting Password</label>
                    <input type="text" class="form-control" id="meeting_password" name="meeting_password" 
                           value="<?php echo htmlspecialchars($_POST['meeting_password'] ?? ''); ?>" 
                           placeholder="Password for online sessions">
                </div>

                <!-- Venue Address -->
                <div class="mb-3">
                    <label for="venue_address" class="form-label">Venue Address</label>
                    <textarea class="form-control" id="venue_address" name="venue_address" rows="3" 
                              placeholder="Physical venue address (for in-person sessions)"><?php echo htmlspecialchars($_POST['venue_address'] ?? ''); ?></textarea>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Additional details about this schedule"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <!-- Session Notes -->
                <div class="mb-3">
                    <label for="session_notes" class="form-label">Session Notes</label>
                    <textarea class="form-control" id="session_notes" name="session_notes" rows="3" 
                              placeholder="Internal notes about this schedule"><?php echo htmlspecialchars($_POST['session_notes'] ?? ''); ?></textarea>
                </div>

                <!-- Requirements -->
                <div class="mb-3">
                    <label for="requirements" class="form-label">Requirements</label>
                    <textarea class="form-control" id="requirements" name="requirements" rows="3" 
                              placeholder="Special requirements for this schedule"><?php echo htmlspecialchars($_POST['requirements'] ?? ''); ?></textarea>
                </div>

                <!-- Materials Included -->
                <div class="mb-3">
                    <label for="materials_included" class="form-label">Materials Included</label>
                    <textarea class="form-control" id="materials_included" name="materials_included" rows="3" 
                              placeholder="Materials included with this schedule"><?php echo htmlspecialchars($_POST['materials_included'] ?? ''); ?></textarea>
                </div>

                <div class="row">
                    <!-- Status Options -->
                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   <?php echo (isset($_POST['is_active']) && $_POST['is_active']) ? 'checked' : 'checked'; ?>>
                            <label class="form-check-label" for="is_active">
                                Active Schedule
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_open_for_registration" name="is_open_for_registration" value="1" 
                                   <?php echo (isset($_POST['is_open_for_registration']) && $_POST['is_open_for_registration']) ? 'checked' : 'checked'; ?>>
                            <label class="form-check-label" for="is_open_for_registration">
                                Open for Registration
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="schedules.php" class="btn btn-secondary me-md-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide fields based on delivery mode
document.getElementById('delivery_mode').addEventListener('change', function() {
    const deliveryMode = this.value;
    const venueField = document.getElementById('venue_address');
    const meetingFields = [document.getElementById('meeting_link'), document.getElementById('meeting_password')];
    
    if (deliveryMode === 'online') {
        venueField.closest('.mb-3').style.display = 'none';
        meetingFields.forEach(field => field.closest('.mb-3').style.display = 'block');
    } else if (deliveryMode === 'physical') {
        venueField.closest('.mb-3').style.display = 'block';
        meetingFields.forEach(field => field.closest('.mb-3').style.display = 'none');
    } else { // hybrid
        venueField.closest('.mb-3').style.display = 'block';
        meetingFields.forEach(field => field.closest('.mb-3').style.display = 'block');
    }
});

// Trigger on page load
document.getElementById('delivery_mode').dispatchEvent(new Event('change'));

// Auto-set end date based on start date (add 4 weeks by default)
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate);
    endDate.setDate(endDate.getDate() + 28); // Add 4 weeks
    
    const endDateField = document.getElementById('end_date');
    if (!endDateField.value) {
        endDateField.value = endDate.toISOString().split('T')[0];
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
