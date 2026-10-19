<?php
require_once __DIR__ . '/auth.php';
require_login('admin');
$conn = db_connect();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $events]);
    exit;
}

if ($method === 'POST') {
    $action = $_POST['action_type'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $eventDate = trim($_POST['event_date'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = trim($_POST['status'] ?? 'Open');

        if (empty($title) || empty($eventDate)) {
            echo json_encode(['status' => 'error', 'message' => 'Title and Date are required.']);
            exit;
        }

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, category, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $description, $eventDate, $category, $status);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Event added successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error.']);
            }
            $stmt->close();
        } elseif ($action === 'edit') {
            $recId = intval($_POST['record_id'] ?? 0);
            $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, category = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $title, $description, $eventDate, $category, $status, $recId);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Event updated successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error.']);
            }
            $stmt->close();
        }
    }
    exit;
}

if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $delId = intval($_DELETE['id'] ?? 0);

    if ($delId > 0) {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $delId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete event.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
    }
    exit;
}