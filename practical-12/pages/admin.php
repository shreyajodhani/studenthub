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
            <p>Admin Control Dashboard</p>
        </header>

        <nav class="navbar">
            <div style="font-weight: bold; color: #ffffff; font-size: 1.1rem;">
                Welcome, Admin
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box" style="text-align: center; padding: 40px 20px;">
            <h2>Admin Management Modules</h2>
            <p style="color: #666; margin-bottom: 30px;">Select a module below to manage system records and content.</p>

            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <div class="box" style="flex: 1; min-width: 280px; background: #fdfbfe; border: 2px solid #4b1682;">
                    <h3>Manage Students</h3>
                    <p style="margin: 15px 0; color: #555;">Add, view, edit, search, and delete student academic profiles.</p>
                    <a href="manage_students.php" class="btn" style="text-decoration: none; display: inline-block;">Open Student CRUD</a>
                </div>

                <div class="box" style="flex: 1; min-width: 280px; background: #fdfbfe; border: 2px solid #4b1682;">
                    <h3>Manage Events & Posters</h3>
                    <p style="margin: 15px 0; color: #555;">Create events, schedule dates, update status, and upload posters.</p>
                    <a href="manage_events.php" class="btn" style="text-decoration: none; display: inline-block;">Open Event CRUD</a>
                </div>
            </div>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>
    </div>
</body>
</html>