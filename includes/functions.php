<?php
session_start();
require_once __DIR__ . '/../db/db_connect.php';

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function check_role($required_role) {
    if (!is_logged_in()) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($_SESSION['role'] !== $required_role) {
        header("Location: ../index.php");
        exit();
    }
}

// Function to redirect based on role
function redirect_by_role() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'teacher':
            header("Location: teacher/dashboard.php");
            break;
        case 'student':
            header("Location: student/dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

// Function to get user data
function get_user_data($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get exam data
function get_exam_data($exam_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get questions for an exam
function get_exam_questions($exam_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY id");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to calculate exam score
function calculate_score($exam_id, $user_answers) {
    global $conn;
    
    $questions = get_exam_questions($exam_id);
    $exam = get_exam_data($exam_id);
    
    $correct_answers = 0;
    $wrong_answers = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $correct_option = $question['correct_option'];
        
        if (isset($user_answers[$question_id]) && $user_answers[$question_id] === $correct_option) {
            $correct_answers++;
        } elseif (isset($user_answers[$question_id]) && $user_answers[$question_id] !== $correct_option) {
            $wrong_answers++;
        }
    }
    
    $score = ($correct_answers * $exam['positive_marks']) - ($wrong_answers * $exam['negative_marks']);
    
    return [
        'score' => max(0, $score),
        'total_questions' => $total_questions,
        'correct_answers' => $correct_answers,
        'wrong_answers' => $wrong_answers
    ];
}

// Function to save exam result
function save_exam_result($user_id, $exam_id, $score_data) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO results (user_id, exam_id, score, total_questions, correct_answers, wrong_answers) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidiii", $user_id, $exam_id, $score_data['score'], $score_data['total_questions'], $score_data['correct_answers'], $score_data['wrong_answers']);
    
    if ($stmt->execute()) {
        update_ranking($exam_id);
        return true;
    }
    return false;
}

// Function to update ranking
function update_ranking($exam_id) {
    global $conn;
    
    // Delete existing rankings for this exam
    $stmt = $conn->prepare("DELETE FROM ranking WHERE exam_id = ?");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    
    // Get all results for this exam ordered by score
    $stmt = $conn->prepare("SELECT user_id, score FROM results WHERE exam_id = ? ORDER BY score DESC");
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        $stmt2 = $conn->prepare("INSERT INTO ranking (user_id, exam_id, score, rank_position) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iidi", $row['user_id'], $exam_id, $row['score'], $rank);
        $stmt2->execute();
        $rank++;
    }
}

// Function to get user ranking
function get_user_ranking($user_id, $exam_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT rank_position FROM ranking WHERE user_id = ? AND exam_id = ?");
    $stmt->bind_param("ii", $user_id, $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['rank_position'] : null;
}

// Function to format time
function format_time($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return sprintf("%02d:%02d", $hours, $mins);
}

// Function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate mobile
function validate_mobile($mobile) {
    return preg_match("/^[0-9]{10}$/", $mobile);
}

// Function to display success message
function show_success($message) {
    return "<div class='alert alert-success'><i class='fas fa-check-circle me-2'></i>$message</div>";
}

// Function to display error message
function show_error($message) {
    return "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle me-2'></i>$message</div>";
}

// Function to display warning message
function show_warning($message) {
    return "<div class='alert alert-warning'><i class='fas fa-exclamation-circle me-2'></i>$message</div>";
}

// Function to display info message
function show_info($message) {
    return "<div class='alert alert-info'><i class='fas fa-info-circle me-2'></i>$message</div>";
}
?> 