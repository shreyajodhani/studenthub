<?php
require_once __DIR__ . '/auth.php';
start_secure_session();

function id_label($role) {
    return ($role === 'admin') ? 'Admin Name' : 'Student ID';
}

if (empty($_SESSION['csrf_token'])) {$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors       = [];$old          = ['user' => '', 'role' => 'student'];
$flash        = '';$loginDone    = false;
$loginMessage = '';$redirectTo   = '';

if (isset($_SESSION['flash_success'])) {
    $flash =$_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {$user = trim(field('user'));
    $pass = field('password');$role = field('role');

    $old['user'] =$user;
    $old['role'] = in_array($role, ['student', 'admin'], true) ?$role : 'student';

    if (!hash_equals($_SESSION['csrf_token'], field('csrf_token'))) {$errors[] = "Your form has expired. Please try again.";
    } elseif (!in_array($role, ['student', 'admin'], true)) {$errors[] = "Please choose a role.";
    } elseif ($user === '' || $pass === '') {$errors[] = "Please enter your " . id_label($role) . " and password.";
    } else {
        if ($role === 'student') {
            $user = strtoupper($user);
        }

        $conn = db_connect();

        if (!$conn) {$errors[] = "Could not connect to the database. Please try again later.";
        } else {
            $stmt =$conn->prepare("SELECT id, `user`, password, role FROM users WHERE `user` = ? LIMIT 1");

            if ($stmt === false) {
                error_log("Login prepare failed: " . $conn->error);$errors[] = "Something went wrong. Please try again later.";
            } else {
                $stmt->bind_param("s", $user);$stmt->execute();
                $stmt->bind_result($dbId, $dbUser,$dbHash, $dbRole);$found = $stmt->fetch();$stmt->close();

                if (!$found) {
                    $errors[] = "This " . id_label($role) . " is not registered. Please register first.";
                } elseif ($dbRole !==$role) {
                    $errors[] = "This " . id_label($role) . " is not registered as " . ($role === 'admin' ? 'an Admin' : 'a Student') . ". Please choose the correct role.";
                } elseif (!password_verify($pass, $dbHash)) {$errors[] = "Wrong password. Please try again.";
                } else {
                    login_user($dbId, $dbUser,$dbRole);
                    unset($_SESSION['csrf_token']);

                    $loginDone    = true;
                    $loginMessage = "Logged in as " . ($dbRole === 'admin' ? 'Admin' : 'Student');
                    $redirectTo   = home_for_role($dbRole);
                }
            }
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>StudentHub - Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/script.js" defer></script>
</head>
<body<?php if ($loginDone): ?> data-login-message="<?php echo e($loginMessage); ?>" data-login-redirect="<?php echo e($redirectTo); ?>"<?php endif; ?>>

    <div class="container" style="align-items: stretch;">

        <header class="box header-box">
            <h1>STUDENT HUB PORTAL</h1>
            <p>Welcome to StudentHub Portal</p>
        </header>

        <main class="box form-box" style="margin: 0 auto; width: 100%;">
            <h2>Login</h2>

            <?php if ($loginDone): ?>
                <div class="message success" role="status">
                    <strong>Login successful.</strong>
                    If you are not taken to your page, <a href="<?php echo e($redirectTo); ?>">click here</a>.
                </div>
            <?php else: ?>

                <?php if ($flash !== ''): ?>
                    <div class="message success" role="status"><?php echo e($flash); ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="message error" role="alert">
                        <strong>Login failed:</strong>
                        <ul>
                            <?php foreach ($errors as$error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">

                    <div class="field">
                        <label for="role">Login as</label>
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
                        <input type="password" id="password" name="password" maxlength="64" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="btn">Login</button>
                </form>

                <div class="form-footer">
                    Not registered yet? <a href="../index.php">Register here</a>
                </div>

            <?php endif; ?>
        </main>

        <footer class="footer">
            &copy; 2026 CHARUSAT University - StudentHub Portal. All Rights Reserved.
        </footer>

    </div>

</body>
</html>