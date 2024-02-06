<?php
    session_start();
?>
<?php require "functions.php" ?>
<?php
    if(isset($_GET['logout'])){
        logout();
     }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- navbar section -->
<header class="navbar-section">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="bi bi-chat"></i> Lynk!</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="account.php">my account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="?logout">logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">signup</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="employer.php">employer</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<!-- rest of your HTML content -->

</body>
</html>


    <!-- hero section  -->

    <section id="home" class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-12 col-sm-12 text-content">
                <h1>Website Under Construction</h1>
                <p>We'll be ready soon! Click here for our current site</p>
            </div>

            <!-- Button, wrapped in a div for alignment -->
            <div class="col-lg-4 col-md-12 col-sm-12 d-flex align-items-center">
                <button class="btn"><a href="https://www.lynkjob.co">Go to linkjob.co</a></button>
            </div>

            <!-- Image -->
            <div class="col-lg-4 col-md-12 col-sm-12">
                <img src="images/lynx.jpg" alt="" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </div>
</section>




    
        
    <!-- Job Listings Section -->
    <section class="job-listings" id="job-listings">
        <div class="container">
            <div class="row">
                <?php
                $jobs = getAllJobs();
                foreach ($jobs as $job): ?>
                    <div class="col-md-4 mb-4">
                        <div class="job-card p-3">
                            <h5><?php echo htmlspecialchars($job['title']); ?></h5>
                            <p>Company: <?php echo htmlspecialchars($job['company']); ?></p>
                            <p>Wage: <?php echo htmlspecialchars($job['salary']); ?> per hour</p>
                            <!-- Button to view job details -->
                            <a href="job-details.php?id=<?php echo htmlspecialchars($job['job_id']); ?>" class="btn btn-primary">See Job Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>






    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>

</html>