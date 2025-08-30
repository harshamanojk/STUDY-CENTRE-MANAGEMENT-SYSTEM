<?php
session_start();

if (isset($_SESSION['uid'])) {
    header('Location: LOGIN_FORM.php');
    exit();
}

// Database connection parameters
$servername = "localhost";
$username = "root";
$password_db = ""; // Change this to your database password
$dbname = "eduaxis";

// Create connection
$conn = new mysqli($servername, $username, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables and error variables initialization
$email = $password = $contact = $name = "";
$erroremail = $errorpassword = $errorcontact = $errorname = "";
$notification = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate name
    if (empty($_POST["txtname"])) {
        $errorname = "* Name is required";
    } else {
        $name = test_input($_POST["txtname"]);
    }

    // Validate email
    if (empty($_POST["txtemail"])) {
        $erroremail = "* Email is required";
    } else {
        $email = test_input($_POST["txtemail"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erroremail = "* Invalid email format";
        }
    }

    // Validate password
    if (empty($_POST["txtpassword"])) {
        $errorpassword = "* Password is required";
    } else {
        $password = test_input($_POST["txtpassword"]);
        if (strlen($password) < 8) {
            $errorpassword = "* Password must be at least 8 characters";
        }
    }

    // Validate contact
    if (empty($_POST["txtcontact"])) {
        $errorcontact = "* Contact number is required";
    } else {
        $contact = test_input($_POST["txtcontact"]);
    }

    // If no validation errors, proceed
    if ($errorname == "" && $erroremail == "" && $errorpassword == "" && $errorcontact == "") {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $notification = "❌ Account already exists with this email!";
        } else {
            // Insert new user
            $password_hashed = md5($password); // For better security, consider password_hash()
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, contact) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password_hashed, $contact);

            if ($stmt->execute()) {
                echo "<script>alert('Registration successful! Please login.'); window.location='LOGIN_FORM.php';</script>";
                exit();
            } else {
                $notification = "❌ Error: " . $stmt->error;
            }

            $stmt->close();
        }
        $checkStmt->close();
    }
}

// Function to sanitize input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>REGISTER | EduAxis</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="CSS/access.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <div class="form-container">
        <div class="img text-center">
            <img src="IMGS/LOGO.jpg" alt="Logo">
        </div>
        <h2 class="text-center"><u><b>EDUAXIS</b></u></h2>
        <p class="text-center" style="font-size: 18px;"><u>REGISTER</u></p>

        <?php if ($notification): ?>
            <div class="alert alert-danger text-center">
                <?= $notification ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <div class="form-group">
                <input type="text" class="form-control" id="name" name="txtname" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>">
                <span class="error"><?= $errorname ?></span>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" id="email" name="txtemail" placeholder="Email" value="<?= htmlspecialchars($email) ?>">
                <span class="error"><?= $erroremail ?></span>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="txtpassword" placeholder="Password">
                <span class="error"><?= $errorpassword ?></span>
            </div>
            <div class="form-group">
                <input type="tel" class="form-control" id="contact" name="txtcontact" placeholder="Contact Number" value="<?= htmlspecialchars($contact) ?>">
                <span class="error"><?= $errorcontact ?></span>
            </div>
            <button type="submit" class="btn btn-primary btn-block" name="btn-signup">Sign Up</button>
        </form>
    </div>
</div>

<footer class="text-center py-4 bg-dark text-light">
    <p>&copy; 2025 EduAxis. All rights reserved.</p>
</footer>

</body>
</html>
