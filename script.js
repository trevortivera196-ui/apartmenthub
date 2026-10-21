// Sample Listings Data
const listings = [
    {
        id: 1,
        title: "Modern Downtown Apartment",
        type: "apartment",
        price: 1850,
        address: "123 Main Street, Downtown",
        beds: 2,
        baths: 2,
        sqft: 1100,
        featured: true,
        description: "Stunning modern apartment in the heart of downtown. Features floor-to-ceiling windows, hardwood floors, and a gourmet kitchen with stainless steel appliances. Building amenities include a rooftop pool, fitness center, and 24/7 concierge."
    },
    {
        id: 2,
        title: "Cozy Studio near Campus",
        type: "studio",
        price: 1200,
        address: "456 College Ave, University District",
        beds: 0,
        baths: 1,
        sqft: 450,
        featured: true,
        description: "Perfect studio for students or young professionals. Walking distance to campus and public transit. Includes all utilities, laundry in building, and dedicated study space. Pet-friendly building!"
    },
    {
        id: 3,
        title: "Luxury Waterfront Condo",
        type: "condo",
        price: 3200,
        address: "789 Harbor View, Marina District",
        beds: 3,
        baths: 2,
        sqft: 1800,
        featured: true,
        description: "Breathtaking waterfront condo with panoramic bay views. This luxury residence features premium finishes, a private balcony, and access to world-class amenities including marina, spa, and fine dining."
    },
    {
        id: 4,
        title: "Spacious Family Home",
        type: "house",
        price: 2800,
        address: "321 Oak Lane, Suburbs",
        beds: 4,
        baths: 3,
        sqft: 2400,
        featured: true,
        description: "Beautiful family home in a quiet neighborhood. Features a large backyard, two-car garage, updated kitchen, and finished basement. Excellent schools nearby. Perfect for families!"
    },
    {
        id: 5,
        title: "Urban Loft with City Views",
        type: "apartment",
        price: 2200,
        address: "555 Industrial Blvd, Arts District",
        beds: 1,
        baths: 1,
        sqft: 950,
        featured: false,
        description: "Stylish loft in converted warehouse. Features exposed brick, high ceilings, and massive windows overlooking the city. Walk to galleries, restaurants, and nightlife."
    },
    {
        id: 6,
        title: "Garden Level Apartment",
        type: "apartment",
        price: 1650,
        address: "777 Garden Court, Midtown",
        beds: 2,
        baths: 1,
        sqft: 850,
        featured: false,
        description: "Charming garden-level apartment with private patio. Updated kitchen, hardwood floors throughout, and plenty of natural light. Small pet considered."
    },
    {
        id: 7,
        title: "High-Rise Executive Suite",
        type: "condo",
        price: 3800,
        address: "999 Sky Tower, Financial District",
        beds: 2,
        baths: 2,
        sqft: 1400,
        featured: true,
        description: "Executive living at its finest. Panoramic city views, premium appliances, smart home technology, and access to building business center, gym, and rooftop lounge."
    },
    {
        id: 8,
        title: "Affordable 2BR Apartment",
        type: "apartment",
        price: 1400,
        address: "234 Elm Street, Eastside",
        beds: 2,
        baths: 1,
        sqft: 780,
        featured: false,
        description: "Great value 2-bedroom apartment in a well-maintained building. Close to shopping, parks, and public transit. Laundry facilities available."
    },
    {
        id: 9,
        title: "Pet-Friendly Townhouse",
        type: "house",
        price: 2400,
        address: "567 Pine Road, Westside",
        beds: 3,
        baths: 2,
        sqft: 1650,
        featured: false,
        description: "Spacious townhouse perfect for pet owners. Private backyard, attached garage, and modern updates throughout. Walking distance to dog park."
    }
];

// Current filter state
let currentFilter = 'all';

// Initialize vacancies on page load
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    setupOutsideClickListener();
    renderListings(listings);
    renderVacancies(vacancies);
});
function renderListings(listingsToRender) {
    const grid = document.getElementById('listingsGrid');
    
    if (listingsToRender.length === 0) {
        grid.innerHTML = '<p style="text-align: center; grid-column: 1/-1; color: #6b7280; padding: 40px;">No listings found matching your criteria.</p>';
        return;
    }
    
    grid.innerHTML = listingsToRender.map(listing => createListingCard(listing)).join('');
    
    // Add click listeners to cards
    document.querySelectorAll('.listing-card').forEach(card => {
        card.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            openPropertyModal(id);
        });
    });
}

