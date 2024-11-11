<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to maintain login state
session_start();

// Initialize variables
$errorMessages = [];
$isLoggedIn = isset($_SESSION['isLoggedIn']) ? $_SESSION['isLoggedIn'] : false;
$loggedInUser = isset($_SESSION['loggedInUser']) ? $_SESSION['loggedInUser'] : "";
$subjects = isset($_SESSION['subjects']) ? $_SESSION['subjects'] : [];

// Get current page (default is dashboard)
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Define an array of users with their email and password
$users = [
    ['email' => 'user1@gmail.com', 'password' => 'password1'],
    ['email' => 'user2@gmail.com', 'password' => 'password2'],
    ['email' => 'user3@gmail.com', 'password' => 'password3'],
    ['email' => 'user4@gmail.com', 'password' => 'password4'],
    ['email' => 'user5@gmail.com', 'password' => 'password5']
];

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Validate email
    if (empty($email)) {
        $errorMessages[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = "Invalid email";
    }

    // Validate password
    if (empty($password)) {
        $errorMessages[] = "Password is required";
    }

    // Check login credentials
    if (empty($errorMessages)) {
        // Check if the provided email and password match any user in the array
        $userFound = false;
        foreach ($users as $user) {
            if ($email === $user['email'] && $password === $user['password']) {
                $_SESSION['isLoggedIn'] = true;
                $_SESSION['loggedInUser'] = $email;
                $isLoggedIn = true;
                $loggedInUser = $email;
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
            $errorMessages[] = "Invalid email or password";
        }
    }
}

// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle add subject form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addSubject"])) {
    $subjectCode = trim($_POST["subjectCode"]);
    $subjectName = trim($_POST["subjectName"]);

    // Validate empty fields
    if (empty($subjectCode)) {
        $errorMessages[] = "Subject code is required.";
    } elseif (empty($subjectName)) {
        $errorMessages[] = "Subject name is required.";
    } else {
        // Check for duplicate subject name and code
        $duplicateNameFound = false;
        $duplicateCodeFound = false;

        foreach ($subjects as $subject) {
            if (strtolower($subject['name']) == strtolower($subjectName)) {
                $duplicateNameFound = true;
            }
            if (strtolower($subject['code']) == strtolower($subjectCode)) {
                $duplicateCodeFound = true;
            }
        }

        if ($duplicateNameFound) {
            $errorMessages[] = "Duplicate Subject: A subject with this name already exists.";
        } elseif ($duplicateCodeFound) {
            $errorMessages[] = "Subject code already exists: A subject with this code already exists.";
        } else {
            // Add the subject if no duplicates found
            $subjects[] = ['code' => $subjectCode, 'name' => $subjectName];
            $_SESSION['subjects'] = $subjects;
            $successMessage = "Subject added successfully!";
        }
    }
}

// Handle delete or edit request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    $action = $_POST["action"];
    $index = (int) $_POST["index"]; // Ensure $index is an integer

    if ($action == "delete") {
        // Delete the subject
        array_splice($subjects, $index, 1);
        $_SESSION['subjects'] = $subjects;
    } elseif ($action == "edit") {
        // Populate form with existing subject for editing
        $subjectToEdit = $subjects[$index];
        $editSubjectCode = $subjectToEdit['code'];
        $editSubjectName = $subjectToEdit['name'];
        $editIndex = $index;  // Store the index to modify the correct subject later
    }
}

