
<?php 
session_start();
require "employer-functions.php";

if (!isset($_SESSION['user'])) {
    header("Location: employer-login.php");
    exit();
}
if (isset($_GET['logout'])) {
    logout();
}

$userJobs = getJobsByCreator($_SESSION['user']);

// Separating jobs into past and future
$pastJobs = array_filter($userJobs, function($job) {
    return $job['past'];
});

$futureJobs = array_filter($userJobs, function($job) {
    return !$job['past'];
});

// Call the checkOutstandingRatings function
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
            <h2>Welcome <?php echo @$_SESSION['name']; ?></h2>
            <a href="employer-change-password.php">Change password</a>
            <a href="employer-create-job.php">List a Job!</a>
            <a href="?logout">Logout</a>
        </div>

        <div style="margin-top: 20px;">
            <a href="update-employer.php">update user info</a>
        </div>

        <?php if ($outstandingRatings > 0): ?>
            <div style="margin-top: 20px;">
                <a href="employer-rating.php" class="btn">Rate Employees Now</a>
            </div>
        <?php endif; ?>

        <div class="jobs-list" style="margin-top: 20px;">
            <h3>Your Future Jobs</h3>
            <?php if (!empty($futureJobs)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($futureJobs as $job): ?>
                        <li style="padding-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                                    Description: <?php echo htmlspecialchars($job['description']); ?><br>
                                    Number of Applications: <?php echo countApplications($job['job_id']); ?><br>
                                </div>
                                <a href="job.php?id=<?php echo $job['job_id']; ?>" class="btn">Details</a>
                            </div>
                            <hr>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You have no future jobs listed.</p>
            <?php endif; ?>

            <br><br><br>
            <h3>Your Past Jobs</h3>
            <?php if (!empty($pastJobs)): ?>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($pastJobs as $job): ?>
                        <li style="padding-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($job['title']); ?></strong><br>
                                    Description: <?php echo htmlspecialchars($job['description']); ?><br>
                                    Number of Applications: <?php echo countApplications($job['job_id']); ?><br>
                                </div>
                                <a href="job.php?id=<?php echo $job['job_id']; ?>" class="btn">Details</a>
                            </div>
                            <hr>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>You have no past jobs listed.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

