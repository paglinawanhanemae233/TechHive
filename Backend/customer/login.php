<?php
/**
 * TechHive Customer Login
 * Handles customer authentication
 */

require_once '../includes/session.php';

// Redirect if already logged in as customer
if (isset($_SESSION['customer_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Load customers data
        $customersFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'customers.json';
        $customers = [];
        if (file_exists($customersFile)) {
            $customers = json_decode(file_get_contents($customersFile), true) ?: [];
        }
        
        // Find customer by email
        $customer = null;
        if (is_array($customers)) {
            foreach ($customers as $c) {
                if (is_array($c) && isset($c['email']) && strtolower($c['email']) === strtolower($email)) {
                    $customer = $c;
                    break;
                }
            }
        }
        
        if ($customer && isset($customer['password_hash']) && password_verify($password, $customer['password_hash'])) {
            if (!$customer['is_active']) {
                $error = 'Your account has been deactivated. Please contact support.';
            } else {
                // Set customer session
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
                $_SESSION['customer_email'] = $customer['email'];
                
                // Update last login
                $customer['last_login'] = date('Y-m-d H:i:s');
                $customers[array_search($customer, $customers)] = $customer;
                file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
                
                // Redirect to dashboard or intended page
                $redirect = $_GET['redirect'] ?? 'dashboard.php';
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
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
    <title>Customer Login - TechHive</title>
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

        .register-link {
            text-align: center;
            margin-top: 2rem;
        }

        .register-link a {
            color: var(--neutral-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            color: var(--primary-indigo);
        }

        .back-to-site {
            text-align: center;
            margin-top: 1rem;
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
            <p>Customer Login</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
        </div>

        <div class="back-to-site">
            <a href="../index.html">← Back to TechHive Store</a>
        </div>
    </div>

    <script>
        // Auto-focus email field
        document.getElementById('email').focus();
        
        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please enter both email and password.');
                return;
            }
        });
    </script>
</body>
</html>
