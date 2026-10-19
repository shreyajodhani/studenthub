<?php
require_once __DIR__ . '/auth.php';
require_login('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Contact</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box" style="text-align: center;">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Contact & Support Helpdesk</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php">Dashboard</a>
                <a href="event.php">Event</a>
                <a href="assignment.php">Assignment</a>
                <a href="contact.php" class="active">Contact</a>
                <a href="profile.php">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box">
            <h2>University Offices & Support</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-top: 15px;">
                <div class="box hover-card">
                    <h3>DEPSTAR Office</h3>
                    <p class="hint">Department Administration</p>
                    <p>Email: office.depstar@charusat.edu.in</p>
                    <p>Phone: +91 2697 265011</p>
                </div>
                <div class="box hover-card">
                    <h3>Technical Helpdesk</h3>
                    <p class="hint">Portal & IT Support</p>
                    <p>Email: support@charusat.edu.in</p>
                    <p>Phone: +91 2697 265022</p>
                </div>
                <div class="box hover-card">
                    <h3>Examination Cell</h3>
                    <p class="hint">Results & Queries</p>
                    <p>Email: exam@charusat.edu.in</p>
                    <p>Phone: +91 2697 265033</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>