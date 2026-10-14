<?php
require_once __DIR__ . '/auth.php';
require_login('admin');

$conn = db_connect();
$successMsg = '';
$errorMsg = '';

if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
    $stmt->bind_param("i", $delId);
    if ($stmt->execute()) {
        $successMsg = "Student record deleted successfully.";
    } else {
        $errorMsg = "Failed to delete student record.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $fullName = trim($_POST['full_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $yearOfStudy = trim($_POST['year_of_study'] ?? '');
    $parentsName = trim($_POST['parents_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');

    if ($_POST['action_type'] === 'add') {
        
        $stmt = $conn->prepare("INSERT INTO student (user_id, full_name, student_id, email, mobile, department, year_of_study, parents_name, address, blood_group) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $fullName, $studentId, $email, $mobile, $department, $yearOfStudy, $parentsName, $address, $bloodGroup);
        if ($stmt->execute()) {
            $successMsg = "New student record added successfully.";
        } else {
            $errorMsg = "Error adding record: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($_POST['action_type'] === 'edit' && isset($_POST['record_id'])) {
        $recId = intval($_POST['record_id']);
        $stmt = $conn->prepare("UPDATE student SET full_name = ?, student_id = ?, email = ?, mobile = ?, department = ?, year_of_study = ?, parents_name = ?, address = ?, blood_group = ? WHERE id = ?");
        $stmt->bind_param("sssssssssi", $fullName, $studentId, $email, $mobile, $department, $yearOfStudy, $parentsName, $address, $bloodGroup, $recId);
        if ($stmt->execute()) {
            $successMsg = "Student record updated successfully.";
        } else {
            $errorMsg = "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch Search and Filter parameters
$searchQuery = trim($_GET['search'] ?? '');
$filterDept = trim($_GET['department'] ?? '');

// Build SQL Query with Search and Filter
$sql = "SELECT * FROM student WHERE 1=1";
$params = [];
$types = "";

if ($searchQuery !== '') {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $likeSearch = "%" . $searchQuery . "%";
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $types .= "sss";
}

if ($filterDept !== '') {
    $sql .= " AND department = ?";
    $params[] = $filterDept;
    $types .= "s";
}

$sql .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$editRecord = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editStmt = $conn->prepare("SELECT * FROM student WHERE id = ? LIMIT 1");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $editRes = $editStmt->get_result();
    if ($editRes->num_rows > 0) {
        $editRecord = $editRes->fetch_assoc();
    }
    $editStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Admin Student Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Admin Student Management Dashboard (CRUD)</p>
        </header>

        <nav class="navbar">
            <div style="font-weight: bold; color: #ffffff; font-size: 1.1rem;">
                Admin Portal Panel
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <?php if (!empty($successMsg)): ?>
            <div class="message success"><?php echo e($successMsg); ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <div class="message error"><?php echo e($errorMsg); ?></div>
        <?php endif; ?>

        <div class="box">
            <div class="crud-header">
                <h2>Student Records Management</h2>
                <a href="admin.php?show_form=1" class="btn" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">+ Add New Student</a>
            </div>

            <form method="GET" action="admin.php" class="search-filter-form">
                <input type="text" name="search" placeholder="Search by name, email, ID..." value="<?php echo e($searchQuery); ?>" style="flex: 1; min-width: 220px;">
                
                <select name="department">
                    <option value="">All Departments</option>
                    <option value="Computer Engineering" <?php echo ($filterDept === 'Computer Engineering') ? 'selected' : ''; ?>>Computer Engineering</option>
                    <option value="Computer Science Engineering" <?php echo ($filterDept === 'Computer Science Engineering') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                    <option value="Civil Engineering" <?php echo ($filterDept === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                    <option value="Mechanical Engineering" <?php echo ($filterDept === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                    <option value="Artificial Intelligence and Machine Learning" <?php echo ($filterDept === 'Artificial Intelligence and Machine Learning') ? 'selected' : ''; ?>>AI & ML</option>
                </select>

                <button type="submit" class="btn" style="width: auto; padding: 10px 20px;">Search</button>
                <?php if ($searchQuery !== '' || $filterDept !== ''): ?>
                    <a href="admin.php" style="padding: 10px; font-weight: bold; color: #721c24; text-decoration: none;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (isset($_GET['show_form']) || $editRecord !== null): ?>
            <div class="box form-box" style="max-width: 100%; margin-top: 10px;">
                <h2><?php echo $editRecord ? 'Edit Student Record' : 'Add New Student Record'; ?></h2>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="action_type" value="<?php echo $editRecord ? 'edit' : 'add'; ?>">
                    <?php if ($editRecord): ?>
                        <input type="hidden" name="record_id" value="<?php echo $editRecord['id']; ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo e($editRecord['full_name'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Student ID</label>
                            <input type="text" name="student_id" value="<?php echo e($editRecord['student_id'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Official Email</label>
                            <input type="text" name="email" value="<?php echo e($editRecord['email'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Mobile Number</label>
                            <input type="text" name="mobile" value="<?php echo e($editRecord['mobile'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department">
                                <option value="Computer Engineering" <?php echo (($editRecord['department'] ?? '') === 'Computer Engineering') ? 'selected' : ''; ?>>Computer Engineering</option>
                                <option value="Computer Science Engineering" <?php echo (($editRecord['department'] ?? '') === 'Computer Science Engineering') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                                <option value="Civil Engineering" <?php echo (($editRecord['department'] ?? '') === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                <option value="Mechanical Engineering" <?php echo (($editRecord['department'] ?? '') === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                <option value="Artificial Intelligence and Machine Learning" <?php echo (($editRecord['department'] ?? '') === 'Artificial Intelligence and Machine Learning') ? 'selected' : ''; ?>>AI & ML</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Year of Study</label>
                            <select name="year_of_study">
                                <option value="1st Year" <?php echo (($editRecord['year_of_study'] ?? '') === '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                <option value="2nd Year" <?php echo (($editRecord['year_of_study'] ?? '') === '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                <option value="3rd Year" <?php echo (($editRecord['year_of_study'] ?? '') === '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                <option value="4th Year" <?php echo (($editRecord['year_of_study'] ?? '') === '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Parent's Name</label>
                            <input type="text" name="parents_name" value="<?php echo e($editRecord['parents_name'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Residential Address</label>
                            <input type="text" name="address" value="<?php echo e($editRecord['address'] ?? ''); ?>" required>
                        </div>
                        <div class="field">
                            <label>Blood Group</label>
                            <select name="blood_group">
                                <?php $bg = $editRecord['blood_group'] ?? 'A+'; ?>
                                <option value="A+" <?php echo ($bg === 'A+') ? 'selected' : ''; ?>>A+</option>
                                <option value="A-" <?php echo ($bg === 'A-') ? 'selected' : ''; ?>>A-</option>
                                <option value="B+" <?php echo ($bg === 'B+') ? 'selected' : ''; ?>>B+</option>
                                <option value="B-" <?php echo ($bg === 'B-') ? 'selected' : ''; ?>>B-</option>
                                <option value="O+" <?php echo ($bg === 'O+') ? 'selected' : ''; ?>>O+</option>
                                <option value="O-" <?php echo ($bg === 'O-') ? 'selected' : ''; ?>>O-</option>
                                <option value="AB+" <?php echo ($bg === 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                <option value="AB-" <?php echo ($bg === 'AB-') ? 'selected' : ''; ?>>AB-</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button type="submit" class="btn" style="flex: 1;"><?php echo $editRecord ? 'Update Record' : 'Save Record'; ?></button>
                        <a href="admin.php" class="btn" style="flex: 1; background: #666666; text-align: center; text-decoration: none; display: inline-block;">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        
        <main class="box">
            <h3 style="margin-bottom: 15px;">Records List (Total: <?php echo $result->num_rows; ?>)</h3>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo e($row['id']); ?></td>
                                    <td><strong><?php echo e($row['student_id']); ?></strong></td>
                                    <td><?php echo e($row['full_name']); ?></td>
                                    <td><?php echo e($row['email']); ?></td>
                                    <td><?php echo e($row['mobile']); ?></td>
                                    <td><?php echo e($row['department']); ?></td>
                                    <td>
                                        <a href="admin.php?edit_id=<?php echo $row['id']; ?>" class="action-btn btn-edit">Edit</a>
                                        <a href="admin.php?delete_id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this student record?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #666; padding: 20px;">No student records found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>
    </div>
</body>
</html>