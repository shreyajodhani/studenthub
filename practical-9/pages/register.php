<?php
mysqli_report(MYSQLI_REPORT_OFF);
error_reporting(E_ALL);
ini_set('display_errors', 0);

$servername = "localhost";
$dbUser     = "root";
$dbPass     = "";
$dbname     = "student_db";

$allowedCourses = ["CSUC102", "MSUD102", "CEUC102", "ITUE101"];
$allowedYears   = ["1st Year", "2nd Year", "3rd Year", "4th Year"];
$allowedGenders = ["Male", "Female", "Other"];

$errors  = [];
$success = "";

$old = [
    'fullName' => '',
    'email'    => '',
    'mobile'   => '',
    'course'   => [],
    'year'     => '',
    'gender'   => '',
    'terms'    => false
];

function field($key) {
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? $_POST[$key] : '';
}

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim(field('fullName'));
    $email    = strtolower(trim(field('email')));
    $mobile   = trim(field('mobile'));
    $pass     = field('password');
    $confirm  = field('confirmPassword');
    $year     = field('year');
    $gender   = field('gender');
    $terms    = isset($_POST['terms']);
    $courses  = (isset($_POST['course']) && is_array($_POST['course'])) ? $_POST['course'] : [];

    $old = [
        'fullName' => $fullName,
        'email'    => $email,
        'mobile'   => $mobile,
        'course'   => $courses,
        'year'     => $year,
        'gender'   => $gender,
        'terms'    => $terms
    ];

    if (!preg_match('/^[A-Za-z\s]{3,30}$/', $fullName)) {
        $errors[] = "Invalid Name: use letters and spaces only (3 to 30 characters).";
    }

    if (!preg_match('/^2\d[a-z]{2,4}\d{3}@charusat\.edu\.in$/i', $email)) {
        $errors[] = "Invalid Email: please use your college email (like 25dce033@charusat.edu.in).";
    }

    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors[] = "Invalid Mobile: enter a 10-digit number starting with 6, 7, 8 or 9.";
    }

    if (!preg_match('/^[a-zA-Z0-9]+$/', $pass)) {
        $errors[] = "Invalid Password: use letters and numbers only.";
    } elseif (strlen($pass) < 6) {
        $errors[] = "Password is too short: use at least 6 characters.";
    }

    if ($pass !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    $validCourses = array_values(array_intersect($allowedCourses, $courses));
    if (count($validCourses) === 0) {
        $errors[] = "Please select at least one course.";
    }

    if (!in_array($year, $allowedYears, true)) {
        $errors[] = "Please select your Year of study.";
    }

    if (!in_array($gender, $allowedGenders, true)) {
        $errors[] = "Please select your Gender.";
    }

    if (!$terms) {
        $errors[] = "You must accept the terms and conditions.";
    }

    if (empty($errors)) {

        $conn = new mysqli($servername, $dbUser, $dbPass, $dbname);

        if ($conn->connect_error) {
            error_log("DB connection failed: " . $conn->connect_error);
            $errors[] = "Could not connect to the database. Please try again later.";
        } else {

            $check = $conn->prepare("SELECT 1 FROM students WHERE email = ? LIMIT 1");

            if ($check === false) {
                error_log("Prepare failed (check): " . $conn->error);
                $errors[] = "Something went wrong. Please try again later.";
            } else {
                $check->bind_param("s", $email);
                $check->execute();
                $check->store_result();
                $alreadyExists = ($check->num_rows > 0);
                $check->close();

                if ($alreadyExists) {
                    $errors[] = "This email is already registered. Please use a different email or log in.";
                } else {

                    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
                    $courseText     = implode(", ", $validCourses);
                    $termsText      = 'Accepted';

                    $stmt = $conn->prepare(
                        "INSERT INTO students (full_name, email, mobile, password, courses, year_of_study, gender, terms_accepted)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    if ($stmt === false) {
                        error_log("Prepare failed (insert): " . $conn->error);
                        $errors[] = "Something went wrong. Please try again later.";
                    } else {
                        $stmt->bind_param("ssssssss", $fullName, $email, $mobile, $hashedPassword, $courseText, $year, $gender, $termsText);

                        if ($stmt->execute()) {
                            $success = "Registration successful! Your account has been created.";
                            $old = [
                                'fullName' => '', 'email' => '', 'mobile' => '',
                                'course' => [], 'year' => '', 'gender' => '', 'terms' => false
                            ];
                        } elseif ($stmt->errno === 1062) {
                            $errors[] = "This email is already registered. Please use a different email or log in.";
                        } else {
                            error_log("Insert failed: " . $stmt->error);
                            $errors[] = "Registration failed. Please try again later.";
                        }
                        $stmt->close();
                    }
                }
            }
            $conn->close();
        }
    }
}
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
                <h2>Student Registration Form</h2>

                <?php if ($success !== ""): ?>
                    <div style="background-color:#ddffdd; color:green; padding:10px; border:1px solid green;">
                        <b><?php echo e($success); ?></b>
                    </div><br>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div style="background-color:#ffdddd; color:red; padding:10px; border:1px solid red; text-align:left;">
                        <b>Please fix the following:</b>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div><br>
                <?php endif; ?>

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

                <form id="regForm" method="POST" action="register.php" onsubmit="return validateRegistration(event)">
                    <table border="1" width="600" align="center" cellpadding="10">
                        <tr>
                            <td align="left">
                                <label for="fullName"><b>Full Name:</b></label><br>
                                <input type="text" id="fullName" name="fullName" value="<?php echo e($old['fullName']); ?>" required><br><br>

                                <label for="email"><b>Email Address:</b></label><br>
                                <input type="email" id="email" name="email" value="<?php echo e($old['email']); ?>" required><br><br>

                                <label for="mobile"><b>Mobile Number:</b></label><br>
                                <input type="tel" id="mobile" name="mobile" value="<?php echo e($old['mobile']); ?>"><br><br>

                                <label for="password"><b>Password:</b></label><br>
                                <input type="password" id="password" name="password" required><br><br>

                                <label for="confirmPassword"><b>Confirm Password:</b></label><br>
                                <input type="password" id="confirmPassword" name="confirmPassword"><br><br>

                                <label for="course"><b>Course:</b> <small>(Hold Ctrl/Cmd to select multiple)</small></label><br>
                                <select id="course" name="course[]" multiple size="4">
                                    <?php foreach ($allowedCourses as $c): ?>
                                        <option value="<?php echo e($c); ?>" <?php echo in_array($c, $old['course'], true) ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                                    <?php endforeach; ?>
                                </select><br><br>

                                <label for="year"><b>Year of Study:</b></label><br>
                                <select id="year" name="year">
                                    <option value="">-- Select Year --</option>
                                    <?php foreach ($allowedYears as $y): ?>
                                        <option value="<?php echo e($y); ?>" <?php echo ($old['year'] === $y) ? 'selected' : ''; ?>><?php echo e($y); ?></option>
                                    <?php endforeach; ?>
                                </select><br><br>

                                <label><b>Gender:</b></label><br>
                                <input type="radio" name="gender" value="Male" id="male" <?php echo ($old['gender'] === 'Male') ? 'checked' : ''; ?>> <label for="male">Male</label>
                                <input type="radio" name="gender" value="Female" id="female" <?php echo ($old['gender'] === 'Female') ? 'checked' : ''; ?>> <label for="female">Female</label>
                                <input type="radio" name="gender" value="Other" id="other" <?php echo ($old['gender'] === 'Other') ? 'checked' : ''; ?>> <label for="other">Other</label><br><br>

                                <input type="checkbox" id="terms" name="terms" <?php echo $old['terms'] ? 'checked' : ''; ?>>
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