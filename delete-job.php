<?php
require "employer-functions.php";
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: employer-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $jobId = $_POST['job_id'];
    $creatorId = $_SESSION['user'];

    if (deleteJob($jobId, $creatorId)) {
        header("Location: employer-account.php");
        exit();
    } else {
        echo "Error deleting job or unauthorized action.";
    }
} else {
    header("Location: employer-account.php");
    exit();
}
?>
