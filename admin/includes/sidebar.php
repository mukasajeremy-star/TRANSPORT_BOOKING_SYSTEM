<?php
// admin/includes/sidebar.php
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <h3 class="text-white mb-4">
            <i class="fas fa-cog"></i> Admin Panel
        </h3>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" 
               href="index.php">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>" 
               href="bookings.php">
                <i class="fas fa-calendar-check"></i> Bookings
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
               href="users.php">
                <i class="fas fa-users"></i> Users
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'vehicles.php' ? 'active' : ''; ?>" 
               href="vehicles.php">
                <i class="fas fa-car"></i> Vehicles
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" 
               href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'routes.php' ? 'active' : ''; ?>" 
               href="routes.php">
                <i class="fas fa-map"></i> Routes
            </a>
        </li>

        <li class="nav-divider"></li>

        <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" 
               href="settings.php">
                <i class="fas fa-sliders-h"></i> Settings
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</nav>