// Create HTML for a listing card
function createListingCard(listing) {
    const bedsText = listing.beds === 0 ? 'Studio' : (listing.beds === 1 ? '1 Bed' : `${listing.beds} Beds`);
    const isSaved = isListingSaved(listing.id);
    
    return `
        <div class="listing-card" data-id="${listing.id}">
            <div class="listing-image">
                🏠
                ${listing.featured ? '<span class="listing-badge">Featured</span>' : ''}
                <button class="listing-favorite" style="color: ${isSaved ? 'red' : ''}" onclick="event.stopPropagation(); toggleFavorite(${listing.id})">
                    ♥
                </button>
            </div>
            <div class="listing-content">
                <div class="listing-price">$${listing.price.toLocaleString()}<span>/month</span></div>
                <h3 class="listing-title">${listing.title}</h3>
                <div class="listing-address">📍 ${listing.address}</div>
                <div class="listing-features">
                    <span class="listing-feature">🛏️ ${bedsText}</span>
                    <span class="listing-feature">🚿 ${listing.baths} Bath${listing.baths > 1 ? 's' : ''}</span>
                    <span class="listing-feature">📐 ${listing.sqft.toLocaleString()} sqft</span>
                </div>
            </div>
        </div>
    `;
}

// Filter listings by type
function filterListings(type) {
    currentFilter = type;
    
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter and render
    const filtered = type === 'all' 
        ? listings 
        : listings.filter(l => l.type === type);
    renderListings(filtered);
}

// Search apartments
function searchApartments() {
    const location = document.getElementById('location').value.toLowerCase();
    const propertyType = document.getElementById('propertyType').value;
    const priceRange = document.getElementById('priceRange').value;
    const bedrooms = document.getElementById('bedrooms').value;
    
    let filtered = listings;
    
    // Filter by location
    if (location) {
        filtered = filtered.filter(l => 
            l.address.toLowerCase().includes(location) ||
            l.title.toLowerCase().includes(location)
        );
    }
    
    // Filter by property type
    if (propertyType) {
        filtered = filtered.filter(l => l.type === propertyType);
    }
    
    // Filter by price range
    if (priceRange) {
        const [min, max] = priceRange.split('-').map(p => p.replace('+', ''));
        filtered = filtered.filter(l => {
            if (priceRange.endsWith('+')) {
                return l.price >= parseInt(min);
            }
            return l.price >= parseInt(min) && l.price <= parseInt(max);
        });
    }
    
    // Filter by bedrooms
    if (bedrooms) {
        if (bedrooms === 'studio') {
            filtered = filtered.filter(l => l.beds === 0);
        } else if (bedrooms === '4+') {
            filtered = filtered.filter(l => l.beds >= 4);
        } else {
            filtered = filtered.filter(l => l.beds === parseInt(bedrooms));
        }
    }
    
    // Render results and scroll to listings
    renderListings(filtered);
    document.getElementById('listings').scrollIntoView({ behavior: 'smooth' });
    
    // Update filter buttons to "all" since we're using custom search
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('.filter-btn').classList.add('active');
}

// Toggle favorite / Save listing
function toggleFavorite(id) {
    // Check if user is logged in
    if (!currentUser) {
        showToast('Please login to save listings', 'error');
        openLoginModal();
        return;
    }
    
    let savedListings = getSavedListings();
    const btn = event.target.closest('.listing-favorite');
    
    if (savedListings.includes(id)) {
        savedListings = savedListings.filter(s => s !== id);
        if (btn) btn.style.color = '';
        showToast('Removed from saved listings', 'success');
    } else {
        savedListings.push(id);
        if (btn) btn.style.color = 'red';
        showToast('Listing saved!', 'success');
    }
    
    localStorage.setItem('savedListings', JSON.stringify(savedListings));
}

// Get saved listings from localStorage
function getSavedListings() {
    return JSON.parse(localStorage.getItem('savedListings') || '[]');
}

