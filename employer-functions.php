<?php 
require "config.php";

function dbConnect() {
    $mysqli = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);

    if ($mysqli->connect_errno != 0) {
        return FALSE;
    } else {
        return $mysqli;
    }
}

function register($email, $first_name, $last_name, $company, $password, $confirm_password, $address, $phone, $company_description, $industry) {
   $mysqli = dbConnect();
   if ($mysqli == false) {
       return false;
   }

    // Sanitize inputs
    $args = [
        "email" => filter_var(trim($email), FILTER_SANITIZE_EMAIL),
        "first_name" => filter_var(trim($first_name), FILTER_SANITIZE_STRING),
        "last_name" => filter_var(trim($last_name), FILTER_SANITIZE_STRING),
        "company" => filter_var(trim($company), FILTER_SANITIZE_STRING),
        "password" => trim($password),
        "confirm_password" => trim($confirm_password),
        "address" => filter_var(trim($address), FILTER_SANITIZE_STRING),
        "phone" => filter_var(trim($phone), FILTER_SANITIZE_STRING),
        "company_description" => filter_var(trim($company_description), FILTER_SANITIZE_STRING),
        "industry" => filter_var(trim($industry), FILTER_SANITIZE_STRING)
    ];

   $args = array_map(function ($value) {
       return trim($value);
   }, $args);

   foreach ($args as $value) {
       if (empty($value)) {
           return "All fields are required";
       }
   }

   foreach ($args as $value) {
       if (preg_match("/([<|>])/", $value)) {
           return "<> characters are not allowed";
       }
   }

   $args = array_map(function ($value) {
       return htmlspecialchars($value);
   }, $args);

    if (!filter_var($args["email"], FILTER_VALIDATE_EMAIL)) {
       return "Email is not valid";
    }

    if (mb_strlen($args["first_name"]) > 40) {
       return "The first name must be under 40 characters";
    }

    if (mb_strlen($args["last_name"]) > 40) {
      return "The last name must be under 40 characters";
    }

    if (mb_strlen($args["password"]) > 20) {
       return "The password must be under 20 characters";
    }

    if (mb_strlen($args["company"]) > 20) {
        return "The company name must be under 20 characters";
    }

    if (mb_strlen($args["address"]) > 50) {
        return "The address must be under 50 characters";
    }

    if (mb_strlen($args["phone"]) > 20) {
        return "Invalid phone number";
    }

    if ($args["password"] != $args["confirm_password"]) {
        return "The passwords don't match";
    }

    if ($stmt = $mysqli->prepare("SELECT email FROM employers WHERE email = ?")) {
        $stmt->bind_param("s", $args["email"]);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return "Email already exists";
        }
        $stmt->close();
    }

    // Generate UUID for employer_id
    $employer_id = generateUUID();

    // Create sign up date
    $sign_up_date = date('Y-m-d H:i:s'); // Current date and time

    // Generate verification code
    $verification_code = createVerificationCode();
    if (!sendVerificationCode($args["email"], $verification_code)) {
        return "Error sending verification code. Please try again";
    }

    // Hash password
    $hashed_password = password_hash($args["password"], PASSWORD_DEFAULT);

    // Insert employer into database
    $stmt = $mysqli->prepare("INSERT INTO employers (employer_id, email, first_name, last_name, company, address, phone, company_description, industry, password, sign_up_date, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssss", $employer_id, $args["email"], $args["first_name"], $args["last_name"], $args["company"], $args["address"], $args["phone"], $args["company_description"], $args["industry"], $hashed_password, $sign_up_date, $verification_code);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        return "An error occurred. Please try again";
    } else {
        $_SESSION['email'] = $args["email"];
        $_SESSION['employer_id'] = $employer_id;
        $_SESSION['verification-code'] = $verification_code;
        header("Location: employer-auth.php");
        exit();
    }
}

