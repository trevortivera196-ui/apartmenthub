<?php require_once 'config.php';

if (!isLoggedIn()) {
    redirect('index.php');
}

$listings = getListings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Listings - <?php echo SITE_NAME; ?></title>
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
        .header-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: #f3f4f6; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: var(--transition); }
        .tenant-content { padding: 30px; }
        .search-section { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .search-inputs { display: flex; gap: 15px; flex-wrap: wrap; }
        .search-inputs input, .search-inputs select { flex: 1; min-width: 150px; padding: 12px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.95rem; }
        .search-inputs input:focus, .search-inputs select:focus { outline: none; border-color: var(--primary-color); }
        .filter-buttons { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .filter-btn { padding: 10px 20px; background: white; border: 2px solid #e5e7eb; border-radius: 25px; cursor: pointer; font-size: 0.9rem; transition: var(--transition); }
        .filter-btn:hover, .filter-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .listings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .listing-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: var(--transition); cursor: pointer; }
        .listing-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .listing-image { height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 4rem; position: relative; }
        .listing-badge { position: absolute; top: 15px; left: 15px; background: #fbbf24; color: #78350f; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .listing-favorite { position: absolute; top: 15px; right: 15px; width: 36px; height: 36px; background: white; border: none; border-radius: 50%; cursor: pointer; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; transition: var(--transition); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .listing-favorite:hover { transform: scale(1.1); }
        .listing-content { padding: 20px; }
        .listing-price { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; }
        .listing-price span { font-size: 0.9rem; font-weight: 400; color: var(--text-light); }
        .listing-title { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 8px; }
        .listing-address { color: var(--text-light); margin-bottom: 12px; font-size: 0.9rem; }
        .listing-features { display: flex; gap: 15px; }
        .listing-feature { font-size: 0.85rem; color: var(--text-light); }
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .tenant-sidebar { transform: translateX(-100%); } .tenant-sidebar.show { transform: translateX(0); } .tenant-main { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .tenant-content { padding: 15px; } .search-inputs { flex-direction: column; } }
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
                    <a href="tenant-listings.php" class="tenant-sidebar-link active">
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
                    <h1>Browse Listings</h1>
                    <p>Find your perfect home from our curated listings</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="tenant-content">
                <div class="search-section">
                    <div class="search-inputs">
                        <input type="text" id="location" placeholder="Enter location...">
                        <select id="propertyType">
                            <option value="">Property Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="studio">Studio</option>
                            <option value="condo">Condo</option>
                            <option value="house">House</option>
                        </select>
                        <select id="priceRange">
                            <option value="">Price Range</option>
                            <option value="0-1000">$0 - $1,000</option>
                            <option value="1000-1500">$1,000 - $1,500</option>
                            <option value="1500-2000">$1,500 - $2,000</option>
                            <option value="2000+">$2,000+</option>
                        </select>
                        <button class="btn btn-primary" onclick="searchListings()">Search</button>
                    </div>
                </div>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterListings('all', this)">All</button>
                    <button class="filter-btn" onclick="filterListings('apartment', this)">Apartments</button>
                    <button class="filter-btn" onclick="filterListings('studio', this)">Studios</button>
                    <button class="filter-btn" onclick="filterListings('condo', this)">Condos</button>
                    <button class="filter-btn" onclick="filterListings('house', this)">Houses</button>
                </div>
                <div class="listings-grid" id="listingsGrid">
                    <?php foreach ($listings as $listing): ?>
                    <div class="listing-card" data-id="<?php echo $listing['id']; ?>" onclick="viewListing(<?php echo $listing['id']; ?>)">
                        <div class="listing-image">
                            🏠
                            <?php if ($listing['featured']): ?>
                            <span class="listing-badge">Featured</span>
                            <?php endif; ?>
                            <button class="listing-favorite" onclick="event.stopPropagation(); toggleFavorite(<?php echo $listing['id']; ?>)">♥</button>
                        </div>
                        <div class="listing-content">
                            <div class="listing-price">$<?php echo number_format($listing['price']); ?><span>/month</span></div>
                            <h3 class="listing-title"><?php echo sanitize($listing['title']); ?></h3>
                            <div class="listing-address">📍 <?php echo sanitize($listing['address']); ?></div>
                            <div class="listing-features">
                                <span class="listing-feature">🛏️ <?php echo $listing['beds'] === 0 ? 'Studio' : $listing['beds'] . ' Bed'; ?></span>
                                <span class="listing-feature">🚿 <?php echo $listing['baths']; ?> Bath</span>
                                <span class="listing-feature">📐 <?php echo number_format($listing['sqft']); ?> sqft</span>
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
        function filterListings(type, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast('Filtering by ' + type, 'success');
        }
        function searchListings() { showToast('Searching listings...', 'success'); }
        function viewListing(id) { showToast('Opening listing #' + id, 'success'); }
        function toggleFavorite(id) { showToast(id + ' saved!', 'success'); }
    </script>
</body>
</html>