<?php require_once 'config.php';

if (!isAdminLoggedIn()) {
    redirect('admin-login.php');
}

$payments = [
    ['id' => 1, 'tenant' => 'John Smith', 'email' => 'john@email.com', 'property' => '123 Main Street, Unit 4A', 'amount' => 1850, 'dueDate' => '2024-10-01', 'paidDate' => '2024-09-28', 'status' => 'Completed', 'method' => 'Card'],
    ['id' => 2, 'tenant' => 'Sarah Johnson', 'email' => 'sarah@email.com', 'property' => '456 Oak Avenue, Unit 2B', 'amount' => 1450, 'dueDate' => '2024-10-01', 'paidDate' => null, 'status' => 'Overdue', 'method' => 'Bank'],
    ['id' => 3, 'tenant' => 'Mike Davis', 'email' => 'mike@email.com', 'property' => '789 Pine Road, Unit 1C', 'amount' => 2100, 'dueDate' => '2024-10-05', 'paidDate' => null, 'status' => 'Pending', 'method' => 'Card'],
    ['id' => 4, 'tenant' => 'Emily Chen', 'email' => 'emily@email.com', 'property' => '123 Main Street, Unit 5B', 'amount' => 1650, 'dueDate' => '2024-09-25', 'paidDate' => '2024-09-24', 'status' => 'Completed', 'method' => 'Bank'],
    ['id' => 5, 'tenant' => 'David Wilson', 'email' => 'david@email.com', 'property' => '555 College Ave, Unit 3A', 'amount' => 950, 'dueDate' => '2024-10-01', 'paidDate' => '2024-10-01', 'status' => 'Completed', 'method' => 'Check'],
    ['id' => 6, 'tenant' => 'Lisa Brown', 'email' => 'lisa@email.com', 'property' => '888 Industrial Blvd, Unit 2D', 'amount' => 1800, 'dueDate' => '2024-10-03', 'paidDate' => null, 'status' => 'Pending', 'method' => 'Card'],
    ['id' => 7, 'tenant' => 'James Taylor', 'email' => 'james@email.com', 'property' => '456 Oak Avenue, Unit 1A', 'amount' => 1350, 'dueDate' => '2024-09-28', 'paidDate' => null, 'status' => 'Overdue', 'method' => 'Bank'],
];