// Check if a listing is saved
function isListingSaved(id) {
    return getSavedListings().includes(id);
}

// Open property modal
function openPropertyModal(id) {
    const listing = listings.find(l => l.id === id);
    if (!listing) return;
    
    const modal = document.getElementById('propertyModal');
    const modalBody = document.getElementById('modalBody');
    
    const bedsText = listing.beds === 0 ? 'Studio' : `${listing.beds} Bedroom${listing.beds > 1 ? 's' : ''}`;
    
    modalBody.innerHTML = `
        <div class="modal-image">🏠</div>
        <div class="modal-body">
            <div class="listing-price">$${listing.price.toLocaleString()}<span>/month</span></div>
            <h2>${listing.title}</h2>
            <div class="listing-address">📍 ${listing.address}</div>
            
            <div class="modal-features">
                <span class="listing-feature">🛏️ ${bedsText}</span>
                <span class="listing-feature">🚿 ${listing.baths} Bath${listing.baths > 1 ? 's' : ''}</span>
                <span class="listing-feature">📐 ${listing.sqft.toLocaleString()} sqft</span>
            </div>
            
            <p class="modal-description">${listing.description}</p>
            
            <button class="btn btn-primary modal-contact-btn" onclick="contactAgent(${listing.id})">Contact Agent</button>
        </div>
    `;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close modal
function closeModal() {
    document.getElementById('propertyModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.getElementById('propertyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Contact agent (placeholder)
function contactAgent(id) {
    alert('Contact form would open for property #' + id + '\n\nIn a real application, this would open a messaging system or contact form.');
}

// View all listings
function viewAllListings() {
    document.getElementById('listings').scrollIntoView({ behavior: 'smooth' });
    filterListings('all');
}

// Submit contact form
function submitContactForm(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    alert(`Thank you for your message!\n\nWe received your inquiry and will respond within 24 hours.\n\nSubmitted: ${JSON.stringify(data, null, 2)}`);
    
    e.target.reset();
}

// Mobile menu toggle
function toggleMobileMenu() {
    document.querySelector('.nav').classList.toggle('show');
}

// Setup smooth scrolling for anchor links
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
                // Close mobile menu if open
                document.querySelector('.nav').classList.remove('show');
            }
        });
    });
}

// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.98)';
        header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.15)';
    } else {
        header.style.background = '#ffffff';
        header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
    }
});

// Animate elements on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.querySelectorAll('.listing-card, .feature-card, .step-card, .testimonial-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

// Trigger initial animations for elements in view
setTimeout(() => {
    document.querySelectorAll('.listing-card, .feature-card, .step-card, .testimonial-card').forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight) {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }
    });
}, 100);
// Authentication Functions
let currentUser = null;

// Check for saved session on page load
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    setupOutsideClickListener();
});

// Check if user is logged in
function checkSession() {
    const savedUser = localStorage.getItem('apartmentHubUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        updateUIForLoggedInUser();
    }
}

// Save user session
function saveSession(user) {
    localStorage.setItem('apartmentHubUser', JSON.stringify(user));
}

// Clear user session
function clearSession() {
    localStorage.removeItem('apartmentHubUser');
    currentUser = null;
}

// Update UI for logged in user
function updateUIForLoggedInUser() {
    if (!currentUser) return;
    
    document.getElementById('authButtons').style.display = 'none';
    document.getElementById('userMenu').style.display = 'block';
    document.getElementById('userInitial').textContent = currentUser.firstName.charAt(0).toUpperCase();
}

// Update UI for logged out user
function updateUIForLoggedOutUser() {
    document.getElementById('authButtons').style.display = 'flex';
    document.getElementById('userMenu').style.display = 'none';
}

