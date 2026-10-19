<?php
require_once __DIR__ . '/pages/auth.php';
start_secure_session();

function id_label($role) {
    return ($role === 'admin') ? 'Admin Name' : 'Student ID';
}

if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];$old    = ['user' => '', 'role' => 'student'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$user      = trim(field('user'));
    $pass      = field('password');$role      = field('role');

    $old['user'] =$user;
    $old['role'] = in_array($role, ['student', 'admin'], true) ?$role : 'student';

    if (!hash_equals($_SESSION['csrf_token'], field('csrf_token'))) {$errors[] = "Your form has expired. Please try again.";
    } else {
        if (!in_array($role, ['student', 'admin'], true)) {$errors[] = "Please choose a role.";
        } else {
            if ($role === 'student' && !preg_match('/^2\d[A-Za-z]{2,4}\d{3}$/', $user)) {$errors[] = "Invalid Student ID: it should look like 25DCE033.";
            }
            if ($role === 'admin' && !preg_match('/^[A-Za-z0-9]{4,20}$/', $user)) {$errors[] = "Invalid Admin Name: use 4 to 20 letters or numbers.";
            }
        }

        if (strlen($pass) < 8 || strlen($pass) > 64) {$errors[] = "Invalid Password: use 8 to 64 characters.";
        }
        if (!preg_match('/[A-Za-z]/', $pass)) {$errors[] = "Invalid Password: must include at least one letter.";
        }
        if (!preg_match('/\d/', $pass)) {$errors[] = "Invalid Password: must include at least one number.";
        }

        if (empty($errors)) {
            if ($role === 'student') {
                $user = strtoupper($user);
            }

            $conn = db_connect();

            if (!$conn) {$errors[] = "Could not connect to the database. Please try again later.";
            } else {
                $check =$conn->prepare("SELECT 1 FROM users WHERE `user` = ? LIMIT 1");

                if ($check === false) {
                    error_log("Register prepare failed (check): " . $conn->error);$errors[] = "Something went wrong. Please try again later.";
                } else {
                    $check->bind_param("s", $user);$check->execute();
                    $check->store_result();$alreadyExists = ($check->num_rows > 0);$check->close();

                    if ($alreadyExists) {
                        $errors[] = "This " . id_label($role) . " is already registered. Please log in instead.";
                    } else {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $stmt =$conn->prepare("INSERT INTO users (`user`, password, role) VALUES (?, ?, ?)");

                        if ($stmt === false) {
                            error_log("Register prepare failed (insert): " . $conn->error);$errors[] = "Something went wrong. Please try again later.";
                        } else {
                            $stmt->bind_param("sss", $user, $hash,$role);

                            if ($stmt->execute()) {
                                $stmt->close();$conn->close();

                                unset($_SESSION['csrf_token']);$_SESSION['flash_success'] = "Registration successful! Please log in.";
                                header("Location: pages/login.php");
                                exit;
                            } elseif ($stmt->errno === 1062) {
                                $errors[] = "This " . id_label($role) . " is already registered. Please log in instead.";
                            } else {
                                error_log("Register insert failed: " . $stmt->error);$errors[] = "Registration failed. Please try again later.";
                            }
                            $stmt->close();
                        }
                    }
                }
                $conn->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Register</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>

    <div class="container" style="align-items: stretch;">

        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Welcome to StudentHub Portal</p>
        </header>

        <main class="box form-box" style="margin: 0 auto; width: 100%;">
            <h2>Register</h2>

            <div id="clientErrors" class="message error" role="alert" hidden></div>

            <?php if (!empty($errors)): ?>
                <div class="message error" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul>
                        <?php foreach ($errors as$error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="index.php">
                <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

                <div class="field">
                    <label for="role">Register as</label>
                    <select id="role" name="role">
                        <option value="student" <?php echo ($old['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="admin" <?php echo ($old['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="field">
                    <label for="user" id="userLabel"><?php echo e(id_label($old['role'])); ?></label>
                    <input type="text" id="user" name="user" value="<?php echo e($old['user']); ?>" maxlength="20" autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" maxlength="64" autocomplete="new-password" required>
                    <small class="hint">At least 8 characters, with a letter and a number.</small>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>

            <div class="form-footer">
                Already registered? <a href="pages/login.php">Login here</a>
            </div>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>

    </div>

</body>
</html>