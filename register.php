<?php
session_start();
require_once 'db/db_connect.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid request';
    } else {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $gender = sanitize_input($_POST['gender']);
        $mobile = sanitize_input($_POST['mobile']);
        $college = sanitize_input($_POST['college']);
        
        // Validation
        if (empty($name) || empty($email) || empty($password) || empty($gender) || empty($mobile) || empty($college)) {
            $error = 'All fields are required';
        } elseif (!validate_email($email)) {
            $error = 'Please enter a valid email address';
        } elseif (!validate_mobile($mobile)) {
            $error = 'Please enter a valid 10-digit mobile number';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, gender, mobile, college) VALUES (?, ?, ?, 'student', ?, ?, ?)");
                $stmt->bind_param("ssssss", $name, $email, $hashed_password, $gender, $mobile, $college);
                
                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Online Examination System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                Online Exam System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="about.php">
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                    <a class="nav-link" href="contact.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a class="nav-link active" href="register.php">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card fade-in">
                    <div class="card-header text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-plus fa-3x text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-0">Student Registration</h4>
                        <p class="text-muted mb-0">Create your account to start taking exams</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger bounce-in">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success bounce-in">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label fw-semibold">
                                        <i class="fas fa-user me-2 text-primary"></i>Full Name
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                           placeholder="Enter your full name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                           placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2 text-primary"></i>Password
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                           placeholder="Enter password" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label fw-semibold">
                                        <i class="fas fa-lock me-2 text-primary"></i>Confirm Password
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password" 
                                           placeholder="Confirm password" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label fw-semibold">
                                        <i class="fas fa-venus-mars me-2 text-primary"></i>Gender
                                    </label>
                                    <select class="form-select form-select-lg" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="mobile" class="form-label fw-semibold">
                                        <i class="fas fa-phone me-2 text-primary"></i>Mobile Number
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="mobile" name="mobile" 
                                           placeholder="Enter mobile number" maxlength="10" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="college" class="form-label fw-semibold">
                                    <i class="fas fa-university me-2 text-primary"></i>College/Institution
                                </label>
                                <input type="text" class="form-control form-control-lg" id="college" name="college" 
                                       placeholder="Enter your college/institution" required>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? 
                                <a href="login.php" class="text-primary fw-semibold">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-white-50 mb-2">
                        <i class="fas fa-heart text-danger"></i> 
                        Built with modern technologies for the best user experience
                    </p>
                    <div class="text-white-50">
                        <p class="mb-1">
                            <strong>Created by:</strong> 
                            <a href="https://github.com/mmrcode" target="_blank" class="text-white text-decoration-none">
                                <i class="fab fa-github me-1"></i>Mohammad Muqsit Raja
                            </a>
                        </p>
                        <small class="text-white-50">
                            <i class="fas fa-code me-1"></i>Full Stack Developer
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html> 