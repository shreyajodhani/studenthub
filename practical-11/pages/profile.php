<?php
require_once __DIR__ . '/auth.php';
require_login('student');

$conn = db_connect();
$userId = $_SESSION['user_id'];
$profile = null;

if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM student WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $profile = $res->fetch_assoc();
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Profile</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box" style="text-align: center;">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Student Profile Portal</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php">Dashboard</a>
                <a href="event.php">Event</a>
                <a href="assignment.php">Assignment</a>
                <a href="contact.php">Contact</a>
                <a href="profile.php" class="active">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box">
            <?php if (!$profile): ?>
                <div class="message info" style="text-align: center; padding: 40px;">
                    <h2>Please fill your details</h2>
                    <p>You have not registered your profile information yet. Please click below to complete your record.</p>
                    <a href="details.php" class="btn" style="display: inline-block; margin-top: 15px; width: auto; padding: 12px 30px; text-decoration: none;">Add Details</a>
                </div>
            <?php else: ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2>Personal Profile</h2>
                    <a href="details.php" class="btn" style="width: auto; padding: 10px 20px; text-decoration: none;">Edit Details</a>
                </div>

                <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                    <div class="box" style="padding: 10px; background: #ffffff;">
                        <?php if (!empty($profile['profile_picture']) && file_exists($profile['profile_picture'])): ?>
                            <img src="<?php echo e($profile['profile_picture']); ?>" alt="Profile Picture" style="width: 150px; height: 180px; object-fit: cover; display: block; border-radius: 4px;">
                        <?php else: ?>
                            <div style="width: 150px; height: 180px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; font-weight: bold; border-radius: 4px;">No Image</div>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; flex: 1; min-width: 300px;">
                        <div>
                            <strong class="hint">Full Name</strong>
                            <p style="font-size: 1.1rem; font-weight: bold; color: #2c1654;"><?php echo e($profile['full_name']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Student ID</strong>
                            <p style="font-size: 1.1rem; font-weight: bold; color: #2c1654;"><?php echo e($profile['student_id']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Official Email</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['email']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Mobile Number</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['mobile']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Department</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['department']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Year of Study</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['year_of_study']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Parent's Name</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['parents_name']); ?></p>
                        </div>
                        <div>
                            <strong class="hint">Blood Group</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['blood_group']); ?></p>
                        </div>
                        <div style="grid-column: span 2;">
                            <strong class="hint">Residential Address</strong>
                            <p style="font-size: 1.05rem;"><?php echo e($profile['address']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>