<?php require_once 'config.php';

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    showAlert('Please login to access the dashboard', 'error');
    redirect('index.php');
}

// Get user data
$user = $_SESSION['user'] ?? ['firstName' => 'Tenant', 'email' => 'tenant@example.com'];

// Get saved listings
$savedListings = isset($_SESSION['saved_listings']) ? $_SESSION['saved_listings'] : [];
$listings = getListings();
$savedListingDetails = array_filter($listings, function($l) use ($savedListings) {
    return in_array($l['id'], $savedListings);
});

// Get notifications
$notifications = [
    ['type' => 'tour', 'title' => 'Tour Confirmed', 'message' => 'Your tour for Modern Downtown Apartment is confirmed for tomorrow at 2 PM.', 'time' => '2h ago', 'unread' => true],
    ['type' => 'application', 'title' => 'Application Update', 'message' => 'Your application for Luxury Waterfront Condo is being reviewed.', 'time' => '1d ago', 'unread' => true],
    ['type' => 'listing', 'title' => 'New Listing Match', 'message' => 'A new 2BR apartment matching your search is now available.', 'time' => '2d ago', 'unread' => false]
];

// Get messages
$messages = [
    ['sender' => 'ApartmentHub Support', 'initials' => 'A', 'message' => 'Welcome to ApartmentHub! Let us know if you need help...', 'time' => '2h ago', 'unread' => true],
    ['sender' => 'Modern Downtown Apt', 'initials' => 'M', 'message' => 'Your tour request has been confirmed for tomorrow at 2 PM.', 'time' => '1d ago', 'unread' => true]
];

// Get tours
$tours = [
    ['property' => 'Modern Downtown Apartment', 'date' => 'Oct 20, 2026 at 2:00 PM'],
    ['property' => 'Luxury Waterfront Condo', 'date' => 'Oct 22, 2026 at 10:00 AM'}
];

