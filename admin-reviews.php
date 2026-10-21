<?php require_once 'config.php';

if (!isAdminLoggedIn()) {
    redirect('admin-login.php');
}

$reviews = [
    ['id' => 1, 'name' => 'James Wilson', 'rentingSince' => '2022', 'rating' => 5, 'property' => 'Modern Downtown Apartment', 'propertyId' => 1, 'unit' => '4A', 'type' => '2 Bed, 2 Bath', 'content' => 'Absolutely love living here! The apartment is beautifully maintained, and the management team is incredibly responsive. The location is perfect with easy access to public transportation and nearby amenities. The amenities in the building are top-notch, especially the fitness center and rooftop area. Highly recommend for anyone looking for a quality living experience!', 'date' => '2024-10-15', 'hasResponse' => true, 'response' => 'Thank you so much for your wonderful review, James! We\'re thrilled to hear you\'re enjoying your stay with us. Our team works hard to maintain the quality you deserve, and we\'re especially proud of our rooftop amenities. We look forward to continuing to serve you!'],
    ['id' => 2, 'name' => 'Sarah Mitchell', 'rentingSince' => '2024', 'rating' => 4, 'property' => 'Cozy Studio near Campus', 'propertyId' => 2, 'unit' => '2B', 'type' => 'Studio', 'content' => 'Great location for students! The studio is well-designed and perfect for living alone. The only reason I\'m giving 4 stars instead of 5 is that the laundry facilities could be improved - sometimes have to wait for machines. Otherwise, a fantastic place to live during school.', 'date' => '2024-10-18', 'hasResponse' => false],
    ['id' => 3, 'name' => 'Michael Chen', 'rentingSince' => '2023', 'rating' => 3, 'property' => 'Luxury Waterfront Condo', 'propertyId' => 3, 'unit' => '1C', 'type' => '3 Bed, 2 Bath', 'content' => 'The view from this place is amazing and the apartment is spacious. However, I\'ve had some issues with maintenance response times. When I reported a leaking faucet, it took almost a week to get it fixed. The facilities are beautiful but the service needs improvement.', 'date' => '2024-10-10', 'hasResponse' => true, 'response' => 'We apologize for the delayed maintenance response, Michael. We\'ve spoken with our maintenance team and implemented a new priority system for repair requests. We appreciate your feedback and hope you\'ll give us another chance to improve your experience.'],
    ['id' => 4, 'name' => 'Lisa Thompson', 'rentingSince' => 'Former Tenant', 'rating' => 1, 'property' => 'Urban Loft with City Views', 'propertyId' => 5, 'unit' => '3A', 'type' => '1 Bed, 1 Bath', 'content' => 'Unfortunately, my experience was not good. The apartment had issues from day one - the heating system didn\'t work properly during winter, and after multiple complaints, it still wasn\'t fixed. The noise from the street was also a major issue. I would not recommend this property.', 'date' => '2024-09-28', 'hasResponse' => false],
    ['id' => 5, 'name' => 'David Park', 'rentingSince' => '2021', 'rating' => 5, 'property' => 'Spacious Family Home', 'propertyId' => 4, 'unit' => '5B', 'type' => '4 Bed, 3 Bath', 'content' => 'We\'ve been living here for 3 years and it\'s been wonderful! The neighborhood is safe, the schools are excellent, and the home itself has everything we need. The backyard is perfect for our kids and pets. We\'ve renewed our lease twice and would happily stay longer!', 'date' => '2024-10-12', 'hasResponse' => false],
    ['id' => 6, 'name' => 'Emma Rodriguez', 'rentingSince' => '2024', 'rating' => 4, 'property' => 'Modern Downtown Apartment', 'propertyId' => 1, 'unit' => '2A', 'type' => '1 Bed, 1 Bath', 'content' => 'Great apartment in a prime location! The building amenities are fantastic - I use the gym and pool regularly. My only complaint is that parking is expensive but that\'s expected downtown. The unit itself is well-maintained and the views are amazing.', 'date' => '2024-10-08', 'hasResponse' => true, 'response' => 'Thanks for the great review, Emma! We\'re glad you\'re enjoying the amenities. We understand parking concerns - we\'re currently working on adding more affordable options. Let us know if there\'s anything else we can help with!'],
];

