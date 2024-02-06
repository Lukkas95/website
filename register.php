<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "functions.php";
?>
<?php 
   if(isset($_POST['register'])){
      $response = register($_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['password'], $_POST['confirm-password'], $_POST['phone'], $_POST['birthdate'], $_POST['address'], $_POST['bio'], $_POST['experience']);
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Register page</title>
</head>
<body>
   
    <form action="" method="POST">
      <h2>Register form</h2>
      <p class="info">
         Please fill out the form to create your account.
      </p>

      <p class="have-account">
         <a href="employer-register.php">Sign up as an employer</a>
      </p>
      <br><br>
 
      <label>Email</label>
      <input type="text" name="email" value="<?php echo @$_POST['email'] ?>">

      <label>First name</label>
      <input type="text" name="first_name" value="<?php echo @$_POST['first_name'] ?>">

      <label>Last name</label>
      <input type="text" name="last_name" value="<?php echo @$_POST['last_name'] ?>">

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo @$_POST['phone'] ?>">

      <label>Birthdate</label>
      <input type="date" name="birthdate" value="<?php echo @$_POST['birthdate'] ?>">

      <label>Address</label>
      <textarea name="address"><?php echo @$_POST['address'] ?></textarea>

      <label>Bio</label>
      <textarea name="bio"><?php echo @$_POST['bio'] ?></textarea>

      <label>Experience</label>
      <textarea name="experience"><?php echo @$_POST['experience'] ?></textarea>

      <label>Password</label>
      <input type="password" name="password" value="<?php echo @$_POST['password'] ?>">

      <label>Confirm Password</label>
      <input type="password" name="confirm-password" value="<?php echo @$_POST['confirm-password'] ?>">
   
      <button type="submit" name="register">Register</button>
      
      <p class="have-account">
         <a href="login.php">Already have an account?</a>
      </p>
      <p class="have-account">
         <a href="index.php">back</a>
      </p>
      
      <p class="error"><?php echo @$response ?></p>     
   </form>

</body>
</html>
