<?php 
require "functions.php";

if (isset($_POST['send-email'])) {
    $response = passwordReset($_POST['email']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Password Reset</title>
</head>
<body>
    <form action="" method="post">
        <h2>Password Reset</h2>
        <p class="info">
            Please enter your email so we can send you a new password.
        </p>

        <label for="email">Email</label>
        <input type="text" name="email" id="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <button type="submit" name="send-email">Send</button>

        <?php if (isset($response)): ?>
            <p class="response <?php echo $response === "success" ? 'success' : 'error'; ?>">
                <?php 
                if ($response === "success") {
                    echo "Please go to your email account and copy your new password.";
                } else {
                    echo htmlspecialchars($response);
                }
                ?>
            </p>
        <?php endif; ?>

        <p class="forgot-password">
            <a href="login.php">Back to login page</a>
        </p>
    </form>
</body>
</html>
