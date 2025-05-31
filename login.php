<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

require_once 'config.php';

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $login_error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                // Password is correct, log in the user
                $_SESSION['student_id'] = $id;
                $_SESSION['student_name'] = $name;
                header('Location: profile.php');
                exit;
            } else {
                $login_error = "Incorrect password.";
            }
        } else {
            $login_error = "No account found with that email.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login - Student Management System</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #333;
        margin: 0;
        padding: 0;
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
    }
    .container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        width: 360px;
        padding: 40px;
        text-align: center;
    }
    h1 {
        margin-bottom: 24px;
        font-weight: 700;
        color: #4a4a4a;
    }
    .error-message {
        background-color: #ffcccc;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 15px;
        color: #d8000c;
        font-weight: 600;
        text-align: left;
    }
    form {
        display: flex;
        flex-direction: column;
    }
    input[type="email"],
    input[type="password"] {
        padding: 12px;
        margin-bottom: 15px;
        border: 1.5px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        transition: border-color 0.3s;
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #764ba2;
        outline: none;
    }
    button {
        background: #764ba2;
        color: white;
        border: none;
        padding: 12px;
        font-size: 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s;
    }
    button:hover {
        background: #5a3677;
    }
    p {
        margin-top: 15px;
    }
    a {
        color: #764ba2;
        font-weight: 600;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <?php if (!empty($login_error)): ?>
        <div class="error-message"><?=htmlspecialchars($login_error)?></div>
    <?php elseif (!empty($_SESSION['register_success'])): ?>
        <div style="color: green; margin-bottom: 15px; font-weight: 600; text-align: left;">
            <?=htmlspecialchars($_SESSION['register_success'])?>
        </div>
        <?php unset($_SESSION['register_success']); ?>
    <?php endif; ?>
    <form action="login.php" method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Email" required autocomplete="username" />
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password" />
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="index.php" style="font-weight: 700;">Register here</a></p>
</div>
</body>
</html>
