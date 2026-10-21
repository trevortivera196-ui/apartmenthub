<?php require_once 'config.php';

if (!isAdminLoggedIn()) {
    redirect('admin-login.php');
}

$applications = [
    ['id' => 1, 'name' => 'John Smith', 'email' => 'john@email.com', 'phone' => '(555) 123-4567', 'property' => 'Modern Downtown Apartment', 'unit' => '4A', 'type' => '2 Bed, 2 Bath', 'income' => 8500, 'credit' => 'Good', 'status' => 'Pending', 'date' => '2024-10-18', 'notes' => 'Excellent rental history'],
    ['id' => 2, 'name' => 'Sarah Johnson', 'email' => 'sarah@email.com', 'phone' => '(555) 234-5678', 'property' => 'Cozy Studio near Campus', 'unit' => '2B', 'type' => 'Studio', 'income' => 4200, 'credit' => 'Fair', 'status' => 'Pending', 'date' => '2024-10-17', 'notes' => 'Student with guarantor'],
    ['id' => 3, 'name' => 'Mike Davis', 'email' => 'mike@email.com', 'phone' => '(555) 345-6789', 'property' => 'Luxury Waterfront Condo', 'unit' => '1C', 'type' => '3 Bed, 2 Bath', 'income' => 12000, 'credit' => 'Excellent', 'status' => 'Approved', 'date' => '2024-10-15', 'notes' => 'Background check complete'],
    ['id' => 4, 'name' => 'Emily Chen', 'email' => 'emily@email.com', 'phone' => '(555) 456-7890', 'property' => 'Spacious Family Home', 'unit' => '5B', 'type' => '4 Bed, 3 Bath', 'income' => 9500, 'credit' => 'Good', 'status' => 'Pending', 'date' => '2024-10-14', 'notes' => 'Pets: 2 cats'],
    ['id' => 5, 'name' => 'David Wilson', 'email' => 'david@email.com', 'phone' => '(555) 567-8901', 'property' => 'Urban Loft with City Views', 'unit' => '3A', 'type' => '1 Bed, 1 Bath', 'income' => 6800, 'credit' => 'Good', 'status' => 'Rejected', 'date' => '2024-10-12', 'notes' => 'Insufficient income'],
    ['id' => 6, 'name' => 'Lisa Thompson', 'email' => 'lisa@email.com', 'phone' => '(555) 678-9012', 'property' => 'Modern Downtown Apartment', 'unit' => '2A', 'type' => '1 Bed, 1 Bath', 'income' => 7500, 'credit' => 'Excellent', 'status' => 'Pending', 'date' => '2024-10-19', 'notes' => 'Move-in requested for Nov 1'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1f2937; color: white; position: fixed; height: 100vh; overflow-y: auto; z-index: 100; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #374151; }
        .sidebar-header .logo { font-size: 1.5rem; font-weight: 700; color: white; margin-bottom: 5px; }
        .sidebar-header .logo span { color: #60a5fa; }
        .sidebar-header p { font-size: 0.85rem; color: #9ca3af; }
        .sidebar-nav { padding: 20px 0; }
        .nav-section { margin-bottom: 25px; }
        .nav-section-title { padding: 0 20px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 10px; }
        .sidebar-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #d1d5db; text-decoration: none; transition: var(--transition); cursor: pointer; border-left: 3px solid transparent; }
        .sidebar-link:hover, .sidebar-link.active { background: #374151; color: white; border-left-color: #60a5fa; }
        .sidebar-link .icon { font-size: 1.2rem; width: 24px; text-align: center; }
        .sidebar-link .badge { margin-left: auto; background: #ef4444; color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; }
        .sidebar-footer { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px; border-top: 1px solid #374151; background: #1f2937; }
        .admin-user { display: flex; align-items: center; gap: 12px; }
        .admin-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .admin-info strong { display: block; font-size: 0.9rem; }
        .admin-info span { font-size: 0.8rem; color: #9ca3af; }
        .main-content { flex: 1; margin-left: 260px; background: #f3f4f6; }
        .top-header { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 50; }
        .header-left h1 { font-size: 1.5rem; color: var(--text-dark); margin-bottom: 3px; }
        .header-left p { font-size: 0.9rem; color: var(--text-light); }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition); position: relative; }
        .page-content { padding: 30px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { font-size: 1.8rem; font-weight: 700; color: var(--primary-color); }
        .stat-card p { font-size: 0.9rem; color: var(--text-light); }
        .stat-card.pending h3 { color: #f59e0b; }
        .stat-card.approved h3 { color: #10b981; }
        .stat-card.rejected h3 { color: #ef4444; }
        
        .filter-bar { background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .filter-bar label { font-weight: 500; color: var(--text-dark); }
        .filter-bar select, .filter-bar input { padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .filter-bar input { flex: 1; min-width: 200px; }
        .filter-bar select:focus, .filter-bar input:focus { outline: none; border-color: var(--primary-color); }
        
        .applications-table { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .table-header { padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .table-header h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .data-table th { background: #f9fafb; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
        .data-table td { font-size: 0.9rem; color: var(--text-dark); }
        .data-table tr:hover { background: #f9fafb; }
        
        .avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; }
        .applicant-cell { display: flex; align-items: center; gap: 12px; }
        .applicant-info strong { display: block; font-size: 0.95rem; }
        .applicant-info span { font-size: 0.8rem; color: #6b7280; }
        
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.approved { background: #d1fae5; color: #059669; }
        .status-badge.rejected { background: #fee2e2; color: #dc2626; }
        
        .action-btns { display: flex; gap: 8px; }
        .action-btn { padding: 8px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 5px; transition: var(--transition); }
        .action-btn.view { background: #dbeafe; color: #2563eb; }
        .action-btn.approve { background: #d1fae5; color: #059669; }
        .action-btn.reject { background: #fee2e2; color: #dc2626; }
        .action-btn:hover { transform: scale(1.05); }
        
        .income-ratio { font-weight: 600; }
        .income-ratio.good { color: #10b981; }
        .income-ratio.warning { color: #f59e0b; }
        .income-ratio.bad { color: #ef4444; }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .filter-bar { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"><?php echo SITE_NAME; ?><span>Hub</span></div>
                <p>Admin Portal</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="admin-dashboard.php" class="sidebar-link">
                        <span class="icon">📊</span>
                        Dashboard
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Property Management</div>
                    <a href="admin-properties.php" class="sidebar-link">
                        <span class="icon">🏠</span>
                        Properties
                    </a>
                    <a href="admin-vacancies.php" class="sidebar-link">
                        <span class="icon">🚪</span>
                        Vacancies
                        <span class="badge">12</span>
                    </a>
                    <a href="admin-listings.php" class="sidebar-link">
                        <span class="icon">📋</span>
                        Listings
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Media</div>
                    <a href="admin-gallery.php" class="sidebar-link">
                        <span class="icon">🖼️</span>
                        Gallery
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Tenant Management</div>
                    <a href="admin-tenants.php" class="sidebar-link">
                        <span class="icon">👥</span>
                        Tenants
                    </a>
                    <a href="admin-applications.php" class="sidebar-link active">
                        <span class="icon">📝</span>
                        Applications
                        <span class="badge"><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'Pending')); ?></span>
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Financial</div>
                    <a href="admin-payroll.php" class="sidebar-link">
                        <span class="icon">💰</span>
                        Payroll
                    </a>
                    <a href="admin-payments.php" class="sidebar-link">
                        <span class="icon">💳</span>
                        Payments
                    </a>
                    <a href="admin-reports.php" class="sidebar-link">
                        <span class="icon">📈</span>
                        Reports
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="admin-messages.php" class="sidebar-link">
                        <span class="icon">💬</span>
                        Messages
                        <span class="badge">3</span>
                    </a>
                    <a href="admin-reviews.php" class="sidebar-link">
                        <span class="icon">⭐</span>
                        Reviews
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="admin-settings.php" class="sidebar-link">
                        <span class="icon">⚙️</span>
                        Settings
                    </a>
                </div>
            </nav>
            <div class="sidebar-footer">
                <div class="admin-user">
                    <div class="admin-avatar">A</div>
                    <div class="admin-info">
                        <strong>Administrator</strong>
                        <span><?php echo ADMIN_EMAIL; ?></span>
                    </div>
                </div>
                <button class="logout-btn" onclick="window.location.href='admin-login.php?logout=1'">
                    <span>🚪</span> Logout
                </button>
            </div>
        </aside>
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Applications</h1>
                    <p>Review and manage tenant applications</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="page-header">
                    <h1>Tenant Applications</h1>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo count($applications); ?></h3>
                        <p>Total Applications</p>
                    </div>
                    <div class="stat-card pending">
                        <h3><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'Pending')); ?></h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="stat-card approved">
                        <h3><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'Approved')); ?></h3>
                        <p>Approved</p>
                    </div>
                    <div class="stat-card rejected">
                        <h3><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'Rejected')); ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
                
                <div class="filter-bar">
                    <label>Search:</label>
                    <input type="text" id="searchInput" placeholder="Search by name or email..." onkeyup="filterApplications()">
                    <label>Status:</label>
                    <select id="statusFilter" onchange="filterApplications()">
                        <option value="all">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    <label>Property:</label>
                    <select id="propertyFilter" onchange="filterApplications()">
                        <option value="all">All Properties</option>
                        <option value="Modern Downtown Apartment">Modern Downtown Apartment</option>
                        <option value="Cozy Studio near Campus">Cozy Studio near Campus</option>
                        <option value="Luxury Waterfront Condo">Luxury Waterfront Condo</option>
                        <option value="Spacious Family Home">Spacious Family Home</option>
                    </select>
                </div>
                
                <div class="applications-table">
                    <div class="table-header">
                        <h3>Application List (<?php echo count($applications); ?>)</h3>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Property</th>
                                <th>Monthly Income</th>
                                <th>Credit Score</th>
                                <th>Income Ratio</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <?php 
                            $rent = 1850; // Base rent estimate
                            $ratio = $app['income'] / $rent;
                            $ratioClass = $ratio >= 3 ? 'good' : ($ratio >= 2.5 ? 'warning' : 'bad');
                            ?>
                            <tr>
                                <td>
                                    <div class="applicant-cell">
                                        <div class="avatar"><?php echo strtoupper(substr($app['name'], 0, 1)); ?></div>
                                        <div class="applicant-info">
                                            <strong><?php echo sanitize($app['name']); ?></strong>
                                            <span><?php echo sanitize($app['email']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo sanitize($app['property']); ?></strong><br>
                                    <small style="color: #6b7280;">Unit <?php echo sanitize($app['unit']); ?> - <?php echo sanitize($app['type']); ?></small>
                                </td>
                                <td>$<?php echo number_format($app['income']); ?></td>
                                <td><?php echo sanitize($app['credit']); ?></td>
                                <td class="income-ratio <?php echo $ratioClass; ?>"><?php echo number_format($ratio, 1); ?>x</td>
                                <td><span class="status-badge <?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($app['date'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn view" onclick="viewApplication(<?php echo $app['id']; ?>)">👁️</button>
                                        <?php if ($app['status'] === 'Pending'): ?>
                                        <button class="action-btn approve" onclick="approveApplication(<?php echo $app['id']; ?>)">✓</button>
                                        <button class="action-btn reject" onclick="rejectApplication(<?php echo $app['id']; ?>)">✗</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- View Application Modal -->
    <div id="viewModal" class="modal-overlay">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Application Details</h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body" id="applicationDetails">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>
    
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        
        function viewApplication(id) {
            const details = document.getElementById('applicationDetails');
            details.innerHTML = '<p>Loading application details...</p>';
            document.getElementById('viewModal').classList.add('active');
            showToast('Viewing application #' + id, 'success');
        }
        
        function approveApplication(id) {
            if (confirm('Approve this application?')) {
                showToast('Application #' + id + ' approved!', 'success');
            }
        }
        
        function rejectApplication(id) {
            if (confirm('Reject this application?')) {
                showToast('Application #' + id + ' rejected!', 'success');
            }
        }
        
        function closeModal() {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            document.body.style.overflow = '';
        }
        
        function filterApplications() {
            showToast('Filtering applications...', 'success');
        }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
        
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        });
    </script>
</body>
</html>