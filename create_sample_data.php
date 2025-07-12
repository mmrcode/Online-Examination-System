<?php
require_once 'db/db_connect.php';

echo "<h2>Creating Comprehensive Sample Data for Online Examination System</h2>";

// Function to create users
function createUser($conn, $name, $email, $password, $role, $gender, $mobile, $college) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, gender, mobile, college) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $gender, $mobile, $college);
        $stmt->execute();
        return $conn->insert_id;
    } else {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        return $user['id'];
    }
}

// Function to create exam
function createExam($conn, $title, $duration, $total_marks, $positive_marks, $negative_marks, $teacher_id) {
    $stmt = $conn->prepare("SELECT id FROM exams WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    if ($stmt->get_result()->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO exams (title, duration, total_marks, positive_marks, negative_marks, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siiddi", $title, $duration, $total_marks, $positive_marks, $negative_marks, $teacher_id);
        $stmt->execute();
        return $conn->insert_id;
    } else {
        $result = $stmt->get_result();
        $exam = $result->fetch_assoc();
        return $exam['id'];
    }
}

// Function to add questions
function addQuestions($conn, $exam_id, $questions) {
    foreach ($questions as $q) {
        $stmt = $conn->prepare("INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $exam_id, $q['question'], $q['option_a'], $q['option_b'], $q['option_c'], $q['option_d'], $q['correct']);
        $stmt->execute();
    }
}

// Create Teachers
echo "<h3>Creating Teachers...</h3>";
$teachers = [
    ['Dr. Sarah Johnson', 'sarah.johnson@university.edu', 'teacher123', 'female', '555-0101', 'Harvard University'],
    ['Prof. Michael Chen', 'michael.chen@university.edu', 'teacher123', 'male', '555-0102', 'MIT'],
    ['Dr. Emily Rodriguez', 'emily.rodriguez@university.edu', 'teacher123', 'female', '555-0103', 'Stanford University'],
    ['Prof. David Kim', 'david.kim@university.edu', 'teacher123', 'male', '555-0104', 'UC Berkeley'],
    ['Dr. Lisa Thompson', 'lisa.thompson@university.edu', 'teacher123', 'female', '555-0105', 'Yale University']
];

$teacher_ids = [];
foreach ($teachers as $teacher) {
    $id = createUser($conn, $teacher[0], $teacher[1], $teacher[2], 'teacher', $teacher[3], $teacher[4], $teacher[5]);
    $teacher_ids[] = $id;
    echo "<p>✅ Teacher created: {$teacher[0]} ({$teacher[1]})</p>";
}

// Create Students
echo "<h3>Creating Students...</h3>";
$students = [
    ['Alex Johnson', 'alex.johnson@student.edu', 'student123', 'male', '555-0201', 'Harvard University'],
    ['Maria Garcia', 'maria.garcia@student.edu', 'student123', 'female', '555-0202', 'MIT'],
    ['James Wilson', 'james.wilson@student.edu', 'student123', 'male', '555-0203', 'Stanford University'],
    ['Sophia Lee', 'sophia.lee@student.edu', 'student123', 'female', '555-0204', 'UC Berkeley'],
    ['Daniel Brown', 'daniel.brown@student.edu', 'student123', 'male', '555-0205', 'Yale University'],
    ['Emma Davis', 'emma.davis@student.edu', 'student123', 'female', '555-0206', 'Harvard University'],
    ['Ryan Miller', 'ryan.miller@student.edu', 'student123', 'male', '555-0207', 'MIT'],
    ['Olivia Taylor', 'olivia.taylor@student.edu', 'student123', 'female', '555-0208', 'Stanford University'],
    ['William Anderson', 'william.anderson@student.edu', 'student123', 'male', '555-0209', 'UC Berkeley'],
    ['Ava Martinez', 'ava.martinez@student.edu', 'student123', 'female', '555-0210', 'Yale University'],
    ['Noah Thompson', 'noah.thompson@student.edu', 'student123', 'male', '555-0211', 'Harvard University'],
    ['Isabella White', 'isabella.white@student.edu', 'student123', 'female', '555-0212', 'MIT'],
    ['Lucas Harris', 'lucas.harris@student.edu', 'student123', 'male', '555-0213', 'Stanford University'],
    ['Mia Clark', 'mia.clark@student.edu', 'student123', 'female', '555-0214', 'UC Berkeley'],
    ['Ethan Lewis', 'ethan.lewis@student.edu', 'student123', 'male', '555-0215', 'Yale University']
];

$student_ids = [];
foreach ($students as $student) {
    $id = createUser($conn, $student[0], $student[1], $student[2], 'student', $student[3], $student[4], $student[5]);
    $student_ids[] = $id;
    echo "<p>✅ Student created: {$student[0]} ({$student[1]})</p>";
}

// Create Exams with Questions
echo "<h3>Creating Exams and Questions...</h3>";

// Mathematics Exam by Dr. Sarah Johnson
$math_exam_id = createExam($conn, 'Advanced Calculus', 60, 100, 2.0, 0.5, $teacher_ids[0]);
$math_questions = [
    [
        'question' => 'What is the derivative of f(x) = x³ + 2x² - 5x + 3?',
        'option_a' => '3x² + 4x - 5',
        'option_b' => '3x² + 4x + 5',
        'option_c' => '3x² - 4x - 5',
        'option_d' => '3x² - 4x + 5',
        'correct' => 'a'
    ],
    [
        'question' => 'What is the integral of ∫(2x + 3)dx?',
        'option_a' => 'x² + 3x + C',
        'option_b' => 'x² + 3x',
        'option_c' => '2x² + 3x + C',
        'option_d' => 'x² + 6x + C',
        'correct' => 'a'
    ],
    [
        'question' => 'What is the limit of (x² - 4)/(x - 2) as x approaches 2?',
        'option_a' => '0',
        'option_b' => '2',
        'option_c' => '4',
        'option_d' => 'Undefined',
        'correct' => 'c'
    ],
    [
        'question' => 'What is the value of sin(π/2)?',
        'option_a' => '0',
        'option_b' => '1',
        'option_c' => '-1',
        'option_d' => 'π/2',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the area under the curve y = x² from x = 0 to x = 3?',
        'option_a' => '6',
        'option_b' => '9',
        'option_c' => '12',
        'option_d' => '18',
        'correct' => 'b'
    ]
];
addQuestions($conn, $math_exam_id, $math_questions);
echo "<p>✅ Advanced Calculus exam created with 5 questions</p>";

// Physics Exam by Prof. Michael Chen
$physics_exam_id = createExam($conn, 'Quantum Physics', 90, 100, 2.0, 0.5, $teacher_ids[1]);
$physics_questions = [
    [
        'question' => 'What is the equation for the energy of a photon?',
        'option_a' => 'E = mc²',
        'option_b' => 'E = hf',
        'option_c' => 'E = ½mv²',
        'option_d' => 'E = mgh',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the uncertainty principle?',
        'option_a' => 'ΔxΔp ≥ h/4π',
        'option_b' => 'ΔxΔp ≤ h/4π',
        'option_c' => 'ΔxΔp = h/4π',
        'option_d' => 'ΔxΔp = 0',
        'correct' => 'a'
    ],
    [
        'question' => 'What is the wave function in quantum mechanics?',
        'option_a' => 'A real number',
        'option_b' => 'A complex number',
        'option_c' => 'An integer',
        'option_d' => 'A vector',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the Schrödinger equation?',
        'option_a' => 'iℏ∂ψ/∂t = Ĥψ',
        'option_b' => 'E = mc²',
        'option_c' => 'F = ma',
        'option_d' => 'PV = nRT',
        'correct' => 'a'
    ],
    [
        'question' => 'What is quantum entanglement?',
        'option_a' => 'When particles are connected',
        'option_b' => 'When particles share properties',
        'option_c' => 'When particles are independent',
        'option_d' => 'When particles collide',
        'correct' => 'b'
    ]
];
addQuestions($conn, $physics_exam_id, $physics_questions);
echo "<p>✅ Quantum Physics exam created with 5 questions</p>";

// Computer Science Exam by Dr. Emily Rodriguez
$cs_exam_id = createExam($conn, 'Data Structures & Algorithms', 75, 100, 2.0, 0.5, $teacher_ids[2]);
$cs_questions = [
    [
        'question' => 'What is the time complexity of binary search?',
        'option_a' => 'O(1)',
        'option_b' => 'O(log n)',
        'option_c' => 'O(n)',
        'option_d' => 'O(n²)',
        'correct' => 'b'
    ],
    [
        'question' => 'Which data structure uses LIFO principle?',
        'option_a' => 'Queue',
        'option_b' => 'Stack',
        'option_c' => 'Tree',
        'option_d' => 'Graph',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the time complexity of bubble sort?',
        'option_a' => 'O(n)',
        'option_b' => 'O(n log n)',
        'option_c' => 'O(n²)',
        'option_d' => 'O(log n)',
        'correct' => 'c'
    ],
    [
        'question' => 'Which algorithm is used for shortest path?',
        'option_a' => 'Bubble Sort',
        'option_b' => 'Dijkstra',
        'option_c' => 'Binary Search',
        'option_d' => 'Quick Sort',
        'correct' => 'b'
    ],
    [
        'question' => 'What is a binary tree?',
        'option_a' => 'A tree with 2 nodes',
        'option_b' => 'A tree where each node has at most 2 children',
        'option_c' => 'A tree with binary data',
        'option_d' => 'A tree with 2 levels',
        'correct' => 'b'
    ]
];
addQuestions($conn, $cs_exam_id, $cs_questions);
echo "<p>✅ Data Structures & Algorithms exam created with 5 questions</p>";

// Chemistry Exam by Prof. David Kim
$chemistry_exam_id = createExam($conn, 'Organic Chemistry', 60, 100, 2.0, 0.5, $teacher_ids[3]);
$chemistry_questions = [
    [
        'question' => 'What is the functional group in alcohols?',
        'option_a' => '-COOH',
        'option_b' => '-OH',
        'option_c' => '-NH₂',
        'option_d' => '-CHO',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the IUPAC name for CH₃CH₂OH?',
        'option_a' => 'Methane',
        'option_b' => 'Ethanol',
        'option_c' => 'Methanol',
        'option_d' => 'Propanol',
        'correct' => 'b'
    ],
    [
        'question' => 'What type of reaction is esterification?',
        'option_a' => 'Addition',
        'option_b' => 'Substitution',
        'option_c' => 'Condensation',
        'option_d' => 'Elimination',
        'correct' => 'c'
    ],
    [
        'question' => 'What is the molecular formula for benzene?',
        'option_a' => 'C₆H₆',
        'option_b' => 'C₆H₁₂',
        'option_c' => 'C₆H₅',
        'option_d' => 'C₆H₄',
        'correct' => 'a'
    ],
    [
        'question' => 'What is the hybridization of carbon in methane?',
        'option_a' => 'sp',
        'option_b' => 'sp²',
        'option_c' => 'sp³',
        'option_d' => 'sp⁴',
        'correct' => 'c'
    ]
];
addQuestions($conn, $chemistry_exam_id, $chemistry_questions);
echo "<p>✅ Organic Chemistry exam created with 5 questions</p>";

// Biology Exam by Dr. Lisa Thompson
$biology_exam_id = createExam($conn, 'Cell Biology', 45, 100, 2.0, 0.5, $teacher_ids[4]);
$biology_questions = [
    [
        'question' => 'What is the powerhouse of the cell?',
        'option_a' => 'Nucleus',
        'option_b' => 'Mitochondria',
        'option_c' => 'Golgi apparatus',
        'option_d' => 'Endoplasmic reticulum',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the process of cell division called?',
        'option_a' => 'Photosynthesis',
        'option_b' => 'Mitosis',
        'option_c' => 'Respiration',
        'option_d' => 'Osmosis',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the function of ribosomes?',
        'option_a' => 'Energy production',
        'option_b' => 'Protein synthesis',
        'option_c' => 'DNA replication',
        'option_d' => 'Waste removal',
        'correct' => 'b'
    ],
    [
        'question' => 'What is the cell membrane made of?',
        'option_a' => 'Phospholipid bilayer',
        'option_b' => 'Cellulose',
        'option_c' => 'Protein only',
        'option_d' => 'Carbohydrates only',
        'correct' => 'a'
    ],
    [
        'question' => 'What is the function of lysosomes?',
        'option_a' => 'Energy storage',
        'option_b' => 'Protein synthesis',
        'option_c' => 'Waste digestion',
        'option_d' => 'DNA storage',
        'correct' => 'c'
    ]
];
addQuestions($conn, $biology_exam_id, $biology_questions);
echo "<p>✅ Cell Biology exam created with 5 questions</p>";

// Create some sample results
echo "<h3>Creating Sample Results...</h3>";
$exams = [$math_exam_id, $physics_exam_id, $cs_exam_id, $chemistry_exam_id, $biology_exam_id];

foreach ($student_ids as $student_id) {
    // Each student takes 2-3 random exams
    $random_exams = array_rand($exams, rand(2, 3));
    if (!is_array($random_exams)) {
        $random_exams = [$random_exams];
    }
    
    foreach ($random_exams as $exam_index) {
        $exam_id = $exams[$exam_index];
        
        // Generate random results
        $total_questions = 5;
        $correct_answers = rand(2, 5);
        $wrong_answers = $total_questions - $correct_answers;
        $score = ($correct_answers * 2) - ($wrong_answers * 0.5);
        
        // Check if result already exists
        $stmt = $conn->prepare("SELECT id FROM results WHERE user_id = ? AND exam_id = ?");
        $stmt->bind_param("ii", $student_id, $exam_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO results (user_id, exam_id, score, total_questions, correct_answers, wrong_answers) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iidiii", $student_id, $exam_id, $score, $total_questions, $correct_answers, $wrong_answers);
            $stmt->execute();
        }
    }
}

echo "<p>✅ Sample results created for students</p>";

// Create some feedback
echo "<h3>Creating Sample Feedback...</h3>";
$feedback_messages = [
    "Great platform! The interface is very user-friendly.",
    "The exam timer feature is very helpful.",
    "Would love to see more practice questions.",
    "The instant results feature is amazing!",
    "The system is very reliable and fast.",
    "Good variety of questions in the exams.",
    "The mobile interface works perfectly.",
    "Clear instructions and easy navigation.",
    "Excellent platform for online learning.",
    "The ranking system motivates students well."
];

foreach ($student_ids as $index => $student_id) {
    if ($index < count($feedback_messages)) {
        $message = $feedback_messages[$index];
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $student_id, $message);
        $stmt->execute();
    }
}

echo "<p>✅ Sample feedback created</p>";

echo "<h3>Sample Data Creation Complete!</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Summary:</h4>";
echo "<ul>";
echo "<li><strong>Teachers:</strong> 5 teachers created</li>";
echo "<li><strong>Students:</strong> 15 students created</li>";
echo "<li><strong>Exams:</strong> 5 exams with 25 total questions</li>";
echo "<li><strong>Results:</strong> Sample results for students</li>";
echo "<li><strong>Feedback:</strong> Sample feedback from students</li>";
echo "</ul>";
echo "</div>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h4>Login Credentials:</h4>";
echo "<p><strong>Teachers:</strong></p>";
echo "<ul>";
foreach ($teachers as $teacher) {
    echo "<li>{$teacher[0]}: {$teacher[1]} / {$teacher[2]}</li>";
}
echo "</ul>";
echo "<p><strong>Students:</strong></p>";
echo "<ul>";
foreach (array_slice($students, 0, 5) as $student) {
    echo "<li>{$student[0]}: {$student[1]} / {$student[2]}</li>";
}
echo "<li>... and 10 more students</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='index.php' class='btn btn-primary'>Go to Homepage</a></p>";
?> 