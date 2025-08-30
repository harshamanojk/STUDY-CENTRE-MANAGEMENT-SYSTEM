<?php

session_start();

if (isset($_SESSION['uid'])==true) {
    header('Location: STUDENT_DASHBOARD.php');
}


$email = "";
$password = "";
$error = "";
$enter = "";


if ($_SERVER['REQUEST_METHOD'] =='POST') {

$email = $_POST['txtemail'];
$password = $_POST['txtpassword'];

 if ($email == '' ||  $password == '') {
       $error = "Please fill all the fields";
 } else {
       $servername = "localhost";
        $username = "root";
        $password = ""; // Change this to your database password
        $dbname = "eduaxis";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

                       $email = $_POST['txtemail'];
                        $password = $_POST['txtpassword'];
                        $password1 = md5($password);
                
                    $result = $conn-> query("SELECT * FROM users WHERE email = '$email' AND password = '$password1' " );
                    if ( $result-> num_rows ==0) {
                      $error = "Email or password is wrong";
                    } else {
                   
                      $data = mysqli_fetch_assoc($result);

                       $_SESSION['uid'] =  $data['id'];
                       $_SESSION['fullname'] =  $data['name'];
                     
                       $_SESSION['email'] =  $data['email'];
                        

                        header('location:STUDENT_DASHBOARD.php');
                                                 
                }
                    }
                    

                

              }
              
        
    



    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
?>    






<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>LOGIN | EduAxis </title>
<!-- Bootstrap and custom CSS links -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<link rel="stylesheet" href="CSS/access.css">
<!-- jQuery and Bootstrap JS links -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    
<div class="container">
    <div class="form-container">
        <div class="img">
            <img src="IMGS/LOGO.jpg" alt="Logo">
</div>
    <h2 style="text-align:center"><u><b>EDUAXIS</b></u></h2>
    <p style="font-size: 18px; text-align: center;"><u>LOGIN</u></p>
    <form method="post" action="LOGIN_FORM.php">
        <div class="form-group">
            <input type="email" class="form-control" id="email" name="txtemail" placeholder="Email">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="txtpassword" placeholder="Password">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="remember"> <span style="color: #706e6e;">Remember me</span> </label>
            </div>
            <button type="submit" class="btn btn-primary" name="btn-login">Login</button>
            <span class="error"><?php echo $error; ?></span>
        </form>
        <br>
</div>
    </div>
</div>
  <footer class="text-center py-4 bg-dark text-light">
    <p>&copy; 2025 EduAxis. All rights reserved.</p>
  </footer>
</body>
</html>