$avgRating = 4.6;
$totalReviews = count($reviews);
$responseRate = 50;
$pendingReviews = count(array_filter($reviews, fn($r) => !$r['hasResponse']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews - <?php echo SITE_NAME; ?> Admin</title>
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
        
        .review-overview { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .overview-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; }
        .overview-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .overview-icon.stars { background: #fef3c7; }
        .overview-icon.reviews { background: #dbeafe; }
        .overview-icon.response { background: #d1fae5; }
        .overview-icon.pending { background: #ede9fe; }
        .overview-info h3 { font-size: 2rem; font-weight: 700; color: var(--text-dark); }
        .overview-info p { font-size: 0.9rem; color: var(--text-light); }
        
        .rating-breakdown { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 30px; }
        .rating-breakdown h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 20px; }
        .rating-row { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; }
        .rating-label { width: 60px; font-size: 0.9rem; color: var(--text-dark); }
        .rating-bar { flex: 1; height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden; }
        .rating-fill { height: 100%; background: #fbbf24; border-radius: 5px; }
        .rating-count { width: 50px; text-align: right; font-size: 0.9rem; color: var(--text-light); }
        
        .filters-bar { background: white; padding: 20px 25px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .filters-bar label { font-size: 0.9rem; color: var(--text-light); }
        .filters-bar select { padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .filters-bar button { padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; }
        
        .reviews-container { display: flex; flex-direction: column; gap: 20px; }
        .review-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; }
        .review-card.pinned { border-left: 4px solid #fbbf24; }
        .review-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; flex-wrap: wrap; gap: 15px; }
        .reviewer-info { display: flex; gap: 15px; align-items: center; }
        .reviewer-avatar { width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1.2rem; }
        .reviewer-details h4 { font-size: 1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 3px; }
        .reviewer-details p { font-size: 0.85rem; color: var(--text-light); }
        .review-rating { display: flex; align-items: center; gap: 5px; }
        .star { color: #fbbf24; font-size: 1.2rem; }
        .star.empty { color: #e5e7eb; }
        .review-rating span { margin-left: 8px; font-weight: 600; color: var(--text-dark); }
        .review-property { background: #f9fafb; padding: 15px; border-radius: 8px; margin: 15px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .review-property-info h5 { font-size: 0.95rem; font-weight: 600; color: var(--text-dark); margin-bottom: 3px; }
        .review-property-info p { font-size: 0.85rem; color: var(--text-light); }
        .review-property-img { width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .review-content { color: var(--text-dark); line-height: 1.6; margin-bottom: 15px; }
        .review-date { font-size: 0.85rem; color: #9ca3af; }
        
        .review-actions { display: flex; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb; flex-wrap: wrap; }
        .review-action-btn { padding: 8px 16px; border: 2px solid #e5e7eb; border-radius: 6px; background: white; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: var(--transition); }
        .review-action-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .review-action-btn.approve { background: #d1fae5; border-color: #10b981; color: #059669; }
        .review-action-btn.reject { background: #fee2e2; border-color: #ef4444; color: #dc2626; }
        
        .admin-response { background: #f0f9ff; border-left: 4px solid var(--primary-color); padding: 15px; margin-top: 15px; border-radius: 0 8px 8px 0; }
        .admin-response-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .admin-response-header .icon { font-size: 1.2rem; }
        .admin-response-header span { font-size: 0.85rem; font-weight: 600; color: var(--primary-color); }
        .admin-response p { font-size: 0.9rem; color: var(--text-dark); margin: 0; }
        
        .pending-badge { background: #fef3c7; color: #d97706; padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        
        .response-form { margin-top: 15px; }
        .response-form textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; resize: vertical; min-height: 80px; margin-bottom: 10px; }
        .response-form textarea:focus { outline: none; border-color: var(--primary-color); }
        .response-form button { padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .review-overview { grid-template-columns: 1fr; } .filters-bar { flex-direction: column; align-items: stretch; } }
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
                    <a href="admin-reviews.php" class="sidebar-link active">
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
                    <h1>Reviews</h1>
                    <p>Manage and respond to tenant reviews</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="review-overview">
                    <div class="overview-card">
                        <div class="overview-icon stars">⭐</div>
                        <div class="overview-info">
                            <h3><?php echo $avgRating; ?></h3>
                            <p>Average Rating</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-icon reviews">💬</div>
                        <div class="overview-info">
                            <h3><?php echo $totalReviews; ?></h3>
                            <p>Total Reviews</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-icon response">💭</div>
                        <div class="overview-info">
                            <h3><?php echo $responseRate; ?>%</h3>
                            <p>Response Rate</p>
                        </div>
                    </div>
                    <div class="overview-card">
                        <div class="overview-icon pending">⏳</div>
                        <div class="overview-info">
                            <h3><?php echo $pendingReviews; ?></h3>
                            <p>Pending Review</p>
                        </div>
                    </div>
                </div>
                
                <div class="rating-breakdown">
                    <h3>Rating Distribution</h3>
                    <div class="rating-row">
                        <span class="rating-label">5 stars</span>
                        <div class="rating-bar"><div class="rating-fill" style="width: 65%;"></div></div>
                        <span class="rating-count">225</span>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">4 stars</span>
                        <div class="rating-bar"><div class="rating-fill" style="width: 20%;"></div></div>
                        <span class="rating-count">69</span>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">3 stars</span>
                        <div class="rating-bar"><div class="rating-fill" style="width: 8%;"></div></div>
                        <span class="rating-count">28</span>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">2 stars</span>
                        <div class="rating-bar"><div class="rating-fill" style="width: 4%;"></div></div>
                        <span class="rating-count">14</span>
                    </div>
                    <div class="rating-row">
                        <span class="rating-label">1 star</span>
                        <div class="rating-bar"><div class="rating-fill" style="width: 3%;"></div></div>
                        <span class="rating-count">11</span>
                    </div>
                </div>
                
                <div class="filters-bar">
                    <label>Rating:</label>
                    <select id="filterRating">
                        <option value="all">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                    <label>Property:</label>
                    <select id="filterProperty">
                        <option value="all">All Properties</option>
                        <option value="1">123 Main Street</option>
                        <option value="2">456 Oak Avenue</option>
                        <option value="3">789 Pine Road</option>
                    </select>
                    <label>Status:</label>
                    <select id="filterStatus">
                        <option value="all">All</option>
                        <option value="pending">Pending Response</option>
                        <option value="responded">Responded</option>
                    </select>
                    <button onclick="applyFilters()">Apply Filters</button>
                    <button onclick="resetFilters()" style="background: #6b7280;">Reset</button>
                </div>
                
                <div class="reviews-container">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-card <?php echo $review['rating'] == 5 && !$review['hasResponse'] ? 'pinned' : ''; ?>">
                        <div class="review-header">
                            <div class="reviewer-info">
                                <div class="reviewer-avatar"><?php echo strtoupper(substr($review['name'], 0, 1)); ?></div>
                                <div class="reviewer-details">
                                    <h4><?php echo sanitize($review['name']); ?></h4>
                                    <p><?php echo $review['rentingSince'] === 'Former Tenant' ? 'Former Tenant' : 'Renting since ' . $review['rentingSince']; ?></p>
                                </div>
                            </div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>">★</span>
                                <?php endfor; ?>
                                <span><?php echo number_format($review['rating'], 1); ?></span>
                                <?php if (!$review['hasResponse']): ?>
                                <span class="pending-badge">Pending Response</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="review-property">
                            <div class="review-property-info">
                                <h5><?php echo sanitize($review['property']); ?></h5>
                                <p><?php echo sanitize($review['unit']); ?> • <?php echo sanitize($review['type']); ?></p>
                            </div>
                            <div class="review-property-img">🏠</div>
                        </div>
                        <p class="review-content"><?php echo sanitize($review['content']); ?></p>
                        <span class="review-date">Posted on <?php echo date('F j, Y', strtotime($review['date'])); ?></span>
                        
                        <?php if ($review['hasResponse']): ?>
                        <div class="admin-response">
                            <div class="admin-response-header">
                                <span class="icon">💬</span>
                                <span>Response from <?php echo SITE_NAME; ?></span>
                            </div>
                            <p><?php echo sanitize($review['response']); ?></p>
                        </div>
                        <div class="review-actions">
                            <button class="review-action-btn" onclick="markHelpful(<?php echo $review['id']; ?>)">👍 Helpful (12)</button>
                            <button class="review-action-btn" onclick="reportReview(<?php echo $review['id']; ?>)">🚩 Report</button>
                        </div>
                        <?php else: ?>
                        <div class="response-form">
                            <textarea id="response-<?php echo $review['id']; ?>" placeholder="Write your response..."></textarea>
                            <button onclick="submitResponse(<?php echo $review['id']; ?>)">Submit Response</button>
                        </div>
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
        
        function submitResponse(id) {
            const response = document.getElementById('response-' + id).value;
            if (!response.trim()) {
                showToast('Please write a response', 'error');
                return;
            }
            showToast('Response submitted!', 'success');
        }
        
        function markHelpful(id) { showToast('Marked as helpful!', 'success'); }
        function reportReview(id) { if (confirm('Report this review?')) showToast('Review reported', 'success'); }
        function applyFilters() { showToast('Applying filters...', 'success'); }
        function resetFilters() { showToast('Filters reset', 'success'); }
        
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