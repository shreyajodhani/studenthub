<?php
require_once __DIR__ . '/auth.php';
require_login('student');
$conn = db_connect();

$studentId = $_SESSION['user_id'] ?? 0;
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = intval($_POST['event_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $studStmt = $conn->prepare("SELECT id FROM student WHERE user_id = ? LIMIT 1");
    $studStmt->bind_param("i", $studentId);
    $studStmt->execute();
    $studRes = $studStmt->get_result();
    if ($studRow = $studRes->fetch_assoc()) {
        $realStudentId = $studRow['id'];

        if ($action === 'register') {
            $check = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND student_id = ?");
            $check->bind_param("ii", $eventId, $realStudentId);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                $ins = $conn->prepare("INSERT INTO event_registrations (event_id, student_id) VALUES (?, ?)");
                $ins->bind_param("ii", $eventId, $realStudentId);
                if ($ins->execute()) {
                    $successMsg = "Successfully registered for the event.";
                } else {
                    $errorMsg = "Failed to register.";
                }
                $ins->close();
            } else {
                $errorMsg = "You are already registered for this event.";
            }
            $check->close();
        } elseif ($action === 'cancel') {
            $del = $conn->prepare("DELETE FROM event_registrations WHERE event_id = ? AND student_id = ?");
            $del->bind_param("ii", $eventId, $realStudentId);
            if ($del->execute()) {
                $successMsg = "Registration cancelled successfully.";
            } else {
                $errorMsg = "Failed to cancel registration.";
            }
            $del->close();
        }
    } else {
        $errorMsg = "Student profile not found.";
    }
    $studStmt->close();
}

$studQuery = $conn->prepare("SELECT id FROM student WHERE user_id = ? LIMIT 1");
$studQuery->bind_param("i", $studentId);
$studQuery->execute();
$studRes = $studQuery->get_result();
$realStudentId = ($studRes->num_rows > 0) ? $studRes->fetch_assoc()['id'] : 0;
$studQuery->close();

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
    <div class="container" style="align-items: stretch;">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Upcoming Campus Events</p>
        </header>

        <nav class="box navbar" style="display: flex; justify-content: space-between; align-items: center; margin: 0;">
            <div>
                <a href="dashboard.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Dashboard</a>
                <a href="event.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Event</a>
                <a href="assignment.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Assignment</a>
                <a href="contact.php" style="color: #fff; font-weight: bold; text-decoration: none; margin-right: 15px;">Contact</a>
                <a href="profile.php" style="color: #fff; font-weight: bold; text-decoration: none;">Profile</a>
            </div>
            <div><a href="logout.php" class="logout-link">Logout</a></div>
        </nav>

        <?php if (!empty($successMsg)): ?><div class="message success"><?php echo e($successMsg); ?></div><?php endif; ?>
        <?php if (!empty($errorMsg)): ?><div class="message error"><?php echo e($errorMsg); ?></div><?php endif; ?>

        <main class="box" style="margin: 0 auto; width: 100%;">
            <h2>Campus Events & Activities</h2>
            <p style="color: #666; margin-bottom: 20px;">Check out the latest technical, cultural, and sports events organized on campus.</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <?php if ($eventsResult->num_rows > 0): ?>
                    <?php while ($row = $eventsResult->fetch_assoc()): ?>
                        <?php
                        $eventId = $row['id'];
                        $regCheck = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND student_id = ?");
                        $regCheck->bind_param("ii", $eventId, $realStudentId);
                        $regCheck->execute();
                        $isRegistered = ($regCheck->get_result()->num_rows > 0);
                        $regCheck->close();
                        ?>
                        <div class="box" style="border: 1px solid #e0e0e0; background: #faf8fc; display: flex; flex-direction: column; justify-content: space-between; margin: 0;">
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
                                <?php if ($row['status'] === 'Open'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                                        <?php if ($isRegistered): ?>
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn" style="background: #c62828; padding: 6px 12px; font-size: 0.85rem;">Cancel Registration</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="register">
                                            <button type="submit" class="btn" style="padding: 6px 12px; font-size: 0.85rem;">Register Now</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>
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