function generateUUID() {
    if (function_exists('random_bytes')) {
        $data = random_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback for older PHP versions
    // Note: This method is less secure and should be replaced with a better one in production
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function createVerificationCode() {
   $str = "0123456789";
   $random = str_shuffle($str);
   $verification_code = substr($random, 0, 6);
   return $verification_code;
}

function sendVerificationCode($email, $verification_code) {
   $subject = "Verification Code";
   $body = "Verification code" . "\r\n" . $verification_code;

   $headers = "MIME-Version: 1.0" . "\r\n";
   $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
   $headers .= "From: support@lynkjob.com \r\n"; 
   return mail($email, $subject, $body, $headers);
}


function verifyEmail($verification_code) {
   $mysqli = dbConnect();
   if ($mysqli == false) {
       return false;
   }

   $verification_code = trim($verification_code);
   if ($verification_code == "") {
       return "Please enter the 6 digits code";
   }

   if (!preg_match("/^\d{6}$/", $verification_code)) {
       return "The string expects a 6 digits number";
   }

   $res = $mysqli->query("SELECT verification_code FROM employers WHERE verification_code = '$verification_code'");
   if ($res->num_rows != 1) {
       return "Wrong verification code";
   } else {
       $update = $mysqli->query("UPDATE employers SET email_status = 'verified' WHERE verification_code = '$verification_code'");
       if ($mysqli->affected_rows != 1) {
           return "something went wrong. Please try again";
       } else {
           header("Location: employer-login.php");
           exit();
       }
   }
}


function login($email, $password) {
    $mysqli = dbConnect();
    if (!$mysqli) {
        return false;
    }

    $email = trim($email);
    $password = trim($password);
    
    if ($email == "" || $password == "") {
        return "Both fields are required";
    }

    $sql = "SELECT * FROM employers WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data == NULL) {
        return "Wrong email or password";
    }

    if ($data["email_status"] == "pending") {
        $_SESSION['email'] = $data["email"];
        $_SESSION['verification-code'] = $data["verification_code"];

        sendVerificationCode($data["email"], $data["verification_code"]);
        header("location: employer-auth.php");
        exit();
    }

    if (!password_verify($password, $data["password"])) {
        return "Wrong email or password";
    } else {
        $_SESSION["user"] = $data["employer_id"];
        $_SESSION["industry"] = $data["industry"];
        // Create the name string by concatenating first_name and last_name
        $name = $data["first_name"] . " " . $data["last_name"];
        $_SESSION["name"] = $name;
        header("location: employer-account.php");
        exit();
    }
} // Closing the login function.



function passwordReset($email) {
   $mysqli = dbConnect();
   if (!$mysqli) {
       return false;
   }

   $email = trim($email);
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
       return "Email is not valid";
   }

   $stmt = $mysqli->prepare("SELECT email FROM employers WHERE email = ?");
   $stmt->bind_param("s", $email);
   $stmt->execute();
   $result = $stmt->get_result();
   $data = $result->fetch_assoc();

   if ($data == NULL) {
       return "Email doesn't exist in the database";
   }

   $str = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
   $password_length = 7;
   $shuffled_str = str_shuffle($str);
   $new_pass = substr($shuffled_str, 0, $password_length);

   $subject = "Password recovery";
   $body = "You can log in with your new password" . "\r\n" . $new_pass;

   $headers = "MIME-Version: 1.0" . "\r\n";
   $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
   $headers .= "From: support@lynkjob.com \r\n";

   $sent = mail($email, $subject, $body, $headers);
   if ($sent == false) {
       return "Email not sent. Please try again";
   } else {
       $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

       $stmt = $mysqli->prepare("UPDATE employers SET password = ? WHERE email = ?");
       $stmt->bind_param("ss", $hashed_password, $email);
       $stmt->execute();

       if ($stmt->affected_rows != 1) {
           return "There was a connection error, please try again.";
       } else {
           return "success";
       }			
   }
} // Closing the passwordReset function.

function logout() {
   session_destroy();
   header("location: index.php");
   exit();
}