// Open Login Modal
function openLoginModal() {
    document.getElementById('loginModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close Login Modal
function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Open Signup Modal
function openSignupModal() {
    document.getElementById('signupModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close Signup Modal
function closeSignupModal() {
    document.getElementById('signupModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Switch from login to signup
function switchToSignup() {
    closeLoginModal();
    openSignupModal();
}

// Switch from signup to login
function switchToLogin() {
    closeSignupModal();
    openLoginModal();
}

// Handle Login
function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    // Validate inputs
    if (!email || !password) {
        showToast('Please fill in all fields', 'error');
        return;
    }
    
    // Simulate login (in real app, this would be an API call)
    // Demo: accept any email/password with at least 5 chars
    if (email.length >= 5 && password.length >= 5) {
        currentUser = {
            id: Date.now(),
            email: email,
            firstName: email.split('@')[0],
            lastName: '',
            loginTime: new Date().toISOString()
        };
        
        saveSession(currentUser);
        updateUIForLoggedInUser();
        closeLoginModal();
        showToast('Welcome back! Login successful.', 'success');
        
        // Reset form
        document.getElementById('loginForm').reset();
    } else {
        showToast('Invalid credentials. Try email: tenant@example.com, password: password123', 'error');
    }
}

// Handle Signup
function handleSignup(e) {
    e.preventDefault();
    
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('signupEmail').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('signupPassword').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;
    
    // Validate inputs
    if (!firstName || !lastName || !email || !password) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    if (!agreeTerms) {
        showToast('Please agree to the Terms of Service', 'error');
        return;
    }
    
    if (password.length < 8) {
        showToast('Password must be at least 8 characters', 'error');
        return;
    }
    
    // Simulate signup (in real app, this would be an API call)
    currentUser = {
        id: Date.now(),
        firstName: firstName,
        lastName: lastName,
        email: email,
        phone: phone,
        createdAt: new Date().toISOString()
    };
    
    saveSession(currentUser);
    updateUIForLoggedInUser();
    closeSignupModal();
    showToast('Account created successfully! Welcome to ApartmentHub.', 'success');
    
    // Reset form
    document.getElementById('signupForm').reset();
}

// Toggle User Dropdown
function toggleUserDropdown() {
    document.getElementById('userDropdown').classList.toggle('show');
}

// Logout
function logout() {
    clearSession();
    updateUIForLoggedOutUser();
    showToast('You have been logged out', 'success');
}

// Setup outside click listener to close dropdowns
function setupOutsideClickListener() {
    document.addEventListener('click', function(e) {
        // Close user dropdown when clicking outside
        const userMenu = document.querySelector('.user-menu');
        const dropdown = document.getElementById('userDropdown');
        
        if (userMenu && !userMenu.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLoginModal();
        closeSignupModal();
        closeModal();
    }
});

// Close modals on outside click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    
    toast.className = 'toast ' + type;
    toastMessage.textContent = message;
    toast.classList.add('show');
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// ==================== VACANCIES DATA ====================
const vacancies = [
    {
        id: 1,
        title: "Cozy Studio Room",
        type: "Studio",
        price: 850,
        address: "123 Main Street, Downtown",
        beds: 0,
        baths: 1,
        sqft: 350,
        status: "available",
        moveIn: "Immediate",
        amenities: ["Furnished", "WiFi", "Laundry"]
    },
    {
        id: 2,
        title: "Bright 1BR Room",
        type: "1 Bedroom",
        price: 1200,
        address: "456 Oak Avenue, Midtown",
        beds: 1,
        baths: 1,
        sqft: 500,
        status: "available",
        moveIn: "Nov 1, 2024",
        amenities: ["Kitchen", "A/C", "Parking"]
    },
    {
        id: 3,
        title: "Spacious 2BR Suite",
        type: "2 Bedrooms",
        price: 1750,
        address: "789 Pine Road, Westside",
        beds: 2,
        baths: 2,
        sqft: 950,
        status: "limited",
        moveIn: "Immediate",
        amenities: ["Balcony", "Gym", "Pet Friendly"]
    },
    {
        id: 4,
        title: "Deluxe Master Room",
        type: "Master Bedroom",
        price: 1450,
        address: "321 Elm Street, University District",
        beds: 1,
        baths: 1,
        sqft: 600,
        status: "available",
        moveIn: "Dec 1, 2024",
        amenities: ["Private Bath", "Walk-in Closet", "Desk"]
    },
    {
        id: 5,
        title: "Shared Room - Male",
        type: "Shared Room",
        price: 550,
        address: "555 College Ave, Campus Area",
        beds: 1,
        baths: 1,
        sqft: 250,
        status: "urgent",
        moveIn: "Immediate",
        amenities: ["All Bills Included", "Study Room"]
    },
    {
        id: 6,
        title: "Shared Room - Female",
        type: "Shared Room",
        price: 550,
        address: "556 College Ave, Campus Area",
        beds: 1,
        baths: 1,
        sqft: 250,
        status: "available",
        moveIn: "Nov 15, 2024",
        amenities: ["All Bills Included", "Study Room"]
    },
    {
        id: 7,
        title: "Loft Style Room",
        type: "Loft",
        price: 1650,
        address: "888 Industrial Blvd, Arts District",
        beds: 1,
        baths: 1,
        sqft: 700,
        status: "available",
        moveIn: "Immediate",
        amenities: ["High Ceilings", "Exposed Brick", "Windows"]
    },
    {
        id: 8,
        title: "Garden View Room",
        type: "1 Bedroom",
        price: 1350,
        address: "999 Garden Court, Green Neighborhood",
        beds: 1,
        baths: 1,
        sqft: 550,
        status: "limited",
        moveIn: "Nov 20, 2024",
        amenities: ["Garden Access", "Quiet", "Parking"]
    },
    {
        id: 9,
        title: "Downtown Executive Room",
        type: "Executive Suite",
        price: 2200,
        address: "100 Financial District, Tower A",
        beds: 1,
        baths: 1,
        sqft: 650,
        status: "available",
        moveIn: "Immediate",
        amenities: ["Concierge", "Rooftop", "Smart Home"]
    },
    {
        id: 10,
        title: "Budget Friendly Room",
        type: "Room",
        price: 750,
        address: "222 Suburban Lane, Eastside",
        beds: 1,
        baths: 1,
        sqft: 400,
        status: "available",
        moveIn: "Immediate",
        amenities: ["Bus Route", "Shopping Nearby"]
    },
    {
        id: 11,
        title: "Family Size Room",
        type: "3 Bedrooms",
        price: 2400,
        address: "444 Family Circle, Suburbs",
        beds: 3,
        baths: 2,
        sqft: 1200,
        status: "available",
        moveIn: "Dec 15, 2024",
        amenities: ["Yard", "Garage", "Schools Nearby"]
    },
    {
        id: 12,
        title: "Penthouse Room",
        type: "Penthouse",
        price: 3500,
        address: "777 Sky Tower, Downtown",
        beds: 2,
        baths: 2,
        sqft: 1100,
        status: "available",
        moveIn: "Immediate",
        amenities: ["Panoramic Views", "Private Terrace", "Spa"]
    }
];

// Current vacancy filter
let currentVacancyFilter = 'all';

// Render vacancies
function renderVacancies(vacanciesToRender) {
    const grid = document.getElementById('vacanciesGrid');
    
    if (vacanciesToRender.length === 0) {
        grid.innerHTML = '<p style="text-align: center; grid-column: 1/-1; color: #6b7280; padding: 40px;">No vacant rooms found in this price range.</p>';
        return;
    }
    
    grid.innerHTML = vacanciesToRender.map(vacancy => createVacancyCard(vacancy)).join('');
    
    // Add click listeners
    document.querySelectorAll('.vacancy-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.classList.contains('vacancy-save') && !e.target.classList.contains('vacancy-btn')) {
                const id = parseInt(this.dataset.id);
                openVacancyModal(id);
            }
        });
    });
}

// Create vacancy card HTML
function createVacancyCard(vacancy) {
    const statusClass = vacancy.status;
    const statusText = vacancy.status.charAt(0).toUpperCase() + vacancy.status.slice(1);
    
    return `
        <div class="vacancy-card" data-id="${vacancy.id}">
            <div class="vacancy-image">
                🚪
                <span class="vacancy-status ${statusClass}">${statusText}</span>
                <button class="vacancy-save" onclick="event.stopPropagation(); saveVacancy(${vacancy.id})">
                    ♥
                </button>
            </div>
            <div class="vacancy-content">
                <div class="vacancy-header">
                    <div class="vacancy-price">$${vacancy.price}<span>/month</span></div>
                    <span class="vacancy-type">${vacancy.type}</span>
                </div>
                <h3 class="vacancy-title">${vacancy.title}</h3>
                <div class="vacancy-address">📍 ${vacancy.address}</div>
                <div class="vacancy-amenities">
                    <span class="vacancy-amenity">🛏️ ${vacancy.beds === 0 ? 'Studio' : vacancy.beds + ' Bed'}</span>
                    <span class="vacancy-amenity">🚿 ${vacancy.baths} Bath</span>
                    <span class="vacancy-amenity">📐 ${vacancy.sqft} sqft</span>
                </div>
                <div class="vacancy-footer">
                    <span class="vacancy-movein">Move-in: <strong>${vacancy.moveIn}</strong></span>
                    <button class="vacancy-btn" onclick="event.stopPropagation(); applyToVacancy(${vacancy.id})">
                        Apply Now
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Filter vacancies by price
function filterVacancies(priceRange) {
    currentVacancyFilter = priceRange;
    
    // Update active button
    document.querySelectorAll('.price-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    let filtered = vacancies;
    
    if (priceRange !== 'all') {
        const [min, max] = priceRange.split('-').map(p => p.replace('+', ''));
        
        if (priceRange.endsWith('+')) {
            filtered = vacancies.filter(v => v.price >= parseInt(min));
        } else {
            filtered = vacancies.filter(v => v.price >= parseInt(min) && v.price <= parseInt(max));
        }
    }
    
    renderVacancies(filtered);
}

// Save vacancy to favorites
function saveVacancy(id) {
    const savedVacancies = JSON.parse(localStorage.getItem('savedVacancies') || '[]');
    
    if (savedVacancies.includes(id)) {
        showToast('Removed from saved', 'success');
    } else {
        savedVacancies.push(id);
        localStorage.setItem('savedVacancies', JSON.stringify(savedVacancies));
        showToast('Saved to your list!', 'success');
    }
}

// Apply to vacancy
function applyToVacancy(id) {
    const vacancy = vacancies.find(v => v.id === id);
    if (!vacancy) return;
    
    if (!currentUser) {
        showToast('Please login to apply', 'error');
        openLoginModal();
        return;
    }
    
    alert(`Application for: ${vacancy.title}\n\nIn a real application, this would open a application form with:\n- Personal information\n- Income verification\n- Credit check authorization\n- Lease agreement`);
}

// Open vacancy detail modal
function openVacancyModal(id) {
    const vacancy = vacancies.find(v => v.id === id);
    if (!vacancy) return;
    
    const modal = document.getElementById('propertyModal');
    const modalBody = document.getElementById('modalBody');
    const statusClass = vacancy.status;
    const statusText = vacancy.status.charAt(0).toUpperCase() + vacancy.status.slice(1);
    
    modalBody.innerHTML = `
        <div class="vacancy-detail-grid">
            <div class="vacancy-detail-image">🚪</div>
            <div class="vacancy-detail-info">
                <span class="vacancy-status ${statusClass}">${statusText}</span>
                <h2>${vacancy.title}</h2>
                <p class="vacancy-detail-address">📍 ${vacancy.address}</p>
                <div class="vacancy-detail-price">$${vacancy.price}<span>/month</span></div>
                <div class="vacancy-detail-features">
                    <div class="feature">
                        <span>🛏️</span>
                        <label>${vacancy.beds === 0 ? 'Studio' : vacancy.beds + ' Bed'}</label>
                    </div>
                    <div class="feature">
                        <span>🚿</span>
                        <label>${vacancy.baths} Bath</label>
                    </div>
                    <div class="feature">
                        <span>📐</span>
                        <label>${vacancy.sqft} sqft</label>
                    </div>
                </div>
                <p><strong>Move-in Available:</strong> ${vacancy.moveIn}</p>
                <p style="margin-top: 10px;"><strong>Amenities:</strong> ${vacancy.amenities.join(', ')}</p>
                <button class="btn btn-primary modal-contact-btn" style="margin-top: 20px;" onclick="applyToVacancy(${vacancy.id})">
                    Apply Now
                </button>
            </div>
        </div>
    `;
    
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Initialize vacancies on page load
document.addEventListener('DOMContentLoaded', function() {
    checkSession();
    setupOutsideClickListener();
    renderListings(listings);
    renderVacancies(vacancies);
});