<?php
    session_start();
?>
<?php require "functions.php" ?>
<?php
    if(isset($_GET['logout'])){
        logout();
     }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Page</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            margin-top: 50px;
        }
        h1 {
            font-size: 3em;
        }
        .button {
            display: inline-block;
            margin: 20px;
            padding: 10px 20px;
            font-size: 1em;
            text-decoration: none;
            color: white;
            background-color: blue;
            border: none;
            cursor: pointer;
        }
        .button a {
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Employer</h1>
    <button class="button"><a href="employer-register.php">Sign Up</a></button>
    <button class="button"><a href="employer-login.php">Log In</a></button>
    <button class="button"><a href="index.php">Back</a></button>
</body>
</html>
