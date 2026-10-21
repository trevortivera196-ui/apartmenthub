<?php
/**
 * ApartmentHub Configuration
 * Database connection and common settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'apartmenthub');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'ApartmentHub');
define('SITE_URL', 'http://localhost/apartment');
define('ADMIN_EMAIL', 'admin@apartmenthub.com');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Helper Functions
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

function displayAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<div class='alert alert-{$alert['type']}'>{$alert['message']}</div>";
        unset($_SESSION['alert']);
    }
}

// Get all listings from database or default data
function getListings() {
    // Default listings data if database not available
    return [
        [
            'id' => 1,
            'title' => 'Modern Downtown Apartment',
            'type' => 'apartment',
            'price' => 1850,
            'address' => '123 Main Street, Downtown',
            'beds' => 2,
            'baths' => 2,
            'sqft' => 1100,
            'featured' => true,
            'description' => 'Stunning modern apartment in the heart of downtown. Features floor-to-ceiling windows, hardwood floors, and a gourmet kitchen with stainless steel appliances.'
        ],
        [
            'id' => 2,
            'title' => 'Cozy Studio near Campus',
            'type' => 'studio',
            'price' => 1200,
            'address' => '456 College Ave, University District',
            'beds' => 0,
            'baths' => 1,
            'sqft' => 450,
            'featured' => true,
            'description' => 'Perfect studio for students or young professionals. Walking distance to campus and public transit. Includes all utilities, laundry in building.'
        ],
        [
            'id' => 3,
            'title' => 'Luxury Waterfront Condo',
            'type' => 'condo',
            'price' => 3200,
            'address' => '789 Harbor View, Marina District',
            'beds' => 3,
            'baths' => 2,
            'sqft' => 1800,
            'featured' => true,
            'description' => 'Breathtaking waterfront condo with panoramic bay views. This luxury residence features premium finishes and world-class amenities.'
        ],
        [
            'id' => 4,
            'title' => 'Spacious Family Home',
            'type' => 'house',
            'price' => 2800,
            'address' => '321 Oak Lane, Suburbs',
            'beds' => 4,
            'baths' => 3,
            'sqft' => 2400,
            'featured' => true,
            'description' => 'Beautiful family home in a quiet neighborhood. Features a large backyard, two-car garage, and excellent schools nearby.'
        ],
        [
            'id' => 5,
            'title' => 'Urban Loft with City Views',
            'type' => 'apartment',
            'price' => 2200,
            'address' => '555 Industrial Blvd, Arts District',
            'beds' => 1,
            'baths' => 1,
            'sqft' => 950,
            'featured' => false,
            'description' => 'Stylish loft in converted warehouse. Features exposed brick, high ceilings, and massive windows overlooking the city.'
        ]
    ];
}

// Get all vacancies from database or default data
function getVacancies() {
    return [
        [
            'id' => 1,
            'title' => 'Cozy Studio Room',
            'type' => 'Studio',
            'price' => 850,
            'address' => '123 Main Street, Downtown',
            'beds' => 0,
            'baths' => 1,
            'sqft' => 350,
            'status' => 'available',
            'moveIn' => 'Immediate',
            'amenities' => ['Furnished', 'WiFi', 'Laundry']
        ],
        [
            'id' => 2,
            'title' => 'Bright 1BR Room',
            'type' => '1 Bedroom',
            'price' => 1200,
            'address' => '456 Oak Avenue, Midtown',
            'beds' => 1,
            'baths' => 1,
            'sqft' => 500,
            'status' => 'available',
            'moveIn' => 'Nov 1, 2026',
            'amenities' => ['Kitchen', 'A/C', 'Parking']
        ],
        [
            'id' => 3,
            'title' => 'Spacious 2BR Suite',
            'type' => '2 Bedrooms',
            'price' => 1750,
            'address' => '789 Pine Road, Westside',
            'beds' => 2,
            'baths' => 2,
            'sqft' => 950,
            'status' => 'limited',
            'moveIn' => 'Immediate',
            'amenities' => ['Balcony', 'Gym', 'Pet Friendly']
        ]
    ];
}

// Save user to database
function saveUser($userData) {
    // For demo, store in session
    $_SESSION['user'] = $userData;
    $_SESSION['user_id'] = $userData['id'] ?? time();
    return true;
}

// Save admin to database
function saveAdmin($adminData) {
    $_SESSION['admin'] = $adminData;
    $_SESSION['admin_id'] = $adminData['id'] ?? 1;
    return true;
}

// Logout user
function userLogout() {
    session_destroy();
    redirect('index.php');
}

// Logout admin
function adminLogout() {
    session_destroy();
    redirect('admin-login.php');
}
?>