<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: profile.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Student Management System - Login / Register</title>
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
    form {
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
    }
    input[type="text"], input[type="email"], input[type="password"] {
        padding: 12px;
        margin-bottom: 15px;
        border: 1.5px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        transition: border-color 0.3s;
    }
    input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
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
    .toggle-btn {
        background: none;
        border: none;
        color: #764ba2;
        cursor: pointer;
        font-size: 14px;
        text-decoration: underline;
        margin-top: 10px;
    }
    .message {
        color: red;
        margin-bottom: 10px;
        font-size: 14px;
    }
</style>
<script>
    function showRegister() {
        document.getElementById('login-form').style.display = 'none';
        document.getElementById('register-form').style.display = 'block';
        clearMessages();
    }
    function showLogin() {
        document.getElementById('login-form').style.display = 'block';
        document.getElementById('register-form').style.display = 'none';
        clearMessages();
    }
    function clearMessages() {
        document.querySelectorAll('.message').forEach(e => e.textContent = '');
    }
</script>
</head>
<body>


<div class="container">
    <h1>Student Marks and Attendence portal </h1>


    <!-- Login Form -->
    <form id="login-form" action="login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required autocomplete="username" />
        <input type="password" name="password" placeholder="Password" required autocomplete="current-password" />
        <button type="submit">Login</button>
        <button type="button" class="toggle-btn" onclick="showRegister()">Don't have an account? Register</button>
    </form>


    <!-- Register Form -->
    <form id="register-form" action="register.php" method="POST" style="display:none;">
        <input type="text" name="name" placeholder="Full Name" required autocomplete="name" />
        <input type="email" name="email" placeholder="Email" required autocomplete="email" />
        <input type="password" name="password" placeholder="Password" required autocomplete="new-password" />
        <input type="password" name="password_confirm" placeholder="Confirm Password" required autocomplete="new-password" />
        <button type="submit">Register</button>
        <button type="button" class="toggle-btn" onclick="showLogin()">Already have an account? Login</button>
    </form>
</div>


</body>
</html>