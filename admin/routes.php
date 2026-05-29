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
    
    // Fetch all routes
    $stmt = $pdo->prepare("
        SELECT id, name, start_location, end_location, distance, created_at 
        FROM routes 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle add/delete route
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_route') {
            $name = trim($_POST['name']);
            $start_location = trim($_POST['start_location']);
            $end_location = trim($_POST['end_location']);
            $distance = (float)$_POST['distance'];
            
            $insertStmt = $pdo->prepare("
                INSERT INTO routes (name, start_location, end_location, distance) 
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$name, $start_location, $end_location, $distance]);
            
            $success = "Route added successfully!";
            header("Refresh:1");
        } elseif ($_POST['action'] === 'delete_route') {
            $route_id = $_POST['route_id'];
            
            $deleteStmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
            $deleteStmt->execute([$route_id]);
            
            $success = "Route deleted successfully!";
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
    <title>Manage Routes - Admin Panel</title>
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
                            <i class="fas fa-map"></i> Manage Routes
                        </h1>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                            <i class="fas fa-plus"></i> Add New Route
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
                        <h5 class="mb-0">All Routes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Route Name</th>
                                        <th>Start Location</th>
                                        <th>End Location</th>
                                        <th>Distance (km)</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($routes) > 0): ?>
                                        <?php foreach ($routes as $route): ?>
                                            <tr>
                                                <td><strong>#<?php echo htmlspecialchars($route['id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($route['name']); ?></td>
                                                <td><?php echo htmlspecialchars($route['start_location']); ?></td>
                                                <td><?php echo htmlspecialchars($route['end_location']); ?></td>
                                                <td><?php echo htmlspecialchars($route['distance']); ?> km</td>
                                                <td><?php echo date('M d, Y', strtotime($route['created_at'])); ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this route?')">
                                                        <input type="hidden" name="action" value="delete_route">
                                                        <input type="hidden" name="route_id" value="<?php echo $route['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No routes found</p>
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

    <!-- Add Route Modal -->
    <div class="modal fade" id="addRouteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Route</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Route Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Location</label>
                            <input type="text" name="start_location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Location</label>
                            <input type="text" name="end_location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" name="distance" class="form-control" step="0.1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Route</button>
                        <input type="hidden" name="action" value="add_route">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
