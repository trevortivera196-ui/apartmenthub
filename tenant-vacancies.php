<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$vacancies = getVacancies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacancies - <?php echo SITE_NAME; ?></title>
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
        .page-header { margin-bottom: 25px; }
        .page-header h1 { font-size: 1.8rem; color: var(--text-dark); margin-bottom: 5px; }
        .page-header p { color: var(--text-light); }
        .price-filters { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .price-btn { padding: 10px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 25px; cursor: pointer; font-size: 0.9rem; transition: var(--transition); }
        .price-btn:hover, .price-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .vacancies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px; }
        .vacancy-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: var(--transition); cursor: pointer; }
        .vacancy-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .vacancy-image { height: 160px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 3.5rem; position: relative; }
        .vacancy-status { position: absolute; top: 12px; left: 12px; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .vacancy-status.available { background: #10b981; color: white; }
        .vacancy-status.limited { background: #f59e0b; color: white; }
        .vacancy-status.urgent { background: #ef4444; color: white; }
        .vacancy-save { position: absolute; top: 12px; right: 12px; width: 36px; height: 36px; background: white; border: none; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .vacancy-save:hover { transform: scale(1.1); }
        .vacancy-content { padding: 20px; }
        .vacancy-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .vacancy-price { font-size: 1.4rem; font-weight: 700; color: var(--primary-color); }
        .vacancy-price span { font-size: 0.85rem; font-weight: 400; color: var(--text-light); }
        .vacancy-type { background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 15px; font-size: 0.75rem; font-weight: 500; }
        .vacancy-title { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .vacancy-address { color: var(--text-light); margin-bottom: 12px; font-size: 0.9rem; }
        .vacancy-amenities { display: flex; gap: 12px; margin-bottom: 15px; }
        .vacancy-amenity { font-size: 0.85rem; color: var(--text-light); }
        .vacancy-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f3f4f6; }
        .vacancy-movein { font-size: 0.85rem; color: var(--text-light); }
        .vacancy-movein strong { color: var(--text-dark); }
        .vacancy-btn { padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 500; transition: var(--transition); }
        .vacancy-btn:hover { background: var(--primary-dark); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .tenant-sidebar { transform: translateX(-100%); } .tenant-sidebar.show { transform: translateX(0); } .tenant-main { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .tenant-content { padding: 15px; } .vacancies-grid { grid-template-columns: 1fr; } }
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
                    <a href="tenant-vacancies.php" class="tenant-sidebar-link active">
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
                    <h1>Available Vacancies</h1>
                    <p>Browse rooms and suites available for rent</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <div class="page-header">
                    <h1>Find Your Perfect Room</h1>
                    <p>From affordable shared rooms to luxury suites</p>
                </div>
                <div class="price-filters">
                    <button class="price-btn active" onclick="filterVacancies('all', this)">All Prices</button>
                    <button class="price-btn" onclick="filterVacancies('0-800', this)">Under $800</button>
                    <button class="price-btn" onclick="filterVacancies('800-1200', this)">$800-$1,200</button>
                    <button class="price-btn" onclick="filterVacancies('1200-2000', this)">$1,200-$2,000</button>
                    <button class="price-btn" onclick="filterVacancies('2000+', this)">$2,000+</button>
                </div>
                <div class="vacancies-grid" id="vacanciesGrid">
                    <?php foreach ($vacancies as $vacancy): ?>
                    <div class="vacancy-card" data-id="<?php echo $vacancy['id']; ?>">
                        <div class="vacancy-image">
                            🚪
                            <span class="vacancy-status <?php echo $vacancy['status']; ?>"><?php echo ucfirst($vacancy['status']); ?></span>
                            <button class="vacancy-save" onclick="event.stopPropagation(); saveVacancy(<?php echo $vacancy['id']; ?>)">♥</button>
                        </div>
                        <div class="vacancy-content">
                            <div class="vacancy-header">
                                <div class="vacancy-price">$<?php echo number_format($vacancy['price']); ?><span>/month</span></div>
                                <span class="vacancy-type"><?php echo sanitize($vacancy['type']); ?></span>
                            </div>
                            <h3 class="vacancy-title"><?php echo sanitize($vacancy['title']); ?></h3>
                            <div class="vacancy-address">📍 <?php echo sanitize($vacancy['address']); ?></div>
                            <div class="vacancy-amenities">
                                <span class="vacancy-amenity">🛏️ <?php echo $vacancy['beds'] === 0 ? 'Studio' : $vacancy['beds'] . ' Bed'; ?></span>
                                <span class="vacancy-amenity">🚿 <?php echo $vacancy['baths']; ?> Bath</span>
                                <span class="vacancy-amenity">📐 <?php echo number_format($vacancy['sqft']); ?> sqft</span>
                            </div>
                            <div class="vacancy-footer">
                                <span class="vacancy-movein">Move-in: <strong><?php echo sanitize($vacancy['moveIn']); ?></strong></span>
                                <button class="vacancy-btn" onclick="event.stopPropagation(); applyToVacancy(<?php echo $vacancy['id']; ?>)">Apply Now</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    <script src="script.js"></script>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function filterVacancies(range, btn) {
            document.querySelectorAll('.price-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast('Filtering: ' + range, 'success');
        }
        function saveVacancy(id) { showToast('Vacancy #' + id + ' saved!', 'success'); }
        function applyToVacancy(id) { showApplicationModal(); }
    </script>
</body>
</html>