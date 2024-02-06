<?php 
session_start();
require "employer-functions.php"; // Assume this file contains the necessary functions and the checkPics function.

// Check if the user is logged in, if not, redirect
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$jobId = $_GET['id'] ?? null; // Retrieve the job ID from the URL

if (!$jobId) {
    echo "No job specified.";
    exit();
}

// Handle Delete Job Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job_id'])) {
    deleteJob($_POST['delete_job_id']);
    header("Location: employer-account.php");
    exit();
}

// Handle Toggle Job Visibility Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_job_id'])) {
    toggleJobVisibility($_POST['toggle_job_id']);
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$jobId); // Reload the current page
    exit();
}

// Handle Decide on Application Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['decide_application_id'], $_POST['decision'])) {
    decideOnApplication($_POST['decide_application_id'], $_POST['decision']);
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$jobId); // Reload the current page
    exit();
}

// Fetch all job details
$jobDetails = getJob($jobId);
if (!$jobDetails) {
    echo "Job details not found.";
    exit();
}

// Fetch all dates for the job
$jobDates = getJobDates($jobId);

// Fetch all applications for the job
$applications = getApplications($jobId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Job Details</title>
</head>
<body>
    <div class="job-container">
    <h1><?php echo htmlspecialchars($jobDetails['title']); ?></h1>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($jobDetails['description']); ?></p>

        <h2>Dates</h2>
        <ul>
            <?php foreach ($jobDates as $date): ?>
                <li><?php echo htmlspecialchars($date['date']); ?>: from <?php echo htmlspecialchars($date['start_time']); ?> to <?php echo htmlspecialchars($date['end_time']); ?></li>
            <?php endforeach; ?>
        </ul>

        <!-- Delete Job -->
        <form action="" method="post">
        <input type="hidden" name="delete_job_id" value="<?php echo $jobId; ?>">
            <button type="submit" onclick="return confirm('Are you sure you want to delete this job?');">Delete</button>
        </form>

        <!-- Toggle Job Visibility -->
        <form action="" method="post">
            <input type="hidden" name="toggle_job_id" value="<?php echo $jobId; ?>">
            <input type="hidden" name="action" value="<?php echo $jobDetails['hidden'] ? 'show' : 'hide'; ?>">
            <button type="submit"><?php echo $jobDetails['hidden'] ? 'List' : 'Hide'; ?></button>
        </form>
    </div>
    <br><br><br>
    <div class="applications-container">
        <h2>Applications</h2>
        <br>
        <?php 
        if (!empty($applications)): 
            foreach ($applications as $application):
                // Call checkPics for each application
                $picNumbers = checkPics($application['user_id']);
                ?>
                <div class="application">
                <p>Applicant: <?php echo htmlspecialchars($application['first_name']) . ' ' . htmlspecialchars($application['last_name']); ?></p>
                    <p>Rating: <?php echo $application['rating'] !== null ? htmlspecialchars($application['rating']) : 'No rating yet'; ?></p>
                    <p>Application: <?php echo htmlspecialchars($application['letter']); ?></p>

                    <!-- Display pictures if any -->
                    <?php foreach ($picNumbers as $picPath): ?>
                        <img src="<?php echo htmlspecialchars($picPath); ?>" alt="Image" style="width: 200px; height: 200px; object-fit: cover;">
                    <?php endforeach; ?>

                    
                    <?php if ($application['state'] == 'pending'): ?>
                        <!-- Decide on Application -->
                        <form action="" method="post">
                            <input type="hidden" name="decide_application_id" value="<?php echo $application['application_id']; ?>">
                            <button type="submit" name="decision" value="accepted">Accept</button>
                            <button type="submit" name="decision" value="rejected">Reject</button>
                        </form>
                    <?php else: ?>
                        <p>Status: <?php echo htmlspecialchars($application['state']); ?></p>
                    <?php endif; ?>
                    

                </div>
                <hr>
                <br>
            <?php endforeach; 
        else: ?>
            <p>No applications yet.</p>
        <?php endif; ?>
    </div>
    <a href="employer-account.php" class="back-button">Go Back to Account</a>
    <br><br><br><br><br><br><br>


</body>
</html>


