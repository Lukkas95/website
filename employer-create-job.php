<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
require "employer-functions.php";

if(isset($_POST['submit'])){
    // Ensure that job_dates is sent as an array
    $jobDates = isset($_POST['job_dates']) ? $_POST['job_dates'] : [];
    $response = create_job($_POST['title'], $_POST['address'], $_POST['salary'], $_POST['description'], $_POST['district'], $jobDates);
    
    // Check if the job was created successfully, assuming $response indicates success somehow
    if ($response = "Job created successfully") { // You might need to adjust this condition based on how $response indicates success
        header('Location: employer-job-created.php'); // Redirect to the job created page
        exit; // Prevent further execution of the script
    }
}
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
   
    <form action="" method="POST">
      <h2>List a Job!</h2>
      <p class="info">
         Please fill out the form to list a Job
      </p>

      <label>Job Title</label>
      <input type="text" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">

      <label>Address</label>
      <input type="text" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">

      <label>Hourly Salary per hour</label>
      <input type="number" step="0.01" name="salary" value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">

      <label>Description</label>
      <textarea name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>

      <label>District</label>
      <select name="district">
         <!-- Replace with actual district options -->
         <option value="district1">District 1</option>
         <option value="district2">District 2</option>
         <!-- More districts -->
      </select>

      <div id="jobDatesSection">
         <label>Job Dates</label>
         <div class="job-date">
            <input type="date" name="job_dates[0][date]">
            <input type="time" name="job_dates[0][start_time]">
            <input type="time" name="job_dates[0][end_time]">
         </div>
      </div>
      <a href="#" id="addDate" class="button-style">Add Date</a>

      <button type="submit" name="submit">Create Job</button>

      <p class="have-account">
         <a href="employer-account.php">Back</a>
      </p>
      
      <p class="error"><?php echo @$response ?></p>      
   </form>

   <script>
      $(document).ready(function() {
         var dateIndex = 1;
         $("#addDate").click(function(e) {
            e.preventDefault();
            var newDateField = '<div class="job-date">' +
                               '<input type="date" name="job_dates[' + dateIndex + '][date]">' +
                               '<input type="time" name="job_dates[' + dateIndex + '][start_time]">' +
                               '<input type="time" name="job_dates[' + dateIndex + '][end_time]">' +
                               '</div>';
            $("#jobDatesSection").append(newDateField);
            dateIndex++;
         });
      });
   </script>

</body>
</html>
