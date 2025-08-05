<?php
require_once '../vendor/autoload.php';
require_once '../includes/config.php';

use Dompdf\Dompdf;

$program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;

if (!$program_id) {
    die('Program ID is required.');
}

$program_query = "SELECT * FROM programs WHERE id = :id";
$stmt = $db->prepare($program_query);
$stmt->execute(['id' => $program_id]);
$program = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$program) {
    die('Program not found.');
}

// Fetch schedules for this program
$schedules_query = "SELECT * FROM program_schedules WHERE program_id = :program_id ORDER BY start_date, start_time";
$schedules_stmt = $db->prepare($schedules_query);
$schedules_stmt->execute(['program_id' => $program_id]);
$schedules = $schedules_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch curriculum for this program
$curriculum_query = "SELECT * FROM program_curriculum WHERE program_id = :program_id ORDER BY id";
$curriculum_stmt = $db->prepare($curriculum_query);
$curriculum_stmt->execute(['program_id' => $program_id]);
$curriculum = $curriculum_stmt->fetchAll(PDO::FETCH_ASSOC);

$dompdf = new Dompdf();

// Start building HTML content
$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; }
h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
h2 { color: #34495e; margin-top: 25px; border-left: 4px solid #3498db; padding-left: 10px; }
h3 { color: #2c3e50; margin-top: 20px; }
.section { margin-bottom: 20px; }
.info-row { margin-bottom: 8px; }
.label { font-weight: bold; color: #2c3e50; }
.schedule-item, .curriculum-item { background: #f8f9fa; padding: 10px; margin-bottom: 10px; border-left: 3px solid #3498db; }
.duration { color: #e74c3c; font-weight: bold; }
.fee { color: #27ae60; font-weight: bold; }
ul { padding-left: 20px; }
li { margin-bottom: 5px; }
</style></head><body>';

$html .= '<h1>' . htmlspecialchars($program['title']) . '</h1>';

// Basic program information
$html .= '<div class="section">';
$html .= '<h2>Program Overview</h2>';
$html .= '<div class="info-row"><span class="label">Description:</span> ' . nl2br(htmlspecialchars($program['description'])) . '</div>';

if (!empty($program['category'])) {
    $html .= '<div class="info-row"><span class="label">Category:</span> ' . htmlspecialchars($program['category']) . '</div>';
}
if (!empty($program['duration'])) {
    $html .= '<div class="info-row"><span class="label">Duration:</span> <span class="duration">' . htmlspecialchars($program['duration']) . '</span></div>';
}
if (!empty($program['fee'])) {
    $html .= '<div class="info-row"><span class="label">Fee:</span> <span class="fee">$' . number_format($program['fee'], 2) . '</span></div>';
}
if (!empty($program['requirements'])) {
    $html .= '<div class="info-row"><span class="label">Requirements:</span> ' . nl2br(htmlspecialchars($program['requirements'])) . '</div>';
}
if (!empty($program['benefits'])) {
    $html .= '<div class="info-row"><span class="label">Benefits:</span> ' . nl2br(htmlspecialchars($program['benefits'])) . '</div>';
}
if (!empty($program['target_audience'])) {
    $html .= '<div class="info-row"><span class="label">Target Audience:</span> ' . nl2br(htmlspecialchars($program['target_audience'])) . '</div>';
}
if (!empty($program['career_outcomes'])) {
    $html .= '<div class="info-row"><span class="label">Career Outcomes:</span> ' . nl2br(htmlspecialchars($program['career_outcomes'])) . '</div>';
}
if (!empty($program['certification'])) {
    $html .= '<div class="info-row"><span class="label">Certification:</span> ' . nl2br(htmlspecialchars($program['certification'])) . '</div>';
}
if (!empty($program['materials_included'])) {
    $html .= '<div class="info-row"><span class="label">Materials Included:</span> ' . nl2br(htmlspecialchars($program['materials_included'])) . '</div>';
}
$html .= '</div>';

// Schedules section
if (!empty($schedules)) {
    $html .= '<div class="section">';
    $html .= '<h2>Program Schedules</h2>';
    foreach ($schedules as $schedule) {
        $html .= '<div class="schedule-item">';
        $html .= '<h3>Schedule ' . ($schedule['id'] ?? '') . '</h3>';
        if (!empty($schedule['start_date'])) {
            $html .= '<div class="info-row"><span class="label">Start Date:</span> ' . date('F j, Y', strtotime($schedule['start_date'])) . '</div>';
        }
        if (!empty($schedule['end_date'])) {
            $html .= '<div class="info-row"><span class="label">End Date:</span> ' . date('F j, Y', strtotime($schedule['end_date'])) . '</div>';
        }
        if (!empty($schedule['start_time']) && !empty($schedule['end_time'])) {
            $html .= '<div class="info-row"><span class="label">Time:</span> ' . date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time'])) . '</div>';
        }
        if (!empty($schedule['days_of_week'])) {
            $html .= '<div class="info-row"><span class="label">Days:</span> ' . htmlspecialchars($schedule['days_of_week']) . '</div>';
        }
        if (!empty($schedule['location'])) {
            $html .= '<div class="info-row"><span class="label">Location:</span> ' . htmlspecialchars($schedule['location']) . '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
}

// Curriculum section
if (!empty($curriculum)) {
    $html .= '<div class="section">';
    $html .= '<h2>Program Curriculum</h2>';
    foreach ($curriculum as $index => $module) {
        $html .= '<div class="curriculum-item">';
        $html .= '<h3>Module ' . ($index + 1) . ': ' . htmlspecialchars($module['module_title']) . '</h3>';
        if (!empty($module['module_description'])) {
            $html .= '<div class="info-row">' . nl2br(htmlspecialchars($module['module_description'])) . '</div>';
        }
        if (!empty($module['topics'])) {
            $html .= '<div class="info-row"><span class="label">Topics Covered:</span></div>';
            $topics = explode(',', $module['topics']);
            $html .= '<ul>';
            foreach ($topics as $topic) {
                $html .= '<li>' . htmlspecialchars(trim($topic)) . '</li>';
            }
            $html .= '</ul>';
        }
        if (!empty($module['duration'])) {
            $html .= '<div class="info-row"><span class="label">Duration:</span> ' . htmlspecialchars($module['duration']) . '</div>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
}

$html .= '</body></html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('program_details_' . $program_id . '.pdf');

