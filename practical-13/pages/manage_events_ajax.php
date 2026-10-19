<?php
require_once __DIR__ . '/auth.php';
require_login('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - AJAX Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>AJAX & Fetch-Based Event Management</p>
        </header>

        <nav class="navbar">
            <div><a href="admin.php" style="color: #fff; font-weight: bold; text-decoration: none;">&larr; Back to Dashboard</a></div>
            <div><a href="logout.php" class="logout-link">Logout</a></div>
        </nav>

        <div id="alert-box" style="display: none;" class="message"></div>

        <div class="box form-box" style="max-width: 100%;">
            <h2 id="form-title">Add New Event</h2>
            <form id="event-form">
                <input type="hidden" id="action_type" name="action_type" value="add">
                <input type="hidden" id="record_id" name="record_id" value="">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                    <div class="field"><label>Event Title</label><input type="text" id="title" name="title" required></div>
                    <div class="field"><label>Event Date</label><input type="date" id="event_date" name="event_date" required></div>
                    <div class="field"><label>Category</label>
                        <select id="category" name="category">
                            <option value="Technical">Technical</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Sports">Sports</option>
                        </select>
                    </div>
                    <div class="field"><label>Status</label>
                        <select id="status" name="status">
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                        </select>
                    </div>
                    <div class="field" style="grid-column: span 2;"><label>Description</label><textarea id="description" name="description" rows="3" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px;" required></textarea></div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" id="submit-btn" class="btn" style="flex: 1;">Save Event</button>
                    <button type="button" id="cancel-btn" onclick="resetForm()" class="btn" style="flex: 1; background: #666; display: none;">Cancel Edit</button>
                </div>
            </form>
        </div>

        <main class="box">
            <h3 style="margin-bottom: 15px;">Events List</h3>
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-table-body">
                    </tbody>
                </table>
            </div>
        </main>

        <footer class="footer">&copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.</footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', fetchEvents);

        function showAlert(message, type) {
            const box = document.getElementById('alert-box');
            box.className = 'message ' + type;
            box.textContent = message;
            box.style.display = 'block';
            setTimeout(() => { box.style.display = 'none'; }, 4000);
        }

        function fetchEvents() {
            fetch('api_events.php')
                .then(response => response.json())
                .then(result => {
                    const tbody = document.getElementById('events-table-body');
                    tbody.innerHTML = '';
                    if (result.status === 'success' && result.data.length > 0) {
                        result.data.forEach(event => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${event.id}</td>
                                    <td><strong>${event.title}</strong></td>
                                    <td>${event.category}</td>
                                    <td>${event.event_date}</td>
                                    <td>${event.status}</td>
                                    <td>
                                        <button onclick="editEvent(${event.id}, '${escapeHtml(event.title)}', '${event.event_date}', '${event.category}', '${event.status}', '${escapeHtml(event.description)}')" class="action-btn btn-edit">Edit</button>
                                        <button onclick="deleteEvent(${event.id})" class="action-btn btn-delete">Delete</button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No events found.</td></tr>';
                    }
                });
        }

        document.getElementById('event-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api_events.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    resetForm();
                    fetchEvents();
                } else {
                    showAlert(result.message, 'error');
                }
            });
        });

        function deleteEvent(id) {
            if (confirm('Are you sure you want to delete this event?')) {
                fetch('api_events.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        showAlert(result.message, 'success');
                        fetchEvents();
                    } else {
                        showAlert(result.message, 'error');
                    }
                });
            }
        }

        function editEvent(id, title, date, category, status, description) {
            document.getElementById('action_type').value = 'edit';
            document.getElementById('record_id').value = id;
            document.getElementById('title').value = title;
            document.getElementById('event_date').value = date;
            document.getElementById('category').value = category;
            document.getElementById('status').value = status;
            document.getElementById('description').value = description;
            
            document.getElementById('form-title').textContent = 'Edit Event';
            document.getElementById('submit-btn').textContent = 'Update Event';
            document.getElementById('cancel-btn').style.display = 'inline-block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('event-form').reset();
            document.getElementById('action_type').value = 'add';
            document.getElementById('record_id').value = '';
            document.getElementById('form-title').textContent = 'Add New Event';
            document.getElementById('submit-btn').textContent = 'Save Event';
            document.getElementById('cancel-btn').style.display = 'none';
        }

        function escapeHtml(str) {
            return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        }
    </script>
</body>
</html>