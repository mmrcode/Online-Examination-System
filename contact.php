<?php
session_start();
require_once 'db/db_connect.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error_message = 'Invalid request';
    } else {
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $subject = sanitize_input($_POST['subject']);
        $message = sanitize_input($_POST['message']);
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error_message = 'Please fill in all fields';
        } else {
            // Store contact message in database (you can create a contacts table)
            // For now, we'll just show a success message
            $success_message = 'Thank you for your message! We will get back to you soon.';
            
            // Clear form data
            $name = $email = $subject = $message = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Online Examination System</title>
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
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                    <a class="nav-link" href="about.php">
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                    <a class="nav-link active" href="contact.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    <?php else: ?>
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-center fade-in">
                <h1 class="display-4 mb-4 text-white fw-bold">
                    <i class="fas fa-envelope me-3"></i>
                    Contact Us
                </h1>
                <p class="lead mb-5 text-white-50 fs-5">
                    Get in touch with us for support, feedback, or any questions
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Content -->
    <div class="container mt-5">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-8 slide-in">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-paper-plane me-2"></i>
                            Send us a Message
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success_message): ?>
                            <div class="alert alert-success bounce-in">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger bounce-in">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error_message; ?>
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
                                           value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                           placeholder="Enter your full name" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-semibold">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                           placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label fw-semibold">
                                    <i class="fas fa-tag me-2 text-primary"></i>Subject
                                </label>
                                <input type="text" class="form-control form-control-lg" id="subject" name="subject" 
                                       value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"
                                       placeholder="Enter subject" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label fw-semibold">
                                    <i class="fas fa-comment me-2 text-primary"></i>Message
                                </label>
                                <textarea class="form-control form-control-lg" id="message" name="message" rows="5" 
                                          placeholder="Enter your message" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-4 slide-in" style="animation-delay: 0.2s;">
                <div class="card h-100">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Get in Touch
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Address</h6>
                                    <p class="text-muted mb-0">123 Education Street<br>Tech City, TC 12345</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Phone</h6>
                                    <p class="text-muted mb-0">+1 (555) 123-4567</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="text-muted mb-0">support@onlineexam.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info text-white rounded-circle p-3 me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Business Hours</h6>
                                    <p class="text-muted mb-0">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM</p>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Links -->
                        <div class="text-center">
                            <h6 class="fw-bold mb-3">Follow Us</h6>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-outline-primary rounded-circle">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-info rounded-circle">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="btn btn-outline-danger rounded-circle">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary rounded-circle">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="fw-bold mb-0">
                            <i class="fas fa-question-circle me-2"></i>
                            Frequently Asked Questions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="accordion" id="faqAccordion1">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq1">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1">
                                                How do I register as a student?
                                            </button>
                                        </h2>
                                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion1">
                                            <div class="accordion-body">
                                                Click on the "Register" button on the homepage and fill in your details. You'll receive a confirmation email once your account is created.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq2">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                                How secure is the examination system?
                                            </button>
                                        </h2>
                                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion1">
                                            <div class="accordion-body">
                                                Our system uses advanced security measures including encrypted connections, secure session management, and anti-cheating features.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="accordion" id="faqAccordion2">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq3">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                                Can I take exams on mobile devices?
                                            </button>
                                        </h2>
                                        <div id="collapse3" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion2">
                                            <div class="accordion-body">
                                                Yes! Our platform is fully responsive and works perfectly on smartphones, tablets, and desktop computers.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faq4">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                                How do I get my exam results?
                                            </button>
                                        </h2>
                                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion2">
                                            <div class="accordion-body">
                                                Results are available immediately after completing an exam. You can view them in your student dashboard under the "Results" section.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        // Add smooth scrolling and enhance animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add intersection observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all cards and sections
            document.querySelectorAll('.card, .fade-in, .slide-in').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html> 