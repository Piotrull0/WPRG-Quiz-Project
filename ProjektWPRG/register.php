<?php
require "db_conn.php";
global $conn;

$msg = '';

if(isset($_POST["nick"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["cpassword"])) {

    $nick = $_POST["nick"];
    $email = $_POST["email"];
    $pass = $_POST["password"];
    $cpass = $_POST["cpassword"];
    $passHash = password_hash($pass, PASSWORD_ARGON2ID);

    $cemail = $conn->prepare("SELECT email FROM accounts WHERE email = ?");
    $cemail->bind_param("s", $email);
    $cemail->execute();
    $result = $cemail->get_result();


    if (mysqli_num_rows($result) == 0) {

        if ($pass === $cpass) {
            $register = $conn->prepare("INSERT INTO accounts (email, password) VALUES (?,?)");
            $register->bind_param("ss", $email, $passHash);
            $register->execute();
            $register->close();

            $register2 = $conn->prepare("INSERT INTO userprofile (nickname) VALUES (?)");
            $register2->bind_param("s", $nick);
            $register2->execute();
            $register2->close();

            $getForeign = $conn->prepare("SELECT id FROM userprofile WHERE nickname = ?");
            $getForeign->bind_param("s", $nick);
            $getForeign->execute();
            $getForeign = $getForeign->get_result();
            $resForeign = $getForeign->fetch_assoc()['id'];

            $setForeign = $conn->prepare("UPDATE accounts SET userProfileId = ? WHERE email = ?");
            $setForeign->bind_param("is", $resForeign, $email);
            $setForeign->execute();
            $setForeign->close();

            $msg = '<p class="text-center p-3 text-success">Account created!</p>';

            $getForeign->close();
            header('Refresh: 1; URL=index.php');
        }
        else {
            $msg = '<p class="text-center p-3 text-danger">Passwords do not match!</p>';
        }
    }
    else
        $msg = '<p class="text-center p-3 text-danger">Account with that email already exists!</p>';

    $cemail->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include "navbar.php"; ?>
    <form class="container mt-5 col-3 border border-dark border-3 p-3" method="post">
        <div class="row align-items-center text-center border-bottom border-3 py-3" >
            <h1 class="col-sm-3"><i class="bi bi-person-fill-add"></i></h1>
            <h1 class="col-sm-6" >Register</h1>
        </div>
        <div class="mt-3 mb-3">
            <label for="nick" class="form-label">Nickname</label>
            <input type="text" class="form-control" id="nick" name="nick" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="cpassword" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="cpassword" name="cpassword" required>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-primary mt-3">Submit</button>
        </div>
        <?php
            echo $msg;
        ?>
    </form>
    <?php
    include 'footer.php'
    ?>
</body>
</html>

