<?php
require_once __DIR__ . '/auth.php';
require_login('admin');

$conn = db_connect();
$successMsg = '';
$errorMsg = '';

// Create upload directory if not exists
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    // Fetch image to delete file from disk
    $imgStmt = $conn->prepare("SELECT poster_image FROM events WHERE id = ?");
    $imgStmt->bind_param("i", $delId);
    $imgStmt->execute();
    $imgRes = $imgStmt->get_result();
    if ($imgRow = $imgRes->fetch_assoc()) {
        if (!empty($imgRow['poster_image']) && file_exists(__DIR__ . '/../' . $imgRow['poster_image'])) {
            unlink(__DIR__ . '/../' . $imgRow['poster_image']);
        }
    }
    $imgStmt->close();

    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $delId);
    if ($stmt->execute()) {
        $successMsg = "Event deleted successfully.";
    } else {
        $errorMsg = "Failed to delete event.";
    }
    $stmt->close();
}

// Handle Add / Edit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = trim($_POST['status'] ?? 'Open');
    $posterPath = null;

    // Handle File Upload Validation
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['poster'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes, true)) {
                $errorMsg = "Invalid file type. Only JPEG, PNG, and WEBP images are allowed.";
            } elseif ($file['size'] > $maxSize) {
                $errorMsg = "File size exceeds the maximum limit of 2MB.";
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $uniqueName = 'poster_' . time . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $uploadDir . $uniqueName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $posterPath = 'uploads/' . $uniqueName;
                } else {
                    $errorMsg = "Failed to move uploaded file.";
                }
            }
        } else {
            $errorMsg = "Error uploading file.";
        }
    }

    if (empty($errorMsg)) {
        if ($_POST['action_type'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, category, status, poster_image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $title, $description, $eventDate, $category, $status, $posterPath);
            if ($stmt->execute()) {
                $successMsg = "Event added successfully.";
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($_POST['action_type'] === 'edit' && isset($_POST['record_id'])) {
            $recId = intval($_POST['record_id']);
            if ($posterPath !== null) {
                // Update with new poster
                $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, category = ?, status = ?, poster_image = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $title, $description, $eventDate, $category, $status, $posterPath, $recId);
            } else {
                // Keep existing poster
                $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, category = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $title, $description, $eventDate, $category, $status, $recId);
            }
            if ($stmt->execute()) {
                $successMsg = "Event updated successfully.";
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$editRecord = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editStmt = $conn->prepare("SELECT * FROM events WHERE id = ? LIMIT 1");
    $editStmt->bind_param("i", $editId);
    $editStmt->execute();
    $editRes = $editStmt->get_result();
    if ($editRes->num_rows > 0) {
        $editRecord = $editRes->fetch_assoc();
    }
    $editStmt->close();
}

$eventsResult = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Manage Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Event Management & Poster Upload Module</p>
        </header>

        <nav class="navbar">
            <div><a href="admin.php" style="color: #fff; font-weight: bold; text-decoration: none;">&larr; Back to Dashboard</a></div>
            <div><a href="logout.php" class="logout-link">Logout</a></div>
        </nav>

        <?php if (!empty($successMsg)): ?><div class="message success"><?php echo e($successMsg); ?></div><?php endif; ?>
        <?php if (!empty($errorMsg)): ?><div class="message error"><?php echo e($errorMsg); ?></div><?php endif; ?>

        <div class="box">
            <div class="crud-header">
                <h2>Event Records</h2>
                <a href="manage_events.php?show_form=1" class="btn" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">+ Add New Event</a>
            </div>
        </div>

        <?php if (isset($_GET['show_form']) || $editRecord !== null): ?>
            <div class="box form-box" style="max-width: 100%; margin-top: 10px;">
                <h2><?php echo $editRecord ? 'Edit Event' : 'Add New Event'; ?></h2>
                <form method="POST" action="manage_events.php" enctype="multipart/form-data">
                    <input type="hidden" name="action_type" value="<?php echo $editRecord ? 'edit' : 'add'; ?>">
                    <?php if ($editRecord): ?>
                        <input type="hidden" name="record_id" value="<?php echo $editRecord['id']; ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                        <div class="field"><label>Event Title</label><input type="text" name="title" value="<?php echo e($editRecord['title'] ?? ''); ?>" required></div>
                        <div class="field"><label>Event Date</label><input type="date" name="event_date" value="<?php echo e($editRecord['event_date'] ?? ''); ?>" required></div>
                        <div class="field"><label>Category</label>
                            <select name="category">
                                <option value="Technical" <?php echo (($editRecord['category'] ?? '') === 'Technical') ? 'selected' : ''; ?>>Technical</option>
                                <option value="Cultural" <?php echo (($editRecord['category'] ?? '') === 'Cultural') ? 'selected' : ''; ?>>Cultural</option>
                                <option value="Sports" <?php echo (($editRecord['category'] ?? '') === 'Sports') ? 'selected' : ''; ?>>Sports</option>
                            </select>
                        </div>
                        <div class="field"><label>Status</label>
                            <select name="status">
                                <option value="Open" <?php echo (($editRecord['status'] ?? '') === 'Open') ? 'selected' : ''; ?>>Open</option>
                                <option value="Closed" <?php echo (($editRecord['status'] ?? '') === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        <div class="field" style="grid-column: span 2;"><label>Description</label><textarea name="description" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required><?php echo e($editRecord['description'] ?? ''); ?></textarea></div>
                        <div class="field" style="grid-column: span 2;"><label>Event Poster (JPEG/PNG/WEBP, Max 2MB)</label><input type="file" name="poster" accept="image/jpeg,image/png,image/webp"></div>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button type="submit" class="btn" style="flex: 1;"><?php echo $editRecord ? 'Update Event' : 'Save Event'; ?></button>
                        <a href="manage_events.php" class="btn" style="flex: 1; background: #666; text-align: center; text-decoration: none; display: inline-block;">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <main class="box">
            <h3 style="margin-bottom: 15px;">Events List</h3>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($eventsResult->num_rows > 0): ?>
                            <?php while ($row = $eventsResult->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['poster_image'])): ?>
                                            <img src="../<?php echo e($row['poster_image']); ?>" alt="Poster" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <span>No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo e($row['title']); ?></strong></td>
                                    <td><?php echo e($row['category']); ?></td>
                                    <td><?php echo e($row['event_date']); ?></td>
                                    <td><?php echo e($row['status']); ?></td>
                                    <td>
                                        <a href="manage_events.php?edit_id=<?php echo $row['id']; ?>" class="action-btn btn-edit">Edit</a>
                                        <a href="manage_events.php?delete_id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center; padding: 20px;">No events found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="footer">&copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.</footer>
    </div>
</body>
</html>