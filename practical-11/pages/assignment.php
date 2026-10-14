<?php
require_once __DIR__ . '/auth.php';
require_login('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Assignment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box" style="text-align: center;">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Assignments Portal</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php">Dashboard</a>
                <a href="event.php">Event</a>
                <a href="assignment.php" class="active">Assignment</a>
                <a href="contact.php">Contact</a>
                <a href="profile.php">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box">
            <h2>Pending Assignments</h2>
            <div class="box hover-card" style="margin-bottom: 16px;">
                <h3>Data Structures - AVL Trees Implementation</h3>
                <p class="hint">Due Date: 25 October 2026</p>
                <p>Implement rotation algorithms for AVL trees in C++ with test cases.</p>
            </div>
            <div class="box hover-card" style="margin-bottom: 16px;">
                <h3>Database Systems - ER Diagram Normalization</h3>
                <p class="hint">Due Date: 28 October 2026</p>
                <p>Normalize the given university database schema up to 3NF.</p>
            </div>

            <h2 style="margin-top: 30px;">Completed Assignments</h2>
            <div class="box hover-card" style="opacity: 0.95;">
                <h3>Web Development - Portfolio Layout</h3>
                <p class="hint">Submitted on: 01 October 2026 | Status: Evaluated</p>
                <p>Responsive multi-page layout using CSS Flexbox and custom CSS variables.</p>
            </div>
        </main>
    </div>
</body>
</html>