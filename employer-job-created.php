<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   <title>List a Job</title>
</head>
<body>
   
    <form action="employer-account.php" method="POST">
      <h2>Job successfully created!</h2>
      <p class="info">
         Your job is now listed and our users can apply!
      </p>

      <button type="submit" name="submit">Go back to your account</button>
      
   </form>

</body>
</html>
