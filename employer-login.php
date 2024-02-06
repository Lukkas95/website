<?php require "employer-functions.php" ?>
<?php 
   if(isset($_POST['login'])){
      $response = login($_POST['email'], $_POST['password']);
   }
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Employer Login</title>
</head>
<body>
<form action="" method="post" autocomplete="off">
      <h2>Employer Login</h2>
      <p class="info">
         Please enter your email and password to log-in.
      </p>
 
      <label>E-mail</label>
      <input type="text" name="email" value="<?php echo @$_POST['email'] ?>">
   
      <label>Password</label>
      <input type="text" name="password" value="<?php echo @$_POST['password'] ?>">
   
      <button type="submit" name="login">Login</button>
      
      <p class="forgot-password">
         <a href="employer-forgot-password.php">Forgot your password?</a>
      </p>
      <p class="create-account">
         <a href="employer-register.php">Create an account</a>
      </p>
      <p class="error"><?php echo @$response ?></p>		
   </form>
</body>
</html>	