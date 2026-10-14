<?php
require_once __DIR__ . '/auth.php';
require_login('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Admin Management Dashboard</p>
        </header>

        <nav class="navbar">
            <div style="font-weight: bold; color: #ffffff; font-size: 1.1rem;">
                Admin Portal Panel
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <div class="notice-banner">
            <strong>Welcome, Admin!</strong> Manage university assignments and upcoming events from your control panel below.
        </div>

        <main style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <div class="box hover-card" onclick="alert('Assignment creation form will be integrated soon.')">
                <h3>Add Assignment</h3>
                <p>Publish new academic assignments, set due dates, and assign them to departments.</p>
                <span class="hint">Click to add assignment &rarr;</span>
            </div>

            <div class="box hover-card" onclick="alert('Event creation form will be integrated soon.')">
                <h3>Add Events</h3>
                <p>Create and announce university workshops, technical festivals, and sports events.</p>
                <span class="hint">Click to add event &rarr;</span>
            </div>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>
    </div>
</body>
</html>