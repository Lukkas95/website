<?php 
require "employer-functions.php";
session_start();
if (isset($_POST['change'])) {
    $response = changePassword($_POST['password'], $_POST['confirm_password']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Change Password</title>
</head>
<body>
    <form action="" method="post">
        <h2>Change Password</h2>
       
        <label>New password</label>
        <input type="text" name="password" value="<?php echo @$_POST['password'] ?>">
 
        <label>Confirm new password</label>
        <input type="text" name="confirm_password" value="<?php echo @$_POST['confirm_password'] ?>">

        <button type="submit" name="change">Change</button>

        <?php if (isset($response)): ?>
            <p class="response <?php echo $response === "success" ? 'success' : 'error'; ?>">
                <?php 
                if ($response === "success") {
                    echo "Password was updated!";
                } else {
                    echo htmlspecialchars($response);
                }
                ?>
            </p>
        <?php endif; ?>

        <p class="forgot-password">
            <a href="employer-account.php">Back to your account overview</a>
        </p>
    </form>
</body>
</html>
