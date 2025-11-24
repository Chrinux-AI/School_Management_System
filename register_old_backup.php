<?php

/**
 * User Registration Page
 */

session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

$message = '';
$message_type = '';

// Check if registration is enabled
$setting = db()->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'registration_enabled'");
$registration_enabled = $setting ? (bool)$setting['setting_value'] : true;

if (!$registration_enabled) {
    $message = 'Registration is currently disabled. Please contact the administrator.';
    $message_type = 'error';
}

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register']) && $registration_enabled) {
    $errors = [];

    try {
        // Validate inputs
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $role = sanitize($_POST['role'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');        // Check for empty required fields
        if (empty($username)) $errors[] = 'Username is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($password)) $errors[] = 'Password is required';
        if (empty($first_name)) $errors[] = 'First name is required';
        if (empty($last_name)) $errors[] = 'Last name is required';
        if (empty($role)) $errors[] = 'Role is required';

        // Validation
        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        if (!in_array($role, ['student', 'parent', 'teacher'])) {
            $errors[] = 'Invalid role selected';
        }

        // Additional validation for students
        if ($role === 'student') {
            if (empty($_POST['date_of_birth'])) {
                $errors[] = 'Date of birth is required for students';
            }
            if (empty($_POST['grade_level'])) {
                $errors[] = 'Grade level is required for students';
            }
        }

        // Check if username exists
        $existing = db()->fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            $errors[] = 'Username already exists';
        }

        // Check if email exists
        $existing = db()->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email already registered';
        }

        if (empty($errors)) {
            // Create user with pending status
            $user_data = [
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => $role,
                'phone' => $phone,
                'status' => 'pending' // Requires admin approval
            ];

            $user_id = db()->insert('users', $user_data);

            if ($user_id) {
                try {
                    // If registering as student, create student record
                    if ($role === 'student') {
                        $student_data = [
                            'student_id' => 'STU' . str_pad($user_id, 6, '0', STR_PAD_LEFT),
                            'user_id' => $user_id,  // Link to user account
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => 'pending',
                            'grade_level' => (int)$_POST['grade_level'],
                            'date_of_birth' => $_POST['date_of_birth'],
                            'created_by' => $user_id,
                            'enrollment_date' => date('Y-m-d')
                        ];
                        db()->insert('students', $student_data);
                    }

                    // If registering as teacher, create teacher record
                    if ($role === 'teacher') {
                        $teacher_data = [
                            'employee_id' => 'TCH' . str_pad($user_id, 6, '0', STR_PAD_LEFT),
                            'user_id' => $user_id,
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'email' => $email,
                            'phone' => $phone,
                            'status' => 'pending',
                            'hire_date' => date('Y-m-d')
                        ];
                        db()->insert('teachers', $teacher_data);
                    }

                    // Log activity
                    log_activity($user_id, 'register', 'users', $user_id);

                    // Send registration notifications
                    send_registration_notification($user_id, $email, $first_name . ' ' . $last_name, $role);

                    $message = 'Registration successful! Your account is pending approval. You will receive access once an administrator approves your registration.';
                    $message_type = 'success';
                } catch (Exception $e) {
                    // If there's an error creating student/teacher record, delete the user
                    db()->delete('users', 'id = ?', [$user_id]);
                    $errors[] = 'Registration failed: ' . $e->getMessage();
                }
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }

        if (!empty($errors)) {
            $message = implode('<br>', $errors);
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = 'Registration error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/attendance/manifest.json">
    <meta name="theme-color" content="#00BFFF">
    <link rel="apple-touch-icon" href="/attendance/assets/images/icons/icon-192x192.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, 30px);
            }
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            padding: 50px;
            position: relative;
            z-index: 1;
            animation: slideIn 0.5s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .logo-icon i {
            font-size: 40px;
            color: white;
        }

        .logo h1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .logo p {
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #1e293b;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group label i {
            color: #667eea;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: shake 0.5s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee 0%, #fdd 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-10px);
            }

            75% {
                transform: translateX(10px);
            }
        }

        .alert i {
            font-size: 20px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .btn:disabled {
            background: #94a3b8;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #64748b;
            font-size: 14px;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            grid-column: 1 / -1;
            margin-bottom: 20px;
        }

        .role-option {
            position: relative;
        }

        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .role-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 20px 10px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }

        .role-option input[type="radio"]:checked+label {
            border-color: #667eea;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8eeff 100%);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .role-option label i {
            font-size: 32px;
            color: #667eea;
        }

        .role-option label span {
            font-weight: 600;
            color: #1e293b;
        }

        .conditional-fields {
            display: none;
            grid-column: 1 / -1;
        }

        .conditional-fields.active {
            display: contents;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .register-container {
                padding: 35px 25px;
            }

            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="cyber-bg">
    <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="starfield"></div>
    <div class="cyber-grid"></div>

        <div class="register-container">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join <?php echo APP_NAME; ?> today!</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($registration_enabled && $message_type !== 'success'): ?>
            <form method="POST" id="registerForm">
                <!-- Role Selection -->
                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" id="role_student" name="role" value="student" required>
                        <label for="role_student">
                            <i class="fas fa-user-graduate"></i>
                            <span>Student</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="role_parent" name="role" value="parent" required>
                        <label for="role_parent">
                            <i class="fas fa-user-friends"></i>
                            <span>Parent</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="role_teacher" name="role" value="teacher" required>
                        <label for="role_teacher">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>Teacher</span>
                        </label>
                    </div>
                </div>

                <div class="form-grid">
                    <!-- Basic Information -->
                    <div class="form-group">
                        <label for="first_name">
                            <i class="fas fa-user"></i> First Name *
                        </label>
                        <input type="text" id="first_name" name="first_name" required placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label for="last_name">
                            <i class="fas fa-user"></i> Last Name *
                        </label>
                        <input type="text" id="last_name" name="last_name" required placeholder="Enter last name">
                    </div>

                    <div class="form-group full-width">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address *
                        </label>
                        <input type="email" id="email" name="email" required placeholder="your.email@example.com">
                    </div>

                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-at"></i> Username *
                        </label>
                        <input type="text" id="username" name="username" required placeholder="Choose username" minlength="3">
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i> Phone
                        </label>
                        <input type="tel" id="phone" name="phone" placeholder="+1234567890">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password *
                        </label>
                        <input type="password" id="password" name="password" required placeholder="Min. 8 characters" minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm Password *
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
                    </div>

                    <!-- Student-specific fields -->
                    <div id="student_fields" class="conditional-fields">
                        <div class="form-group">
                            <label for="date_of_birth">
                                <i class="fas fa-calendar"></i> Date of Birth *
                            </label>
                            <input type="date" id="date_of_birth" name="date_of_birth">
                        </div>

                        <div class="form-group">
                            <label for="grade_level">
                                <i class="fas fa-graduation-cap"></i> Grade Level *
                            </label>
                            <select id="grade_level" name="grade_level">
                                <option value="">Select Level</option>
                                <option value="100">100 Level</option>
                                <option value="200">200 Level</option>
                                <option value="300">300 Level</option>
                                <option value="400">400 Level</option>
                                <option value="500">500 Level</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <button type="submit" name="register" class="btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Create Account</span>
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>

        <div class="login-link">
            Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login here</a>
        </div>
    </div>

    <script>
        // Handle role selection
        const roleInputs = document.querySelectorAll('input[name="role"]');
        const studentFields = document.getElementById('student_fields');

        roleInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value === 'student') {
                    studentFields.classList.add('active');
                    document.getElementById('date_of_birth').required = true;
                    document.getElementById('grade_level').required = true;
                } else {
                    studentFields.classList.remove('active');
                    document.getElementById('date_of_birth').required = false;
                    document.getElementById('grade_level').required = false;
                }
            });
        });

        // Password match validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/pwa-manager.js"></script>
    <script src="../assets/js/pwa-analytics.js"></script>
</body>

</html>