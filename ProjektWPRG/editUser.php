<?php
session_start();

require "remember.php";
global $conn;


if (!isset($_SESSION["accountType"]) || $_SESSION["accountType"] != "admin") {
    header("Location: index.php");
    exit;
}

$nickname = '';
$email = '';
$password = '';
$accountType = '';
$accountId = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $accountId != null) {

    $nickname = $_POST['nick'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $accountType = $_POST['usrType'];

    $oldData = $conn->prepare("SELECT email FROM accounts WHERE id = ?");
    $oldData->bind_param("i", $accountId);
    $oldData->execute();
    $oldResult = $oldData->get_result()->fetch_assoc();
    $oldEmail = $oldResult['email'];
    $oldData->close();

    $getUserProfileId = $conn->prepare("SELECT userProfileId FROM accounts WHERE id = ?");
    $getUserProfileId->bind_param("i", $accountId);
    $getUserProfileId->execute();
    $result = $getUserProfileId->get_result();
    $userProfileId = $result->fetch_assoc()['userProfileId'];
    $getUserProfileId->close();

    $updateUser = $conn->prepare("UPDATE userprofile SET nickname = ? WHERE id = ?");
    $updateUser->bind_param("si", $nickname, $userProfileId);
    $updateUser->execute();
    $updateUser->close();

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $updateAccount = $conn->prepare("UPDATE accounts SET email = ?, password = ?, accountType = ? WHERE id = ?");
        $updateAccount->bind_param("sssi", $email, $hashedPassword, $accountType, $accountId);

        if (isset($_SESSION["email"]) && $_SESSION["email"] == $oldEmail) {

            $_SESSION["email"] = $email;
            $_SESSION["accountType"] = $accountType;
            $_SESSION["nickname"] = $nickname;
            $_SESSION["password"] = $hashedPassword;
        }
    }
    else {
        $updateAccount = $conn->prepare("UPDATE accounts SET email = ?, accountType = ? WHERE id = ?");
        $updateAccount->bind_param("ssi", $email, $accountType, $accountId);

        if (isset($_SESSION["email"]) && $_SESSION["email"] == $oldEmail) {
            
            $_SESSION["email"] = $email;
            $_SESSION["accountType"] = $accountType;
            $_SESSION["nickname"] = $nickname;
        }
    }

    $updateAccount->execute();
    $updateAccount->close();

    header("Location: adminAccounts.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $accountId != null) {

    $getData = $conn->prepare("SELECT u.nickname, a.email, a.accountType FROM userprofile u JOIN accounts a ON u.id = a.userProfileId WHERE a.id = ?");
    $getData->bind_param("i", $accountId);
    $getData->execute();
    $resData = $getData->get_result();

    if ($resData->num_rows == 1) {
        $row = $resData->fetch_assoc();
        $nickname = $row["nickname"];
        $email = $row["email"];
        $accountType = $row["accountType"];
    }
    else {
        echo "User not found.";
    }

    $getData->close();
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
<?php
    echo '
        <form class="container mt-5 col-3 border border-dark border-3 p-3" method="post">
            <div class="row align-items-center text-center border-bottom border-3 py-3" >
                <h1 class="col-sm-3"><i class="bi bi-person-fill-gear"></i></h1>
                <h1 class="col-sm-6" >Edit user</h1>
            </div>
            <div class="mt-3 mb-3">
                <label for="nick" class="form-label">Nickname</label>
                <input type="text" class="form-control" id="nick" name="nick" value="'.$nickname.'" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" class="form-control" id="email" name="email" value="'.$email.'" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="usrType" class="form-label">Account type</label>
                <select class="form-select" aria-label="usrType" id="usrType" name="usrType" required>
                    <option value="user" '.($accountType == 'user' ? 'selected' : '').'>user</option>
                    <option value="moderator" '.($accountType == 'moderator' ? 'selected' : '').'>moderator</option>
                    <option value="admin" '.($accountType == 'admin' ? 'selected' : '').'>admin</option>
                </select>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary mt-3">Submit</button>
            </div>
        </form>
    ';
?>
<?php
include 'footer.php'
?>
</body>
</html>