$totalCollected = 6450;
$totalPending = 3150;
$totalOverdue = 2800;
$onTimeCount = 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - <?php echo SITE_NAME; ?> Admin</title>
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
        
        .payment-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .payment-stat { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 20px; }
        .payment-stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; }
        .payment-stat-icon.blue { background: #dbeafe; }
        .payment-stat-icon.green { background: #d1fae5; }
        .payment-stat-icon.yellow { background: #fef3c7; }
        .payment-stat-icon.red { background: #fee2e2; }
        .payment-stat-info h3 { font-size: 1.8rem; font-weight: 700; color: var(--text-dark); }
        .payment-stat-info p { font-size: 0.9rem; color: var(--text-light); }
        
        .filters-bar { background: white; padding: 20px 25px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .filters-bar label { font-size: 0.9rem; color: var(--text-light); }
        .filters-bar select, .filters-bar input { padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .filters-bar button { padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; }
        .filters-bar button:hover { background: var(--primary-dark); }
        
        .payments-table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .table-header { padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .table-header h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .header-actions { display: flex; gap: 10px; }
        .action-btn { padding: 8px 16px; border: 2px solid #e5e7eb; border-radius: 6px; background: white; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: var(--transition); }
        .action-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .action-btn.primary { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        
        .payments-table { width: 100%; border-collapse: collapse; }
        .payments-table th, .payments-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .payments-table th { background: #f9fafb; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
        .payments-table td { font-size: 0.9rem; color: var(--text-dark); }
        .payments-table tr:hover { background: #f9fafb; }
        .payments-table tr.overdue { background: #fef2f2; }
        
        .status-badge { padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; font-weight: 500; }
        .status-badge.completed { background: #d1fae5; color: #059669; }
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.failed { background: #fee2e2; color: #dc2626; }
        .status-badge.overdue { background: #fecaca; color: #991b1b; }
        
        .amount { font-weight: 600; color: var(--text-dark); }
        
        .table-actions { display: flex; gap: 8px; }
        .table-action-btn { width: 32px; height: 32px; border-radius: 6px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; transition: var(--transition); }
        .table-action-btn.view { background: #dbeafe; color: #2563eb; }
        .table-action-btn.refund { background: #fef3c7; color: #d97706; }
        .table-action-btn.remind { background: #ede9fe; color: #7c3aed; }
        .table-action-btn:hover { transform: scale(1.1); }
        
        .pagination { padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .pagination-info { font-size: 0.9rem; color: var(--text-light); }
        .pagination-btns { display: flex; gap: 8px; }
        .pagination-btn { width: 36px; height: 36px; border: 2px solid #e5e7eb; border-radius: 8px; background: white; cursor: pointer; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .pagination-btn:hover, .pagination-btn.active { border-color: var(--primary-color); color: var(--primary-color); }
        .pagination-btn.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .filters-bar { flex-direction: column; align-items: stretch; } }
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
                    <a href="admin-payments.php" class="sidebar-link active">
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
                    <a href="admin-reviews.php" class="sidebar-link">
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
                    <h1>Payments</h1>
                    <p>Track and manage tenant payments</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="payment-stats">
                    <div class="payment-stat">
                        <div class="payment-stat-icon blue">💰</div>
                        <div class="payment-stat-info">
                            <h3>$<?php echo number_format($totalCollected); ?></h3>
                            <p>Collected This Month</p>
                        </div>
                    </div>
                    <div class="payment-stat">
                        <div class="payment-stat-icon yellow">⏳</div>
                        <div class="payment-stat-info">
                            <h3>$<?php echo number_format($totalPending); ?></h3>
                            <p>Pending Payments</p>
                        </div>
                    </div>
                    <div class="payment-stat">
                        <div class="payment-stat-icon red">⚠️</div>
                        <div class="payment-stat-info">
                            <h3>$<?php echo number_format($totalOverdue); ?></h3>
                            <p>Overdue</p>
                        </div>
                    </div>
                    <div class="payment-stat">
                        <div class="payment-stat-icon green">✅</div>
                        <div class="payment-stat-info">
                            <h3><?php echo $onTimeCount; ?></h3>
                            <p>On-Time Payments</p>
                        </div>
                    </div>
                </div>
                
                <div class="filters-bar">
                    <label>Status:</label>
                    <select id="filterStatus">
                        <option value="all">All Status</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                    <label>Property:</label>
                    <select id="filterProperty">
                        <option value="all">All Properties</option>
                        <option value="123 Main Street">123 Main Street</option>
                        <option value="456 Oak Avenue">456 Oak Avenue</option>
                        <option value="789 Pine Road">789 Pine Road</option>
                    </select>
                    <button onclick="applyFilters()">Apply Filters</button>
                    <button onclick="resetFilters()" style="background: #6b7280;">Reset</button>
                </div>
                
                <div class="payments-table-container">
                    <div class="table-header">
                        <h3>💳 Payment Transactions</h3>
                        <div class="header-actions">
                            <button class="action-btn" onclick="exportPayments('csv')">📄 Export</button>
                            <button class="action-btn primary" onclick="showRecordPayment()">➕ Record Payment</button>
                        </div>
                    </div>
                    <table class="payments-table" id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                            <tr class="<?php echo strtolower($payment['status']); ?>">
                                <td>
                                    <strong><?php echo sanitize($payment['tenant']); ?></strong><br>
                                    <small style="color: #6b7280;"><?php echo sanitize($payment['email']); ?></small>
                                </td>
                                <td><?php echo sanitize($payment['property']); ?></td>
                                <td class="amount">$<?php echo number_format($payment['amount']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($payment['dueDate'])); ?></td>
                                <td><?php echo $payment['paidDate'] ? date('M d, Y', strtotime($payment['paidDate'])) : '—'; ?></td>
                                <td><span class="status-badge <?php echo strtolower($payment['status']); ?>"><?php echo $payment['status']; ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="table-action-btn view" onclick="viewPayment(<?php echo $payment['id']; ?>)" title="View">👁️</button>
                                        <?php if ($payment['status'] === 'Completed'): ?>
                                        <button class="table-action-btn refund" onclick="refundPayment(<?php echo $payment['id']; ?>)" title="Refund">💰</button>
                                        <?php elseif ($payment['status'] === 'Overdue'): ?>
                                        <button class="table-action-btn remind" onclick="sendReminder(<?php echo $payment['id']; ?>)" title="Remind">🔔</button>
                                        <?php elseif ($payment['status'] === 'Pending'): ?>
                                        <button class="table-action-btn view" onclick="markPaid(<?php echo $payment['id']; ?>)" title="Mark Paid">✓</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <div class="pagination-info">Showing 1-<?php echo count($payments); ?> of <?php echo count($payments); ?> transactions</div>
                        <div class="pagination-btns">
                            <button class="pagination-btn">◀</button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn">▶</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div id="recordPaymentModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2>Record Payment</h2>
                <button class="modal-close" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body">
                <form onsubmit="submitPayment(event)">
                    <div class="form-group">
                        <label>Tenant</label>
                        <select id="paymentTenant" required>
                            <option value="">Select Tenant</option>
                            <option value="1">John Smith - 123 Main Street, Unit 4A</option>
                            <option value="2">Sarah Johnson - 456 Oak Avenue, Unit 2B</option>
                            <option value="3">Mike Davis - 789 Pine Road, Unit 1C</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount ($)</label>
                        <input type="number" id="paymentAmount" placeholder="0.00" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" id="paymentDate" required>
                    </div>
                    <div class="form-group">
                        <label>Payment Method</label>
                        <select id="paymentMethod" required>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="paymentNotes" placeholder="Optional notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Record Payment</button>
                </form>
            </div>
        </div>
    </div>
    
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('paymentDate').valueAsDate = new Date();
        });
        
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        
        function showRecordPayment() {
            document.getElementById('recordPaymentModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
            document.body.style.overflow = '';
        }
        
        function submitPayment(e) {
            e.preventDefault();
            closeModal();
            showToast('Payment recorded successfully!', 'success');
        }
        
        function viewPayment(id) { showToast('Viewing payment #' + id, 'success'); }
        function refundPayment(id) { if (confirm('Process refund for payment #' + id + '?')) showToast('Refund processed!', 'success'); }
        function sendReminder(id) { if (confirm('Send payment reminder to tenant #' + id + '?')) showToast('Reminder sent!', 'success'); }
        function markPaid(id) { if (confirm('Mark payment #' + id + ' as paid?')) showToast('Payment marked as paid!', 'success'); }
        
        function applyFilters() { showToast('Applying filters...', 'success'); }
        function resetFilters() { showToast('Filters reset', 'success'); }
        function exportPayments(format) { showToast('Exporting as ' + format.toUpperCase() + '...', 'success'); }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
        
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        });
    </script>
</body>
</html>