<?php
    include_once("config.php");
    $baseDirectory = "/home/httpd/vhosts/universe9.ch/httpdocs/available/";
    $uploadDirectory = "roompics/";

    $errors = []; // Store errors here

    $fileExtensionsAllowed = ['jpg','png','jpeg']; // These will be the only file extensions allowed 

    $fileName = $_FILES['newRoomPic']['name'];
    $fileSize = $_FILES['newRoomPic']['size'];
    $fileTmpName  = $_FILES['newRoomPic']['tmp_name'];
    $fileType = $_FILES['newRoomPic']['type'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $newFileName = $_POST['newFileName'];

    $fileIndex = $_POST['fileIndex'];
    $id = $_POST['id'];
    if ($fileIndex === "pic1") {
      $statement = "UPDATE prod_rooms SET pic1=? WHERE id=?";
    } else if ($fileIndex === "pic2") {
      $statement = "UPDATE prod_rooms SET pic2=? WHERE id=?";
    } else if ($fileIndex === "pic3") {
      $statement = "UPDATE prod_rooms SET pic3=? WHERE id=?";
    }

    //for statement full name
    $newFileNameFull = $newFileName . "." . $fileExtension;

    // for final upload location full path
    $uploadPath = $baseDirectory . $uploadDirectory . $newFileNameFull;

    function compress($source, $destination, $quality, $fileType) {
        $info = getimagesize($source);

        if ($fileType == 'image/jpeg') 
            $image = imagecreatefromjpeg($source);
        elseif ($fileType == 'image/png') 
            $image = imagecreatefrompng($source);

        imagejpeg($image, $destination, $quality);
        return $destination;
    }

    if (isset($_POST['submit'])) {

        if (! in_array($fileExtension, $fileExtensionsAllowed)) {
            $errors[] = "This file extension is not allowed. Please upload a JPG or PNG file.";
        } else {
            $check = getimagesize($fileTmpName);
            if($check == false) {
                $errors[] = "File is not a valid image.";
            }
        }

        if ($fileSize > 8000000) {
            $errors[] = "File exceeds maximum size (8MB)";
        }

        if (empty($errors)) {
            $didCompress = compress($fileTmpName, $fileTmpName, 90, $fileType);

            $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

            $stmt = $mysqli->prepare($statement);
            $stmt->bind_param("si", $newFileNameFull, $id);
            $stmt->execute();

            if ($didUpload && $didCompress) {
                echo "<h3>The file " . $newFileNameFull . " has been uploaded.</h3>";
                echo "<a href='javascript:window.history.back();'>Back</a>";
            } else {
                echo "<h1>An error occurred. Please contact the administrator.</h1>";
            }
        } else {
            foreach ($errors as $error) {
                echo "These are the errors" . "<br>" . $error . "\n";
            }
        }
    }
?>
