<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "functions.php";

$user_id = $_SESSION['user']; // Assuming the user's ID is stored in the session
$unratedJobs = getUnratedJobsDetailsForUser($user_id);

if (isset($_POST['submit_rating'])) {
    // Assuming you have a similar function for users to rate employers
    $result = createUserJobRating($_POST['user_id'], $_POST['employer_id'], $_POST['job_id'], $_POST['rating'], $_POST['comment']);
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
   <title>Rate Your Employers</title>
</head>
<body>
   
    <h2>Rate Your Employers</h2>
    <a href="account.php" class="back-button">Back to Account</a>
    <?php if (isset($errorMessage)): ?>
        <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <?php foreach ($unratedJobs as $job): ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="rating-box">
                <h3>Rate your employer</h3>
                <p>Employer: <?php echo htmlspecialchars($job['employer_company']); ?></p>
                <p>Job Title: <?php echo htmlspecialchars($job['job_title']); ?></p>
                <p>Date: <?php echo htmlspecialchars($job['last_date']); ?></p>

                <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job['job_id']); ?>">
                <input type="hidden" name="employer_id" value="<?php echo htmlspecialchars($job['employer_id']); ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

                <label for="rating">Rating (1-5):</label>
                <input type="number" id="rating" name="rating" min="1" max="5" required>

                <label for="comment">Comment:</label>
                <textarea id="comment" name="comment" rows="4" required></textarea>

                <button type="submit" name="submit_rating">Send Rating</button>
            </div>
        </form>
    <?php endforeach; ?>

</body>
</html>
