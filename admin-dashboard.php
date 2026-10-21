<?php require_once 'config.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    showAlert('Please login to access the admin dashboard', 'error');
    redirect('admin-login.php');
}

$admin = $_SESSION['admin'] ?? ['firstName' => 'Admin', 'email' => 'admin@apartmenthub.com'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?> Admin</title>
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
        .header-right { display: flex; align-items: center; gap: 20px; }
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition); position: relative; }
        .header-btn:hover { background: #e5e7eb; }
        .dashboard-content { padding: 30px; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: var(--radius); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .stat-icon.blue { background: #dbeafe; }
        .stat-icon.green { background: #d1fae5; }
        .stat-icon.yellow { background: #fef3c7; }
        .stat-icon.purple { background: #ede9fe; }
        .stat-info h3 { font-size: 1.8rem; font-weight: 700; color: var(--text-dark); margin-bottom: 5px; }
        .stat-info p { font-size: 0.9rem; color: var(--text-light); }
        .stat-trend { margin-left: auto; font-size: 0.85rem; font-weight: 500; }
        .stat-trend.up { color: #10b981; }
        .stat-trend.down { color: #ef4444; }
        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .content-card { background: white; border-radius: var(--radius); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }
        .card-header { padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .card-header .link { color: var(--primary-color); font-size: 0.9rem; cursor: pointer; }
        .card-header .link:hover { text-decoration: underline; }
        .card-body { padding: 20px 25px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .data-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
        .data-table td { font-size: 0.9rem; color: var(--text-dark); }
        .data-table tr:hover { background: #f9fafb; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.active { background: #d1fae5; color: #059669; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.inactive { background: #fee2e2; color: #dc2626; }
        .status-badge.available { background: #d1fae5; color: #059669; }
        .status-badge.occupied { background: #dbeafe; color: #2563eb; }
        .action-btns { display: flex; gap: 8px; }
        .action-btn { width: 30px; height: 30px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        .action-btn.edit { background: #dbeafe; color: #2563eb; }
        .action-btn.delete { background: #fee2e2; color: #dc2626; }
        .action-btn:hover { transform: scale(1.1); }
        .activity-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #e5e7eb; }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .activity-icon.new-application { background: #dbeafe; }
        .activity-icon.new-listing { background: #d1fae5; }
        .activity-icon.payment { background: #fef3c7; }
        .activity-icon.message { background: #ede9fe; }
        .activity-content { flex: 1; }
        .activity-content p { font-size: 0.9rem; color: var(--text-dark); margin-bottom: 3px; }
        .activity-content p strong { font-weight: 600; }
        .activity-content span { font-size: 0.8rem; color: #9ca3af; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .quick-action { display: flex; flex-direction: column; align-items: center; gap: 10px; padding: 20px; background: #f9fafb; border-radius: 10px; cursor: pointer; transition: var(--transition); border: none; }
        .quick-action:hover { background: #f3f4f6; transform: translateY(-2px); }
        .quick-action .icon { font-size: 1.8rem; }
        .quick-action span { font-size: 0.9rem; font-weight: 500; color: var(--text-dark); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: var(--shadow-lg); z-index: 200; }
        
        .gallery-preview { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px; }
        .gallery-thumb { width: 100%; aspect-ratio: 1; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2rem; cursor: pointer; transition: var(--transition); position: relative; overflow: hidden; }
        .gallery-thumb:hover { transform: scale(1.05); }
        .gallery-thumb .view-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: var(--transition); }
        .gallery-thumb:hover .view-overlay { opacity: 1; }
        .view-overlay span { color: white; font-size: 0.8rem; background: rgba(0,0,0,0.6); padding: 4px 8px; border-radius: 4px; }
        .gallery-more { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .gallery-more span { font-size: 1.5rem; font-weight: 600; }
        .gallery-more small { font-size: 0.75rem; }
        
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } .content-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } .quick-actions { grid-template-columns: 1fr; } .dashboard-content { padding: 15px; } }
        @media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">Apartment<span>Hub</span></div>
                <p>Admin Portal</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <a href="admin-dashboard.php" class="sidebar-link active">
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
                    <a href="admin-applications.php" class="sidebar-link">
                        <span class="icon">📝</span>
                        Applications
                        <span class="badge">5</span>
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
                    <div class="admin-avatar"><?php echo strtoupper(substr($admin['firstName'], 0, 1)); ?></div>
                    <div class="admin-info">
                        <strong><?php echo sanitize($admin['firstName'] . ' ' . $admin['lastName']); ?></strong>
                        <span><?php echo sanitize($admin['email']); ?></span>
                    </div>
                </div>
                <button class="logout-btn" onclick="adminLogout()">
                    <span>🚪</span> Logout
                </button>
            </div>
        </aside>
        <main class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <h1>Dashboard</h1>
                    <p id="welcomeText">Welcome back! Here's what's happening today.</p>
                </div>
                <div class="header-right">
                    <button class="header-btn">🔍</button>
                    <button class="header-btn">🔔</button>
                </div>
            </header>
            <div class="dashboard-content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon blue">🏠</div>
                        <div class="stat-info">
                            <h3>156</h3>
                            <p>Total Properties</p>
                        </div>
                        <span class="stat-trend up">↑ 12%</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">🚪</div>
                        <div class="stat-info">
                            <h3>12</h3>
                            <p>Vacant Rooms</p>
                        </div>
                        <span class="stat-trend down">↓ 5%</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon yellow">👥</div>
                        <div class="stat-info">
                            <h3>1,248</h3>
                            <p>Active Tenants</p>
                        </div>
                        <span class="stat-trend up">↑ 8%</span>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple">💰</div>
                        <div class="stat-info">
                            <h3>$45.2K</h3>
                            <p>Monthly Revenue</p>
                        </div>
                        <span class="stat-trend up">↑ 15%</span>
                    </div>
                </div>
                <div class="content-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h2>Recent Applications</h2>
                            <span class="link" onclick="window.location.href='admin-applications.php'">View All</span>
                        </div>
                        <div class="card-body">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Applicant</th>
                                        <th>Property</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>John Smith</td>
                                        <td>Cozy Studio Room</td>
                                        <td><span class="status-badge pending">Pending</span></td>
                                        <td>Oct 19, 2026</td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="action-btn edit">✓</button>
                                                <button class="action-btn delete">✗</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sarah Johnson</td>
                                        <td>Bright 1BR Room</td>
                                        <td><span class="status-badge pending">Pending</span></td>
                                        <td>Oct 18, 2026</td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="action-btn edit">✓</button>
                                                <button class="action-btn delete">✗</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mike Davis</td>
                                        <td>Spacious 2BR Suite</td>
                                        <td><span class="status-badge active">Approved</span></td>
                                        <td>Oct 17, 2026</td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="action-btn edit">✓</button>
                                                <button class="action-btn delete">✗</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Emily Chen</td>
                                        <td>Garden View Room</td>
                                        <td><span class="status-badge pending">Pending</span></td>
                                        <td>Oct 16, 2026</td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="action-btn edit">✓</button>
                                                <button class="action-btn delete">✗</button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 25px;">
                        <div class="content-card">
                            <div class="card-header">
                                <h2>Recent Activity</h2>
                            </div>
                            <div class="card-body">
                                <div class="activity-item">
                                    <div class="activity-icon new-application">📝</div>
                                    <div class="activity-content">
                                        <p><strong>John Smith</strong> applied for <strong>Cozy Studio</strong></p>
                                        <span>2 hours ago</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon payment">💳</div>
                                    <div class="activity-content">
                                        <p><strong>Payment received</strong> from Sarah Johnson - $1,200</p>
                                        <span>4 hours ago</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon new-listing">🏠</div>
                                    <div class="activity-content">
                                        <p>New listing added: <strong>Penthouse Room</strong></p>
                                        <span>Yesterday</span>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon message">💬</div>
                                    <div class="activity-content">
                                        <p><strong>New message</strong> from tenant at 123 Main St</p>
                                        <span>Yesterday</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-card">
                            <div class="card-header">
                                <h2>Quick Actions</h2>
                            </div>
                            <div class="card-body">
                                <div class="quick-actions">
                                    <button class="quick-action" onclick="window.location.href='admin-properties.php'">
                                        <span class="icon">➕</span>
                                        <span>Add Property</span>
                                    </button>
                                    <button class="quick-action" onclick="window.location.href='admin-vacancies.php'">
                                        <span class="icon">🚪</span>
                                        <span>Add Vacancy</span>
                                    </button>
                                    <button class="quick-action" onclick="window.location.href='admin-applications.php'">
                                        <span class="icon">📝</span>
                                        <span>Review Apps</span>
                                    </button>
                                    <button class="quick-action" onclick="window.location.href='admin-reports.php'">
                                        <span class="icon">📊</span>
                                        <span>View Reports</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h2>Vacancy Overview by Property</h2>
                    </div>
                    <div class="card-body">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Total Units</th>
                                    <th>Occupied</th>
                                    <th>Vacant</th>
                                    <th>Occupancy Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>123 Main Street</td>
                                    <td>24</td>
                                    <td>22</td>
                                    <td>2</td>
                                    <td>92%</td>
                                    <td><span class="status-badge active">Good</span></td>
                                </tr>
                                <tr>
                                    <td>456 Oak Avenue</td>
                                    <td>18</td>
                                    <td>15</td>
                                    <td>3</td>
                                    <td>83%</td>
                                    <td><span class="status-badge active">Good</span></td>
                                </tr>
                                <tr>
                                    <td>789 Pine Road</td>
                                    <td>12</td>
                                    <td>10</td>
                                    <td>2</td>
                                    <td>83%</td>
                                    <td><span class="status-badge active">Good</span></td>
                                </tr>
                                <tr>
                                    <td>555 College Ave</td>
                                    <td>36</td>
                                    <td>30</td>
                                    <td>6</td>
                                    <td>83%</td>
                                    <td><span class="status-badge active">Good</span></td>
                                </tr>
                                <tr>
                                    <td>888 Industrial Blvd</td>
                                    <td>20</td>
                                    <td>18</td>
                                    <td>2</td>
                                    <td>90%</td>
                                    <td><span class="status-badge active">Good</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="content-card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h2>🏠 Property Gallery</h2>
                        <span class="link" onclick="window.location.href='admin-gallery.php'">Manage Gallery →</span>
                    </div>
                    <div class="card-body">
                        <div class="gallery-preview">
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">🛋️</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">🍳</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🛏️</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">🚿</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">🌅</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);">🪴</div>
                            <div class="gallery-thumb" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">🪑</div>
                            <div class="gallery-thumb gallery-more" onclick="window.location.href='admin-gallery.php'">
                                <span>+24</span>
                                <small>View All</small>
                            </div>
                        </div>
                        <p style="margin-top: 15px; color: #6b7280; font-size: 0.9rem;">Total images: <strong>24</strong> | <strong>6</strong> properties</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            setInterval(updateDateTime, 60000);
        });
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            document.querySelector('.header-left p').textContent = now.toLocaleDateString('en-US', options);
        }
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function showSection(section) {
            document.querySelectorAll('.sidebar-link').forEach(link => link.classList.remove('active'));
            event.target.closest('.sidebar-link').classList.add('active');
            if (window.innerWidth <= 992) { document.getElementById('sidebar').classList.remove('show'); }
            showToast('Navigating to ' + section + '...', 'success');
        }
        function addNewProperty() { showToast('Opening property form...', 'success'); }
        function addNewVacancy() { showToast('Opening vacancy form...', 'success'); }
        function adminLogout() { window.location.href = 'admin-login.php?logout=1'; }
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
    </script>
</body>
</html>