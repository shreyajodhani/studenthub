<?php
// Database Configuration
$servername = "localhost";
$username   = "root";     // Default XAMPP username
$password   = "";         // Default XAMPP password (blank)
$dbname     = "student_db";

// 1. Create connection to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form inputs
    $fullName = $_POST['fullName'] ?? '';
    $email    = $_POST['email'] ?? '';
    $mobile   = $_POST['mobile'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Convert multiple selected courses into a comma-separated string
    $courses  = isset($_POST['course']) ? implode(", ", $_POST['course']) : 'None';
    
    $year     = $_POST['year'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $terms    = isset($_POST['terms']) ? 'Accepted' : 'Not Accepted';

    // 3. Insert data into table using Prepared Statement (secure from SQL injection)
    $stmt = $conn->prepare("INSERT INTO students (full_name, email, mobile, password, courses, year_of_study, gender, terms_accepted) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssss", $fullName, $email, $mobile, $password, $courses, $year, $gender, $terms);

    if ($stmt->execute()) {
        echo "<script>alert('Student registered successfully in MySQL Database!');</script>";
    } else {
        echo "<script>alert('Error saving record: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}
                                                                                                                            `1`
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="../css/script.css">
    <script src="../js/script.js"></script>
</head>
<body>
    <table border="1" width="95%" align="center" cellpadding="15">
        <tr>
            <td align="center">
                <table border="1" width="400" align="center" cellpadding="10">
                    <tr>
                        <td align="center">
                            <h2>Student Registration Form</h2>
                        </td>
                    </tr>
                </table>
                <br>
                
                <table border="1" width="100%" cellpadding="8">
                    <tr>
                        <td align="center">
                            <nav>
                                | <a href="../index.html">HOME</a> |
                                <a href="dashboard.html">DASHBOARD</a> |
                                <a href="courses.html">COURSE</a> |
                                <a href="attendence.html">ATTENDENCE</a> |
                                <a href="assignment.html">ASSIGNMENT</a> |
                                <a href="result.html">RESULT</a> |
                                <a href="login.html">LOGIN</a> |
                            </nav>
                        </td>
                    </tr>
                </table>
                <br>

                <form id="regForm" method="POST" action="" onsubmit="return validateRegistration(event)">
                    <table border="1" width="600" align="center" cellpadding="10">
                        <tr>
                            <td align="left">
                                <label for="fullName"><b>Full Name:</b></label><br>
                                <input type="text" id="fullName" name="fullName" required><br><br>

                                <label for="email"><b>Email Address:</b></label><br>
                                <input type="email" id="email" name="email" required><br><br>

                                <label for="mobile"><b>Mobile Number:</b></label><br>
                                <input type="tel" id="mobile" name="mobile"><br><br>

                                <label for="password"><b>Password:</b></label><br>
                                <input type="password" id="password" name="password" required><br><br>

                                <label for="confirmPassword"><b>Confirm Password:</b></label><br>
                                <input type="password" id="confirmPassword" name="confirmPassword"><br><br>

                                <label for="course"><b>Course:</b> <small>(Hold Ctrl/Cmd to select multiple)</small></label><br>
                                <select id="course" name="course[]" multiple size="4">
                                    <option value="CSUC102">CSUC102</option>
                                    <option value="MSUD102">MSUD102</option>
                                    <option value="CEUC102">CEUC102</option>
                                    <option value="ITUE101">ITUE101</option>
                                </select><br><br>

                                <label for="year"><b>Year of Study:</b></label><br>
                                <select id="year" name="year">
                                    <option value="">-- Select Year --</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select><br><br>

                                <label><b>Gender:</b></label><br>
                                <input type="radio" name="gender" value="Male" id="male"> <label for="male">Male</label>
                                <input type="radio" name="gender" value="Female" id="female"> <label for="female">Female</label>
                                <input type="radio" name="gender" value="Other" id="other"> <label for="other">Other</label><br><br>

                                <input type="checkbox" id="terms" name="terms">
                                <label for="terms">I accept the terms and conditions</label><br><br>

                                <button type="submit">Register</button>
                                <button type="reset">Reset</button>
                            </td>
                        </tr>
                    </table>
                </form>

                <br><br>
            </td>
        </tr>
    </table>
</body>
</html>