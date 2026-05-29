<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

// Get dashboard statistics
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Total bookings
    $bookingsStmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $totalBookings = $bookingsStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total users
    $usersStmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='user'");
    $totalUsers = $usersStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total vehicles
    $vehiclesStmt = $pdo->query("SELECT COUNT(*) as total FROM vehicles");
    $totalVehicles = $vehiclesStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending bookings
    $pendingStmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status='pending'");
    $pendingBookings = $pendingStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Transport Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/admin-style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <?php include 'includes/navbar.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h1 class="h3 mb-3 text-gray-800">
                            <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                        </h1>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-0">Total Bookings</h6>
                                        <h2 class="mb-0 text-primary"><?php echo $totalBookings ?? 0; ?></h2>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-0">Pending Bookings</h6>
                                        <h2 class="mb-0 text-warning"><?php echo $pendingBookings ?? 0; ?></h2>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-0">Total Users</h6>
                                        <h2 class="mb-0 text-success"><?php echo $totalUsers ?? 0; ?></h2>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-0">Total Vehicles</h6>
                                        <h2 class="mb-0 text-info"><?php echo $totalVehicles ?? 0; ?></h2>
                                    </div>
                                    <div class="stat-icon bg-info">
                                        <i class="fas fa-car"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-rocket"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="bookings.php" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> View Bookings
                                </a>
                                <a href="users.php" class="btn btn-outline-success">
                                    <i class="fas fa-user-plus"></i> Manage Users
                                </a>
                                <a href="vehicles.php" class="btn btn-outline-info">
                                    <i class="fas fa-car"></i> Manage Vehicles
                                </a>
                                <a href="reports.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-chart-bar"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Bookings</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>User</th>
                                                <th>Vehicle</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">
                                                    <a href="bookings.php">View all bookings →</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