function changePassword($password, $confirm_password){
    $mysqli = dbConnect();
    if ($mysqli == false) {
        return false;
    }
 
    $args = [
        "password" => $password,
        "confirm-password" => $confirm_password
    ];
 
    $args = array_map(function ($value) {
        return trim($value);
    }, $args);
 
    foreach ($args as $value) {
        if (empty($value)) {
            return "All fields are required";
        }
    }
 
    foreach ($args as $value) {
        if (preg_match("/([<|>])/", $value)) {
            return "<> characters are not allowed";
        }
    }
 
    $args = array_map(function ($value) {
        return htmlspecialchars($value);
    }, $args);
 
    if (mb_strlen($args["password"]) > 20) {
        return "The password must be under 20 characters";
    }

    if ($args["password"] != $args["confirm-password"]) {
        return "The passwords don't match";
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $employer_id = $_SESSION['user'];

    $stmt = $mysqli->prepare("UPDATE employers SET password = ? WHERE employer_id = ?");
    $stmt->bind_param("ss", $hashed_password, $employer_id);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        return "There was a connection error, please try again.";
    } else {
        return "success";
    }	
}

function create_job($title, $address, $salary, $description, $district, $jobDates) {
    $mysqli = dbConnect();
    if ($mysqli == false) {
        return "Database connection failed";
    }

    if (empty($jobDates)) {
        return "At least one job date must be provided";
    }

    // Sanitize inputs
    $args = [
        "title" => filter_var(trim($title), FILTER_SANITIZE_STRING),
        "address" => filter_var(trim($address), FILTER_SANITIZE_STRING),
        "salary" => filter_var(trim($salary), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
        "description" => filter_var(trim($description), FILTER_SANITIZE_STRING),
        "district" => filter_var(trim($district), FILTER_SANITIZE_STRING),
    ];

    foreach ($args as $key => $value) {
        if (empty($value) && $key !== 'description') { // Description can be empty
            return "All fields are required";
        }
    }

    // Start transaction
    $mysqli->begin_transaction();

    try {
        // Prepare job entry
        $employer_id = $_SESSION['user']; // Assuming this is the employer's UUID
        $industry = $_SESSION['industry']; // Assuming industry is stored in session
        $created_date = date('Y-m-d'); // Current date
        $job_id = generateUUID(); // Assuming this function exists and generates a unique UUID

        // Insert job into jobs table
        $stmt = $mysqli->prepare("INSERT INTO jobs (job_id, employer_id, title, address, salary, description, industry, district, created_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $job_id, $employer_id, $args["title"], $args["address"], $args["salary"], $args["description"], $industry, $args["district"], $created_date);
        $stmt->execute();

        if ($stmt->affected_rows != 1) {
            throw new Exception("Error inserting job");
        }

        // Insert dates into job_dates table
        foreach ($jobDates as $date) {
            $stmt = $mysqli->prepare("INSERT INTO job_dates (job_dates_id, job_id, date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            $job_dates_id = generateUUID(); // Generate a new UUID for each date
            $stmt->bind_param("sssss", $job_dates_id, $job_id, $date['date'], $date['start_time'], $date['end_time']);
            $stmt->execute();

            if ($stmt->affected_rows != 1) {
                throw new Exception("Error inserting job dates");
            }
        }

        // Commit transaction
        $mysqli->commit();
        return "Job created successfully";

    } catch (Exception $e) {
        // Rollback transaction
        $mysqli->rollback();
        return "An error occurred: " . $e->getMessage();
    } finally {
        $mysqli->close();
    }
}


function getJobsByCreator($employer_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $query = "SELECT j.*, 
                     EXISTS (
                         SELECT 1 
                         FROM job_dates jd 
                         WHERE jd.job_id = j.job_id 
                         AND jd.date < CURDATE()
                     ) AS past 
              FROM jobs j 
              WHERE j.deleted = 0 AND j.employer_id = ?";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $employer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $mysqli->close();

    return $jobs;
}


function deleteJob($jobId) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        return "Database connection error";
    }

    // Update the statement to set the 'deleted' field to true (1)
    $stmt = $mysqli->prepare("UPDATE jobs SET deleted = 1 WHERE job_id = ?");
    if (!$stmt) {
        $mysqli->close();
        return "Statement preparation error";
    }

    $stmt->bind_param("s", $jobId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return true; // Successful soft delete
    } else {
        $stmt->close();
        $mysqli->close();
        return false; // No rows updated, possibly wrong job ID or already deleted
    }
}


function countApplications($jobId) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        return "Database connection error";
    }

    $stmt = $mysqli->prepare("SELECT COUNT(*) AS application_count FROM applications WHERE applications.deleted = 0 AND job_id = ?");
    $stmt->bind_param("s", $jobId);
    $stmt->execute();
    $stmt->bind_result($applicationCount);
    $stmt->fetch();

    $stmt->close();
    $mysqli->close();

    return $applicationCount;
}

