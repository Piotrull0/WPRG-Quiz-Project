<?php
session_start();

require "remember.php";
global $conn;

$nickMsg = '';
$passMsg = '';

$gimage = $conn->prepare("SELECT profilePicture FROM userprofile u JOIN accounts a on u.id = a.userProfileId WHERE email = ?;");
$gimage->bind_param("s", $_SESSION['email']);
$gimage->execute();
$gimage = $gimage->get_result();
$resimage = $gimage->fetch_assoc()['profilePicture'];
$gimage->close();

$_SESSION['profPic'] = $resimage;

// Change nickname
if (isset($_POST['nick'])) {
    $nick = $_POST['nick'];

    $gname = $conn->prepare("UPDATE userprofile u JOIN accounts a ON u.id = a.userProfileId SET u.nickname = ? WHERE a.email = ?;");
    $gname->bind_param("ss", $nick, $_SESSION['email']);
    $gname->execute();

    if ($gname->affected_rows == 1) {
        $nickMsg = '<p class="text-center p-3 text-success">Nickname changed!</p>';
        $_SESSION['nickname'] = $nick;
    }
    $gname->close();
}

// Change password
if (isset($_POST['password']) && isset($_POST['npassword']) && isset($_POST['cpassword'])) {
    $pass = $_POST['password'];
    $npass = $_POST['npassword'];
    $cpass = $_POST['cpassword'];
    $npassHash = password_hash($npass, PASSWORD_ARGON2ID);

    if (password_verify($pass, $_SESSION['password'])) {
        if ($npass == $cpass) {

            $cpass = $conn->prepare("UPDATE accounts SET password=? WHERE email = ?");
            $cpass->bind_param("ss", $npassHash, $_SESSION['email']);
            $cpass->execute();

            if ($cpass->affected_rows == 1) {
                $passMsg = '<p class="text-center p-3 text-success">Password changed!</p>';
                $_SESSION['password'] = $npassHash;
                header("Refresh:1; url=index.php");
            }
        }
        else {
            $passMsg = '<p class="text-center p-3 text-danger">Passwords do not match!</p>';
        }
    }
    else {
        $passMsg = '<p class="text-center p-3 text-danger">Wrong password!</p>';
    }

    if (isset($cpass)) {
        $cpass->close();
    }
}

// Change picture
$picSrc = $_SESSION['profile_pic'] ?? 'uploads/profilePictures/default.jpg';
$picMsg = '';

if (isset($_FILES['imageUpload'])) {

    $uploadDir = 'uploads/profilePictures/';
    $file = $_FILES['imageUpload'];
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileError = $file['error'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($ext, $allowed)) {
        list($width, $height) = getimagesize($fileTmp);
        $new_width = 200;
        $new_height = 200;

        $image_p = imagecreatetruecolor($new_width, $new_height);

        if ($ext ==  'png') {
            $image = imagecreatefrompng($fileTmp);
        }
        else {
            $image = imagecreatefromjpeg($fileTmp);
        }

        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        $newFileName = uniqid('profile_', true) . '.jpg';
        $destination = $uploadDir . $newFileName;

            imagejpeg($image_p, $destination, 100);

            if ($_SESSION['profPic'] != 'uploads/profilePictures/default.jpg' && file_exists($_SESSION['profPic'])) {
                unlink($_SESSION['profPic']);
            }

            $simage = $conn->prepare("UPDATE userprofile u JOIN accounts a ON u.id = a.userProfileId SET u.profilePicture = ? WHERE a.email = ?");
            $simage->bind_param("ss", $destination, $_SESSION['email']);
            $simage->execute();
            $simage->close();

            $_SESSION['profPic'] = $destination;

            header("Refresh:0;");
            imagedestroy($image_p);
            imagedestroy($image);
    }
    else {
        $picMsg = "<p class='text-danger text-center'>Only JPG and PNG formats are allowed!</p>";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Account</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="styles/manageAccount.css">
    <script>
        $(document).ready(function () {
            $("#profilePicture").click(function () {
                $("#imageUpload").click();
            });
            $(document).ready(function () {
                $("#text").click(function () {
                    $("#imageUpload").click();
                });
            });
            $("#imageUpload").change(function () {
                $("#picForm").submit();
            });
        });
    </script>
</head>
<body class="d-flex flex-column min-vh-100">
<?php include "navbar.php"; ?>
    <?php
        if (isset($_SESSION['email'])) {
        echo '
        <div class="row w-100 mt-5 justify-content-around align-items-start">
            <div class="col-3 border border-dark border-3 p-3">
                <div class="col align-items-center text-center border-bottom border-3 py-3" >
                    <h1>Change picture</h1>
                </div>
                <form method="post" id="picForm" enctype="multipart/form-data" class="justify-content-center align-items-center d-grid">
                    <div id="cont">
                        <img class="img-fluid mt-3"  id="profilePicture" src="' . $_SESSION['profPic'] . '?t=' . time() . '" alt="Picture" id="image">
                        <input id="imageUpload" type="file" name="imageUpload" placeholder="Photo" required="">
                        <div id="mid">
                            <div id="text">Change picture</div>
                        </div>
                    </div>
                    '.$picMsg.'
                </form>
            </div>
            <form class="col-3 border border-dark border-3 p-3" method="post">
                <div class="col align-items-center text-center border-bottom border-3 py-3" >
                    <h1>Change nickname</h1>
                </div>
                <div class="mt-3 mb-3">
                    <input type="text" class="form-control" id="nick" name="nick" value="'.$_SESSION['nickname'].'">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-3">Change</button>
                </div>
                '.$nickMsg.'
            </form>
            <form class="col-3 border border-dark border-3 p-3" method="post">
                <div class="col align-items-center text-center border-bottom border-3 py-3" >
                    <h1>Change password</h1>
                </div>
                <div class="mt-3 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                </div>
                <div class="mb-3">
                    <label for="npassword" class="form-label">New password</label>
                    <input type="password" class="form-control" id="npassword" name="npassword">
                </div>
                <div class="mb-3">
                    <label for="cpassword" class="form-label">Confirm password</label>
                    <input type="password" class="form-control" id="cpassword" name="cpassword">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary mt-3">Change</button>
                </div>
                '.$passMsg.'
            </form>
        </div>';
        }
        else {
            echo '
            <div class="w-100 mt-5 text-center" >
                <h2 class="text-danger">Error: Not logged in</h2>
            </div>';
        }
    ?>
<?php include 'footer.php' ?>
</body>
</html>

