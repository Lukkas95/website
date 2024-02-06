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

function register($email, $first_name, $last_name, $password, $confirm_password, $phone, $birthdate, $address, $bio, $experience) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error.";
    }

    // Trim and sanitize input
    $email = htmlspecialchars(trim($email));
    $first_name = htmlspecialchars(trim($first_name));
    $last_name = htmlspecialchars(trim($last_name));
    $phone = htmlspecialchars(trim($phone));
    $birthdate = htmlspecialchars(trim($birthdate));
    $address = htmlspecialchars(trim($address));
    $bio = htmlspecialchars(trim($bio));
    $experience = htmlspecialchars(trim($experience));
    $password = trim($password);
    $confirm_password = trim($confirm_password);

    // Validation checks
    if (empty($email) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password) || empty($phone) || empty($birthdate) || empty($address) || empty($bio) || empty($experience)) {
        return "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        return "Passwords do not match.";
    }

    // Validate birthdate
    $birthdate_timestamp = strtotime($birthdate);
    $hundred_years_ago = strtotime('-100 years');
    $current_time = time();
    if ($birthdate_timestamp > $current_time || $birthdate_timestamp < $hundred_years_ago) {
        return "Invalid birthdate. Date must be in the past and not older than 100 years.";
    }

    // Check if email already exists
    if ($stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            return "Email already exists.";
        }
        $stmt->close();
    }

    // Generate UUID
    $user_id = generateUUID();

    // Create sign up date
    $sign_up_date = date('Y-m-d H:i:s', $current_time);

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $verification_code = createVerificationCode();
    if (!sendVerificationCode($email, $verification_code)) {
        return "Error sending verification code. Please try again";
    }

    // Insert new user into database
    if ($stmt = $mysqli->prepare("INSERT INTO users (user_id, email, first_name, last_name, phone, birthdate, address, bio, experience, password_hash, sign_up_date, verification_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
        $stmt->bind_param("sssssssssssi", $user_id, $email, $first_name, $last_name, $phone, $birthdate, $address, $bio, $experience, $hashed_password, $sign_up_date, $verification_code);
        if (!$stmt->execute()) {
            return "An error occurred. Please try again.";
        }
        $stmt->close();
    } else {
        return "An error occurred. Please try again.";
    }

    $_SESSION['email'] = $args["email"];
    $_SESSION['verification-code'] = $verification_code;
    header("Location: auth.php");
    exit();
}

// Function to generate a UUID
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

   $res = $mysqli->query("SELECT verification_code FROM users WHERE verification_code = '$verification_code'");
   if ($res->num_rows != 1) {
       return "Wrong verification code";
   } else {
       $update = $mysqli->query("UPDATE users SET email_status = 'verified' WHERE verification_code = '$verification_code'");
       if ($mysqli->affected_rows != 1) {
           return "something went wrong. Please try again";
       } else {
           header("Location: login.php");
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

    $sql = "SELECT * FROM users WHERE email = ?";
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
        header("location: auth.php");
        exit();
    }

    if (!password_verify($password, $data["password_hash"])) {
        return "Wrong email or password";
    } else {
        $_SESSION["user"] = $data["user_id"];
        // Create the name string by concatenating first_name and last_name
        $name = $data["first_name"] . " " . $data["last_name"];
        $_SESSION["name"] = $name;
        header("location: index.php");
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

   $stmt = $mysqli->prepare("SELECT email FROM users WHERE email = ?");
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

       $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
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
    $user_id = $_SESSION['user'];

    $stmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("ss", $hashed_password, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        return "There was a connection error, please try again.";
    } else {
        return "success";
    }	
}

function getAllJobs() {
    // Assuming dbConnect() establishes a database connection
    $mysqli = dbConnect();
    if (!$mysqli) {
        return []; // Return an empty array in case of a connection error
    }

    $result = $mysqli->query("SELECT * FROM jobs WHERE deleted = 0 AND hidden = 0");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getJobDetails($job_id) {
    // Assuming dbConnect() establishes a database connection
    $mysqli = dbConnect();
    if (!$mysqli) {
        return null; // Return null in case of a connection error
    }

    // Prepare a statement to avoid SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM jobs WHERE job_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $job_id); 
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Fetch a single row as an associative array
        } else {
            return null; // Return null if no job is found
        }
    } else {
        return null; // Return null in case of an error in preparing the statement
    }
}

