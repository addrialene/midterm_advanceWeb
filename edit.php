<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Initialize variables
$errorMessages = [];
$students = isset($_SESSION['students']) ? $_SESSION['students'] : [];

// Check if student ID is passed for editing
if (isset($_GET['id'])) {
    $studentId = $_GET['id'];

    // Find the student in the session array
    $studentToEdit = null;
    foreach ($students as $index => $student) {
        if ($student['id'] === $studentId) {
            $studentToEdit = $student;
            break;
        }
    }

    // If student is not found, redirect back to the register page
    if ($studentToEdit === null) {
        header("Location: reg_stud.php");
        exit();
    }

    // Handle form submission for updating the student
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["updateStudent"])) {
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
                if ($student['id'] === $studentId && $student['id'] !== $studentToEdit['id']) {
                    $duplicateFound = true;
                    break;
                }
            }

            if ($duplicateFound) {
                $errorMessages[] = "A student with this ID already exists.";
            } else {
                // Update student details in the session array
                $students[$index] = ['id' => $studentId, 'firstName' => $studentFirstName, 'lastName' => $studentLastName];
                $_SESSION['students'] = $students;

                // Redirect back to register student page after update
                header("Location: reg_stud.php");
                exit();
            }
        }
    }
} else {
    // Redirect to register page if no student ID is provided
    header("Location: reg_stud.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #fce4ec; /* Light pink background */
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0; 
        }
        .container { 
            width: 80%; 
            max-width: 600px; 
            text-align: center; 
            background-color: #fff; /* White background for the form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .error { 
            color: #fff; 
            background-color: #f44336; /* Red error background */
            padding: 10px; 
            border-radius: 5px; 
            margin-bottom: 20px; 
        }
        .form-section { 
            padding: 20px; 
            border-radius: 5px; 
            background-color: #f8bbd0; /* Soft pink background for form */
        }
        .form-section label, .form-section input { 
            width: 100%; 
            display: block; 
            margin: 10px 0; 
            font-size: 16px;
        }
        .form-section input { 
            padding: 8px; 
            border: 1px solid #e1bee7; /* Light purple border */
            border-radius: 4px;
        }
        .form-section button { 
            width: 100%; 
            padding: 12px; 
            background-color: #ec407a; /* Pink button */
            color: #fff; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px;
        }
        .form-section button:hover { 
            background-color: #d81b60; /* Darker pink on hover */
        }
        a button { 
            background-color: #c2185b; /* Slightly darker pink for back button */
            padding: 10px 20px; 
            color: #fff; 
            font-size: 16px; 
            border: none; 
            border-radius: 5px;
            text-decoration: none;
        }
        a button:hover { 
            background-color: #8e1f42; /* Darker pink on hover */
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-section">
        <h2>Edit Student</h2>
        <?php if (!empty($errorMessages)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errorMessages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Form to edit student details -->
        <form method="POST">
            <label for="studentId">Student ID</label>
            <input type="text" name="studentId" value="<?php echo htmlspecialchars($studentToEdit['id']); ?>" required>

            <label for="studentFirstName">First Name</label>
            <input type="text" name="studentFirstName" value="<?php echo htmlspecialchars($studentToEdit['firstName']); ?>" required>

            <label for="studentLastName">Last Name</label>
            <input type="text" name="studentLastName" value="<?php echo htmlspecialchars($studentToEdit['lastName']); ?>" required>

            <button type="submit" name="updateStudent">Update Student</button>
        </form>

        <br>
        <a href="reg_stud.php"><button type="button">Back to Student Register</button></a>
    </div>
</div>

</body>
</html>