function getJob($job_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $stmt = $mysqli->prepare("SELECT * FROM jobs WHERE deleted = 0 AND job_id = ?");
    $stmt->bind_param("s", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc(); // Corrected line

    $stmt->close();
    $mysqli->close();

    return $job; // This will now return a single job as an associative array.
}


function getJobDates($job_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $stmt = $mysqli->prepare("SELECT * FROM job_dates WHERE job_id = ?");
    $stmt->bind_param("s", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job_dates = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $mysqli->close();

    return $job_dates;
}

function getApplications($job_id) {
    $mysqli = dbConnect();
    if (!$mysqli) {
        // Log error, handle the error appropriately
        return null; // Or handle the error as per your application's design
    }

    $stmt = $mysqli->prepare("SELECT * FROM applications JOIN users ON applications.user_id = users.user_id WHERE applications.deleted = 0 AND job_id = ?");
    if (!$stmt) {
        // Log error, handle the error appropriately
        $mysqli->close();
        return null; // Or handle the error as per your application's design
    }

    $stmt->bind_param("s", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $applications = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $mysqli->close();

    return $applications;
}

function toggleJobVisibility($jobId) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        return "Database connection error";
    }

    // First, get the current visibility state of the job
    $query = $mysqli->prepare("SELECT hidden FROM jobs WHERE job_id = ?");
    if (!$query) {
        $mysqli->close();
        return "Statement preparation error for SELECT";
    }

    $query->bind_param("s", $jobId);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows == 0) {
        $query->close();
        $mysqli->close();
        return "Job not found";
    }

    $row = $result->fetch_assoc();
    $currentVisibility = $row['hidden'];

    // Now toggle the visibility
    $newVisibility = $currentVisibility ? 0 : 1; // Assuming 1 for visible and 0 for hidden

    $stmt = $mysqli->prepare("UPDATE jobs SET hidden = ? WHERE job_id = ?");
    if (!$stmt) {
        $mysqli->close();
        return "Statement preparation error for UPDATE";
    }

    $stmt->bind_param("is", $newVisibility, $jobId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $mysqli->close();
        return true; // Successful toggle
    } else {
        $stmt->close();
        $mysqli->close();
        return false; // No rows updated, possibly wrong job ID
    }
}

function decideOnApplication($applicationId, $decision) {
    $mysqli = dbConnect();
    if (!$mysqli) {
        return "Database connection error";
    }

    // Trim the decision and ensure it matches the ENUM values
    $decision = trim($decision);

    $stmt = $mysqli->prepare("UPDATE applications SET state = ? WHERE application_id = ?");
    if (!$stmt) {
        $mysqli->close();
        return "Statement preparation error: " . $mysqli->error;
    }

    $stmt->bind_param("ss", $decision, $applicationId);
    $stmt->execute();

    if ($stmt->error) {
        $error = $stmt->error;
        $stmt->close();
        $mysqli->close();
        return "SQL error: " . $error;
    }

    if ($stmt->affected_rows > 0) {
        if ($decision === 'accepted') {
            // Assuming sendContactMails is defined and available
            sendContactMails($applicationId);
        }
        $stmt->close();
        $mysqli->close();
        return true;
    } else {
        $stmt->close();
        $mysqli->close();
        return false;
    }
}

    

