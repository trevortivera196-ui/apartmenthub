-- ApartmentHub Database Schema
-- Run this in MySQL to create the database structure

CREATE DATABASE IF NOT EXISTS apartmenthub;
USE apartmenthub;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    type ENUM('apartment', 'studio', 'condo', 'house', 'loft') NOT NULL,
    total_units INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Listings table
CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    beds INT DEFAULT 0,
    baths INT DEFAULT 1,
    sqft INT,
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Vacancies table
CREATE TABLE vacancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    title VARCHAR(200) NOT NULL,
    type VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    address VARCHAR(200) NOT NULL,
    beds INT DEFAULT 1,
    baths INT DEFAULT 1,
    sqft INT,
    status ENUM('available', 'limited', 'urgent') DEFAULT 'available',
    move_in_date VARCHAR(50),
    amenities JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    listing_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    current_address TEXT,
    move_in_date DATE,
    lease_term INT DEFAULT 12,
    annual_income VARCHAR(50),
    employment_status VARCHAR(50),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (listing_id) REFERENCES listings(id)
);

-- Tours table
CREATE TABLE tours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    listing_id INT,
    tenant_name VARCHAR(100) NOT NULL,
    tenant_email VARCHAR(100) NOT NULL,
    tenant_phone VARCHAR(20) NOT NULL,
    tour_date DATE NOT NULL,
    tour_time VARCHAR(10) NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (listing_id) REFERENCES listings(id)
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    read_status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    read_status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Payroll table
CREATE TABLE payroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    employer_name VARCHAR(200),
    position VARCHAR(100),
    annual_income DECIMAL(12, 2),
    employment_start_date DATE,
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_type ENUM('rent', 'deposit', 'fee', 'other') DEFAULT 'rent',
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    due_date DATE,
    paid_date DATE,
    transaction_id VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    property_id INT,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(200),
    content TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_response TEXT,
    response_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Gallery table
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    title VARCHAR(200) NOT NULL,
    category VARCHAR(50),
    image_url VARCHAR(500),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id)
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (email, password, first_name, last_name, role) 
VALUES ('admin@apartmenthub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'Super Administrator');

-- Insert sample properties
INSERT INTO properties (name, address, type, total_units) VALUES
('123 Main Street', '123 Main Street, Downtown', 'apartment', 24),
('456 Oak Avenue', '456 Oak Avenue, Midtown', 'condo', 18),
('789 Pine Road', '789 Pine Road, Westside', 'house', 12);

-- Insert sample listings
INSERT INTO listings (property_id, title, description, price, beds, baths, sqft, featured) VALUES
(1, 'Modern Downtown Apartment', 'Stunning modern apartment in the heart of downtown.', 1850, 2, 2, 1100, TRUE),
(1, 'Cozy Studio', 'Perfect studio for students or young professionals.', 1200, 0, 1, 450, TRUE),
(2, 'Luxury Waterfront Condo', 'Breathtaking waterfront condo with panoramic bay views.', 3200, 3, 2, 1800, TRUE),
(3, 'Spacious Family Home', 'Beautiful family home in a quiet neighborhood.', 2800, 4, 3, 2400, TRUE);