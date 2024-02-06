<?php require "functions.php" ?>
<?php 
    session_start();
    if(!isset($_SESSION['user'])){
        header("Location: login.php");
        exit();
    }
    if(isset($_GET['logout'])){
        logout();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_application_id'])) {
      $applicationIdToDelete = $_POST['delete_application_id'];
      deleteApplication($applicationIdToDelete);
      header("Location: ".$_SERVER['PHP_SELF']); // Reload the current page
      exit();
  }

    // Assuming getMyApplications() is defined in functions.php
    $applications = getMyApplications($_SESSION['user']);

    // Loop through each application to fetch job dates
    foreach ($applications as $key => $application) {
        $jobId = $application['job_id']; // Make sure this matches the actual key name in your array
        $applications[$key]['dates'] = getJobDates($jobId); // Fetch dates for each job
    }

    $outstandingRatings = checkOutstandingRatings($_SESSION['user']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>User account</title>
</head>
<body>
 
   <div class="page">
      <div class="top-bar">
         <h2>Welcome <?php echo @$_SESSION['name'] ?></h2>
         <a href="?logout">Logout</a>
         <a href="change-password.php">Change password</a>
         <a href="index.php">Main Page</a>
      </div>

      
      <div style="margin-top: 20px;">
         <a href="upload-pics.php">upload pics</a>
      </div>
      <div style="margin-top: 20px;">
         <a href="update-user.php">update user info</a>
      </div>

      <?php if ($outstandingRatings > 0): ?>
            <div style="margin-top: 20px;">
                <a href="user-rating.php" class="btn">Rate Employees Now</a>
            </div>
       <?php endif; ?>

      <div class="applications-section">
         <br>
         <h3>My Applications</h3>
         <br><br>
         <?php foreach ($applications as $application): ?>
            <div class="application">
               <p><strong>Job Title:</strong> <?php echo $application['title']; ?></p>
               <p><strong>Employer:</strong> <?php echo $application['company']; ?></p>
               <p><strong>Dates:</strong></p>
               <ul>
                     <?php if(isset($application['dates']) && is_array($application['dates'])): ?>
                        <?php foreach ($application['dates'] as $dateInfo): ?>
                           <li>
                                 <?php echo htmlspecialchars($dateInfo['date']) . ": " . htmlspecialchars($dateInfo['start_time']) . " - " . htmlspecialchars($dateInfo['end_time']); ?>
                           </li>
                        <?php endforeach; ?>
                     <?php else: ?>
                        <li>No dates available</li>
                     <?php endif; ?>
               </ul>
               <p><strong>Status:</strong> <?php echo $application['state']; ?></p>
               <?php if ($application['state'] == 'pending'): ?>
        <!-- Delete button in a form -->
        <form method="post" action="">
            <input type="hidden" name="delete_application_id" value="<?php echo $application['application_id']; ?>">
            <button type="submit">Delete</button>
        </form>
    <?php endif; ?>
            </div>
            <br><br>
         <?php endforeach; ?>
      </div>

   </div>	
 
</body>
</html>