// Get payroll data
$payroll = $_SESSION['payroll'] ?? ['annualIncome' => 0, 'employer' => 'Add your employment info', 'docs' => 0, 'status' => 'Pending'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - <?php echo SITE_NAME; ?></title>
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
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition); position: relative; }
        .tenant-content { padding: 30px; }
        .welcome-banner { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .welcome-banner h2 { margin-bottom: 10px; }
        .welcome-banner p { opacity: 0.9; }
        
        .tenant-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .tenant-stat { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px; }
        .tenant-stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .tenant-stat-icon.blue { background: #dbeafe; }
        .tenant-stat-icon.green { background: #d1fae5; }
        .tenant-stat-icon.yellow { background: #fef3c7; }
        .tenant-stat-icon.purple { background: #ede9fe; }
        .tenant-stat-info h3 { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); }
        .tenant-stat-info p { font-size: 0.9rem; color: var(--text-light); }
        
        .tenant-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .tenant-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .tenant-card-header { padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .tenant-card-header h2 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .tenant-card-header .link { color: var(--primary-color); font-size: 0.9rem; cursor: pointer; }
        .tenant-card-header .link:hover { text-decoration: underline; }
        .tenant-card-body { padding: 20px 25px; }
        
        .saved-listing { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #e5e7eb; align-items: center; }
        .saved-listing:last-child { border-bottom: none; padding-bottom: 0; }
        .saved-listing:first-child { padding-top: 0; }
        .saved-thumb { width: 80px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
        .saved-info { flex: 1; }
        .saved-info h4 { font-size: 1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 5px; }
        .saved-info p { font-size: 0.85rem; color: var(--text-light); margin-bottom: 5px; }
        .saved-price { font-size: 1.1rem; font-weight: 700; color: var(--primary-color); }
        .saved-actions { display: flex; gap: 8px; }
        .saved-btn { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: var(--transition); }
        .saved-btn.view { background: #dbeafe; color: #2563eb; }
        .saved-btn.delete { background: #fee2e2; color: #dc2626; }
        .saved-btn:hover { transform: scale(1.1); }
        
        .message-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
        .message-item:last-child { border-bottom: none; }
        .message-avatar { width: 40px; height: 40px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; flex-shrink: 0; }
        .message-content { flex: 1; }
        .message-header { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .message-sender { font-weight: 600; font-size: 0.9rem; color: var(--text-dark); }
        .message-time { font-size: 0.75rem; color: #9ca3af; }
        .message-text { font-size: 0.85rem; color: var(--text-light); }
        .message-unread { width: 8px; height: 8px; background: var(--primary-color); border-radius: 50%; flex-shrink: 0; margin-top: 6px; }
        
        .activity-item { display: flex; gap: 15px; padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .activity-icon.saved { background: #dbeafe; }
        .activity-icon.tour { background: #d1fae5; }
        .activity-icon.application { background: #fef3c7; }
        .activity-content p { font-size: 0.9rem; color: var(--text-dark); margin-bottom: 3px; }
        .activity-content span { font-size: 0.8rem; color: #9ca3af; }
        
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.verified { background: #d1fae5; color: #059669; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.rejected { background: #fee2e2; color: #dc2626; }
        
        .quick-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        .quick-action { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px; background: #f9fafb; border-radius: 10px; cursor: pointer; transition: var(--transition); border: none; }
        .quick-action:hover { background: #f3f4f6; transform: translateY(-2px); }
        .quick-action .icon { font-size: 1.5rem; }
        .quick-action span { font-size: 0.85rem; font-weight: 500; color: var(--text-dark); }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        
        @media (max-width: 992px) { 
            .tenant-sidebar { transform: translateX(-100%); } 
            .tenant-sidebar.show { transform: translateX(0); } 
            .tenant-main { margin-left: 0; } 
            .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } 
            .tenant-grid { grid-template-columns: 1fr; } 
        }
        @media (max-width: 768px) { 
            .tenant-content { padding: 15px; } 
            .tenant-stats { grid-template-columns: repeat(2, 1fr); } 
            .quick-actions { grid-template-columns: 1fr; } 
        }
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
                    <a href="tenant-dashboard.php" class="tenant-sidebar-link active">
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
                    <a href="tenant-listings.php" class="tenant-sidebar-link">
                        <span class="icon">❤️</span>
                        Saved Listings
                        <span class="badge" id="savedCount"><?php echo count($savedListings); ?></span>
                    </a>
                    <a href="tenant-scheduled-tours.php" class="tenant-sidebar-link">
                        <span class="icon">📅</span>
                        Scheduled Tours
                    </a>
                    <a href="tenant-notifications.php" class="tenant-sidebar-link">
                        <span class="icon">🔔</span>
                        Notifications
                    </a>
                    <a href="#" class="tenant-sidebar-link" onclick="showApplicationModal(); return false;">
                        <span class="icon">📝</span>
                        Applications
                    </a>
                    <a href="tenant-messages.php" class="tenant-sidebar-link">
                        <span class="icon">💬</span>
                        Messages
                        <span class="badge">2</span>
                    </a>
                    <a href="tenant-payroll.php" class="tenant-sidebar-link">
                        <span class="icon">💰</span>
                        Payroll & Income
                    </a>
                </div>
            </nav>
            <div class="tenant-sidebar-footer">
                <div class="tenant-user">
                    <div class="tenant-avatar" id="userInitial"><?php echo strtoupper(substr($user['firstName'], 0, 1)); ?></div>
                    <div class="tenant-info">
                        <strong><?php echo sanitize($user['firstName']); ?></strong>
                        <span><?php echo sanitize($user['email']); ?></span>
                    </div>
                </div>
                <button class="logout-btn" onclick="tenantLogout()">
                    <span>🚪</span> Logout
                </button>
            </div>
        </aside>
        <main class="tenant-main">
            <header class="tenant-header">
                <div class="header-left">
                    <h1>My Dashboard</h1>
                    <p>Welcome back, <?php echo sanitize($user['firstName']); ?>! Here's your activity.</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <div class="welcome-banner">
                    <h2>Find Your Perfect Home</h2>
                    <p>Browse new listings and schedule tours today!</p>
                </div>
                
                <div class="tenant-stats">
                    <div class="tenant-stat">
                        <div class="tenant-stat-icon blue">❤️</div>
                        <div class="tenant-stat-info">
                            <h3 id="statSaved"><?php echo count($savedListings); ?></h3>
                            <p>Saved Listings</p>
                        </div>
                    </div>
                    <div class="tenant-stat">
                        <div class="tenant-stat-icon green">📅</div>
                        <div class="tenant-stat-info">
                            <h3><?php echo count($tours); ?></h3>
                            <p>Scheduled Tours</p>
                        </div>
                    </div>
                    <div class="tenant-stat">
                        <div class="tenant-stat-icon yellow">📝</div>
                        <div class="tenant-stat-info">
                            <h3>0</h3>
                            <p>Applications</p>
                        </div>
                    </div>
                    <div class="tenant-stat">
                        <div class="tenant-stat-icon purple">💬</div>
                        <div class="tenant-stat-info">
                            <h3>2</h3>
                            <p>New Messages</p>
                        </div>
                    </div>
                </div>
                
                <div class="tenant-grid">
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>Saved Listings</h2>
                            <span class="link" onclick="window.location.href='tenant-listings.php'">Browse More</span>
                        </div>
                        <div class="tenant-card-body" id="savedListingsContainer">
                            <?php if (empty($savedListingDetails)): ?>
                            <p style="color: var(--text-light); text-align: center; padding: 30px;">
                                No saved listings yet. Browse listings and click the heart icon to save them!
                            </p>
                            <?php else: ?>
                            <?php foreach ($savedListingDetails as $listing): ?>
                            <div class="saved-listing">
                                <div class="saved-thumb">🏠</div>
                                <div class="saved-info">
                                    <h4><?php echo sanitize($listing['title']); ?></h4>
                                    <p><?php echo sanitize($listing['address']); ?></p>
                                    <div class="saved-price">$<?php echo number_format($listing['price']); ?>/mo</div>
                                </div>
                                <div class="saved-actions">
                                    <button class="saved-btn view" onclick="window.location.href='tenant-listings.php'">👁️</button>
                                    <button class="saved-btn delete" onclick="removeSaved(<?php echo $listing['id']; ?>)">🗑️</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>🔔 Notifications</h2>
                            <span class="link" onclick="window.location.href='tenant-notifications.php'">View All</span>
                        </div>
                        <div class="tenant-card-body" id="notificationsContainer">
                            <?php foreach ($notifications as $notif): ?>
                            <div class="message-item" style="<?php echo $notif['unread'] ? 'background: #f0f9ff;' : ''; ?>">
                                <div class="message-avatar" style="background: <?php echo $notif['type'] === 'tour' ? '#10b981' : ($notif['type'] === 'application' ? '#f59e0b' : '#3b82f6'); ?>;">
                                    <?php echo $notif['type'] === 'tour' ? '📅' : ($notif['type'] === 'application' ? '📝' : '🏠'); ?>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-sender"><?php echo sanitize($notif['title']); ?></span>
                                        <span class="message-time"><?php echo sanitize($notif['time']); ?></span>
                                    </div>
                                    <p class="message-text"><?php echo sanitize($notif['message']); ?></p>
                                </div>
                                <?php if ($notif['unread']): ?>
                                <div class="message-unread"></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>💬 Messages</h2>
                            <span class="link" onclick="window.location.href='tenant-messages.php'">View All</span>
                        </div>
                        <div class="tenant-card-body" id="messagesContainer">
                            <?php foreach ($messages as $msg): ?>
                            <div class="message-item" style="<?php echo $msg['unread'] ? 'background: #f0f9ff;' : ''; ?>">
                                <div class="message-avatar"><?php echo sanitize($msg['initials']); ?></div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <span class="message-sender"><?php echo sanitize($msg['sender']); ?></span>
                                        <span class="message-time"><?php echo sanitize($msg['time']); ?></span>
                                    </div>
                                    <p class="message-text"><?php echo sanitize($msg['message']); ?></p>
                                </div>
                                <?php if ($msg['unread']): ?>
                                <div class="message-unread"></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>💰 Payroll & Income</h2>
                            <span class="link" onclick="window.location.href='tenant-payroll.php'">Manage</span>
                        </div>
                        <div class="tenant-card-body" id="payrollContainer">
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #dbeafe;">💵</div>
                                <div class="activity-content">
                                    <p><strong>Annual Income:</strong> $<?php echo number_format($payroll['annualIncome']); ?></p>
                                    <span><?php echo sanitize($payroll['employer']); ?></span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: #d1fae5;">📄</div>
                                <div class="activity-content">
                                    <p><strong>Documents:</strong> <?php echo $payroll['docs']; ?> uploaded</p>
                                    <span>Verification status: <span class="status-badge <?php echo strtolower($payroll['status']); ?>"><?php echo $payroll['status']; ?></span></span>
                                </div>
                            </div>
                            <div style="margin-top: 15px;">
                                <button class="quick-action" style="background: #f0fdf4; border: 1px solid #bbf7d0;" onclick="window.location.href='tenant-payroll.php'">
                                    <span class="icon">📎</span>
                                    <span>Upload Documents</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>📅 Scheduled Tours</h2>
                            <span class="link" onclick="window.location.href='tenant-scheduled-tours.php'">View All</span>
                        </div>
                        <div class="tenant-card-body" id="toursContainer">
                            <?php foreach ($tours as $tour): ?>
                            <div class="activity-item">
                                <div class="activity-icon tour">🏠</div>
                                <div class="activity-content">
                                    <p><strong><?php echo sanitize($tour['property']); ?></strong></p>
                                    <span><?php echo sanitize($tour['date']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="tenant-card">
                        <div class="tenant-card-header">
                            <h2>Quick Actions</h2>
                        </div>
                        <div class="tenant-card-body">
                            <div class="quick-actions">
                                <button class="quick-action" onclick="window.location.href='tenant-listings.php'">
                                    <span class="icon">🔍</span>
                                    <span>Browse Listings</span>
                                </button>
                                <button class="quick-action" onclick="showTourModal()">
                                    <span class="icon">📅</span>
                                    <span>Schedule Tour</span>
                                </button>
                                <button class="quick-action" onclick="showApplicationModal()">
                                    <span class="icon">📝</span>
                                    <span>Submit Application</span>
                                </button>
                                <button class="quick-action" onclick="window.location.href='tenant-messages.php'">
                                    <span class="icon">💬</span>
                                    <span>Send Message</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Tour Modal -->
    <div class="modal-overlay" id="tourModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Schedule a Tour</h2>
                <button class="modal-close" onclick="closeTourModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="tourForm" onsubmit="submitTour(event)">
                    <div class="form-group">
                        <label>Property</label>
                        <select id="tourProperty" required>
                            <option value="">Select Property</option>
                            <?php foreach (getListings() as $listing): ?>
                            <option value="<?php echo $listing['id']; ?>"><?php echo sanitize($listing['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" id="tourName" value="<?php echo sanitize($user['firstName'] . ' ' . $user['lastName']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="tourEmail" value="<?php echo sanitize($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="tourPhone" required>
                    </div>
                    <div class="form-group">
                        <label>Preferred Date</label>
                        <input type="date" id="tourDate" required>
                    </div>
                    <div class="form-group">
                        <label>Preferred Time</label>
                        <select id="tourTime" required>
                            <option value="">Select Time</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Additional Notes (Optional)</label>
                        <textarea id="tourNotes" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Schedule Tour</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div class="modal-overlay" id="applicationModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Apply for Property</h2>
                <button class="modal-close" onclick="closeApplicationModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" onsubmit="submitApplication(event)">
                    <div class="form-group">
                        <label>Property</label>
                        <select id="appProperty" required>
                            <option value="">Select Property</option>
                            <?php foreach (getVacancies() as $vacancy): ?>
                            <option value="<?php echo $vacancy['id']; ?>"><?php echo sanitize($vacancy['title']); ?> - $<?php echo number_format($vacancy['price']); ?>/month</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="appFirstName" value="<?php echo sanitize($user['firstName']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="appLastName" value="<?php echo sanitize($user['lastName']); ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="appEmail" value="<?php echo sanitize($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="appPhone" required>
                    </div>
                    <div class="form-group">
                        <label>Move-in Date</label>
                        <input type="date" id="appMoveIn" required>
                    </div>
                    <div class="form-group">
                        <label>Lease Term</label>
                        <select id="appLeaseTerm" required>
                            <option value="6">6 Months</option>
                            <option value="12" selected>12 Months</option>
                            <option value="24">24 Months</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Annual Income</label>
                        <select id="appIncome" required>
                            <option value="">Select Range</option>
                            <option value="0-30000">$0 - $30,000</option>
                            <option value="30000-50000">$30,000 - $50,000</option>
                            <option value="50000-75000">$50,000 - $75,000</option>
                            <option value="75000-100000">$75,000 - $100,000</option>
                            <option value="100000+">$100,000+</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Employment Status</label>
                        <select id="appEmployment" required>
                            <option value="">Select Status</option>
                            <option value="fulltime">Full-time Employed</option>
                            <option value="parttime">Part-time Employed</option>
                            <option value="selfemployed">Self-employed</option>
                            <option value="student">Student</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-checkbox">
                        <input type="checkbox" id="appTerms" required>
                        <span>I authorize the background and credit check</span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Application</button>
                </form>
            </div>
        </div>
    </div>

    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>

    <script src="script.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
        
        function tenantLogout() {
            window.location.href = 'index.php?logout=1';
        }
        
        function removeSaved(id) {
            showToast('Removed from saved listings', 'success');
        }
    </script>
</body>
</html>