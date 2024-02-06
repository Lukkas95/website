<?php 
session_start();
require "employer-functions.php"; // Assume this file contains getJob, getJobDates, and getApplications functions.

// Check if the user is logged in, if not, log out
if (!isset($_SESSION['user'])) {
    header("Location: index.php"); // Redirect to logout page or login page
    exit();
}

$jobId = $_GET['id'] ?? null; // Retrieve the job ID from the URL

if (!$jobId) {
    echo "No job specified.";
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
//$applications = getApplications($jobId);

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

        <a href="delete-job.php?job_id=<?php echo $jobId; ?>" 
       onclick="return confirm('Are you sure you want to delete this job?');" 
       class="btn">Delete</a>

    <a href="toggle-job-visibility.php?job_id=<?php echo $jobId; ?>&action=<?php echo $jobDetails['hidden'] ? 'show' : 'hide'; ?>" 
       class="btn"><?php echo $jobDetails['hidden'] ? 'Show' : 'Hide'; ?></a>
    </div>
    <br><br><br>
    <div class="applications-container">
    <h2>Applications</h2>
    <br>
    <?php 
    $applications = getApplications($jobId);
    if (!empty($applications)): 
        foreach ($applications as $application): ?>
            <div class="application">
                <p>Applicant: <?php echo htmlspecialchars($application['first_name']) . ' ' . htmlspecialchars($application['last_name']); ?></p>
                <p>Rating: <?php echo $application['rating'] !== null ? htmlspecialchars($application['rating']) : 'No rating yet'; ?></p>
                <p>Application: <?php echo htmlspecialchars($application['letter']); ?></p>
                
                <a href="accept-application.php?app_id=<?php echo $application['app_id']; ?>" class="btn">Accept</a>
                <a href="reject-application.php?app_id=<?php echo $application['app_id']; ?>" class="btn">Reject</a>
            </div>
            <hr>
            <br>
    <?php endforeach; 
    else: ?>
        <p>No applications yet.</p>
    <?php endif; ?>
</div>

</body>
</html>


