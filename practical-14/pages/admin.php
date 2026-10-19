<?php
require_once __DIR__ . '/auth.php';
require_login('admin');
$conn = db_connect();

$studentCount = $conn->query("SELECT COUNT(*) AS count FROM student")->fetch_assoc()['count'];
$eventCount = $conn->query("SELECT COUNT(*) AS count FROM events")->fetch_assoc()['count'];
$regCount = $conn->query("SELECT COUNT(*) AS count FROM event_registrations")->fetch_assoc()['count'];

$registrationsResult = $conn->query("
    SELECT er.id, s.full_name, s.student_id, e.title AS event_title, er.registered_at 
    FROM event_registrations er
    JOIN student s ON er.student_id = s.id
    JOIN events e ON er.event_id = e.id
    ORDER BY er.registered_at DESC
");

$auditResult = $conn->query("SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT 10");
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
            <p>Admin Control Dashboard & RBAC Monitoring</p>
        </header>

        <nav class="navbar">
            <div style="font-weight: bold; color: #ffffff; font-size: 1.1rem;">Welcome, Admin</div>
            <div><a href="logout.php" class="logout-link">Logout</a></div>
        </nav>

        <main class="box">
            <h2>System Overview & Statistics</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin: 20px 0;">
                <div class="box" style="background: #fdfbfe; border: 2px solid #4b1682; text-align: center; padding: 20px;">
                    <h3 style="font-size: 2rem; color: #4b1682; margin-bottom: 5px;"><?php echo $studentCount; ?></h3>
                    <p style="color: #555; font-weight: bold;">Total Students</p>
                </div>
                <div class="box" style="background: #fdfbfe; border: 2px solid #4b1682; text-align: center; padding: 20px;">
                    <h3 style="font-size: 2rem; color: #4b1682; margin-bottom: 5px;"><?php echo $eventCount; ?></h3>
                    <p style="color: #555; font-weight: bold;">Scheduled Events</p>
                </div>
                <div class="box" style="background: #fdfbfe; border: 2px solid #4b1682; text-align: center; padding: 20px;">
                    <h3 style="font-size: 2rem; color: #4b1682; margin-bottom: 5px;"><?php echo $regCount; ?></h3>
                    <p style="color: #555; font-weight: bold;">Event Registrations</p>
                </div>
            </div>

            <h2 style="margin-top: 30px;">Management Modules</h2>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 15px;">
                <div class="box" style="flex: 1; min-width: 280px; background: #fdfbfe; border: 2px solid #4b1682;">
                    <h3>Manage Students</h3>
                    <p style="margin: 15px 0; color: #555;">Add, view, edit, search, and delete student records.</p>
                    <a href="manage_students.php" class="btn" style="text-decoration: none; display: inline-block;">Manage Student Records</a>
                </div>

                <div class="box" style="flex: 1; min-width: 280px; background: #fdfbfe; border: 2px solid #4b1682;">
                    <h3>Manage Events & Posters</h3>
                    <p style="margin: 15px 0; color: #555;">Create events, update status, and upload posters.</p>
                    <a href="manage_events.php" class="btn" style="text-decoration: none; display: inline-block;">Manage Campus Events</a>
                </div>

                <div class="box" style="flex: 1; min-width: 280px; background: #fdfbfe; border: 2px solid #4b1682;">
                    <h3>Live Event Management</h3>
                    <p style="margin: 15px 0; color: #555;">Manage and update campus events in real-time.</p>
                    <a href="manage_events_ajax.php" class="btn" style="text-decoration: none; display: inline-block;">Open Live Event Portal</a>
                </div>
            </div>

            <h2 style="margin-top: 35px; margin-bottom: 15px;">Student Event Registrations</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Registration ID</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Event Title</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($registrationsResult->num_rows > 0): ?>
                            <?php while ($reg = $registrationsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo e($reg['id']); ?></td>
                                    <td><strong><?php echo e($reg['full_name']); ?></strong></td>
                                    <td><?php echo e($reg['student_id']); ?></td>
                                    <td><?php echo e($reg['event_title']); ?></td>
                                    <td><?php echo e($reg['registered_at']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">No student event registrations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h2 style="margin-top: 35px; margin-bottom: 15px;">Audit Logs & System Traceability</h2>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Log ID</th>
                            <th>Admin User</th>
                            <th>Action Performed</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($auditResult->num_rows > 0): ?>
                            <?php while ($log = $auditResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo e($log['id']); ?></td>
                                    <td><strong><?php echo e($log['admin_user']); ?></strong></td>
                                    <td><?php echo e($log['action_performed']); ?></td>
                                    <td><?php echo e($log['ip_address']); ?></td>
                                    <td><?php echo e($log['timestamp']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; padding: 20px;">No audit logs recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="footer">&copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.</footer>
    </div>
</body>
</html>