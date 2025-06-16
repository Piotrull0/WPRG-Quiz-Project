<?php
session_start();
require "remember.php";
global $conn;

$mess = '';

if (!isset($_SESSION["accountType"]) || ($_SESSION["accountType"] != "admin" && $_SESSION["accountType"] != "moderator")) {
    header("Location: index.php");
    exit;
}
else {
    $gcat = $conn->prepare("SELECT id,name FROM categories ORDER BY name");
    $gcat->execute();
    $res = $gcat->get_result();

    if (isset($_POST['catVal'])){
        $catVal = $_POST['catVal'];

        $rcat = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $rcat->bind_param("i", $catVal);
        $rcat->execute();
        $rcat->close();
        header("Refresh:0; url=manageCategories.php");
    }

    if (isset($_POST['catAdd'])){
        $catAdd = $_POST['catAdd'];

        $acat = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $acat->bind_param("s", $catAdd);
        $acat->execute();
        $acat->close();
        header("Refresh:0; url=manageCategories.php");
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
            <h1 class='col-sm-6' > Delete category </h1 >
        </div >
        <form action='manageCategories.php' method='post'>
        <div class='mt-3 mb-3'>
            <label for='catVal' class='form-label'>Select category</label>
            <select class='form-select' name='catVal' id='catVal'>";
            while ($row = $res->fetch_assoc()) {
                $catId = $row['id'];
                $catName = $row['name'];
                echo    '
                        <option value='.$catId.'>'.$catName.'</option>
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
            <h1 class='col-sm-6' > Add category </h1 >
        </div >
        <div class='mt-3 mb-3'>
        <form action='manageCategories.php' method='post'>
            <label for='catAdd' class='form-label'>Category name</label>
            <input type='text' class='form-control' id='catAdd' name='catAdd'>
        </div>
        <div class='mt-3 mb-3 text-center' >
            <input type='submit' class='btn btn-primary' name='add' value='Add'>
        </div >
        </form>
    </div >
</div >
";
}
?>
<?php include 'footer.php' ?>
</body>
</html>