function getJobDates($job_id) {
    // Assuming dbConnect() establishes a database connection
    $mysqli = dbConnect();
    if (!$mysqli) {
        return []; // Return an empty array in case of a connection error
    }

    // Prepare a statement to avoid SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM job_dates WHERE job_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $job_id); 
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an array of associative arrays
        } else {
            return []; // Return an empty array if no dates are found
        }
    } else {
        return []; // Return an empty array in case of an error in preparing the statement
    }
}

function getEmployerDetails($employer_id) {
    // Assuming dbConnect() establishes a database connection
    $mysqli = dbConnect();
    if (!$mysqli) {
        return null; // Return null in case of a connection error
    }

    // Prepare a statement to avoid SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM employers WHERE employer_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $employer_id); 
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Fetch a single row as an associative array
        } else {
            return null; // Return null if no employer is found
        }
    } else {
        return null; // Return null in case of an error in preparing the statement
    }
}

function getMyApplications($user_id) {
    // Assuming dbConnect() establishes a database connection
    $mysqli = dbConnect();
    if (!$mysqli) {
        return []; // Return empty array in case of a connection error
    }

    // Prepare a statement to avoid SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM applications JOIN jobs ON applications.job_id = jobs.job_id JOIN employers ON employers.employer_id = jobs.employer_id WHERE applications.deleted = 0 AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $applications = [];
        if ($result->num_rows > 0) {
            // Fetch all rows as an associative array
            $applications = $result->fetch_all(MYSQLI_ASSOC);
        }
        $stmt->close();
        $mysqli->close();
        return $applications;
    } else {
        $mysqli->close();
        return []; // Return empty array in case of an error in preparing the statement
    }
}


function createApplication($job_id, $user_id, $letter) {
    $application_id = generateUUID(); // Generate a unique UUID for the application
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection

    if (!$mysqli) {
        return false; // Return false in case of a connection error
    }

    // Prepare a statement to avoid SQL injection
    $stmt = $mysqli->prepare("INSERT INTO applications (application_id, job_id, user_id, letter) VALUES (?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("ssss", $application_id, $job_id, $user_id, $letter);
        $success = $stmt->execute();
        

        if ($stmt->affected_rows != 1) {
            $stmt->close();
            return "There was a connection error, please try again.";
        } else {
            $stmt->close();
            return "success";
        }	
        return "fail";
    }
}

function deleteApplication($application_id) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Failed to connect to the database.";
    }

    $stmt = $mysqli->prepare("UPDATE applications SET deleted = 1 WHERE application_id = ?");
    if ($stmt === false) {
        $mysqli->close();
        return "Failed to prepare the statement.";
    }

    $stmt->bind_param("s", $application_id);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        $stmt->close();
        $mysqli->close();
        return "No application was deleted. Please check the application ID.";
    } else {
        $stmt->close();
        $mysqli->close();
        return "Success";
    }	
}

function getUnratedJobsDetailsForUser($user_id) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable error reporting for mysqli

    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error"); // Handle the error appropriately
    }

    $query = "SELECT j.job_id, j.employer_id, a.user_id, j.title AS job_title, jd.last_date, emp.company AS employer_company
    FROM jobs j
    JOIN (
        SELECT jd.job_id, MAX(jd.date) AS last_date
        FROM job_dates jd
        GROUP BY jd.job_id
    ) jd ON j.job_id = jd.job_id
    JOIN applications a ON j.job_id = a.job_id
    LEFT JOIN user_ratings ur ON j.job_id = ur.job_id AND a.user_id = ur.user_id
    JOIN employers emp ON j.employer_id = emp.employer_id
    WHERE a.user_id = ?
    AND jd.last_date < CURDATE()
    AND a.state = 'accepted'
    AND (ur.job_id IS NULL OR ur.user_id != a.user_id);";

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        die("Error preparing statement: " . $mysqli->error); // Handle statement preparation error
    }
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $mysqli->close();

    return $jobs;
}


