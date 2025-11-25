<?php

/**
 * Academics API Endpoint
 * Handles all academic module operations
 */
header('Content-Type: application/json');
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/database.php';

// Authentication check
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

try {
    switch ($action) {
        case 'get_subjects':
            $grade = $_GET['grade'] ?? '';
            $query = "SELECT * FROM subjects WHERE is_active = 1";
            if ($grade) {
                $query .= " AND grade_level = ?";
                $subjects = db()->fetchAll($query, [$grade]);
            } else {
                $subjects = db()->fetchAll($query);
            }
            $response = ['success' => true, 'data' => $subjects];
            break;

        case 'get_exams':
            $grade = $_GET['grade'] ?? '';
            $academic_year = $_GET['academic_year'] ?? '';
            $query = "SELECT * FROM examinations WHERE 1=1";
            $params = [];
            if ($grade) {
                $query .= " AND grade_level = ?";
                $params[] = $grade;
            }
            if ($academic_year) {
                $query .= " AND academic_year = ?";
                $params[] = $academic_year;
            }
            $query .= " ORDER BY exam_date DESC";
            $exams = db()->fetchAll($query, $params);
            $response = ['success' => true, 'data' => $exams];
            break;

        case 'get_student_results':
            if (!isset($_GET['student_id'])) {
                throw new Exception('Student ID required');
            }
            $student_id = intval($_GET['student_id']);
            $query = "SELECT er.*, e.exam_name, s.subject_name
                      FROM exam_results er
                      JOIN examinations e ON er.exam_id = e.id
                      JOIN subjects s ON er.subject_id = s.id
                      WHERE er.student_id = ?
                      ORDER BY e.exam_date DESC";
            $results = db()->fetchAll($query, [$student_id]);
            $response = ['success' => true, 'data' => $results];
            break;

        case 'submit_marks':
            require_admin();
            $exam_id = intval($_POST['exam_id']);
            $student_id = intval($_POST['student_id']);
            $subject_id = intval($_POST['subject_id']);
            $marks_obtained = floatval($_POST['marks_obtained']);
            $total_marks = floatval($_POST['total_marks']);

            $stmt = db()->prepare("INSERT INTO exam_results (exam_id, student_id, subject_id, marks_obtained, total_marks, percentage, grade, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained = ?, total_marks = ?, percentage = ?, grade = ?");

            $percentage = ($marks_obtained / $total_marks) * 100;
            $grade = calculate_grade($percentage);

            $stmt->execute([
                $exam_id,
                $student_id,
                $subject_id,
                $marks_obtained,
                $total_marks,
                $percentage,
                $grade,
                $_POST['remarks'] ?? '',
                $marks_obtained,
                $total_marks,
                $percentage,
                $grade
            ]);

            $response = ['success' => true, 'message' => 'Marks submitted successfully'];
            break;

        case 'get_timetable':
            $class_id = intval($_GET['class_id']);
            $query = "SELECT * FROM timetable WHERE class_id = ? ORDER BY day_of_week, period_number";
            $timetable = db()->fetchAll($query, [$class_id]);
            $response = ['success' => true, 'data' => $timetable];
            break;

        default:
            throw new Exception('Unknown action');
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);

function calculate_grade($percentage)
{
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 40) return 'D';
    return 'F';
}
