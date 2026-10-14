<?php
require_once __DIR__ . '/auth.php';
require_login('student');

$conn = db_connect();
$userId = $_SESSION['user_id'];
$loginUser = $_SESSION['user'];

$errors = [];

$formData = [
    'full_name' => '',
    'student_id' => $loginUser,
    'email' => '',
    'mobile' => '',
    'department' => 'Computer Engineering',
    'year_of_study' => '1st Year',
    'parents_name' => '',
    'address' => '',
    'blood_group' => 'A+'
];

if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM student WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            foreach ($formData as $key => $val) {
                if ($key !== 'student_id') {
                    $formData[$key] = $row[$key] ?? '';
                }
            }
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['full_name'] = trim(field('full_name'));
    $formData['email'] = trim(field('email'));
    $formData['mobile'] = trim(field('mobile'));
    $formData['department'] = trim(field('department'));
    $formData['year_of_study'] = trim(field('year_of_study'));
    $formData['parents_name'] = trim(field('parents_name'));
    $formData['address'] = trim(field('address'));
    $formData['blood_group'] = trim(field('blood_group'));

    if ($formData['full_name'] === '') {
        $errors[] = "Full name is required.";
    }
    if (!preg_match('/^[A-Za-z0-9._%+-]+@charusat\.edu\.in$/', $formData['email'])) {
        $errors[] = "Email must be a valid address ending with @charusat.edu.in.";
    }
    if (!preg_match('/^[6-9]\d{9}$/', $formData['mobile'])) {
        $errors[] = "Mobile number must be 10 digits starting with 6, 7, 8, or 9.";
    }
    if ($formData['parents_name'] === '') {
        $errors[] = "Parent's name is required.";
    }
    if ($formData['address'] === '') {
        $errors[] = "Address is required.";
    }

    $picturePath = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($fileExt, $allowedExt)) {
            $errors[] = "Only JPG, JPEG, PNG, and WEBP image formats are allowed.";
        } else {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $picturePath = '../uploads/' . $newFileName;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }
    }

    if (empty($errors)) {
        $conn = db_connect();
        if ($conn) {
            $check = $conn->prepare("SELECT id, profile_picture FROM student WHERE user_id = ? LIMIT 1");
            $check->bind_param("i", $userId);
            $check->execute();
            $res = $check->get_result();
            $existingRecord = $res->fetch_assoc();
            $check->close();

            if ($picturePath === null && $existingRecord) {
                $picturePath = $existingRecord['profile_picture'];
            }

            if ($existingRecord) {
                $stmt = $conn->prepare("UPDATE student SET full_name = ?, email = ?, mobile = ?, department = ?, year_of_study = ?, parents_name = ?, address = ?, blood_group = ?, profile_picture = ? WHERE user_id = ?");
                $stmt->bind_param("sssssssssi", $formData['full_name'], $formData['email'], $formData['mobile'], $formData['department'], $formData['year_of_study'], $formData['parents_name'], $formData['address'], $formData['blood_group'], $picturePath, $userId);
            } else {
                $stmt = $conn->prepare("INSERT INTO student (user_id, full_name, student_id, email, mobile, department, year_of_study, parents_name, address, blood_group, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssssss", $userId, $formData['full_name'], $loginUser, $formData['email'], $formData['mobile'], $formData['department'], $formData['year_of_study'], $formData['parents_name'], $formData['address'], $formData['blood_group'], $picturePath);
            }

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: profile.php");
                exit;
            } else {
                $errors[] = "Database error: " . $stmt->error;
                $stmt->close();
            }
            $conn->close();
        } else {
            $errors[] = "Database connection failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Details</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box" style="text-align: center;">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Student Details Form</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php">Dashboard</a>
                <a href="event.php">Event</a>
                <a href="assignment.php">Assignment</a>
                <a href="contact.php">Contact</a>
                <a href="profile.php">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <main class="box form-box" style="max-width: 600px;">
            <h2>Enter/Edit Your Details</h2>

            <?php if (!empty($errors)): ?>
                <div class="message error" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="details.php" enctype="multipart/form-data">
                <div class="field">
                    <label for="profile_picture">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>

                <div class="field">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo e($formData['full_name']); ?>" required>
                </div>

                <div class="field">
                    <label for="student_id">Student ID (Locked)</label>
                    <input type="text" id="student_id" value="<?php echo e($loginUser); ?>" disabled style="background: #f0f0f0; cursor: not-allowed;">
                </div>

                <div class="field">
                    <label for="email">Official Email ID</label>
                    <input type="text" id="email" name="email" value="<?php echo e($formData['email']); ?>" placeholder="username@charusat.edu.in" required>
                </div>

                <div class="field">
                    <label for="mobile">Mobile Number</label>
                    <input type="text" id="mobile" name="mobile" maxlength="10" value="<?php echo e($formData['mobile']); ?>" placeholder="9876543210" required>
                </div>

                <div class="field">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="Computer Engineering" <?php echo ($formData['department'] === 'Computer Engineering') ? 'selected' : ''; ?>>Computer Engineering</option>
                        <option value="Computer Science Engineering" <?php echo ($formData['department'] === 'Computer Science Engineering') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                        <option value="Civil Engineering" <?php echo ($formData['department'] === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Mechanical Engineering" <?php echo ($formData['department'] === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Artificial Intelligence and Machine Learning" <?php echo ($formData['department'] === 'Artificial Intelligence and Machine Learning') ? 'selected' : ''; ?>>Artificial Intelligence and Machine Learning</option>
                    </select>
                </div>

                <div class="field">
                    <label for="year_of_study">Year of Study</label>
                    <select id="year_of_study" name="year_of_study">
                        <option value="1st Year" <?php echo ($formData['year_of_study'] === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo ($formData['year_of_study'] === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo ($formData['year_of_study'] === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo ($formData['year_of_study'] === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                </div>

                <div class="field">
                    <label for="parents_name">Parent's Name</label>
                    <input type="text" id="parents_name" name="parents_name" value="<?php echo e($formData['parents_name']); ?>" required>
                </div>

                <div class="field">
                    <label for="address">Residential Address</label>
                    <input type="text" id="address" name="address" value="<?php echo e($formData['address']); ?>" required>
                </div>

                <div class="field">
                    <label for="blood_group">Blood Group</label>
                    <select id="blood_group" name="blood_group">
                        <option value="A+" <?php echo ($formData['blood_group'] === 'A+') ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo ($formData['blood_group'] === 'A-') ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo ($formData['blood_group'] === 'B+') ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo ($formData['blood_group'] === 'B-') ? 'selected' : ''; ?>>B-</option>
                        <option value="O+" <?php echo ($formData['blood_group'] === 'O+') ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo ($formData['blood_group'] === 'O-') ? 'selected' : ''; ?>>O-</option>
                        <option value="AB+" <?php echo ($formData['blood_group'] === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo ($formData['blood_group'] === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                    </select>
                </div>

                <button type="submit" class="btn">Save Details</button>
            </form>
        </main>
    </div>
</body>
</html>