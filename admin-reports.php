<?php require_once 'config.php';

if (!isAdminLoggedIn()) {
    redirect('admin-login.php');
}

$revenueData = [
    ['property' => '123 Main Street', 'units' => 24, 'occupancy' => 92, 'revenue' => 52800, 'expenses' => 12400, 'netIncome' => 40400],
    ['property' => '456 Oak Avenue', 'units' => 18, 'occupancy' => 83, 'revenue' => 36000, 'expenses' => 9800, 'netIncome' => 26200],
    ['property' => '789 Pine Road', 'units' => 12, 'occupancy' => 83, 'revenue' => 24000, 'expenses' => 6200, 'netIncome' => 17800],
    ['property' => '555 College Ave', 'units' => 36, 'occupancy' => 83, 'revenue' => 48600, 'expenses' => 14500, 'netIncome' => 34100],
    ['property' => '888 Industrial Blvd', 'units' => 20, 'occupancy' => 90, 'revenue' => 44000, 'expenses' => 10200, 'netIncome' => 33800],
];

$tenantStats = [
    ['metric' => 'Total Tenants', 'current' => 1248, 'lastMonth' => 1156, 'change' => 7.9, 'positive' => true],
    ['metric' => 'New Move-ins', 'current' => 45, 'lastMonth' => 38, 'change' => 18.4, 'positive' => true],
    ['metric' => 'Move-outs', 'current' => 12, 'lastMonth' => 15, 'change' => 20.0, 'positive' => true],
    ['metric' => 'Lease Renewals', 'current' => 28, 'lastMonth' => 32, 'change' => 12.5, 'positive' => false],
    ['metric' => 'Avg. Lease Term', 'current' => 11.2, 'lastMonth' => 10.8, 'change' => 3.7, 'positive' => true],
];

