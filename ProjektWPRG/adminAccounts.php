<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_SESSION["accountType"]) || $_SESSION["accountType"] != "admin") {
    header("Location: index.php");
    exit;
}

    $table = '';

    $getData = $conn->prepare("SELECT a.id, u.nickname, a.email, a.accountType FROM userprofile u JOIN accounts a on u.id = a.userProfileId");
    $getData->execute();
    $result = $getData->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $table = "
    <div class='container justify-content-center align-items-center'>
        <div class='table-responsive'>
        <table class='table table-hover table-bordered w-100 mt-3'>
            <thead>
                <tr>
                    <th class='act'></th>
                    <th>ID</th>
                    <th>Nickname</th>
                    <th>Email</th>
                    <th>Account Type</th>
                </tr>
            </thead>
            ";

    foreach ($users as $user) {
        $table .= "
                    <tbody>
                    <tr>
                    ";
                    if (!($user['email'] == $_SESSION['email'])){
                       $table.= "<td class='act'><h4><a href='editUser.php?id={$user['id']}'><i class='bi bi-pencil-square'></i></a> <a href='deleteUser.php?id={$user['id']}' onclick=\"return confirm('Are you sure you want to delete this user?');\"></i><i class='bi bi-x-square'></i></a></h4></td>";
                    }
                    else {
                       $table.= "<td class='act'><h4><a href='editUser.php?id={$user['id']}'><i class='bi bi-pencil-square'></i></a> <a href='' onclick=\"alert('Cannot delete your own account!');\"></i><i class='bi bi-x-square-fill'></i></a></h4></td>";
                    }
        $table .= " 
                    <td>{$user['id']}</td>
                    <td>{$user['nickname']}</td>
                    <td>{$user['email']}</td>
                    <td>{$user['accountType']}</td>
                </tr>
                </tbody>
                ";
    }

    $table .= "
        </table>
        </div>
    </div>
    ";
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Quizzy</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/adminAccounts.css">
</head>

<body class="d-flex flex-column min-vh-100">
<?php include 'navbar.php' ?>
<?php
    echo $table;
?>
<?php include 'footer.php' ?>
</body>
</html>