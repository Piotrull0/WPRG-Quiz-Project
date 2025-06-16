<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_SESSION["accountType"]) || ($_SESSION["accountType"] != "admin" && $_SESSION["accountType"] != "moderator")) {
    header("Location: index.php");
    exit;
}
else {
    $gTil = $conn->prepare("SELECT id,title FROM quizzes ORDER BY title");
    $gTil->execute();
    $res = $gTil->get_result();

    if (isset($_POST['qVal'])){
        $catVal = $_POST['qVal'];

        $rTil = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
        $rTil->bind_param("i", $catVal);
        $rTil->execute();
        $rTil->close();

        $rQu = $conn->prepare("DELETE FROM questions WHERE quiz_id = ?");
        $rQu->bind_param("i", $catVal);
        $rQu->execute();
        $rQu->close();

        $rAns = $conn->prepare("DELETE FROM answers WHERE question_id = ?");
        $rAns->bind_param("i", $catVal);
        $rAns->execute();
        $rAns->close();

        header("Refresh:0; url=chooseQuiz.php");
    }
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
<?php if($_SESSION["accountType"] == "admin" || $_SESSION["accountType"] == "moderator") {
    echo "
<div class='row w-100' >
    <div class='container mt-5 col-3 border border-dark border-3 p-3' >
        <div class='row align-items-center text-center border-bottom border-3 py-3'>
            <h1 class='col-sm-3' ><i class='bi bi-dash-circle-fill'></i></h1 >
            <h1 class='col-sm-6' > Delete quiz </h1 >
        </div >
        <form action='chooseQuiz.php' method='post'>
        <div class='mt-3 mb-3'>
            <label for='catVal' class='form-label'>Select quiz</label>
            <select class='form-select' name='qVal' id='catVal'>";
    while ($row = $res->fetch_assoc()) {
        $qId = $row['id'];
        $gTitle = $row['title'];
        echo    '
                        <option value='.$qId.'>'.$gTitle.'</option>
                        ';
    }

    echo "   
            </select>
        </div>
        <div class='mt-3 mb-3 text-center' >
            <input type='submit' class='btn btn-primary' name='delete' value='Delete'>
        </div >
        </form>
    </div >
    <div class='container mt-5 col-3 border border-dark border-3 p-3' >
        <div class='row align-items-center text-center border-bottom border-3 py-3' >
            <h1 class='col-sm-3' ><i class='bi bi-plus-circle-fill'></i></h1 >
            <h1 class='col-sm-6' > Create quiz </h1 >
        </div >
        <div class='mt-3 mb-3 text-center' >
           <a href='manageQuiz.php' class='btn btn-primary mt-5'>Create</a> 
        </div >
    </div >
</div >
";
}
?>
<?php include 'footer.php' ?>
</body>
</html>
