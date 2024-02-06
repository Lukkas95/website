<?php 
session_start();
require "functions.php";

$jobId = $_GET['id'] ?? null;

// Fetch Job Details
$jobDetails = $jobId ? getJobDetails($jobId) : null;
$jobDates = $jobId ? getJobDates($jobId) : null;
$employerDetails = null;

if ($jobDetails && isset($jobDetails['employer_id'])) {
    $employer_id = $jobDetails['employer_id'];
    $employerDetails = getEmployerDetails($employer_id);
}

// Handle the form submission if user is logged in
$response = '';
if(isset($_SESSION['user']) && isset($_POST['send'])){
    $userId = $_SESSION['user'];
    $response = createApplication($jobId, $userId, $_POST['applicationText']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Application Page</title>
</head>
<body>
    <div class="application-container">
        <?php if (isset($_SESSION['user'])): ?>
            <form action="" method="POST" class="application-form">
                <?php if ($jobDetails && $employerDetails): ?>
                    <div class="job-details">
                        <h2><?php echo htmlspecialchars($jobDetails['title']); ?></h2>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($jobDetails['description']); ?></p>
                        <p><strong>Wage:</strong> <?php echo htmlspecialchars($jobDetails['salary']); ?></p>

                        <?php if ($jobDates): ?>
                            <?php foreach ($jobDates as $date): ?>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($date['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($date['start_time']); ?> - <?php echo htmlspecialchars($date['end_time']); ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <p><strong>Employer:</strong> <?php echo htmlspecialchars($employerDetails['first_name']); ?> <?php echo htmlspecialchars($employerDetails['last_name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($employerDetails['address']); ?></p>
                    </div>
                <?php endif; ?>
                
                <br><br>
                <label for="applicationText">Apply here:</label>
                <textarea id="applicationText" name="applicationText"><?php echo @$_POST['applicationText'] ?></textarea>

                <button type="submit" name="send">Send</button>
                <button type="button" onclick="location.href='index.php'">Back</button>

                <p class="error"><?php echo $response ?></p>     
            </form>
        <?php else: ?>
            <form action="" method="POST" class="application-form">
                <?php if ($jobDetails && $employerDetails): ?>
                    <div class="job-details">
                        <h2><?php echo htmlspecialchars($jobDetails['title']); ?></h2>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($jobDetails['description']); ?></p>
                        <p><strong>Wage:</strong> <?php echo htmlspecialchars($jobDetails['salary']); ?></p>

                        <?php if ($jobDates): ?>
                            <?php foreach ($jobDates as $date): ?>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($date['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($date['start_time']); ?> - <?php echo htmlspecialchars($date['end_time']); ?></p>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <p><strong>Employer:</strong> <?php echo htmlspecialchars($employerDetails['first_name']); ?> <?php echo htmlspecialchars($employerDetails['last_name']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($employerDetails['address']); ?></p>
                    </div>
                <?php endif; ?>

                <br><br>
                <p>You must be logged in to apply.</p>
                <button type="button" onclick="location.href='login.php'">Log In</button>
                <button type="button" onclick="location.href='index.php'">Back</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