function checkOutstandingRatings($employer_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $query = "SELECT COUNT(DISTINCT j.job_id) AS outstanding_ratings
              FROM jobs j
              JOIN (
                  SELECT jd.job_id, MAX(jd.date) AS last_date
                  FROM job_dates jd
                  GROUP BY jd.job_id
              ) jd ON j.job_id = jd.job_id
              LEFT JOIN employer_ratings er ON j.job_id = er.job_id AND j.employer_id = er.employer_id
              JOIN applications a ON j.job_id = a.job_id
              WHERE j.employer_id = ? 
              AND jd.last_date < CURDATE()
              AND a.state = 'accepted'
              AND er.job_id IS NULL;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $employer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $mysqli->close();

    return $row['outstanding_ratings'];
}

function getUnratedJobsDetails($employer_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $query = "SELECT j.job_id, j.employer_id, a.user_id, j.title AS job_title, jd.last_date, u.first_name, u.last_name
              FROM jobs j
              JOIN (
                  SELECT jd.job_id, MAX(jd.date) AS last_date
                  FROM job_dates jd
                  GROUP BY jd.job_id
              ) jd ON j.job_id = jd.job_id
              LEFT JOIN employer_ratings er ON j.job_id = er.job_id AND j.employer_id = er.employer_id
              JOIN applications a ON j.job_id = a.job_id
              JOIN users u ON a.user_id = u.user_id
              WHERE j.employer_id = ? 
              AND jd.last_date < CURDATE()
              AND a.state = 'accepted'
              AND er.job_id IS NULL;";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $employer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $mysqli->close();

    return $jobs;
}

function createJobRating($user_id, $employer_id, $job_id, $rating, $comment) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error";
    }

    // Sanitize inputs
    $employer_rating_id = generateUUID();
    $user_id = filter_var(trim($user_id), FILTER_SANITIZE_STRING);
    $employer_id = filter_var(trim($employer_id), FILTER_SANITIZE_STRING);
    $job_id = filter_var(trim($job_id), FILTER_SANITIZE_STRING);
    $comment = filter_var(trim($comment), FILTER_SANITIZE_STRING);

    // Validate rating
    if (!(is_numeric($rating) && ($rating == -1 || ($rating >= 1 && $rating <= 5)))) {
        return "Invalid rating. Rating must be between 1 and 5";
    }

    // Insert job rating into database
    $stmt = $mysqli->prepare("INSERT INTO employer_ratings (employer_rating_id, user_id, employer_id, job_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        return "An error occurred preparing the statement.";
    }

    $stmt->bind_param("ssssis", $employer_rating_id, $user_id, $employer_id, $job_id, $rating, $comment);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        return "An error occurred. Please try again";
    }

    updateAverageUserRating($user_id);

    $stmt->close();
    $mysqli->close();
    return true; // Successfully created the job rating
}

function updateAverageUserRating($user_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error");
    }

    // Query to get the average rating
    $avgQuery = "SELECT AVG(rating) AS average_rating FROM employer_ratings WHERE user_id = ?";
    $avgStmt = $mysqli->prepare($avgQuery);
    if (!$avgStmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $avgStmt->bind_param("s", $user_id);
    $avgStmt->execute();
    $result = $avgStmt->get_result();
    $avgRow = $result->fetch_assoc();
    $avgRating = $avgRow['average_rating'];

    // Query to update the user's average rating
    $updateQuery = "UPDATE users SET rating = ? WHERE user_id = ?";
    $updateStmt = $mysqli->prepare($updateQuery);
    if (!$updateStmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $updateStmt->bind_param("ds", $avgRating, $user_id);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 0) {
        // Handle the case where the update didn't affect any rows
    }

    $avgStmt->close();
    $updateStmt->close();
    $mysqli->close();
}

function checkPics($user_id) {
    $targetDirectory = "images/";
    $fileExtensionsAllowed = ['jpg', 'jpeg', 'png', 'gif'];
    $existingImages = [];

    for ($i = 1; $i <= 4; $i++) {
        foreach ($fileExtensionsAllowed as $ext) {
            $filePath = $targetDirectory . $user_id . "_" . $i . "." . $ext;
            if (file_exists($filePath)) {
                $existingImages[] = $filePath;
                break; // Break this loop if the image is found
            }
        }
    }

    return $existingImages;
}




