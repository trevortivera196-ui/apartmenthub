<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$tours = [
    ['id' => 1, 'property' => 'Modern Downtown Apartment', 'address' => '123 Main Street, Unit 4A', 'date' => '2026-10-20', 'time' => '14:00', 'status' => 'confirmed'],
    ['id' => 2, 'property' => 'Luxury Waterfront Condo', 'address' => '789 Harbor View, Unit 2B', 'date' => '2026-10-22', 'time' => '10:00', 'status' => 'confirmed'],
    ['id' => 3, 'property' => 'Spacious Family Home', 'address' => '321 Oak Lane', 'date' => '2026-10-25', 'time' => '11:00', 'status' => 'pending']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Tours - <?php echo SITE_NAME; ?></title>
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
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { font-size: 1.8rem; color: var(--text-dark); margin-bottom: 5px; }
        .page-header p { color: var(--text-light); }
        .schedule-btn { padding: 12px 24px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.95rem; font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .schedule-btn:hover { background: var(--primary-dark); }
        .tours-container { display: flex; flex-direction: column; gap: 20px; }
        .tour-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; display: flex; gap: 25px; align-items: center; transition: var(--transition); }
        .tour-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .tour-image { width: 120px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; flex-shrink: 0; }
        .tour-details { flex: 1; }
        .tour-title { font-size: 1.2rem; font-weight: 600; color: var(--text-dark); margin-bottom: 5px; }
        .tour-address { color: var(--text-light); margin-bottom: 12px; font-size: 0.9rem; }
        .tour-meta { display: flex; gap: 20px; flex-wrap: wrap; }
        .tour-meta-item { display: flex; align-items: center; gap: 6px; font-size: 0.9rem; color: var(--text-dark); }
        .tour-meta-item .icon { font-size: 1.1rem; }
        .tour-status { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .tour-status.confirmed { background: #d1fae5; color: #059669; }
        .tour-status.pending { background: #fef3c7; color: #d97706; }
        .tour-status.cancelled { background: #fee2e2; color: #dc2626; }
        .tour-actions { display: flex; flex-direction: column; gap: 10px; }
        .tour-action-btn { padding: 10px 20px; border-radius: 8px; font-size: 0.9rem; cursor: pointer; transition: var(--transition); border: none; }
        .tour-action-btn.primary { background: var(--primary-color); color: white; }
        .tour-action-btn.secondary { background: white; border: 2px solid #e5e7eb; color: var(--text-dark); }
        .tour-action-btn.danger { background: #fee2e2; color: #dc2626; }
        .tour-action-btn:hover { transform: translateY(-2px); }
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 12px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; }
        .empty-state h3 { color: var(--text-dark); margin-bottom: 8px; }
        .empty-state p { color: var(--text-light); margin-bottom: 20px; }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .tenant-sidebar { transform: translateX(-100%); } .tenant-sidebar.show { transform: translateX(0); } .tenant-main { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .tenant-content { padding: 15px; } .page-header { flex-direction: column; gap: 15px; align-items: flex-start; } .tour-card { flex-direction: column; text-align: center; } .tour-actions { flex-direction: row; justify-content: center; } .tour-meta { justify-content: center; } }
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
                    <a href="tenant-scheduled-tours.php" class="tenant-sidebar-link active">
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
                    <a href="tenant-payroll.php" class="tenant-sidebar-link">
                        <span class="icon">💰</span>
                        Payroll
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
                    <h1>Scheduled Tours</h1>
                    <p>Manage your property viewings</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <div class="page-header">
                    <div>
                        <h1>Your Tours</h1>
                        <p><?php echo count($tours); ?> scheduled tour(s)</p>
                    </div>
                    <button class="schedule-btn" onclick="showTourModal()">📅 Schedule New Tour</button>
                </div>
                <div class="tours-container">
                    <?php foreach ($tours as $tour): ?>
                    <?php $formattedDate = date('M j, Y', strtotime($tour['date'])); $formattedTime = date('g:i A', strtotime($tour['time'])); ?>
                    <div class="tour-card">
                        <div class="tour-image">🏠</div>
                        <div class="tour-details">
                            <h3 class="tour-title"><?php echo sanitize($tour['property']); ?></h3>
                            <p class="tour-address">📍 <?php echo sanitize($tour['address']); ?></p>
                            <div class="tour-meta">
                                <span class="tour-meta-item"><span class="icon">📅</span> <?php echo $formattedDate; ?></span>
                                <span class="tour-meta-item"><span class="icon">🕐</span> <?php echo $formattedTime; ?></span>
                                <span class="tour-status <?php echo $tour['status']; ?>"><?php echo ucfirst($tour['status']); ?></span>
                            </div>
                        </div>
                        <div class="tour-actions">
                            <button class="tour-action-btn primary" onclick="viewProperty(<?php echo $tour['id']; ?>)">View Property</button>
                            <button class="tour-action-btn secondary" onclick="rescheduleTour(<?php echo $tour['id']; ?>)">Reschedule</button>
                            <button class="tour-action-btn danger" onclick="cancelTour(<?php echo $tour['id']; ?>)">Cancel</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function showTourModal() { showToast('Opening tour scheduler...', 'success'); }
        function viewProperty(id) { showToast('Opening property #' + id, 'success'); }
        function rescheduleTour(id) { showToast('Opening reschedule for tour #' + id, 'success'); }
        function cancelTour(id) { if (confirm('Cancel this tour?')) { showToast('Tour #' + id + ' cancelled', 'success'); } }
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