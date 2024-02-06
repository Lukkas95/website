<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "employer-functions.php";

$employer_id = $_SESSION['user']; // Assuming the employer's ID is stored in the session
$unratedJobs = getUnratedJobsDetails($employer_id);

if (isset($_POST['submit_rating'])) {
    $result = createJobRating($_POST['user_id'], $employer_id, $_POST['job_id'], $_POST['rating'], $_POST['comment']);
    if ($result === true) {
        header("Location: ".$_SERVER['PHP_SELF']); // Reload the page
        exit();
    }
    $errorMessage = $result; // Store the error message if the function returned an error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="styles.css">
   <title>Rate Your Employees</title>
</head>
<body>
   
    <h2>Rate Your Employees</h2>
    <a href="employer-account.php" class="back-button">Back to Account</a>
    <?php if (isset($errorMessage)): ?>
        <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <?php foreach ($unratedJobs as $job): ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="rating-box">
                <h3>Rate your employee</h3>
                <p><?php echo htmlspecialchars($job['first_name']) . " " . htmlspecialchars($job['last_name']); ?></p>
                <p>Job Title: <?php echo htmlspecialchars($job['job_title']); ?></p>
                <p>Date: <?php echo htmlspecialchars($job['last_date']); ?></p>

                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['job_id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($job['user_id']); ?>">

                <label for="rating">Rating (1-5):</label>
                <input type="number" id="rating" name="rating" min="-1" max="5" required>

                <label for="comment">Comment:</label>
                <textarea id="comment" name="comment" rows="4" required></textarea>

                <button type="submit" name="submit_rating">Send Rating</button>
            </div>
        </form>
    <?php endforeach; ?>

</body>
</html>
