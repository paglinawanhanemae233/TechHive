<?php
/**
 * TechHive Customer Registration
 * Handles customer account creation
 */

require_once '../includes/session.php';

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists
        $customersFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'customers.json';
        $customers = [];
        if (file_exists($customersFile)) {
            $customers = json_decode(file_get_contents($customersFile), true) ?: [];
        }
        
        // Check for existing email
        $emailExists = false;
        if (is_array($customers)) {
            foreach ($customers as $customer) {
                if (is_array($customer) && isset($customer['email']) && strtolower($customer['email']) === strtolower($email)) {
                    $emailExists = true;
                    break;
                }
            }
        }
        
        if ($emailExists) {
            $error = 'An account with this email already exists.';
        } else {
            // Create new customer
            $customerId = 'CUST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $newCustomer = [
                'id' => $customerId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $hashedPassword,
                'date_registered' => date('Y-m-d H:i:s'),
                'last_login' => null,
                'is_active' => true
            ];
            
            $customers[] = $newCustomer;
            file_put_contents($customersFile, json_encode($customers, JSON_PRETTY_PRINT));
            
            $success = 'Account created successfully! You can now login.';
            
            // Clear form
            $firstName = $lastName = $email = $phone = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - TechHive</title>
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

        .register-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

        .btn-secondary {
            background: transparent;
            color: var(--primary-indigo);
            border: 2px solid var(--primary-indigo);
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: var(--primary-indigo);
            color: var(--white);
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

        .login-link {
            text-align: center;
            margin-top: 2rem;
        }

        .login-link a {
            color: var(--neutral-blue);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
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
            .register-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>TechHive</h1>
            <p>Create Your Account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required 
                           value="<?php echo htmlspecialchars($firstName ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required 
                           value="<?php echo htmlspecialchars($lastName ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>

        <div class="back-to-site">
            <a href="../index.html">← Back to TechHive Store</a>
        </div>
    </div>

    <script>
        // Auto-focus first name field
        document.getElementById('first_name').focus();
        
        // Handle form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }
        });
    </script>
</body>
</html>
