<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "employer-functions.php";
?>
<?php 
   if(isset($_POST['register'])){
      $response = register($_POST['email'], $_POST['first_name'], $_POST['last_name'], $_POST['company'], $_POST['password'], $_POST['confirm-password'], $_POST['address'], $_POST['phone'], $_POST['company_description'], $_POST['industry']);
   }
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Become an Employer</title>
</head>
<body>
   
    <form action="" method="POST">
      <h2>Become an Employer</h2>
      <p class="info">
         Please fill out the form to create your account.
      </p>
 
      <label>Email</label>
      <input type="text" name="email" value="<?php echo @$_POST['email'] ?>">
      
      <label>First name</label>
      <input type="text" name="first_name" value="<?php echo @$_POST['first_name'] ?>">

      <label>Last name</label>
      <input type="text" name="last_name" value="<?php echo @$_POST['last_name'] ?>">

      <label>Company</label>
      <input type="text" name="company" value="<?php echo @$_POST['company'] ?>">

      <label>Address</label>
      <input type="text" name="address" value="<?php echo @$_POST['address'] ?>">

      <label>Phone Number</label>
      <input type="text" name="phone" value="<?php echo @$_POST['phone'] ?>">

      <label>Company Description</label>
      <textarea name="company_description"><?php echo @$_POST['company_description'] ?></textarea>

      <label>Industry</label>
      <select name="industry">
         <option value="Bar & Restaurants">Bar & Restaurants</option>
         <option value="Events & Festivals">Events & Festivals</option>
         <option value="Catering">Catering</option>
         <option value="Others">Others</option>
      </select>
   
      <label>Password</label>
      <input type="password" name="password" value="<?php echo @$_POST['password'] ?>">

      <label>Confirm Password</label>
      <input type="password" name="confirm-password" value="<?php echo @$_POST['confirm-password'] ?>">
   
      <button type="submit" name="register">Register</button>
      
      <p class="have-account">
         <a href="employer-login.php">Already have an account?</a>
      </p>
      <p class="have-account">
         <a href="index.php">back</a>
      </p>
      
      <p class="error"><?php echo @$response ?></p>		
   </form>

</body>
</html>
