<?php require_once 'config.php';

if (!isAdminLoggedIn()) {
    redirect('admin-login.php');
}

$listings = getListings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings - <?php echo SITE_NAME; ?> Admin</title>
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
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .page-content { padding: 30px; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { font-size: 1.8rem; color: var(--text-dark); }
        .btn-primary { padding: 12px 24px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .filter-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .filter-tab { padding: 10px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 25px; cursor: pointer; font-size: 0.9rem; transition: var(--transition); }
        .filter-tab:hover, .filter-tab.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .data-table th { background: #f9fafb; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
        .data-table td { font-size: 0.9rem; color: var(--text-dark); }
        .data-table tr:hover { background: #f9fafb; }
        .listing-thumb { width: 60px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.featured { background: #fef3c7; color: #d97706; }
        .status-badge.active { background: #d1fae5; color: #059669; }
        .status-badge.pending { background: #dbeafe; color: #2563eb; }
        .action-btns { display: flex; gap: 8px; }
        .action-btn { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: var(--transition); }
        .action-btn.view { background: #dbeafe; color: #2563eb; }
        .action-btn.edit { background: #d1fae5; color: #059669; }
        .action-btn.delete { background: #fee2e2; color: #dc2626; }
        .action-btn:hover { transform: scale(1.1); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .page-header { flex-direction: column; gap: 15px; } }
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
                    <a href="admin-listings.php" class="sidebar-link active">
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
                    <div class="admin-avatar">A</div>
                    <div class="admin-info">
                        <strong>Administrator</strong>
                        <span>admin@apartmenthub.com</span>
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
                    <h1>Listings</h1>
                    <p>Manage property listings</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="page-header">
                    <h1>Property Listings</h1>
                    <button class="btn-primary" onclick="addListing()">➕ Add Listing</button>
                </div>
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterListings('all', this)">All (<?php echo count($listings); ?>)</button>
                    <button class="filter-tab" onclick="filterListings('featured', this)">Featured</button>
                    <button class="filter-tab" onclick="filterListings('apartment', this)">Apartments</button>
                    <button class="filter-tab" onclick="filterListings('condo', this)">Condos</button>
                    <button class="filter-tab" onclick="filterListings('house', this)">Houses</button>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div class="listing-thumb">🏠</div>
                                        <div>
                                            <strong><?php echo sanitize($listing['title']); ?></strong><br>
                                            <small style="color: #6b7280;"><?php echo sanitize($listing['address']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo ucfirst($listing['type']); ?></td>
                                <td><strong>$<?php echo number_format($listing['price']); ?></strong>/mo</td>
                                <td><?php echo $listing['beds']; ?> bed, <?php echo $listing['baths']; ?> bath, <?php echo number_format($listing['sqft']); ?> sqft</td>
                                <td>
                                    <?php if ($listing['featured']): ?>
                                    <span class="status-badge featured">Featured</span>
                                    <?php else: ?>
                                    <span class="status-badge active">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn view" onclick="viewListing(<?php echo $listing['id']; ?>)">👁️</button>
                                        <button class="action-btn edit" onclick="editListing(<?php echo $listing['id']; ?>)">✏️</button>
                                        <button class="action-btn delete" onclick="deleteListing(<?php echo $listing['id']; ?>)">🗑️</button>
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
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function filterListings(type, btn) {
            document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            showToast('Filtering: ' + type, 'success');
        }
        function addListing() { showToast('Opening add listing form...', 'success'); }
        function viewListing(id) { showToast('Viewing listing #' + id, 'success'); }
        function editListing(id) { showToast('Editing listing #' + id, 'success'); }
        function deleteListing(id) { if (confirm('Delete listing #' + id + '?')) { showToast('Listing #' + id + ' deleted', 'success'); } }
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