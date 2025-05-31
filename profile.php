<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$mark_att_success = '';
$mark_att_error = '';
$update_success = '';
$update_error = '';
$subjects = ['DSA', 'OOPS', 'ENGLISH', 'MATHS', 'AEC'];

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance']) && isset($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];

    $stmt = $conn->prepare("SELECT id FROM student_attendance WHERE student_id = ? AND attendance_date = ?");
    $stmt->bind_param("is", $student_id, $attendance_date);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $mark_att_error = "You have already marked attendance for this date.";
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO student_attendance (student_id, attendance_date) VALUES (?, ?)");
        $insert_stmt->bind_param("is", $student_id, $attendance_date);
        if ($insert_stmt->execute()) {
            $mark_att_success = "Attendance marked for $attendance_date.";
        } else {
            $mark_att_error = "Error marking attendance.";
        }
        $insert_stmt->close();
    }
    $stmt->close();
}

// Handle subject marks update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_marks'])) {
    $total_marks = 0;
    $valid = true;

    foreach ($subjects as $subject) {
        $subject_key = strtolower($subject);
        $mark = filter_input(INPUT_POST, $subject_key, FILTER_VALIDATE_FLOAT);
        if ($mark === false || $mark < 0) {
            $update_error = "Invalid marks for $subject.";
            $valid = false;
            break;
        }
        $stmt = $conn->prepare("REPLACE INTO student_marks (student_id, subject_name, marks) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $student_id, $subject, $mark);
        $stmt->execute();
        $stmt->close();
        $total_marks += $mark;
    }

    if ($valid) {
        $percentage = $total_marks / count($subjects);
        $update_success = "Marks updated successfully. Your percentage is " . round($percentage, 2) . "%.";
    }
}

// Fetch student name and notifications
$stmt = $conn->prepare("SELECT name, notifications FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($name, $notifications);
$stmt->fetch();
$stmt->close();

// Fetch attendance stats
$stmt = $conn->prepare("SELECT COUNT(*) FROM student_attendance WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($attended_classes);
$stmt->fetch();
$stmt->close();
$total_classes = 30; // or dynamically calculated if required
$attendance_percentage = ($total_classes > 0) ? round(($attended_classes / $total_classes) * 100, 2) : 0;

// Fetch marks
$marks_data = [];
$total_marks = 0;
foreach ($subjects as $subject) {
    $stmt = $conn->prepare("SELECT marks FROM student_marks WHERE student_id = ? AND subject_name = ?");
    $stmt->bind_param("is", $student_id, $subject);
    $stmt->execute();
    $stmt->bind_result($mark);
    $stmt->fetch();
    $marks_data[$subject] = $mark ?? 0;
    $total_marks += $marks_data[$subject];
    $stmt->close();
}
$percentage = $total_marks / count($subjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 60px 0;
            display: flex;
            justify-content: center;
            color: #333;
        }
        .profile-container {
            background: white;
            border-radius: 12px;
            padding: 30px 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #4a4a4a;
            text-align: center;
        }
        .info-block {
            background: #f5f5f5;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: inset 0 0 8px #ccc;
        }
        .info-label {
            font-weight: bold;
            color: #764ba2;
        }
        .info-value {
            font-size: 17px;
        }
        form.update-form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }
        input[type="date"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1.5px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus {
            border-color: #764ba2;
            outline: none;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .mark-button { background: #28a745; color: white; }
        .mark-button:hover { background: #1e7e34; }
        .update-button { background: #764ba2; color: white; }
        .update-button:hover { background: #5a3677; }
        .logout-button { background: #d9534f; color: white; }
        .logout-button:hover { background: #b52b27; }
        .message { font-weight: 600; margin-top: 10px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
<div class="profile-container">
    <h1>Welcome, <?=htmlspecialchars($name)?>!</h1>

    <div class="info-block">
        <div class="info-label">Attendance</div>
        <div class="info-value"><?= $attendance_percentage ?>%</div>
        <div style="font-size:13px; color:#666;">(<?= $attended_classes ?> attended out of <?= $total_classes ?> classes)</div>
    </div>

    <div class="info-block">
        <div class="info-label">Overall Marks Percentage</div>
        <div class="info-value"><?= round($percentage, 2) ?>%</div>
    </div>

    <div class="info-block">
        <div class="info-label">Subject-wise Marks</div>
        <ul>
            <?php foreach ($marks_data as $subject => $mark): ?>
                <li><strong><?= htmlspecialchars($subject) ?>:</strong> <?= htmlspecialchars($mark) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="info-block">
    <div class="info-label">Quotes</div>
    <div class="info-value notifications"><?=htmlspecialchars($notifications)?></div>
</div>


    <form method="POST" class="update-form">
        <label for="attendance_date">Select Date to Mark Attendance:</label>
        <input type="date" name="attendance_date" required />
        <button type="submit" name="mark_attendance" value="1" class="mark-button">Mark Attendance</button>
        <?php if ($mark_att_success): ?>
            <div class="message success"><?=htmlspecialchars($mark_att_success)?></div>
        <?php elseif ($mark_att_error): ?>
            <div class="message error"><?=htmlspecialchars($mark_att_error)?></div>
        <?php endif; ?>
    </form>

    <form method="POST" class="update-form" novalidate>
        <?php foreach ($subjects as $subject): ?>
            <label for="<?= strtolower($subject) ?>"><?= $subject ?> Marks</label>
            <input type="number" name="<?= strtolower($subject) ?>" step="0.01" min="0" required value="<?= htmlspecialchars($marks_data[$subject] ?? 0) ?>" />
        <?php endforeach; ?>
        <button type="submit" name="update_marks" class="update-button">Update Marks</button>
        <?php if ($update_success): ?>
            <div class="message success"><?=htmlspecialchars($update_success)?></div>
        <?php elseif ($update_error): ?>
            <div class="message error"><?=htmlspecialchars($update_error)?></div>
        <?php endif; ?>
    </form>

    <form action="logout.php" method="POST">
        <button type="submit" class="logout-button">Logout</button>
    </form>
</div>
</body>
</html>
