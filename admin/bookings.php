<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../config/database.php';

$error = '';
$success = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Fetch all bookings
    $stmt = $pdo->prepare("
        SELECT 
            b.id, 
            b.booking_date, 
            b.pickup_location, 
            b.dropoff_location, 
            b.status, 
            b.total_amount,
            u.username,
            v.name as vehicle_name
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN vehicles v ON b.vehicle_id = v.id
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_status') {
            $booking_id = $_POST['booking_id'];
            $status = $_POST['status'];
            
            $updateStmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $updateStmt->execute([$status, $booking_id]);
            
            $success = "Booking status updated successfully!";
            header("Refresh:1");
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin Panel</title>
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
                    <div class="col-md-12">
                        <h1 class="h3 mb-3 text-gray-800">
                            <i class="fas fa-calendar-check"></i> Manage Bookings
                        </h1>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">All Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User</th>
                                        <th>Vehicle</th>
                                        <th>Pickup Location</th>
                                        <th>Dropoff Location</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($bookings) > 0): ?>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td><strong>#<?php echo htmlspecialchars($booking['id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['vehicle_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['pickup_location']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['dropoff_location']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>$<?php echo number_format($booking['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $booking['status'] === 'completed' ? 'success' : 
                                                            ($booking['status'] === 'cancelled' ? 'danger' : 'warning'); 
                                                    ?>">
                                                        <?php echo ucfirst(htmlspecialchars($booking['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?php echo $booking['id']; ?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal -->
                                            <div class="modal fade" id="editModal<?php echo $booking['id']; ?>" 
                                                 tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Booking #<?php echo $booking['id']; ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Update Status</button>
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No bookings found</p>
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
