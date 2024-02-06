<?php require "functions.php" ?>
<?php 
   if(!isset($_SESSION['verification-code'])){
      header("Location: login.php");
      exit(); 	
   }
   if(isset($_POST['verify'])){
      $response = verifyEmail($_POST['verification-code']);
   }
   // This if statement runs if the user clicks on the resent verification code link.
   if(isset($_GET['resend-verification-code'])){
      if(sendVerificationCode($_SESSION['email'], $_SESSION['verification-code'])){
         $success = "Please go to your email account and copy the 6 digits code";
      }else{
         $response = "Something went wrong, please try again";
      }
   }
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Email verification</title>
</head>
<body>
   
<form action="" method="post">
      <h2>Email verification</h2>
      <p class="info">
         Please go to your email account and type in the field below the
         <strong>6 digits verification code</strong> that you received.	
      </p>
      <p class="info">
         After we verify your email you will be redirected to the login page
         to log in to your account.
      </p>
 
      <label>Verification code</label>
      <input type="text" name="verification-code">
      
      <button type="submit" name="verify">Verify</button>
 
      <p class="resend-code">
         <a href="?resend-verification-code">Resend verification code</a>
      </p>
      
      <p class="error"><?php echo @$response ?></p>
      <p class="success"><?php echo @$success ?></p>		
   </form>
 
</body>
</html>