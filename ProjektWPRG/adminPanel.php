<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_SESSION["accountType"]) || ($_SESSION["accountType"] != "admin" && $_SESSION["accountType"] != "moderator")) {
    header("Location: index.php");
    exit;
}
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
</head>
<body class="d-flex flex-column min-vh-100">
<?php include 'navbar.php' ?>
<?php if($_SESSION["accountType"] == "admin") {
    echo "
<div class='row w-100' >
    <div class='container mt-5 col-3 border border-dark border-3 p-3' >
        <div class='row align-items-center text-center border-bottom border-3 py-3'>
            <h1 class='col-sm-3' ><i class='bi bi-person-fill-gear' ></i ></h1 >
            <h1 class='col-sm-6' > Manage accounts </h1 >
        </div >
        <div class='mt-3 mb-3 text-center' >
            <a class='btn btn-primary mt-3' href = 'adminAccounts.php' > Go to accounts </a >
        </div >
    </div >
    <div class='container mt-5 col-3 border border-dark border-3 p-3' >
        <div class='row align-items-center text-center border-bottom border-3 py-3' >
            <h1 class='col-sm-3' ><i class='bi bi-gear-fill' ></i ></h1 >
            <h1 class='col-sm-6' > Manage quizes </h1 >
        </div >
        <div class='mt-3 mb-3 text-center' >
            <a class='btn btn-primary mt-3' href = 'actionQuiz.php' > Go to quizzes </a >
        </div >
    </div >
</div >
";
}
else if($_SESSION["accountType"] == "moderator") {
    echo "
<div class='row w-100' >
    <div class='container mt-5 col-3 border border-dark border-3 p-3' >
        <div class='row align-items-center text-center border-bottom border-3 py-3' >
            <h1 class='col-sm-3' ><i class='bi bi-gear-fill' ></i ></h1 >
            <h1 class='col-sm-6' > Manage quizzes </h1 >
        </div >
        <div class='mt-3 mb-3 text-center' >
            <a class='btn btn-primary mt-3' href = 'actionQuiz.php' > Go to quizzes </a >
        </div >
    </div >
</div >
";
}
?>
    <?php include 'footer.php' ?>
</body>
</html>
