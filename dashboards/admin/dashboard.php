<?php
/**
 * TechHive Admin Dashboard
 * Full system access and user management
 */

require_once '../../includes/session.php';
require_once '../../includes/auth-functions.php';

// Require admin role
requireLogin();
if (!hasRole('admin')) {
    header('Location: ../php-developer/dashboard.php');
    exit();
}

$user = getCurrentUser();
$users = getAllUsers();
$totalUsers = count($users);
$activeUsers = count(array_filter($users, function($u) { return $u['is_active']; }));

// Handle user actions
$action = $_GET['action'] ?? '';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        switch ($action) {
            case 'create_user':
                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => $_POST['password'],
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'role' => $_POST['role']
                ];
                
                $errors = validateUserInput($userData);
                if (empty($errors)) {
                    $result = createUser($userData);
                    if ($result['success']) {
                        $message = 'User created successfully!';
                        header('Location: dashboard.php?message=user_created');
                        exit();
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = implode(', ', $errors);
                }
                break;
                
            case 'update_user':
                $userId = $_POST['user_id'];
                $updateData = [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'email' => $_POST['email'],
                    'is_active' => isset($_POST['is_active'])
                ];
                
                if (isset($_POST['role'])) {
                    $updateData['role'] = $_POST['role'];
                }
                
                $result = updateUser($userId, $updateData);
                if ($result['success']) {
                    $message = 'User updated successfully!';
                    header('Location: dashboard.php?message=user_updated');
                    exit();
                } else {
                    $error = $result['message'];
                }
                break;
                
            case 'delete_user':
                $userId = $_POST['user_id'];
                $result = deleteUser($userId);
                if ($result['success']) {
                    $message = 'User deleted successfully!';
                    header('Location: dashboard.php?message=user_deleted');
                    exit();
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Handle success messages
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'user_created':
            $message = 'User created successfully!';
            break;
        case 'user_updated':
            $message = 'User updated successfully!';
            break;
        case 'user_deleted':
            $message = 'User deleted successfully!';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechHive</title>
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
            background: #f8f9fa;
            color: var(--secondary-blue);
        }

        .header {
            background: linear-gradient(135deg, var(--primary-indigo), var(--secondary-blue));
            color: var(--white);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--light-purple);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 2.5rem;
            color: var(--primary-indigo);
            margin-bottom: 0.5rem;
        }

        .dashboard-subtitle {
            color: var(--neutral-blue);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-indigo);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--neutral-blue);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--primary-indigo);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            background: var(--primary-indigo);
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--accent-blue);
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--white);
        }

        .role-admin {
            background: var(--primary-indigo);
        }

        .role-php_developer {
            background: var(--secondary-blue);
        }

        .role-frontend_developer {
            background: var(--light-purple);
            color: var(--secondary-blue);
        }

        .role-database_manager {
            background: var(--accent-blue);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: var(--white);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .close {
            font-size: 2rem;
            cursor: pointer;
            color: var(--neutral-blue);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--secondary-blue);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-indigo);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            color: var(--white);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-5px);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">TechHive Admin</div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                </div>
                <div>
                    <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">Administrator</div>
                </div>
                <a href="../../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Admin Dashboard</h1>
            <p class="dashboard-subtitle">Complete system control and user management</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeUsers; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4</div>
                <div class="stat-label">User Roles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">100%</div>
                <div class="stat-label">System Access</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-actions">
                <div class="action-card" onclick="openModal('createUserModal')">
                    <div class="action-icon">👤</div>
                    <div>Add New User</div>
                </div>
                <div class="action-card" onclick="window.location.href='user-management.php'">
                    <div class="action-icon">👥</div>
                    <div>Manage Users</div>
                </div>
                <div class="action-card" onclick="window.location.href='system-settings.php'">
                    <div class="action-icon">⚙️</div>
                    <div>System Settings</div>
                </div>
                <div class="action-card" onclick="window.location.href='analytics.php'">
                    <div class="action-icon">📊</div>
                    <div>View Analytics</div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="section">
            <h2 class="section-title">User Management</h2>
            <div style="margin-bottom: 1rem;">
                <button class="btn" onclick="openModal('createUserModal')">Add New User</button>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></div>
                            <div style="font-size: 0.9rem; color: var(--neutral-blue);"><?php echo htmlspecialchars($u['username']); ?></div>
                        </td>
                        <td>
                            <span class="role-badge role-<?php echo $u['role']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $u['role'])); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $u['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo $u['last_login'] ? date('M j, Y g:i A', strtotime($u['last_login'])) : 'Never'; ?></td>
                        <td>
                            <button class="btn btn-small btn-secondary" onclick="editUser('<?php echo $u['user_id']; ?>')">Edit</button>
                            <?php if ($u['user_id'] !== $user['user_id']): ?>
                            <button class="btn btn-small btn-danger" onclick="deleteUser('<?php echo $u['user_id']; ?>')">Delete</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New User</h3>
                <span class="close" onclick="closeModal('createUserModal')">&times;</span>
            </div>
            <form method="POST" action="?action=create_user">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Administrator</option>
                        <option value="php_developer">PHP Developer</option>
                        <option value="frontend_developer">Frontend Developer</option>
                        <option value="database_manager">Database Manager</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                    <button type="submit" class="btn">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editUser(userId) {
            // Implementation for editing user
            alert('Edit user functionality will be implemented');
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '?action=delete_user';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = 'csrf_token';
                csrfToken.value = '<?php echo generateCSRFToken(); ?>';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                form.appendChild(csrfToken);
                form.appendChild(userIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
