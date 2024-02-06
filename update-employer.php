<?php 
session_start(); // Start the session at the beginning of the script
require "employer-functions.php";

// Check if the user session variable is set
if (!isset($_SESSION['user'])) {
    // Redirect to login page or show an error message
    header('Location: login.php');
    exit;
}

$employer_id = $_SESSION['user']; // Using session variable for employer's unique identifier

if(isset($_POST['update'])){
   // Assuming updateProfile function takes the employer's profile details as parameters
   $response = updateProfile($employer_id, $_POST['address'], $_POST['phone'], $_POST['company_description'], $_POST['industry']);
   // Redirect or display a success message after updating the profile
}

// Fetch current employer details
$employerDetails = getEmployerDetails($employer_id);
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
      <input type="text" name="phone" value="<?php echo htmlspecialchars($employerDetails['phone']) ?>">

      <label>Address</label>
      <textarea name="address"><?php echo htmlspecialchars($employerDetails['address']) ?></textarea>

      <label>Company Description</label>
      <textarea name="company_description"><?php echo htmlspecialchars($employerDetails['company_description']) ?></textarea>

      <label>Industry</label>
      <select name="industry">
          <!-- Example industries, replace with your actual options -->
          <option value="<?php echo htmlspecialchars($employerDetails['industry']) ?>" selected><?php echo htmlspecialchars($employerDetails['industry']) ?></option>
          <option value="Bar & Restaurants">Bar & Restaurants</option>
         <option value="Events & Festivals">Events & Festivals</option>
         <option value="Catering">Catering</option>
         <option value="Others">Others</option>
      </select>
   
      <button type="submit" name="update">Update Profile</button>

      <br><br>
      <a href="employer-account.php">Back</a>
      
      <p class="error"><?php echo @$response ?></p>     
   </form>

</body>
</html>
