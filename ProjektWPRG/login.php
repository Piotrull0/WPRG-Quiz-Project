<?php
session_start();

require "remember.php";
global $conn;

$msg = '';

if (isset($_POST['email']) && isset($_POST['password'])) {

    $email = $_POST["email"];
    $pass = $_POST["password"];
    if (isset($_POST['rem'])) {
        $rem = $_POST["rem"];
    }

    $gname = $conn->prepare("SELECT nickname FROM userprofile JOIN accounts a on userprofile.id = a.userProfileId WHERE email = ?");
    $gname->bind_param("s", $email);
    $gname->execute();
    $gresult = $gname->get_result();

    if (mysqli_num_rows($gresult) == 1) {

        $cpass = $conn->prepare("SELECT id, password, accountType FROM accounts WHERE email = ?");
        $cpass->bind_param("s", $email);
        $cpass->execute();
        $cpass = $cpass->get_result();
        $rowCresult = $cpass->fetch_assoc();

        $getId = $rowCresult["id"];
        $getPass = $rowCresult["password"];
        $getType = $rowCresult["accountType"];

        if (password_verify($pass, $getPass)) {
            $_SESSION['id'] = $getId;
            $_SESSION['email'] = $email;
            $_SESSION['nickname'] = $gresult->fetch_assoc()['nickname'];
            $_SESSION['password'] = $getPass;
            $_SESSION['accountType'] = $getType;

            if ($rem) {

                $gid = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
                $gid->bind_param("s", $email);
                $gid->execute();
                $gid = $gid->get_result();
                $gidResult = mysqli_fetch_array($gid)['id'];

                setcookie("usrId", $gidResult, time() + (86400 * 7), "/");

                $gid->close();
            }

            header("location:index.php");
            exit;
        }
        else {
            $msg = "<p class='text-center p-3 text-danger'>Wrong email or password!</p>";
        }
        $cpass->close();
    }
    else {
        $msg = "<p class='text-center p-3 text-danger'>Wrong email or password!</p>";
    }
    $gname->close();
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include "navbar.php"; ?>
    <form class="container mt-5 col-3 border border-dark border-3 p-3" method="post">
        <div class="row align-items-center text-center border-bottom border-3 py-3">
            <h1 class="col-sm-3" ><i class="bi bi-person-fill-check"></i></h1>
            <h1 class="col-sm-6" >Login</h1>
        </div>
        <div class="mt-3 mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" value="true" id="rem" name="rem">
            <label class="form-check-label" for="rem">Remember me</label>
        </div>
        <div id="regLink" class="form-text">If you don't have an account, create one <a href="register.php">here</a>.</div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </div>
        <?php
            echo $msg;
        ?>
    </form>
    <?php include 'footer.php' ?>
</body>
</html>
