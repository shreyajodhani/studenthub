<?php
require_once __DIR__ . '/auth.php';
require_login('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Event</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box" style="text-align: center;">
            <h1>STUDENT HUB PORTAL</h1>
            <p>University Events & Workshops</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php">Dashboard</a>
                <a href="event.php" class="active">Event</a>
                <a href="assignment.php">Assignment</a>
                <a href="contact.php">Contact</a>
                <a href="profile.php">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box">
            <h2>Upcoming Events & Workshops</h2>
            <div class="box hover-card" style="margin-bottom: 16px;">
                <h3>CHARUSAT Annual Tech Fest - Cognizance 2026</h3>
                <p class="hint">Date: 05 - 07 November 2026 | Location: University Auditorium</p>
                <p>Join the biggest technical festival featuring coding marathons and expert keynotes.</p>
            </div>
            <div class="box hover-card" style="margin-bottom: 16px;">
                <h3>Full Stack Web Development Workshop</h3>
                <p class="hint">Date: 14 November 2026 | Location: DEPSTAR Seminar Hall</p>
                <p>Hands-on session covering modern backend integration and database design.</p>
            </div>
            <div class="box hover-card">
                <h3>Inter-Department Cricket Tournament</h3>
                <p class="hint">Date: 20 November 2026 | Location: University Sports Ground</p>
                <p>Annual cricket tournament featuring teams across departments.</p>
            </div>
        </main>
    </div>
</body>
</html>