$totalRevenue = array_sum(array_column($revenueData, 'revenue'));
$totalExpenses = array_sum(array_column($revenueData, 'expenses'));
$totalNetIncome = array_sum(array_column($revenueData, 'netIncome'));
$avgOccupancy = array_sum(array_column($revenueData, 'occupancy')) / count($revenueData);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo SITE_NAME; ?> Admin</title>
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
        
        .report-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .report-stat { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .report-stat h3 { font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 5px; }
        .report-stat p { font-size: 0.9rem; color: var(--text-light); }
        .report-stat .change { font-size: 0.85rem; margin-top: 10px; }
        .report-stat .change.up { color: #10b981; }
        .report-stat .change.down { color: #ef4444; }
        
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .chart-card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; }
        .chart-card h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); margin-bottom: 20px; }
        .chart-placeholder { height: 250px; background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .chart-bar { position: absolute; bottom: 20px; width: 30px; background: var(--primary-color); border-radius: 4px 4px 0 0; animation: barGrow 1s ease-out forwards; }
        @keyframes barGrow { from { height: 0; } }
        
        .report-table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 25px; }
        .report-table-header { padding: 20px 25px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .report-table-header h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-dark); }
        .report-filters { display: flex; gap: 10px; flex-wrap: wrap; }
        .report-filters select { padding: 10px 15px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 0.9rem; }
        .report-filters button { padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .report-table th { background: #f9fafb; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
        .report-table td { font-size: 0.9rem; color: var(--text-dark); }
        .report-table tr:hover { background: #f9fafb; }
        .report-table tr.total { background: #f9fafb; font-weight: 600; }
        .amount { font-weight: 600; color: #10b981; }
        .negative { color: #ef4444; }
        
        .export-btns { display: flex; gap: 10px; }
        .export-btn { padding: 8px 16px; border: 2px solid #e5e7eb; border-radius: 6px; background: white; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: var(--transition); }
        .export-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        
        .logout-btn { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 20px; background: none; border: none; color: #f87171; cursor: pointer; font-size: 0.95rem; transition: var(--transition); margin-top: 10px; }
        .logout-btn:hover { background: #374151; }
        .mobile-menu-toggle { display: none; position: fixed; bottom: 20px; right: 20px; width: 50px; height: 50px; background: var(--primary-color); color: white; border: none; border-radius: 50%; font-size: 1.5rem; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); z-index: 200; }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.show { transform: translateX(0); } .main-content { margin-left: 0; } .mobile-menu-toggle { display: flex; align-items: center; justify-content: center; } .charts-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .page-content { padding: 15px; } .report-filters { width: 100%; } .report-table-header { flex-direction: column; align-items: flex-start; } }
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
                    <a href="admin-reports.php" class="sidebar-link active">
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
                    <h1>Reports & Analytics</h1>
                    <p>Track performance and financial metrics</p>
                </div>
                <div class="header-right">
                    <button class="header-btn" onclick="toggleSidebar()">☰</button>
                </div>
            </header>
            <div class="page-content">
                <div class="report-stats">
                    <div class="report-stat">
                        <h3>$<?php echo number_format($totalRevenue, 0, '.', ','); ?></h3>
                        <p>Total Revenue (YTD)</p>
                        <div class="change up">↑ 15% vs last year</div>
                    </div>
                    <div class="report-stat">
                        <h3>1,248</h3>
                        <p>Active Tenants</p>
                        <div class="change up">↑ 8% vs last year</div>
                    </div>
                    <div class="report-stat">
                        <h3><?php echo number_format($avgOccupancy); ?>%</h3>
                        <p>Occupancy Rate</p>
                        <div class="change up">↑ 3% vs last year</div>
                    </div>
                    <div class="report-stat">
                        <h3>4.6</h3>
                        <p>Avg. Rating</p>
                        <div class="change up">↑ 0.2 vs last year</div>
                    </div>
                </div>
                
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3>Monthly Revenue</h3>
                        <div class="chart-placeholder">
                            <span style="font-size: 3rem; margin-bottom: 10px;">📈</span>
                            <span style="color: var(--text-light);">Revenue Trend Chart</span>
                            <div class="chart-bar" style="left: 10%; height: 80px; animation-delay: 0.1s;"></div>
                            <div class="chart-bar" style="left: 20%; height: 120px; animation-delay: 0.2s;"></div>
                            <div class="chart-bar" style="left: 30%; height: 100px; animation-delay: 0.3s;"></div>
                            <div class="chart-bar" style="left: 40%; height: 150px; animation-delay: 0.4s;"></div>
                            <div class="chart-bar" style="left: 50%; height: 130px; animation-delay: 0.5s;"></div>
                            <div class="chart-bar" style="left: 60%; height: 180px; animation-delay: 0.6s;"></div>
                            <div class="chart-bar" style="left: 70%; height: 160px; animation-delay: 0.7s;"></div>
                            <div class="chart-bar" style="left: 80%; height: 200px; animation-delay: 0.8s;"></div>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Occupancy by Property</h3>
                        <div class="chart-placeholder">
                            <span style="font-size: 3rem; margin-bottom: 10px;">🏢</span>
                            <span style="color: var(--text-light);">Occupancy Distribution</span>
                            <div class="chart-bar" style="left: 10%; height: 140px; background: #10b981; animation-delay: 0.1s;"></div>
                            <div class="chart-bar" style="left: 25%; height: 100px; background: #10b981; animation-delay: 0.2s;"></div>
                            <div class="chart-bar" style="left: 40%; height: 120px; background: #10b981; animation-delay: 0.3s;"></div>
                            <div class="chart-bar" style="left: 55%; height: 90px; background: #f59e0b; animation-delay: 0.4s;"></div>
                            <div class="chart-bar" style="left: 70%; height: 110px; background: #10b981; animation-delay: 0.5s;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="report-table-container">
                    <div class="report-table-header">
                        <h3>💰 Revenue Report</h3>
                        <div class="report-filters">
                            <select id="revenueMonth">
                                <option value="10">October 2024</option>
                                <option value="9">September 2024</option>
                                <option value="8">August 2024</option>
                            </select>
                            <button onclick="generateReport()">Generate Report</button>
                        </div>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Units</th>
                                <th>Occupancy</th>
                                <th>Revenue</th>
                                <th>Expenses</th>
                                <th>Net Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($revenueData as $row): ?>
                            <tr>
                                <td><?php echo sanitize($row['property']); ?></td>
                                <td><?php echo $row['units']; ?></td>
                                <td><?php echo $row['occupancy']; ?>%</td>
                                <td class="amount">$<?php echo number_format($row['revenue']); ?></td>
                                <td>$<?php echo number_format($row['expenses']); ?></td>
                                <td class="amount">$<?php echo number_format($row['netIncome']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total">
                                <td>Total</td>
                                <td><?php echo array_sum(array_column($revenueData, 'units')); ?></td>
                                <td><?php echo number_format($avgOccupancy); ?>%</td>
                                <td class="amount">$<?php echo number_format($totalRevenue); ?></td>
                                <td>$<?php echo number_format($totalExpenses); ?></td>
                                <td class="amount">$<?php echo number_format($totalNetIncome); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="report-table-container">
                    <div class="report-table-header">
                        <h3>👥 Tenant Statistics</h3>
                        <div class="export-btns">
                            <button class="export-btn" onclick="exportReport('csv')">📄 Export CSV</button>
                            <button class="export-btn" onclick="exportReport('pdf')">📑 Export PDF</button>
                        </div>
                    </div>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Current</th>
                                <th>Last Month</th>
                                <th>Change</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenantStats as $stat): ?>
                            <tr>
                                <td><?php echo sanitize($stat['metric']); ?></td>
                                <td><?php echo is_float($stat['current']) ? number_format($stat['current'], 1) . ' mo' : number_format($stat['current']); ?></td>
                                <td><?php echo is_float($stat['lastMonth']) ? number_format($stat['lastMonth'], 1) . ' mo' : number_format($stat['lastMonth']); ?></td>
                                <td class="<?php echo $stat['positive'] ? 'amount' : 'negative'; ?>">
                                    <?php echo $stat['positive'] ? '↑' : '↓'; ?> <?php echo $stat['change']; ?>%
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>
    
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('show'); }
        function generateReport() { showToast('Generating report...', 'success'); }
        function exportReport(format) { showToast('Exporting as ' + format.toUpperCase() + '...', 'success'); }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:15px 25px;border-radius:8px;z-index:3000;background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.style.transform = 'translateX(150%)'; setTimeout(() => toast.remove(), 300); }, 3000);
        }
    </script>
</body>
</html>