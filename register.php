<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $errors = [];

    // Basic validation
    if (strlen($name) < 3) {
        $errors[] = "Name must be at least 3 characters long.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (count($errors) === 0) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        }
        $stmt->close();
    }

    if (count($errors) === 0) {
        // Insert student
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $attendance = 0;
        $marks = 0;
        $notifications = "Success is the sum of small efforts, repeated day in and day out. – Robert Collier";

        $stmt = $conn->prepare("INSERT INTO students (name, email, password, attendance, marks, notifications) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdds", $name, $email, $hashed_password, $attendance, $marks, $notifications);

        if ($stmt->execute()) {
            // Redirect to login page with success message
            $_SESSION['register_success'] = "Registration successful! Please log in.";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Error occurred during registration.";
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
<title>Register - Student Management System</title>
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
    .message {
        margin-bottom: 15px;
        font-size: 14px;
        color: red;
        text-align: left;
    }
    .success {
        color: green;
    }
    a {
        color: #764ba2;
        text-decoration: none;
        font-weight: 600;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
</head>
<body>
<div class="container">
    <h1>Register</h1>
    <?php if (!empty($errors)): ?>
        <div class="message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?=htmlspecialchars($error)?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form action="register.php" method="POST" autocomplete="off">
        <input type="text" name="name" placeholder="Full Name" required value="<?= htmlspecialchars($name ?? '') ?>" />
        <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($email ?? '') ?>" />
        <input type="password" name="password" placeholder="Password" required />
        <input type="password" name="password_confirm" placeholder="Confirm Password" required />
        <button type="submit" style="margin-top:15px; padding:12px; width: 100%; border-radius: 6px; border:none; background:#764ba2; color:white; font-size: 16px; cursor:pointer;">Register</button>
    </form>
    <p style="margin-top:15px;">Already have an account? <a href="index.php">Login here</a></p>
</div>
</body>
</html>
