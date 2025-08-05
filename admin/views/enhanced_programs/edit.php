<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Enhanced Program</h5>
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
                    <!-- Program Details -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Program Title *</label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?= htmlspecialchars($program['title']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration *</label>
                                <input type="text" class="form-control" id="duration" name="duration"
                                       value="<?= htmlspecialchars($program['duration']) ?>"
                                       placeholder="e.g., 3 days, 2 weeks" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Short Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($program['description']) ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fee" class="form-label">Base Fee ($)</label>
                                <input type="number" class="form-control" id="fee" name="fee"
                                       value="<?= $program['fee'] ?: 0 ?>" min="0" step="0.01">
                                <div class="form-text">This is the base fee. Individual schedules can have different fees.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Program Info -->
                    <hr>
                    <h6>Detailed Program Information</h6>

                    <div class="mb-3">
                        <label for="introduction" class="form-label">Program Introduction</label>
                        <textarea class="form-control" id="introduction" name="introduction" rows="4"
                                  placeholder="Detailed introduction to the program, what it covers, and its benefits"><?= htmlspecialchars($program_info['introduction'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="objectives" class="form-label">Learning Objectives</label>
                        <textarea class="form-control" id="objectives" name="objectives" rows="4"
                                  placeholder="List the key learning objectives participants will achieve"><?= htmlspecialchars($program_info['objectives'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="target_audience" class="form-label">Target Audience</label>
                                <textarea class="form-control" id="target_audience" name="target_audience" rows="3"
                                          placeholder="Who is this program designed for?"><?= htmlspecialchars($program_info['target_audience'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="prerequisites" class="form-label">Prerequisites</label>
                                <textarea class="form-control" id="prerequisites" name="prerequisites" rows="3"
                                          placeholder="Any prerequisites or requirements for participants"><?= htmlspecialchars($program_info['prerequisites'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="course_content" class="form-label">Course Content (Modules)</label>
                        <textarea class="form-control" id="course_content" name="course_content" rows="5"
                                  placeholder="Outline the course modules and content structure"><?= htmlspecialchars($program_info['course_content'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="general_notes" class="form-label">General Notes</label>
                        <textarea class="form-control" id="general_notes" name="general_notes" rows="3"
                                  placeholder="Additional notes, requirements, or important information"><?= htmlspecialchars($program_info['general_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="certification_details" class="form-label">Certification Details</label>
                        <textarea class="form-control" id="certification_details" name="certification_details" rows="3"
                                  placeholder="Information about certificates, credentials, or completion recognition"><?= htmlspecialchars($program_info['certification_details'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/admin/enhanced_programs.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Programs
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
});
</script>

