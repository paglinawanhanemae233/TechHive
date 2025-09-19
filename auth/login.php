<?php
/**
 * TechHive Login Page
 * Role-based authentication system
 */

require_once '../includes/session.php';
require_once '../includes/auth-functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $result = authenticateUser($username, $password);
            
            if ($result['success']) {
                setUserSession($result['user']);
                logActivity('login', 'User logged in successfully');
                redirectToDashboard();
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Handle logout message
if (isset($_GET['logout'])) {
    $success = 'You have been logged out successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TechHive Admin</title>
    <style>
        :root {
            --primary-indigo: #4A088C;
            --secondary-blue: #120540;
            --accent-blue: #433C73;
            --light-purple: #AEA7D9;
            --neutral-blue: #727FA6;
            --white: #ffffff;
            --black: #000000;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-indigo), var(--secondary-blue));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-indigo), var(--light-purple));
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            color: var(--primary-indigo);
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: var(--neutral-blue);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--secondary-blue);
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-indigo);
        }

        .btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            color: var(--white);
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(74, 8, 140, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .role-info {
            background: linear-gradient(135deg, var(--light-purple), var(--accent-blue));
            color: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
            text-align: center;
        }

        .role-info h3 {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .role-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .role-item {
            background: rgba(255,255,255,0.1);
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .back-to-site {
            text-align: center;
            margin-top: 2rem;
        }

        .back-to-site a {
            color: var(--neutral-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .back-to-site a:hover {
            color: var(--primary-indigo);
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>TechHive</h1>
            <p>Role-Based Access Control</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="role-info">
            <h3>Available Roles</h3>
            <div class="role-list">
                <div class="role-item">
                    <strong>Admin</strong><br>
                    Full system access
                </div>
                <div class="role-item">
                    <strong>PHP Dev</strong><br>
                    Backend development
                </div>
                <div class="role-item">
                    <strong>Frontend Dev</strong><br>
                    UI/UX development
                </div>
                <div class="role-item">
                    <strong>DB Manager</strong><br>
                    Data management
                </div>
            </div>
        </div>

        <div class="back-to-site">
            <a href="../index.html">← Back to TechHive Store</a>
        </div>
    </div>

    <script>
        // Auto-focus username field
        document.getElementById('username').focus();
        
        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please enter both username and password.');
                return;
            }
        });
    </script>
</body>
</html>
