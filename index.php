<?php require_once 'config.php'; ?>
<?php
// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitize($_POST['email'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    
    if (strlen($email) >= 5 && strlen($password) >= 5) {
        $userData = [
            'id' => time(),
            'email' => $email,
            'firstName' => explode('@', $email)[0],
            'lastName' => '',
            'loginTime' => date('Y-m-d H:i:s')
        ];
        saveUser($userData);
        showAlert('Welcome back! Login successful.', 'success');
        redirect('tenant-dashboard.php');
    } else {
        showAlert('Invalid credentials. Try email: tenant@example.com, password: password123', 'error');
    }
}

// Process signup form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $firstName = sanitize($_POST['firstName'] ?? '');
    $lastName = sanitize($_POST['lastName'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $password = sanitize($_POST['password'] ?? '');
    
    if ($firstName && $lastName && $email && $password) {
        $userData = [
            'id' => time(),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        saveUser($userData);
        showAlert('Account created successfully! Welcome to ApartmentHub.', 'success');
        redirect('tenant-dashboard.php');
    } else {
        showAlert('Please fill in all required fields', 'error');
    }
}

$listings = getListings();
$vacancies = getVacancies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Find Your Perfect Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php displayAlert(); ?>
    
    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <a href="index.php" class="logo">Apartment<span>Hub</span></a>
            <div class="nav-links" id="navLinks">
                <a href="#home">Home</a>
                <a href="#listings">Listings</a>
                <a href="#vacancies">Vacancies</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="nav-auth" id="authButtons">
                <button class="btn btn-outline" onclick="openLoginModal()">Login</button>
                <button class="btn btn-primary" onclick="openSignupModal()">Sign Up</button>
            </div>
            <div class="user-menu" id="userMenu" style="display: none;">
                <div class="user-avatar" id="userInitial" onclick="toggleUserDropdown()">T</div>
                <div class="user-dropdown" id="userDropdown">
                    <a href="tenant-dashboard.php">Dashboard</a>
                    <a href="#" onclick="logout(); return false;">Logout</a>
                </div>
            </div>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Find Your Perfect Home</h1>
            <p>Discover apartments, condos, and homes for rent in your area</p>
            
            <!-- Search Form -->
            <div class="search-box">
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
                        <option value="2000-3000">$2,000 - $3,000</option>
                        <option value="3000+">$3,000+</option>
                    </select>
                    <select id="bedrooms">
                        <option value="">Bedrooms</option>
                        <option value="studio">Studio</option>
                        <option value="1">1 Bedroom</option>
                        <option value="2">2 Bedrooms</option>
                        <option value="3">3 Bedrooms</option>
                        <option value="4+">4+ Bedrooms</option>
                    </select>
                    <button class="btn btn-primary" onclick="searchApartments()">Search</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="about">
        <div class="container">
            <h2 class="section-title">Why Choose <?php echo SITE_NAME; ?>?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Easy Search</h3>
                    <p>Find apartments that match your preferences with our powerful search tools.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Schedule Tours</h3>
                    <p>Book property viewings at your convenience with our online scheduling system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Online Applications</h3>
                    <p>Apply for your favorite properties directly through our platform.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Direct Communication</h3>
                    <p>Connect directly with property managers and landlords.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Listings Section -->
    <section class="listings" id="listings">
        <div class="container">
            <h2 class="section-title">Featured Listings</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterListings('all')">All</button>
                <button class="filter-btn" onclick="filterListings('apartment')">Apartments</button>
                <button class="filter-btn" onclick="filterListings('studio')">Studios</button>
                <button class="filter-btn" onclick="filterListings('condo')">Condos</button>
                <button class="filter-btn" onclick="filterListings('house')">Houses</button>
            </div>
            <div class="listings-grid" id="listingsGrid">
                <?php foreach ($listings as $listing): ?>
                <div class="listing-card" data-id="<?php echo $listing['id']; ?>">
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
                            <span class="listing-feature">🛏️ <?php echo $listing['beds'] === 0 ? 'Studio' : ($listing['beds'] . ' Bed'); ?></span>
                            <span class="listing-feature">🚿 <?php echo $listing['baths']; ?> Bath<?php echo $listing['baths'] > 1 ? 's' : ''; ?></span>
                            <span class="listing-feature">📐 <?php echo number_format($listing['sqft']); ?> sqft</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <button class="btn btn-outline" onclick="viewAllListings()">View All Listings →</button>
            </div>
        </div>
    </section>

    <!-- Vacancies Section -->
    <section class="vacancies" id="vacancies">
        <div class="container">
            <h2 class="section-title">Available Vacancies</h2>
            <p class="section-subtitle">Browse our available rooms and book a tour today</p>
            <div class="price-filters">
                <button class="price-btn active" onclick="filterVacancies('all')">All Prices</button>
                <button class="price-btn" onclick="filterVacancies('0-800')">Under $800</button>
                <button class="price-btn" onclick="filterVacancies('800-1200')">$800-$1200</button>
                <button class="price-btn" onclick="filterVacancies('1200-2000')">$1200-$2000</button>
                <button class="price-btn" onclick="filterVacancies('2000+')">$2000+</button>
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
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Search Properties</h3>
                    <p>Browse through our extensive listings and find properties that match your needs.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Schedule a Tour</h3>
                    <p>Book a convenient time to visit properties that interest you.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Submit Application</h3>
                    <p>Complete your application online with our easy-to-use forms.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Move In</h3>
                    <p>Once approved, sign your lease and move into your new home!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <h2 class="section-title">Contact Us</h2>
            <p class="section-subtitle">Have questions? We're here to help!</p>
            <div class="contact-content">
                <form class="contact-form" onsubmit="submitContactForm(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contactName">Full Name</label>
                            <input type="text" id="contactName" required>
                        </div>
                        <div class="form-group">
                            <label for="contactEmail">Email Address</label>
                            <input type="email" id="contactEmail" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactSubject">Subject</label>
                        <select id="contactSubject">
                            <option value="general">General Inquiry</option>
                            <option value="listing">Listing Inquiry</option>
                            <option value="application">Application Question</option>
                            <option value="support">Technical Support</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
                <div class="contact-info">
                    <div class="contact-item">
                        <span class="contact-icon">📍</span>
                        <div>
                            <h4>Address</h4>
                            <p>123 Main Street, Suite 100<br>New York, NY 10001</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">📞</span>
                        <div>
                            <h4>Phone</h4>
                            <p>(555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">✉️</span>
                        <div>
                            <h4>Email</h4>
                            <p>info@apartmenthub.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <span class="contact-icon">🕐</span>
                        <div>
                            <h4>Hours</h4>
                            <p>Mon-Fri: 9AM - 6PM<br>Sat: 10AM - 4PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Apartment<span>Hub</span></h3>
                    <p>Your trusted partner in finding the perfect rental property.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="#listings">Browse Listings</a>
                    <a href="#vacancies">View Vacancies</a>
                    <a href="#about">About Us</a>
                    <a href="#contact">Contact</a>
                </div>
                <div class="footer-section">
                    <h4>For Tenants</h4>
                    <a href="#">How It Works</a>
                    <a href="#">Application Guide</a>
                    <a href="#">Tenant Resources</a>
                    <a href="#">FAQ</a>
                </div>
                <div class="footer-section">
                    <h4>For Landlords</h4>
                    <a href="#">List Your Property</a>
                    <a href="#">Pricing Plans</a>
                    <a href="#">Property Management</a>
                    <a href="#">Success Stories</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Welcome Back</h2>
                <button class="modal-close" onclick="closeLoginModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="loginForm" method="POST">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <input type="email" id="loginEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </form>
                <p class="modal-switch">Don't have an account? <a href="#" onclick="switchToSignup()">Sign Up</a></p>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div class="modal-overlay" id="signupModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Create Account</h2>
                <button class="modal-close" onclick="closeSignupModal()">✕</button>
            </div>
            <div class="modal-body">
                <form id="signupForm" method="POST">
                    <input type="hidden" name="signup" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email Address</label>
                        <input type="email" id="signupEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Password</label>
                        <input type="password" id="signupPassword" name="password" required minlength="8">
                    </div>
                    <div class="form-checkbox">
                        <input type="checkbox" id="agreeTerms" required>
                        <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
                </form>
                <p class="modal-switch">Already have an account? <a href="#" onclick="switchToLogin()">Login</a></p>
            </div>
        </div>
    </div>

    <!-- Property Modal -->
    <div class="modal-overlay" id="propertyModal">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h2>Property Details</h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

    <!-- Tour Modal -->
    <div class="modal-overlay" id="tourModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Schedule a Tour</h2>
                <button class="modal-close" onclick="closeTourModal()">✕</button>
            </div>
            <div class="modal-body" id="tourModalBody">
                <form id="tourForm" onsubmit="submitTour(event)">
                    <div class="form-group">
                        <label>Property</label>
                        <select id="tourProperty" required>
                            <option value="">Select Property</option>
                            <?php foreach ($listings as $listing): ?>
                            <option value="<?php echo $listing['id']; ?>"><?php echo sanitize($listing['title']); ?> - <?php echo sanitize($listing['address']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" id="tourName" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="tourEmail" required>
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
            <div class="modal-body" id="applicationModalBody">
                <form id="applicationForm" onsubmit="submitApplication(event)">
                    <div class="form-group">
                        <label>Property</label>
                        <select id="appProperty" required>
                            <option value="">Select Property</option>
                            <?php foreach ($vacancies as $vacancy): ?>
                            <option value="<?php echo $vacancy['id']; ?>"><?php echo sanitize($vacancy['title']); ?> - $<?php echo number_format($vacancy['price']); ?>/month</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="appFirstName" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="appLastName" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="appEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="appPhone" required>
                    </div>
                    <div class="form-group">
                        <label>Current Address</label>
                        <textarea id="appAddress" rows="2" required></textarea>
                    </div>
                    <div class="form-row">
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
                    <div class="form-group">
                        <label>Additional Notes</label>
                        <textarea id="appNotes" rows="3" placeholder="Any additional information..."></textarea>
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

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="script.js"></script>
    <script>
        // Check user session on page load
        <?php if (isLoggedIn() && isset($_SESSION['user'])): ?>
        currentUser = <?php echo json_encode($_SESSION['user']); ?>;
        updateUIForLoggedInUser();
        <?php endif; ?>
    </script>
</body>
</html>