<?php
session_start();

// Check if the user is set in the session
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user']; // Assuming this is a string
$targetDirectory = "images/";
$maxFiles = 4;
$fileExtensionsAllowed = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file types
$errors = [];
$existingImages = [];

// Function to check for existing images
function checkForExistingImages($targetDirectory, $user, $maxFiles, $fileExtensionsAllowed) {
    $existingImages = [];
    for ($i = 1; $i <= $maxFiles; $i++) {
        foreach ($fileExtensionsAllowed as $ext) {
            $filePath = $targetDirectory . $user . "_" . $i . "." . $ext;
            if (file_exists($filePath)) {
                $existingImages[$i] = $filePath;
                break; // Stop the loop if image is found
            }
        }
    }
    return $existingImages;
}

// Initially check for existing images
$existingImages = checkForExistingImages($targetDirectory, $user, $maxFiles, $fileExtensionsAllowed);

// Handle file upload or deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    for ($i = 1; $i <= $maxFiles; $i++) {
        // Delete image if delete button is pressed
        if (isset($_POST['delete' . $i])) {
            if (isset($existingImages[$i])) {
                unlink($existingImages[$i]);
                unset($existingImages[$i]);
            }
        }
        // Upload new image
        elseif (isset($_FILES['image' . $i]) && $_FILES['image' . $i]['error'] == 0) {
            $fileName = $_FILES['image' . $i]['name'];
            $fileTmpName = $_FILES['image' . $i]['tmp_name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Create a unique filename
            $newFileName = $user . "_" . $i . "." . $fileExtension;

            // Check file extension
            if (!in_array($fileExtension, $fileExtensionsAllowed)) {
                $errors[] = "File extension not allowed for file: " . $fileName;
                continue;
            }

            // Move the file to the target directory
            if (!move_uploaded_file($fileTmpName, $targetDirectory . $newFileName)) {
                $errors[] = "There was an error uploading your file: " . $fileName;
            } else {
                $existingImages[$i] = $targetDirectory . $newFileName; // Update existing image
            }
        }
    }
    // Re-check for existing images after handling POST request
    $existingImages = checkForExistingImages($targetDirectory, $user, $maxFiles, $fileExtensionsAllowed);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Upload</title>
</head>
<body>
    <h1>Upload Images</h1>
    <!-- Display errors if any -->
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <?php for ($i = 1; $i <= $maxFiles; $i++): ?>
            <div>
                <label for="image<?php echo $i; ?>">Image <?php echo $i; ?>:</label>
                <input type="file" name="image<?php echo $i; ?>" id="image<?php echo $i; ?>"><br>
                <?php if (isset($existingImages[$i])): ?>
                    <img src="<?php echo htmlspecialchars($existingImages[$i]); ?>" style="max-width: 200px;"><br>
                    <button type="submit" name="delete<?php echo $i; ?>">Delete Image <?php echo $i; ?></button>
                <?php endif; ?>
                <br><br>
            </div>
        <?php endfor; ?>
        <button type="submit">Upload/Replace Images</button>
    </form>

    <br>
    <a href="account.php"><button>Back to Account</button></a>
</body>
</html>
