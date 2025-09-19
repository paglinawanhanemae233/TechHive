<?php
/**
 * TechHive Admin Portal
 * Main entry point for role-based access control
 */

require_once '../includes/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    require_once '../includes/auth-functions.php';
    redirectToDashboard();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechHive Admin Portal</title>
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
            background: linear-gradient(135deg, var(--primary-indigo) 0%, var(--secondary-blue) 50%, var(--accent-blue) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.04)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            pointer-events: none;
        }

        .portal-container {
            background: var(--white);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.15), 0 0 0 1px rgba(255,255,255,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 900px;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .portal-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-indigo) 0%, var(--light-purple) 50%, var(--accent-blue) 100%);
            border-radius: 24px 24px 0 0;
        }

        .portal-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .portal-logo {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-indigo), var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .portal-title {
            font-size: 1.5rem;
            color: var(--secondary-blue);
            margin-bottom: 0.5rem;
        }

        .portal-subtitle {
            color: var(--neutral-blue);
            font-size: 1rem;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .role-card {
            background: linear-gradient(135deg, var(--light-purple) 0%, var(--accent-blue) 100%);
            color: var(--white);
            padding: 2rem;
            border-radius: 18px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .role-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            border-color: rgba(255,255,255,0.2);
        }

        .role-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .role-card:hover::before {
            opacity: 1;
        }

        .role-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
        }

        .role-card:hover .role-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .role-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .role-description {
            opacity: 0.9;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .role-features {
            list-style: none;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .role-features li {
            margin-bottom: 0.25rem;
        }

        .role-features li::before {
            content: '✓ ';
            font-weight: bold;
        }

        .login-section {
            text-align: center;
            padding: 2.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 18px;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .login-title {
            font-size: 1.3rem;
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .login-btn {
            background: linear-gradient(135deg, var(--primary-indigo) 0%, var(--accent-blue) 100%);
            color: var(--white);
            padding: 1.2rem 2.5rem;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(74, 8, 140, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(74, 8, 140, 0.4);
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

        .demo-credentials {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: 1px solid #bbdefb;
            border-radius: 18px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .demo-title {
            font-weight: bold;
            color: var(--secondary-blue);
            margin-bottom: 1rem;
        }

        .demo-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .demo-item {
            background: var(--white);
            padding: 1.2rem;
            border-radius: 12px;
            border: 1px solid #e1e5e9;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }

        .demo-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .demo-role {
            font-weight: bold;
            color: var(--primary-indigo);
            margin-bottom: 0.5rem;
        }

        .demo-credentials-text {
            font-size: 0.9rem;
            color: var(--neutral-blue);
        }

        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
            }
            
            .portal-container {
                padding: 2rem 1.5rem;
                margin: 0;
                border-radius: 20px;
            }
            
            .portal-logo {
                font-size: 2.5rem;
            }
            
            .portal-title {
                font-size: 1.3rem;
            }
            
            .roles-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .role-card {
                padding: 1.5rem;
            }
            
            .role-icon {
                font-size: 3rem;
            }
            
            .demo-list {
                grid-template-columns: 1fr;
            }
            
            .login-section {
                padding: 2rem 1.5rem;
            }
            
            .login-btn {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .portal-container {
                padding: 1.5rem 1rem;
            }
            
            .portal-logo {
                font-size: 2rem;
            }
            
            .role-card {
                padding: 1.2rem;
            }
            
            .role-icon {
                font-size: 2.5rem;
            }
            
            .role-title {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="portal-container">
        <div class="portal-header">
            <div class="portal-logo">TechHive</div>
            <h1 class="portal-title">Role-Based Access Control System</h1>
            <p class="portal-subtitle">Team collaboration with specialized dashboards</p>
        </div>

        <div class="roles-grid">
            <div class="role-card" onclick="showLogin('admin')">
                <div class="role-icon">👑</div>
                <div class="role-title">Administrator</div>
                <div class="role-description">Complete system control and user management</div>
                <ul class="role-features">
                    <li>User management</li>
                    <li>System configuration</li>
                    <li>All dashboard access</li>
                    <li>Team coordination</li>
                </ul>
            </div>

            <div class="role-card" onclick="showLogin('php_developer')">
                <div class="role-icon">⚙️</div>
                <div class="role-title">PHP Developer</div>
                <div class="role-description">Backend development and API management</div>
                <ul class="role-features">
                    <li>API endpoint management</li>
                    <li>JSON data processing</li>
                    <li>Backend security</li>
                    <li>Error handling</li>
                </ul>
            </div>

            <div class="role-card" onclick="showLogin('frontend_developer')">
                <div class="role-icon">🎨</div>
                <div class="role-title">Frontend Developer</div>
                <div class="role-description">UI/UX development and design</div>
                <ul class="role-features">
                    <li>UI component development</li>
                    <li>Responsive design</li>
                    <li>User experience</li>
                    <li>Design system</li>
                </ul>
            </div>

            <div class="role-card" onclick="showLogin('database_manager')">
                <div class="role-icon">📊</div>
                <div class="role-title">Database Manager</div>
                <div class="role-description">Data management and content editing</div>
                <ul class="role-features">
                    <li>Product catalog management</li>
                    <li>Content updates</li>
                    <li>Data validation</li>
                    <li>Inventory management</div>
                </ul>
            </div>
        </div>

        <div class="login-section">
            <h2 class="login-title">Ready to Access Your Dashboard?</h2>
            <a href="../auth/login.php" class="login-btn">Login to System</a>
        </div>

        <div class="demo-credentials">
            <div class="demo-title">Demo Credentials for Testing:</div>
            <div class="demo-list">
                <div class="demo-item">
                    <div class="demo-role">Administrator</div>
                    <div class="demo-credentials-text">Username: admin<br>Password: password</div>
                </div>
                <div class="demo-item">
                    <div class="demo-role">PHP Developer</div>
                    <div class="demo-credentials-text">Username: phpdev<br>Password: password</div>
                </div>
                <div class="demo-item">
                    <div class="demo-role">Frontend Developer</div>
                    <div class="demo-credentials-text">Username: frontenddev<br>Password: password</div>
                </div>
                <div class="demo-item">
                    <div class="demo-role">Database Manager</div>
                    <div class="demo-credentials-text">Username: dbmanager<br>Password: password</div>
                </div>
            </div>
        </div>

        <div class="back-to-site">
            <a href="../index.html">← Back to TechHive Store</a>
        </div>
    </div>

    <script>
        function showLogin(role) {
            // Store the selected role in session storage
            sessionStorage.setItem('selectedRole', role);
            
            // Redirect to login page
            window.location.href = '../auth/login.php';
        }

        // Enhanced interactive effects
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
                this.style.boxShadow = '0 25px 50px rgba(0,0,0,0.25)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
            });
        });

        // Enhanced click animation with ripple effect
        document.querySelectorAll('.role-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Create ripple effect
                const ripple = document.createElement('div');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                // Click animation
                this.style.transform = 'translateY(-8px) scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'translateY(-12px) scale(1.02)';
                }, 150);
                
                // Remove ripple after animation
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