// Handle updated subject submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editSubject"])) {
    $editSubjectCode = trim($_POST["subjectCode"]);
    $editSubjectName = trim($_POST["subjectName"]);
    $editIndex = $_POST["editIndex"];

    // Validate empty fields
    if (empty($editSubjectCode)) {
        $errorMessages[] = "Subject code is required.";
    } elseif (empty($editSubjectName)) {
        $errorMessages[] = "Subject name is required.";
    } else {
        // Update the subject in the session
        $subjects[$editIndex] = ['code' => $editSubjectCode, 'name' => $editSubjectName];
        $_SESSION['subjects'] = $subjects;
        $successMessage = "Subject updated successfully!";

        // Clear the form fields after the update
        $editSubjectCode = '';
        $editSubjectName = '';
        $editIndex = null; // Clear the edit index so we can add new subjects
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #fce4ec;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            color: #7b1fa2;
        }

        h1, h2, h3 {
            color: #d81b60;
        }

        .error {
            background-color: #f8bbd0;
            color: #c2185b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .success {
            background-color: #c8e6c9;
            color: #388e3c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #ec407a;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #d81b60;
        }

        .logout-button {
            width: auto;
            padding: 10px 20px;
            background-color: #f50057;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 30px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: 20px;
            box-shadow:0 5px 10px rgba(0, 0, 0, 0.5);
        }

        .logout-button:hover {
            background-color: #c51162;
        }

        .dashboard {
            display: list-item;
            gap: 20px;
            flex-wrap: wrap;
        }

        .dashboard-section {
            background-color: #fce4ec;
            border-radius: 10px;
            padding: 15px;
            flex: 1;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-section button {
            background-color: #f50057;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            color: white;
            border: none;
        }

        .dashboard-section button:hover {
            background-color: #c51162;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($isLoggedIn): ?>
        <!-- Dashboard Section -->
        <?php if ($currentPage == 'dashboard'): ?>
            <div class="dashboard-header">
                <h2>Welcome to the System: <?php echo htmlspecialchars($loggedInUser); ?></h2>
                <form method="post" action="">
                    <button type="submit" name="logout" class="logout-button">Logout</button>
                </form>
            </div>

            <div class="dashboard">
                <div class="dashboard-section">
                    <h3>Add a Subject</h3>
                    <p>This section allows you to add a new subject in the system. Click the button below to proceed with the adding process.</p>
                    <a href="?page=addSubject"><button>Add Subject</button></a>
                </div>

                <div class="dashboard-section">
    <h3>Register a New Student</h3>
    <p>This section allows you to register a new student in the system. Click the button below to proceed with the registration process.</p>
    <a href="reg_stud.php"><button>Register</button></a>
</div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Add Subject Form Section -->
        <?php if ($currentPage == 'addSubject'): ?>
            <div class="add-subject-form">
                <h3>Add a Subject</h3>
                <?php if (!empty($errorMessages)): ?>
                    <div class="error">
                        <strong>Errors</strong>
                        <ul>
                            <?php foreach ($errorMessages as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (isset($successMessage)): ?>
                    <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <label for="subjectCode">Subject Code</label>
                    <input type="text" name="subjectCode" id="subjectCode" value="<?php echo htmlspecialchars($editSubjectCode ?? ''); ?>" required>

                    <label for="subjectName">Subject Name</label>
                    <input type="text" name="subjectName" id="subjectName" value="<?php echo htmlspecialchars($editSubjectName ?? ''); ?>" required>

                    <?php if (isset($editIndex)): ?>
                        <input type="hidden" name="editIndex" value="<?php echo $editIndex; ?>">
                        <button type="submit" name="editSubject">Update Subject</button>
                    <?php else: ?>
                        <button type="submit" name="addSubject">Add Subject</button>
                    <?php endif; ?>
                </form>

                <br>
                <a href="?page=dashboard"><button>Back to Dashboard</button></a>

                <h3>Current Subjects</h3>
                <div class="subject-list">
                    <table>
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $index => $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['code']); ?></td>
                                    <td><?php echo htmlspecialchars($subject['name']); ?></td>
                                    <td class="actions">
                                        <form method="post" action="" style="display:inline;">
                                            <button type="submit" name="action" value="edit">Edit</button>
                                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        </form>
                                        <form method="post" action="" style="display:inline;">
                                            <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure?');">Delete</button>
                                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Login Form Section -->
        <div class="login-form">
            <h2>Login into the System</h2>
            <?php if (!empty($errorMessages)): ?>
                <div class="error">
                    <strong>Errors</strong>
                    <ul>
                        <?php foreach ($errorMessages as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>

                <button type="submit" name="login">Login</button>


            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>