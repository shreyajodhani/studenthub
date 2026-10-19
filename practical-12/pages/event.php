<?php
require_once __DIR__ . '/auth.php';
require_login('student');
$conn = db_connect();
$eventsResult = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Upcoming Campus Events</p>
        </header>

        <nav class="navbar">
            <div>
                <a href="dashboard.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Dashboard</a>
                <a href="assignment.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Assignments</a>
                <a href="event.php" style="color: #fff; font-weight: bold; text-decoration: none;">Events</a>
            </div>
            <div><a href="logout.php" class="logout-link">Logout</a></div>
        </nav>

        <main class="box">
            <h2>Campus Events & Activities</h2>
            <p style="color: #666; margin-bottom: 20px;">Check out the latest technical, cultural, and sports events organized on campus.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <?php if ($eventsResult->num_rows > 0): ?>
                    <?php while ($row = $eventsResult->fetch_assoc()): ?>
                        <div class="box" style="border: 1px solid #e0e0e0; background: #faf8fc; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <?php if (!empty($row['poster_image'])): ?>
                                    <img src="../<?php echo e($row['poster_image']); ?>" alt="Event Poster" style="width: 100%; height: 180px; object-fit: cover; border-radius: 6px; margin-bottom: 15px;">
                                <?php endif; ?>
                                <span style="background: #4b1682; color: #fff; padding: 3px 8px; font-size: 0.75rem; border-radius: 4px; font-weight: bold;"><?php echo e($row['category']); ?></span>
                                <h3 style="margin: 10px 0 5px 0; color: #3c1053;"><?php echo e($row['title']); ?></h3>
                                <p style="font-size: 0.85rem; color: #555; margin-bottom: 10px;"><strong>Date:</strong> <?php echo e($row['event_date']); ?></p>
                                <p style="font-size: 0.9rem; color: #444; line-height: 1.4;"><?php echo nl2br(e($row['description'])); ?></p>
                            </div>
                            <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.85rem; font-weight: bold; color: <?php echo ($row['status'] === 'Open') ? '#2e7d32' : '#c62828'; ?>;">Status: <?php echo e($row['status']); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: #666; grid-column: span 3; text-align: center; padding: 20px;">No events currently scheduled.</p>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer">&copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.</footer>
    </div>
</body>
</html>