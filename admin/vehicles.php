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
    
    // Fetch all vehicles
    $stmt = $pdo->prepare("
        SELECT id, name, type, license_plate, capacity, rate_per_km, status, created_at 
        FROM vehicles 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle add vehicle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_vehicle') {
            $name = trim($_POST['name']);
            $type = trim($_POST['type']);
            $license_plate = trim($_POST['license_plate']);
            $capacity = (int)$_POST['capacity'];
            $rate_per_km = (float)$_POST['rate_per_km'];
            
            $insertStmt = $pdo->prepare("
                INSERT INTO vehicles (name, type, license_plate, capacity, rate_per_km, status) 
                VALUES (?, ?, ?, ?, ?, 'available')
            ");
            $insertStmt->execute([$name, $type, $license_plate, $capacity, $rate_per_km]);
            
            $success = "Vehicle added successfully!";
            header("Refresh:1");
        } elseif ($_POST['action'] === 'delete_vehicle') {
            $vehicle_id = $_POST['vehicle_id'];
            
            $deleteStmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
            $deleteStmt->execute([$vehicle_id]);
            
            $success = "Vehicle deleted successfully!";
            header("Refresh:1");
        } elseif ($_POST['action'] === 'update_status') {
            $vehicle_id = $_POST['vehicle_id'];
            $status = $_POST['status'];
            
            $updateStmt = $pdo->prepare("UPDATE vehicles SET status = ? WHERE id = ?");
            $updateStmt->execute([$status, $vehicle_id]);
            
            $success = "Vehicle status updated successfully!";
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
    <title>Manage Vehicles - Admin Panel</title>
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
                            <i class="fas fa-car"></i> Manage Vehicles
                        </h1>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                            <i class="fas fa-plus"></i> Add New Vehicle
                        </button>
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
                        <h5 class="mb-0">All Vehicles</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>License Plate</th>
                                        <th>Capacity</th>
                                        <th>Rate/km</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($vehicles) > 0): ?>
                                        <?php foreach ($vehicles as $vehicle): ?>
                                            <tr>
                                                <td><strong>#<?php echo htmlspecialchars($vehicle['id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($vehicle['name']); ?></td>
                                                <td><?php echo htmlspecialchars($vehicle['type']); ?></td>
                                                <td><strong><?php echo htmlspecialchars($vehicle['license_plate']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($vehicle['capacity']); ?> seats</td>
                                                <td>$<?php echo number_format($vehicle['rate_per_km'], 2); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $vehicle['status'] === 'available' ? 'success' : 'warning'; 
                                                    ?>">
                                                        <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                            data-bs-target="#statusModal<?php echo $vehicle['id']; ?>">
                                                        <i class="fas fa-edit"></i> Status
                                                    </button>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this vehicle?')">
                                                        <input type="hidden" name="action" value="delete_vehicle">
                                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- Status Modal -->
                                            <div class="modal fade" id="statusModal<?php echo $vehicle['id']; ?>" 
                                                 tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Vehicle Status</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="available" <?php echo $vehicle['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                                        <option value="maintenance" <?php echo $vehicle['status'] === 'maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                                                                        <option value="unavailable" <?php echo $vehicle['status'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Update</button>
                                                                <input type="hidden" name="action" value="update_status">
                                                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No vehicles found</p>
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

    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Vehicle Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="sedan">Sedan</option>
                                <option value="suv">SUV</option>
                                <option value="van">Van</option>
                                <option value="truck">Truck</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">License Plate</label>
                            <input type="text" name="license_plate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity (Seats)</label>
                            <input type="number" name="capacity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rate per KM ($)</label>
                            <input type="number" name="rate_per_km" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Vehicle</button>
                        <input type="hidden" name="action" value="add_vehicle">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
