<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$error = '';
$report_type = isset($_GET['type']) ? $_GET['type'] : 'bookings';
$data = [];

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    if ($report_type === 'bookings') {
        // Bookings report
        $stmt = $pdo->prepare("
            SELECT 
                b.id,
                b.booking_date,
                b.total_amount,
                b.status,
                u.username,
                v.name as vehicle_name
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN vehicles v ON b.vehicle_id = v.id
            ORDER BY b.booking_date DESC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($report_type === 'revenue') {
        // Revenue report
        $stmt = $pdo->prepare("
            SELECT 
                DATE(b.booking_date) as date,
                COUNT(*) as bookings,
                SUM(b.total_amount) as total_revenue,
                AVG(b.total_amount) as avg_amount
            FROM bookings b
            WHERE b.status = 'completed'
            GROUP BY DATE(b.booking_date)
            ORDER BY b.booking_date DESC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($report_type === 'vehicles') {
        // Vehicles report
        $stmt = $pdo->prepare("
            SELECT 
                v.id,
                v.name,
                v.type,
                v.status,
                COUNT(b.id) as total_bookings,
                SUM(b.total_amount) as total_revenue
            FROM vehicles v
            LEFT JOIN bookings b ON v.id = b.vehicle_id AND b.status = 'completed'
            GROUP BY v.id
            ORDER BY total_bookings DESC
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin-style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/navbar.php'; ?>
            
            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-chart-bar"></i> Reports
                        </h1>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-sm btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Report Type Selection -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="?type=bookings" class="btn btn-<?php echo $report_type === 'bookings' ? 'primary' : 'outline-primary'; ?>">
                                <i class="fas fa-calendar"></i> Bookings Report
                            </a>
                            <a href="?type=revenue" class="btn btn-<?php echo $report_type === 'revenue' ? 'primary' : 'outline-primary'; ?>">
                                <i class="fas fa-money-bill"></i> Revenue Report
                            </a>
                            <a href="?type=vehicles" class="btn btn-<?php echo $report_type === 'vehicles' ? 'primary' : 'outline-primary'; ?>">
                                <i class="fas fa-car"></i> Vehicles Report
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Report Data -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <?php 
                                echo $report_type === 'bookings' ? 'All Bookings' : 
                                    ($report_type === 'revenue' ? 'Revenue Summary' : 'Vehicle Performance');
                            ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <?php if ($report_type === 'bookings'): ?>
                                            <th>Booking ID</th>
                                            <th>User</th>
                                            <th>Vehicle</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        <?php elseif ($report_type === 'revenue'): ?>
                                            <th>Date</th>
                                            <th>Bookings</th>
                                            <th>Total Revenue</th>
                                            <th>Average Amount</th>
                                        <?php else: ?>
                                            <th>Vehicle</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Total Bookings</th>
                                            <th>Total Revenue</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($data) > 0): ?>
                                        <?php foreach ($data as $row): ?>
                                            <tr>
                                                <?php if ($report_type === 'bookings'): ?>
                                                    <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['vehicle_name']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                                    <td><span class="badge bg-success"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                                <?php elseif ($report_type === 'revenue'): ?>
                                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($row['bookings']); ?></td>
                                                    <td><strong>$<?php echo number_format($row['total_revenue'], 2); ?></strong></td>
                                                    <td>$<?php echo number_format($row['avg_amount'], 2); ?></td>
                                                <?php else: ?>
                                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                                    <td><span class="badge bg-<?php echo $row['status'] === 'available' ? 'success' : 'warning'; ?>"><?php echo ucfirst(htmlspecialchars($row['status'])); ?></span></td>
                                                    <td><?php echo htmlspecialchars($row['total_bookings'] ?? 0); ?></td>
                                                    <td>$<?php echo number_format($row['total_revenue'] ?? 0, 2); ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No data available
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
