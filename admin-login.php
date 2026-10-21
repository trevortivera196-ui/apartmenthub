<?php require_once 'config.php';

// Process admin login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    
    // Demo admin credentials
    if ($email === 'admin@apartmenthub.com' && $password === 'admin123') {
        $adminData = [
            'id' => 1,
            'email' => $email,
            'firstName' => 'Admin',
            'lastName' => 'User',
            'role' => 'Super Administrator'
        ];
        saveAdmin($adminData);
        showAlert('Welcome back, Administrator!', 'success');
        redirect('admin-dashboard.php');
    } else {
        showAlert('Invalid admin credentials. Try admin@apartmenthub.com / admin123', 'error');
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    showAlert('Logged out successfully', 'success');
    redirect('admin-login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-family); background: #f3f4f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { width: 100%; max-width: 420px; padding: 20px; }
        .login-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08); overflow: hidden; }
        .login-header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 40px 30px; text-align: center; color: white; }
        .login-header h1 { font-size: 1.8rem; margin-bottom: 8px; }
        .login-header p { opacity: 0.9; font-size: 0.95rem; }
        .login-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; font-size: 0.95rem; }
        .form-group input { width: 100%; padding: 14px 16px; border: 2px solid #e5e7eb; border-radius: 10px; font-size: 1rem; transition: var(--transition); }
        .form-group input:focus { outline: none; border-color: #2563eb; }
        .login-btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: var(--transition); }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); }
        .login-footer { text-align: center; padding: 0 30px 30px; }
        .login-footer a { color: #2563eb; text-decoration: none; font-size: 0.9rem; }
        .login-footer a:hover { text-decoration: underline; }
        .demo-credentials { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .demo-credentials h4 { font-size: 0.85rem; color: #0369a1; margin-bottom: 8px; }
        .demo-credentials p { font-size: 0.8rem; color: #0c4a6e; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 20px; color: #6b7280; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { color: #374151; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>ApartmentHub</h1>
                <p>Admin Portal</p>
            </div>
            <div class="login-body">
                <?php displayAlert(); ?>
                <form method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="admin@apartmenthub.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="login-btn">Sign In as Admin</button>
                </form>
            </div>
            <div class="login-footer">
                <div class="demo-credentials">
                    <h4>Demo Credentials</h4>
                    <p>Email: admin@apartmenthub.com<br>Password: admin123</p>
                </div>
                <a href="index.php">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>