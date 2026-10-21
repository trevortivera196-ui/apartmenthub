<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$notifications = [
    ['type' => 'tour', 'title' => 'Tour Confirmed', 'message' => 'Your tour for Modern Downtown Apartment is confirmed for tomorrow at 2 PM.', 'time' => '2h ago', 'unread' => true],
    ['type' => 'application', 'title' => 'Application Update', 'message' => 'Your application for Luxury Waterfront Condo is being reviewed by our team.', 'time' => '1d ago', 'unread' => true],
    ['type' => 'listing', 'title' => 'New Listing Match', 'message' => 'A new 2BR apartment matching your search criteria is now available.', 'time' => '2d ago', 'unread' => false],
    ['type' => 'payment', 'title' => 'Payment Reminder', 'message' => 'Your rent payment of $1,850 is due on November 1st.', 'time' => '3d ago', 'unread' => false],
    ['type' => 'message', 'title' => 'New Message', 'message' => 'You have a new message from Property Manager at 123 Main Street.', 'time' => '5d ago', 'unread' => false],
    ['type' => 'maintenance', 'title' => 'Maintenance Complete', 'message' => 'The AC repair in your unit has been completed successfully.', 'time' => '1w ago', 'unread' => false]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo SITE_NAME; ?></title>
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
        .tenant-sidebar-link .badge { margin-left: auto; background: var(--primary-color); color: white; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; }
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
        .mark-read-btn { padding: 10px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 0.9rem; color: var(--text-dark); transition: var(--transition); }
        .mark-read-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .notifications-list { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .notification-item { display: flex; gap: 15px; padding: 20px 25px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: var(--transition); }
        .notification-item:last-child { border-bottom: none; }
        .notification-item:hover { background: #f9fafb; }
        .notification-item.unread { background: #f0f9ff; }
        .notification-item.unread:hover { background: #e0f2fe; }
        .notification-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .notification-icon.tour { background: #d1fae5; }
        .notification-icon.application { background: #fef3c7; }
        .notification-icon.listing { background: #dbeafe; }
        .notification-icon.payment { background: #ede9fe; }
        .notification-icon.message { background: #fce7f3; }
        .notification-icon.maintenance { background: #fee2e2; }
        .notification-content { flex: 1; }
        .notification-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
        .notification-title { font-weight: 600; font-size: 1rem; color: var(--text-dark); }
        .notification-time { font-size: 0.8rem; color: #9ca3af; }
        .notification-message { font-size: 0.9rem; color: var(--text-light); margin-bottom: 8px; }
        .notification-actions { display: flex; gap: 10px; }
        .notification-action { padding: 6px 12px; background: #f3f4f6; border: none; border-radius: 6px; font-size: 0.8rem; cursor: pointer; color: var(--text-dark); transition: var(--transition); }
        .notification-action:hover { background: #e5e7eb; }
        .notification-action.primary { background: var(--primary-color); color: white; }
        .notification-action.primary:hover { background: var(--primary-dark); }
        .unread-badge { width: 10px; height: 10px; background: var(--primary-color); border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state .icon { font-size: 4rem; margin-bottom: 15px; opacity: 0.5; }
        .empty-state h3 { color: var(--text-dark); margin-bottom: 8px; }
        .empty-state p { color: var(--text-light); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .tenant-sidebar { transform: translateX(-100%); } .tenant-sidebar.show { transform: translateX(0); } .tenant-main { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .tenant-content { padding: 15px; } .page-header { flex-direction: column; gap: 15px; align-items: flex-start; } .notification-item { flex-direction: column; } .notification-actions { margin-top: 10px; } }
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
                    <a href="tenant-notifications.php" class="tenant-sidebar-link active">
                        <span class="icon">🔔</span>
                        Notifications
                        <span class="badge">2</span>
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
                    <h1>Notifications</h1>
                    <p>Stay updated on your apartment search</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <div class="page-header">
                    <div>
                        <h1>Your Notifications</h1>
                        <p><?php echo count($notifications); ?> total notifications</p>
                    </div>
                    <button class="mark-read-btn" onclick="markAllRead()">✓ Mark All as Read</button>
                </div>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notif): ?>
                    <?php $icons = ['tour' => '📅', 'application' => '📝', 'listing' => '🏠', 'payment' => '💳', 'message' => '💬', 'maintenance' => '🔧']; ?>
                    <div class="notification-item <?php echo $notif['unread'] ? 'unread' : ''; ?>" onclick="openNotification(<?php echo $notif['unread'] ? 'true' : 'false'; ?>)">
                        <div class="notification-icon <?php echo $notif['type']; ?>"><?php echo $icons[$notif['type']]; ?></div>
                        <div class="notification-content">
                            <div class="notification-header">
                                <span class="notification-title"><?php echo sanitize($notif['title']); ?></span>
                                <span class="notification-time"><?php echo sanitize($notif['time']); ?></span>
                            </div>
                            <p class="notification-message"><?php echo sanitize($notif['message']); ?></p>
                            <div class="notification-actions">
                                <button class="notification-action primary">View Details</button>
                                <button class="notification-action" onclick="event.stopPropagation(); dismissNotification(this)">Dismiss</button>
                            </div>
                        </div>
                        <?php if ($notif['unread']): ?>
                        <div class="unread-badge"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function markAllRead() { showToast('All notifications marked as read', 'success'); }
        function openNotification(isUnread) { if (isUnread) showToast('Opening notification...', 'success'); }
        function dismissNotification(btn) { btn.closest('.notification-item').style.opacity = '0'; setTimeout(() => btn.closest('.notification-item').remove(), 300); showToast('Notification dismissed', 'success'); }
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