<?php 
session_start(); // Start the session at the beginning of the script
ini_set('display_errors', 1);
require "functions.php";

// Check if the user session variable is set
if (!isset($_SESSION['user'])) {
    // Redirect to login page or show an error message
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']; // Using session variable for user's unique identifier

if(isset($_POST['update'])){
   // Assuming updateProfile function takes the user's profile details as parameters
   $response = updateProfile($user_id, $_POST['phone'], $_POST['address'], $_POST['bio']);
   // Redirect or display a success message after updating the profile
   // You may want to refresh the user details in the session if the email can be updated
}

// Fetch current user details
$userDetails = getUserDetails($user_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Edit Profile</title>
</head>
<body>
   
    <form action="" method="POST">
      <h2>Edit Profile</h2>
      <p class="info">
         Update your profile information.
      </p>

      <label>Phone</label>
      <input type="text" name="phone" value="<?php echo htmlspecialchars($userDetails['phone']) ?>">

      <label>Address</label>
      <textarea name="address"><?php echo htmlspecialchars($userDetails['address']) ?></textarea>

      <label>Bio</label>
      <textarea name="bio"><?php echo htmlspecialchars($userDetails['bio']) ?></textarea>
   
      <button type="submit" name="update">Update Profile</button>


      <br> <br>
      <a href="account.php">back</a>
      
      <p class="error"><?php echo @$response ?></p>     
   </form>

</body>
</html>
