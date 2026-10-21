<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employer = sanitize($_POST['employer'] ?? '');
    $position = sanitize($_POST['position'] ?? '');
    $income = sanitize($_POST['income'] ?? '');
    $startDate = sanitize($_POST['startDate'] ?? '');
    
    $_SESSION['payroll'] = [
        'employer' => $employer,
        'position' => $position,
        'annualIncome' => $income ? str_replace(['$', ','], '', $income) : 0,
        'startDate' => $startDate,
        'status' => 'Pending',
        'docs' => isset($_FILES['documents']) ? count($_FILES['documents']['name']) : 0
    ];
    $message = 'Payroll information saved successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll & Income - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .tenant-wrapper { display: flex; min-height: 100vh; }
        .tenant-sidebar { width: 260px; background: #1f2937; color: white; position: fixed; height: 100vh; overflow-y: auto; z-index: 100; }
        .tenant-sidebar-header { padding: 25px 20px; border-bottom: 1px solid #374151; }
        .tenant-sidebar-header .logo { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 5px; }
        .tenant-sidebar-header .logo span { color: #60a5fa; }
        .tenant-sidebar-header p { font-size: 0.85rem; color: #9ca3af; }
        .tenant-sidebar-nav { padding: 20px 0; }
        .nav-section { margin-bottom: 25px; }
        .nav-section-title { padding: 0 20px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 10px; }
        .tenant-sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #d1d5db; text-decoration: none; transition: var(--transition); cursor: pointer; border-left: 3px solid transparent; }
        .tenant-sidebar-link:hover, .tenant-sidebar-link.active { background: #374151; color: white; border-left-color: #60a5fa; }
        .tenant-sidebar-link .icon { font-size: 1.2rem; width: 24px; text-align: center; }
        .tenant-sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; border-top: 1px solid #374151; background: #1f2937; }
        .tenant-user { display: flex; align-items: center; gap: 12px; }
        .tenant-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .tenant-info strong { display: block; font-size: 0.9rem; }
        .tenant-info span { font-size: 0.8rem; color: #9ca3af; }
        .tenant-main { flex: 1; margin-left: 260px; background: #f3f4f6; }
        .tenant-header { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 50; }
        .tenant-header h1 { font-size: 1.5rem; color: var(--text-dark); margin-bottom: 3px; }
        .tenant-header p { font-size: 0.9rem; color: var(--text-light); }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .tenant-content { padding: 30px; }
        .payroll-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; }
        .payroll-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; }
        .card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #f3f4f6; }
        .card-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .card-icon.blue { background: #dbeafe; }
        .card-icon.green { background: #d1fae5; }
        .card-icon.purple { background: #ede9fe; }
        .card-icon.yellow { background: #fef3c7; }
        .card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .card-subtitle { font-size: 0.85rem; color: var(--text-light); }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
        .info-row:last-child { border-bottom: none; }
        .info-label { font-size: 0.9rem; color: var(--text-light); }
        .info-value { font-size: 0.95rem; font-weight: 500; color: var(--text-dark); }
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.verified { background: #d1fae5; color: #059669; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.rejected { background: #fee2e2; color: #dc2626; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; font-size: 0.95rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.95rem; transition: var(--transition); }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary-color); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .upload-area { border: 2px dashed #e5e7eb; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: var(--transition); }
        .upload-area:hover { border-color: var(--primary-color); background: #f0f7ff; }
        .upload-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .upload-area p { color: var(--text-light); margin-bottom: 5px; }
        .upload-area small { color: #9ca3af; }
        .file-list { margin-top: 15px; }
        .file-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; background: #f9fafb; border-radius: 8px; margin-bottom: 8px; }
        .file-item .name { font-size: 0.9rem; color: var(--text-dark); }
        .file-item .remove { color: #ef4444; cursor: pointer; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-top: 20px; }
        .quick-action { display: flex; align-items: center; gap: 10px; padding: 15px; background: #f9fafb; border-radius: 10px; cursor: pointer; transition: var(--transition); border: none; }
        .quick-action:hover { background: #f3f4f6; }
        .quick-action .icon { font-size: 1.3rem; }
        .quick-action span { font-size: 0.9rem; color: var(--text-dark); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .tenant-sidebar { transform: translateX(-100%); } .tenant-sidebar.show { transform: translateX(0); } .tenant-main { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .tenant-content { padding: 15px; } .payroll-grid { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="tenant-wrapper">
        <aside class="tenant-sidebar" id="sidebar">
            <div class="tenant-sidebar-header">
                <div class="logo">Apartment<span>Hub</span></div>
                <p>Tenant Portal</p>
            </div>
            <nav class="tenant-sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="tenant-dashboard.php" class="tenant-sidebar-link">
                        <span class="icon">📊</span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Search</div>
                    <a href="tenant-listings.php" class="tenant-sidebar-link">
                        <span class="icon">🔍</span>
                        Browse Listings
                    </a>
                    <a href="tenant-vacancies.php" class="tenant-sidebar-link">
                        <span class="icon">🚪</span>
                        Vacancies
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">My Account</div>
                    <a href="tenant-scheduled-tours.php" class="tenant-sidebar-link">
                        <span class="icon">📅</span>
                        Scheduled Tours
                    </a>
                    <a href="tenant-notifications.php" class="tenant-sidebar-link">
                        <span class="icon">🔔</span>
                        Notifications
                    </a>
                    <a href="tenant-messages.php" class="tenant-sidebar-link">
                        <span class="icon">💬</span>
                        Messages
                    </a>
                    <a href="tenant-payroll.php" class="tenant-sidebar-link active">
                        <span class="icon">💰</span>
                        Payroll & Income
                    </a>
                </div>
            </nav>
            <div class="tenant-sidebar-footer">
                <div class="tenant-user">
                    <div class="tenant-avatar">T</div>
                    <div class="tenant-info">
                        <strong>Tenant</strong>
                        <span>tenant@example.com</span>
                    </div>
                </div>
                <button class="logout-btn" onclick="window.location.href='index.php?logout=1'">
                    <span>🚪</span> Logout
                </button>
            </div>
        </aside>
        <main class="tenant-main">
            <header class="tenant-header">
                <div class="header-left">
                    <h1>Payroll & Income</h1>
                    <p>Manage your employment information for applications</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <?php if ($message): ?>
                <div style="background: #d1fae5; color: #059669; padding: 15px 20px; border-radius: 10px; margin-bottom: 25px;"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="payroll-grid">
                    <div class="payroll-card">
                        <div class="card-header">
                            <div class="card-icon blue">💵</div>
                            <div>
                                <div class="card-title">Annual Income</div>
                                <div class="card-subtitle">Your verified annual income</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Employer</span>
                            <span class="info-value"><?php echo sanitize($_SESSION['payroll']['employer'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Position</span>
                            <span class="info-value"><?php echo sanitize($_SESSION['payroll']['position'] ?? 'Not provided'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Annual Income</span>
                            <span class="info-value">$<?php echo number_format($_SESSION['payroll']['annualIncome'] ?? 0); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Start Date</span>
                            <span class="info-value"><?php echo sanitize($_SESSION['payroll']['startDate'] ?? 'Not provided'); ?></span>
                        </div>
                    </div>
                    <div class="payroll-card">
                        <div class="card-header">
                            <div class="card-icon green">📄</div>
                            <div>
                                <div class="card-title">Documents</div>
                                <div class="card-subtitle">Uploaded verification documents</div>
                            </div>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pay Stubs</span>
                            <span class="info-value"><?php echo min(3, $_SESSION['payroll']['docs'] ?? 0); ?> uploaded</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Employment Letter</span>
                            <span class="info-value"><?php echo ($_SESSION['payroll']['docs'] ?? 0) >= 2 ? 'Uploaded' : 'Not uploaded'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tax Returns</span>
                            <span class="info-value"><?php echo ($_SESSION['payroll']['docs'] ?? 0) >= 3 ? 'Uploaded' : 'Not uploaded'; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Verification Status</span>
                            <span class="status-badge <?php echo strtolower($_SESSION['payroll']['status'] ?? 'pending'); ?>"><?php echo $_SESSION['payroll']['status'] ?? 'Pending'; ?></span>
                        </div>
                        <div class="quick-actions">
                            <button class="quick-action">
                                <span class="icon">📎</span>
                                <span>Upload Pay Stub</span>
                            </button>
                            <button class="quick-action">
                                <span class="icon">📋</span>
                                <span>Employment Letter</span>
                            </button>
                        </div>
                    </div>
                    <div class="payroll-card" style="grid-column: 1 / -1;">
                        <div class="card-header">
                            <div class="card-icon purple">📝</div>
                            <div>
                                <div class="card-title">Update Employment Information</div>
                                <div class="card-subtitle">Provide your current employment details</div>
                            </div>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="employer">Employer Name</label>
                                    <input type="text" id="employer" name="employer" placeholder="Company Name">
                                </div>
                                <div class="form-group">
                                    <label for="position">Job Position</label>
                                    <input type="text" id="position" name="position" placeholder="Your job title">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="income">Annual Income ($)</label>
                                    <input type="text" id="income" name="income" placeholder="75000">
                                </div>
                                <div class="form-group">
                                    <label for="startDate">Employment Start Date</label>
                                    <input type="date" id="startDate" name="startDate">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Upload Documents (Pay stubs, Employment letter, etc.)</label>
                                <div class="upload-area" onclick="document.getElementById('documents').click()">
                                    <div class="upload-icon">📤</div>
                                    <p>Drop files here or click to upload</p>
                                    <small>PDF, JPG, PNG up to 10MB each</small>
                                </div>
                                <input type="file" id="documents" name="documents[]" multiple style="display: none;" onchange="handleFileSelect(event)">
                                <div class="file-list" id="fileList"></div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Information</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function handleFileSelect(e) {
            const files = e.target.files;
            const list = document.getElementById('fileList');
            list.innerHTML = '';
            for (let i = 0; i < files.length; i++) {
                list.innerHTML += '<div class="file-item"><span class="name">' + files[i].name + '</span><span class="remove">✕</span></div>';
            }
            showToast(files.length + ' file(s) selected', 'success');
        }
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
    </script>
</body>
</html>