function sendContactMails($applicationId) {
    // Database connection
    $db = dbConnect();
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Step 1: Join applications table with jobs table and get user_id and employer_id
    $query = "SELECT a.user_id, j.employer_id 
              FROM applications a 
              JOIN jobs j ON a.job_id = j.job_id 
              WHERE a.application_id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();

    if (!$application) {
        echo "No application found with ID: $applicationId";
        return;
    }

    $userId = $application['user_id'];
    $employerId = $application['employer_id'];

    // Step 2: Get user details
    $query = "SELECT first_name, last_name, email, phone FROM users WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "No user found with ID: $userId";
        return;
    }

    // Step 3: Get employer details
    $query = "SELECT first_name, email FROM employers WHERE employer_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $employerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $employer = $result->fetch_assoc();

    if (!$employer) {
        echo "No employer found with ID: $employerId";
        return;
    }

    // Step 4: Send an email to the user
    $toUser = $user['email'];
    $subjectUser = "Your Application Status";
    $messageUser = "Hi " . $user['first_name'] . ",\n\nYour application was just accepted! Log into your Lynk account for more info. Your future employer will soon get in touch with you.\n\nCheers,\nYour Lynk-team";
    $headersUser = "From: noreply@lynk.com"; // Replace with your actual domain

    if (mail($toUser, $subjectUser, $messageUser, $headersUser)) {
        echo "Email sent successfully to " . $user['email'];
    } else {
        echo "Email sending failed.";
    }

    // Step 5: Send an email to the employer
    $toEmployer = $employer['email'];
    $subjectEmployer = "Application Accepted";
    $messageEmployer = "Hi " . $employer['first_name'] . ",\n\nThank you for accepting an application! Here are the contact details of " . $user['first_name'] . " " . $user['last_name'] . ":\nPhone: " . $user['phone'] . "\nE-mail: " . $user['email'] . "\nPlease reach out to " . $user['first_name'] . " to discuss the details.\n\nCheers,\nYour Lynk-team";
    $headersEmployer = "From: noreply@lynk.com"; // Replace with your actual domain

    if (mail($toEmployer, $subjectEmployer, $messageEmployer, $headersEmployer)) {
        echo "Email sent successfully to " . $employer['email'];
    } else {
        echo "Email sending failed.";
    }

    // Close statement and connection
    $stmt->close();
    $db->close();
}

function getEmployerDetails($employer_id) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error.";
    }

    $employer_id = htmlspecialchars(trim($employer_id));

    // Fetch employer details
    if ($stmt = $mysqli->prepare("SELECT employer_id, phone, address, company_description, industry FROM employers WHERE employer_id = ?")) {
        $stmt->bind_param("s", $employer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($employer = $result->fetch_assoc()) {
            $stmt->close();
            return $employer;
        } else {
            $stmt->close();
            return "employer not found.";
        }
    } else {
        return "An error occurred. Please try again.";
    }
}

function updateProfile($employer_id, $address, $phone, $company_description, $industry) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error.";
    }

    // Trim and sanitize input
    $employer_id = htmlspecialchars(trim($employer_id));
    $phone = htmlspecialchars(trim($phone));
    $address = htmlspecialchars(trim($address));
    $company_description = htmlspecialchars(trim($company_description));
    $industry = htmlspecialchars(trim($industry));

    // Update employer in database
    if ($stmt = $mysqli->prepare("UPDATE employers SET phone = ?, address = ?, company_description = ?, industry = ? WHERE employer_id = ?")) {
        $stmt->bind_param("sssss", $phone, $address, $company_description, $industry, $employer_id);
        if (!$stmt->execute()) {
            return "An error occurred. Please try again.";
        }
        $stmt->close();
        return "Profile updated successfully.";
    } else {
        return "An error occurred. Please try again.";
    }
}
?>












