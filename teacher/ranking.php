<?php
require_once '../includes/functions.php';
check_role('teacher');

$teacher_id = $_SESSION['user_id'];

// Get all exams created by this teacher
$stmt = $conn->prepare("SELECT id, title FROM exams WHERE created_by = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams = $stmt->get_result();

// Get selected exam or default to first exam
$selected_exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

// Get rankings for selected exam
$rankings = null;
if ($selected_exam_id > 0) {
    $stmt = $conn->prepare("
        SELECT r.*, u.name as student_name, u.email as student_email, u.college
        FROM ranking r
        JOIN users u ON r.user_id = u.id
        WHERE r.exam_id = ?
        ORDER BY r.rank_position ASC
    ");
    $stmt->bind_param("i", $selected_exam_id);
    $stmt->execute();
    $rankings = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rankings - Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Teacher Panel</h5>
                        <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create_exam.php">
                                Create Exam
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_questions.php">
                                Add Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_students.php">
                                View Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="ranking.php">
                                Rankings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Exam Rankings</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportToCSV()">Export CSV</button>
                        </div>
                    </div>
                </div>

                <!-- Exam Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Select Exam</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-6">
                                <select name="exam_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select an exam to view rankings</option>
                                    <?php while ($exam = $exams->fetch_assoc()): ?>
                                        <option value="<?php echo $exam['id']; ?>" <?php echo $selected_exam_id == $exam['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($exam['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($selected_exam_id > 0 && $rankings && $rankings->num_rows > 0): ?>
                    <!-- Rankings Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5>Rankings for: <?php 
                                $stmt = $conn->prepare("SELECT title FROM exams WHERE id = ?");
                                $stmt->bind_param("i", $selected_exam_id);
                                $stmt->execute();
                                echo htmlspecialchars($stmt->get_result()->fetch_assoc()['title']);
                            ?> (<?php echo $rankings->num_rows; ?> students)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="rankingsTable">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>College</th>
                                            <th>Score</th>
                                            <th>Performance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($ranking = $rankings->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($ranking['rank_position'] == 1): ?>
                                                        <span class="badge bg-warning fs-6">🥇 1st</span>
                                                    <?php elseif ($ranking['rank_position'] == 2): ?>
                                                        <span class="badge bg-secondary fs-6">🥈 2nd</span>
                                                    <?php elseif ($ranking['rank_position'] == 3): ?>
                                                        <span class="badge bg-warning fs-6">🥉 3rd</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info"><?php echo $ranking['rank_position']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($ranking['student_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($ranking['student_email']); ?></td>
                                                <td><?php echo htmlspecialchars($ranking['college']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $ranking['score'] >= 80 ? 'success' : ($ranking['score'] >= 60 ? 'warning' : 'danger'); ?> fs-6">
                                                        <?php echo $ranking['score']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $percentage = ($ranking['score'] / 100) * 100; // Assuming max score is 100
                                                    ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-<?php echo $percentage >= 80 ? 'success' : ($percentage >= 60 ? 'warning' : 'danger'); ?>" 
                                                             style="width: <?php echo $percentage; ?>%">
                                                            <?php echo number_format($percentage, 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Total Participants</h5>
                                    <p class="card-text display-6"><?php echo $rankings->num_rows; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Average Score</h5>
                                    <p class="card-text display-6">
                                        <?php
                                        $stmt = $conn->prepare("SELECT AVG(score) as avg_score FROM ranking WHERE exam_id = ?");
                                        $stmt->bind_param("i", $selected_exam_id);
                                        $stmt->execute();
                                        $avg = $stmt->get_result()->fetch_assoc()['avg_score'];
                                        echo $avg ? number_format($avg, 1) : '0';
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Highest Score</h5>
                                    <p class="card-text display-6">
                                        <?php
                                        $stmt = $conn->prepare("SELECT MAX(score) as max_score FROM ranking WHERE exam_id = ?");
                                        $stmt->bind_param("i", $selected_exam_id);
                                        $stmt->execute();
                                        echo $stmt->get_result()->fetch_assoc()['max_score'];
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="card-title">Pass Rate</h5>
                                    <p class="card-text display-6">
                                        <?php
                                        $stmt = $conn->prepare("SELECT (COUNT(CASE WHEN score >= 60 THEN 1 END) * 100.0 / COUNT(*)) as pass_rate FROM ranking WHERE exam_id = ?");
                                        $stmt->bind_param("i", $selected_exam_id);
                                        $stmt->execute();
                                        $pass_rate = $stmt->get_result()->fetch_assoc()['pass_rate'];
                                        echo $pass_rate ? number_format($pass_rate, 1) : '0';
                                        ?>%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performers -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Top 5 Performers</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT r.*, u.name as student_name, u.college
                                        FROM ranking r
                                        JOIN users u ON r.user_id = u.id
                                        WHERE r.exam_id = ?
                                        ORDER BY r.rank_position ASC
                                        LIMIT 5
                                    ");
                                    $stmt->bind_param("i", $selected_exam_id);
                                    $stmt->execute();
                                    $top_performers = $stmt->get_result();
                                    ?>
                                    <div class="list-group">
                                        <?php while ($performer = $top_performers->fetch_assoc()): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($performer['student_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($performer['college']); ?></small>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-primary fs-6"><?php echo $performer['score']; ?></span>
                                                    <br><small class="text-muted">Rank #<?php echo $performer['rank_position']; ?></small>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Score Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $stmt = $conn->prepare("
                                        SELECT 
                                            CASE 
                                                WHEN score >= 90 THEN 'A+ (90-100)'
                                                WHEN score >= 80 THEN 'A (80-89)'
                                                WHEN score >= 70 THEN 'B (70-79)'
                                                WHEN score >= 60 THEN 'C (60-69)'
                                                ELSE 'F (Below 60)'
                                            END as grade_range,
                                            COUNT(*) as count
                                        FROM ranking 
                                        WHERE exam_id = ?
                                        GROUP BY grade_range
                                        ORDER BY MIN(score) DESC
                                    ");
                                    $stmt->bind_param("i", $selected_exam_id);
                                    $stmt->execute();
                                    $grade_distribution = $stmt->get_result();
                                    
                                    while ($grade = $grade_distribution->fetch_assoc()) {
                                        $percentage = ($grade['count'] / $rankings->num_rows) * 100;
                                        echo "<div class='mb-2'>";
                                        echo "<div class='d-flex justify-content-between'>";
                                        echo "<span>" . $grade['grade_range'] . "</span>";
                                        echo "<span>" . $grade['count'] . " students</span>";
                                        echo "</div>";
                                        echo "<div class='progress'>";
                                        echo "<div class='progress-bar' style='width: " . $percentage . "%'></div>";
                                        echo "</div>";
                                        echo "</div>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($selected_exam_id > 0): ?>
                    <div class="alert alert-info">
                        <h6>No rankings available!</h6>
                        <p>No students have taken this exam yet.</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <h6>Select an exam!</h6>
                        <p>Please select an exam from the dropdown above to view rankings.</p>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToCSV() {
            const table = document.getElementById('rankingsTable');
            if (!table) {
                alert('Please select an exam first to export rankings.');
                return;
            }
            
            const rows = table.querySelectorAll('tr');
            let csv = [];
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td, th');
                const rowData = [];
                
                for (let j = 0; j < cols.length; j++) {
                    rowData.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                }
                
                csv.push(rowData.join(','));
            }
            
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'exam_rankings.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>
</html> 