function checkOutstandingRatings($user_id) {
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
    JOIN applications a ON j.job_id = a.job_id
    LEFT JOIN user_ratings ur ON j.job_id = ur.job_id AND a.user_id = ur.user_id
    WHERE a.user_id = ?
    AND jd.last_date < CURDATE()
    AND a.state = 'accepted'
    AND ur.job_id IS NULL;
    ";

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    $mysqli->close();

    return $row['outstanding_ratings'];
}

function createUserJobRating($user_id, $employer_id, $job_id, $rating, $comment) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error";
    }

    // Sanitize inputs
    $user_rating_id = generateUUID();
    $user_id = filter_var(trim($user_id), FILTER_SANITIZE_STRING);
    $employer_id = filter_var(trim($employer_id), FILTER_SANITIZE_STRING);
    $job_id = filter_var(trim($job_id), FILTER_SANITIZE_STRING);
    $comment = filter_var(trim($comment), FILTER_SANITIZE_STRING);

    // Validate rating
    if (!(is_numeric($rating) && ($rating == -1 || ($rating >= 1 && $rating <= 5)))) {
        return "Invalid rating. Rating must be between 1 and 5";
    }

    // Insert job rating into database
    $stmt = $mysqli->prepare("INSERT INTO user_ratings (user_rating_id, user_id, employer_id, job_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        return "An error occurred preparing the statement.";
    }

    $stmt->bind_param("ssssis", $user_rating_id, $user_id, $employer_id, $job_id, $rating, $comment);
    $stmt->execute();

    if ($stmt->affected_rows != 1) {
        return "An error occurred. Please try again";
    }

    updateAverageEmployerRating($employer_id);

    $stmt->close();
    $mysqli->close();
    return true; // Successfully created the job rating
}

function updateAverageEmployerRating($employer_id) {
    $mysqli = dbConnect(); // Assuming dbConnect() establishes a database connection
    if (!$mysqli) {
        die("Database connection error");
    }

    // Query to get the average rating
    $avgQuery = "SELECT AVG(rating) AS average_rating FROM user_ratings WHERE employer_id = ?";
    $avgStmt = $mysqli->prepare($avgQuery);
    if (!$avgStmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $avgStmt->bind_param("s", $employer_id);
    $avgStmt->execute();
    $result = $avgStmt->get_result();
    $avgRow = $result->fetch_assoc();
    $avgRating = $avgRow['average_rating'];

    // Query to update the user's average rating
    $updateQuery = "UPDATE employers SET rating = ? WHERE employer_id = ?";
    $updateStmt = $mysqli->prepare($updateQuery);
    if (!$updateStmt) {
        die("Error preparing statement: " . $mysqli->error);
    }
    $updateStmt->bind_param("ds", $avgRating, $employer_id);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 0) {
        // Handle the case where the update didn't affect any rows
    }

    $avgStmt->close();
    $updateStmt->close();
}

function getUserDetails($user_id) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error.";
    }

    $user_id = htmlspecialchars(trim($user_id));

    // Fetch user details
    if ($stmt = $mysqli->prepare("SELECT user_id, phone, address, bio FROM users WHERE user_id = ?")) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $stmt->close();
            return $user;
        } else {
            $stmt->close();
            return "User not found.";
        }
    } else {
        return "An error occurred. Please try again.";
    }
}

function updateProfile($user_id, $phone, $address, $bio) {
    $mysqli = dbConnect();
    if ($mysqli === false) {
        return "Database connection error.";
    }

    // Trim and sanitize input
    $user_id = htmlspecialchars(trim($user_id));
    $phone = htmlspecialchars(trim($phone));
    $address = htmlspecialchars(trim($address));
    $bio = htmlspecialchars(trim($bio));

    // Update user in database
    if ($stmt = $mysqli->prepare("UPDATE users SET phone = ?, address = ?, bio = ? WHERE user_id = ?")) {
        $stmt->bind_param("ssss", $phone, $address, $bio, $user_id);
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












