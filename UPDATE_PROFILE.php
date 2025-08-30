<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: HOME PAGE.php');
    exit();
}
include('INCLUDES/db.php');

$thisuser = null;
// Fetch the current user info from DB (assuming $conn is your DB connection)
$uid = $_SESSION['uid'];
$res = mysqli_query($conn, "SELECT * FROM `users` WHERE id='$uid'");
if ($res && mysqli_num_rows($res) > 0) {
    $thisuser = mysqli_fetch_assoc($res);
} else {
    // If user not found, redirect to login or error page
    header('Location: HOME PAGE.php');
    exit();
}

$msg = "";

if (isset($_POST['updateAcount'])) {
    if (empty($_FILES['img']['name'])) {
        // Update without image
        $upq = mysqli_query($conn, "UPDATE `users` SET `name`='" . mysqli_real_escape_string($conn, $_POST['name']) . "', `contact`='" . mysqli_real_escape_string($conn, $_POST['contact']) . "', `email`='" . mysqli_real_escape_string($conn, $_POST['email']) . "' WHERE id = '" . $thisuser['id'] . "'");
        if ($upq) {
            $msg = "User updated successfully! _success";
        } else {
            $msg = "Unknown Error! _danger";
        }
    } else {
        // Handle image upload
        $target_dir = "IMGS/";
        $filename = basename($_FILES["img"]["name"]);
        $target_file = $target_dir . time() . "_" . preg_replace('/\s+/', '_', $filename); // avoid conflicts, add timestamp
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["img"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $msg = "File is not an image! _danger";
            $uploadOk = 0;
        }

        if (file_exists($target_file)) {
            $msg = "Sorry, file already exists! _danger";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["img"]["tmp_name"], $target_file)) {
                $upq = mysqli_query($conn, "UPDATE `users` SET `img` = '" . mysqli_real_escape_string($conn, $target_file) . "', `name`='" . mysqli_real_escape_string($conn, $_POST['name']) . "', `contact`='" . mysqli_real_escape_string($conn, $_POST['contact']) . "', `email`='" . mysqli_real_escape_string($conn, $_POST['email']) . "' WHERE id = '" . $thisuser['id'] . "'");
                if ($upq) {
                    $msg = "User updated successfully! _success";
                    echo "<script>location.replace('UPDATE_PROFILE.php');</script>";
                    exit();
                } else {
                    $msg = "Unknown Error! _danger";
                }
            } else {
                $msg = "Sorry, there was an error uploading your file! _danger";
            }
        }
    }
}

if (isset($_POST['changePassword'])) {
    if (md5($_POST['old']) == $thisuser['password']) {
        if ($_POST['new'] == $_POST['confirm']) {
            $hash = md5($_POST['new']);
            $upq = mysqli_query($conn, "UPDATE `users` SET `password`='" . $hash . "' WHERE id = '" . $thisuser['id'] . "'");
            if ($upq) {
                $msg = "Password changed successfully! _success";
            } else {
                $msg = "Unknown Error! _danger";
            }
        } else {
            $msg = "Password don't match! _danger";
        }
    } else {
        $msg = "Incorrect password! _danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UPDATE PROFILE| EduAxis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <style>
        /* Reset */
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }

        /* Wrapper */
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
            gap: 20px;
        }

        /* Logo */
        .top-left-image {
            height: 50px;
            object-fit: contain;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            cursor: pointer;
        }

        .user-info img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .welcome-msg {
            font-size: 14px;
            color: #333;
            line-height: 1.2;
            user-select: none;
        }

        /* Dropdown */
        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            top: 45px;
            right: 0;
            background-color: white;
            min-width: 140px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            border-radius: 4px;
        }

        .dropdown-content a {
            display: block;
            padding: 8px 12px;
            color: #333;
            text-decoration: none;
        }

        .dropdown-content a:hover {
            background-color: #f0f0f0;
        }

        .user-info:hover .dropdown-content {
            display: block;
        }
        
        .main-content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px 60px 20px;
            background: #f8f9fa;
        }

        /* Card */
        .card {
            width: 100%;
            max-width: 600px;
            box-shadow: 0 4px 15px rgb(0 0 0 / 0.1);
            border-radius: 8px;
            background: white;
        }

        /* Profile Image Preview */
        #profile-img {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">

        <!-- Top Header -->
        <header class="top-header">
            <!-- Logo -->
            <img src="IMGS/LOGO.jpg" alt="Logo" class="top-left-image" />

            <!-- User Info -->
            <div class="user-info">
                <?php if (isset($thisuser['img']) && $thisuser['img'] != "") { ?>
                    <img src="<?= htmlspecialchars($thisuser['img']) ?>" alt="User Image" />
                <?php } else { ?>
                    <img src="IMGS/USER.jpg" alt="Default User Image" />
                <?php } ?>
                <div class="welcome-msg">
                    <span>Welcome,</span><br />
                    <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
                </div>
                <div class="dropdown">
                    <div class="dropdown-content">
                        <a href="UPDATE_PROFILE.php" target="_blank">Update Details</a>
                        <a href="LOGOUT.php">Log Out</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="card m-3">
                <div class="card-body">
                    <h4 class="text-center mb-4">Personal Details</h4>
                    <form action="UPDATE_PROFILE.php" method="POST" enctype="multipart/form-data">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <label for="img" class="form-label">Image</label>
                                <input type="file" name="img" id="img" accept="image/*" class="form-control" onchange="showPreview(event);" />
                            </div>
                            <div class="col-md-6 text-center">
                                <?php if (isset($thisuser['img']) && $thisuser['img'] != "") { ?>
                                    <img src="<?= htmlspecialchars($thisuser['img']) ?>" id="profile-img" alt="Profile Image" class="rounded-circle" />
                                <?php } else { ?>
                                    <img src="IMGS/USER.jpg" id="profile-img" alt="Profile Image" class="rounded-circle" />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" required class="form-control" value="<?= htmlspecialchars($thisuser['name']) ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" required class="form-control" value="<?= htmlspecialchars($thisuser['email']) ?>" />
                        </div>
                        <div class="mb-3">
                            <label for="contact" class="form-label">Contact</label>
                            <input type="text" id="contact" name="contact" required class="form-control" value="<?= htmlspecialchars($thisuser['contact']) ?>" />
                        </div>
                        <div class="text-end">
                            <button name="updateAcount" type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>

                    <hr class="my-4" />
                    <form action="DELETE_ACCOUNT.php" method="post" class="mt-4 text-center">
                        <input type="hidden" name="action" value="delete" />
                        <button type="submit" class="btn btn-danger">Delete My Account</button>
                    </form>

                    <?php if (!empty($msg)) {
                        list($text, $type) = explode(' _', $msg);
                        $alertType = ($type === 'success') ? 'alert-success' : 'alert-danger';
                    ?>
                        <div class="alert <?= $alertType ?> mt-3" role="alert">
                            <?= htmlspecialchars($text) ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showPreview(event) {
            if (event.target.files.length > 0) {
                var src = URL.createObjectURL(event.target.files[0]);
                var preview = document.getElementById("profile-img");
                preview.src = src;
                preview.style.display = "block";
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
