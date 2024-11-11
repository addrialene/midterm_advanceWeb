<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Initialize variables
$errorMessages = [];
$students = isset($_SESSION['students']) ? $_SESSION['students'] : [];

// Handle register student form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registerStudent"])) {
    $studentId = trim($_POST["studentId"]);
    $studentFirstName = trim($_POST["studentFirstName"]);
    $studentLastName = trim($_POST["studentLastName"]);

    // Validate empty fields
    if (empty($studentId)) {
        $errorMessages[] = "Student ID is required.";
    }
    if (empty($studentFirstName)) {
        $errorMessages[] = "First name is required.";
    }
    if (empty($studentLastName)) {
        $errorMessages[] = "Last name is required.";
    } else {
        // Check for duplicate student ID
        $duplicateFound = false;
        foreach ($students as $student) {
            if ($student['id'] === $studentId) {
                $duplicateFound = true;
                break;
            }
        }

        if ($duplicateFound) {
            $errorMessages[] = "A student with this ID already exists.";
        } else {
            // Add the student if no duplicates found
            $students[] = ['id' => $studentId, 'firstName' => $studentFirstName, 'lastName' => $studentLastName];
            $_SESSION['students'] = $students;
            $successMessage = "Student registered successfully!";
        }
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $studentId = $_GET['id'];
    $students = array_filter($students, function ($student) use ($studentId) {
        return $student['id'] !== $studentId;
    });
    $_SESSION['students'] = $students;
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh to update the list
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
            background-color: #fce4ec; /* Soft pink background */
        }
        .container { 
            width: 80%; 
            max-width: 600px; 
            text-align: center; 
            background-color: #fff; /* White background for the container */
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }
        .error { 
            color: #a94442; 
            background-color: #f8d7da; 
            padding: 10px; 
            border: 1px solid #ebccd1; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .form-section, .student-list { 
            border: 1px solid #ddd; 
            padding: 20px; 
            border-radius: 5px; 
            margin-top: 20px; 
            background-color: #ffe4e1; /* Light pink for form sections */
        }
        .form-section label, .form-section input { 
            width: 100%; 
            display: block; 
            margin: 10px 0; 
        }
        .form-section input { 
            padding: 8px; 
            border-radius: 5px; 
            border: 1px solid #f8b0b5; /* Soft pink border for inputs */
        }
        .form-section button { 
            width: 100%; 
            padding: 10px; 
            background-color: #ff69b4; /* Hot pink button */
            color: #fff; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
        }
        .form-section button:hover { 
            background-color: #ff1493; /* Darker pink on hover */
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        table, th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
        }
        th { 
            background-color: #f2f2f2; 
        }
        .action-btn { 
            color: #fff; 
            border: none; 
            padding: 5px 10px; 
            cursor: pointer; 
        }
        .edit-btn { 
            background-color: #ff66b2; /* Pink edit button */
        }
        .delete-btn { 
            background-color: #ff4d4d; /* Red delete button */
        }
        a.action-link { 
            color: #ff69b4; 
            text-decoration: none; 
        }
        a.action-link:hover { 
            text-decoration: underline; 
        }
        .success {
            color: #3c763d;
            background-color: #dff0d8;
            padding: 10px;
            border: 1px solid #d0e9c6;
            border-radius: 5px;
            margin-top: 10px;
        }
        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .breadcrumb a {
            color: #ff69b4;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="logIN.php?page=dashboard">Dashboard</a> &gt; Register a New Student
    </div>

    <div class="form-section">
        <h2>Register Student</h2>
        <?php if (!empty($errorMessages)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errorMessages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (isset($successMessage)): ?>
            <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="studentId">Student ID</label>
            <input type="text" name="studentId" required>

            <label for="studentFirstName">First Name</label>
            <input type="text" name="studentFirstName" required>

            <label for="studentLastName">Last Name</label>
            <input type="text" name="studentLastName" required>

            <button type="submit" name="registerStudent">Register Student</button>
        </form>

        <br>
        <a href="logIN.php?page=dashboard"><button type="button">Back to Dashboard</button></a>
    </div>

    <!-- Student List Section -->
    <?php if (!empty($students)): ?>
    <div class="student-list">
        <h3>Student List</h3>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['firstName']); ?></td>
                    <td><?php echo htmlspecialchars($student['lastName']); ?></td>
                    <td>
                        <!-- Edit and Delete Buttons -->
                        <button class="action-btn edit-btn" onclick="window.location.href='edit.php?id=<?php echo urlencode($student['id']); ?>'">Edit</button>
                        <button class="action-btn delete-btn" onclick="return confirmDelete(<?php echo htmlspecialchars(json_encode($student['id'])); ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>No student record found.</p>
    <?php endif; ?>
</div>

<!-- Confirmation for Deleting -->
<script>
function confirmDelete(studentId) {
    if (confirm("Are you sure you want to delete this student?")) {
        window.location.href = "?action=delete&id=" + studentId;
    }
    return false; // prevent the default action
}
</script>

</body>
</html>
