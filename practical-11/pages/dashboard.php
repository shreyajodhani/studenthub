<?php
require_once __DIR__ . '/auth.php';
require_login('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Academic Dashboard</p>
        </header>

        <nav class="navbar">
            <div style="display: flex; gap: 8px;">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="event.php">Event</a>
                <a href="assignment.php">Assignment</a>
                <a href="contact.php">Contact</a>
                <a href="profile.php">Profile</a>
            </div>
            <div>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </nav>

        <div class="notice-banner">
            <strong>Welcome to StudentHub!</strong> Check out your dashboard for the latest notices and academic updates.
        </div>

        <main style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <div class="box hover-card" onclick="openModal('calendarModal')">
                <h3>Academic Calendar</h3>
                <p>View semester events, exams, and holidays.</p>
                <span class="hint">Click to view details &rarr;</span>
            </div>

            <div class="box hover-card" onclick="openModal('attendanceModal')">
                <h3>Attendance</h3>
                <p style="font-size: 1.8rem; font-weight: bold; color: #2e7d32; margin: 8px 0;">84.5%</p>
                <p>Overall aggregate attendance across subjects.</p>
                <span class="hint">Click for subject breakdown &rarr;</span>
            </div>

            <div class="box hover-card" onclick="openModal('timetableModal')">
                <h3>Daily Timetable</h3>
                <p><strong>Today:</strong> <?php echo date('d M Y'); ?></p>
                <p>Engineering lectures schedule.</p>
                <span class="hint">Click to view schedule &rarr;</span>
            </div>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>
    </div>

    <div id="calendarModal" class="modal-overlay">
        <div class="box" style="width: 90%; max-width: 550px; background: #ffffff;">
            <span onclick="closeModal('calendarModal')" style="float: right; cursor: pointer; font-weight: bold; font-size: 1.2rem;">&times;</span>
            <h2>Academic Calendar</h2>
            <table class="modal-table">
                <tr>
                    <th>Event / Milestone</th>
                    <th>Date / Duration</th>
                </tr>
                <tr>
                    <td>Diwali Break</td>
                    <td>10 Nov - 15 Nov 2026</td>
                </tr>
                <tr>
                    <td>Cognizance Tech Fest</td>
                    <td>05 Dec - 07 Dec 2026</td>
                </tr>
                <tr>
                    <td>Mid-Sem Examinations</td>
                    <td>15 Nov - 22 Nov 2026</td>
                </tr>
                <tr>
                    <td>End-Sem Submissions</td>
                    <td>20 Dec 2026</td>
                </tr>
            </table>
        </div>
    </div>

    <div id="attendanceModal" class="modal-overlay">
        <div class="box" style="width: 90%; max-width: 550px; background: #ffffff;">
            <span onclick="closeModal('attendanceModal')" style="float: right; cursor: pointer; font-weight: bold; font-size: 1.2rem;">&times;</span>
            <h2>Attendance Breakdown</h2>
            <p style="margin-top: 8px;"><strong>Overall Attendance:</strong> <span style="color: #2e7d32; font-weight: bold;">84.5%</span></p>
            <table class="modal-table">
                <tr>
                    <th>Subject</th>
                    <th>Attended / Total</th>
                    <th>Percentage</th>
                </tr>
                <tr>
                    <td>Data Structures</td>
                    <td>44 / 50</td>
                    <td>88%</td>
                </tr>
                <tr>
                    <td>Object Oriented Programming</td>
                    <td>41 / 50</td>
                    <td>82%</td>
                </tr>
                <tr>
                    <td>Database Management Systems</td>
                    <td>34 / 40</td>
                    <td>85%</td>
                </tr>
                <tr>
                    <td>Web Development</td>
                    <td>25 / 30</td>
                    <td>83%</td>
                </tr>
            </table>
        </div>
    </div>

    <div id="timetableModal" class="modal-overlay">
        <div class="box" style="width: 90%; max-width: 550px; background: #ffffff;">
            <span onclick="closeModal('timetableModal')" style="float: right; cursor: pointer; font-weight: bold; font-size: 1.2rem;">&times;</span>
            <h2>Today's Timetable (<?php echo date('d M Y'); ?>)</h2>
            <table class="modal-table">
                <tr>
                    <th>Time Slot</th>
                    <th>Subject Name</th>
                    <th>Type</th>
                </tr>
                <tr>
                    <td>09:00 AM - 10:00 AM</td>
                    <td>Data Structures</td>
                    <td>Laboratory</td>
                </tr>
                <tr>
                    <td>10:00 AM - 11:00 AM</td>
                    <td>Object Oriented Programming</td>
                    <td>Lecture</td>
                </tr>
                <tr>
                    <td>11:15 AM - 12:15 PM</td>
                    <td>Database Management Systems</td>
                    <td>Lecture</td>
                </tr>
                <tr>
                    <td>01:15 PM - 03:00 PM</td>
                    <td>Web Development</td>
                    <td>Practical</td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'flex'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>