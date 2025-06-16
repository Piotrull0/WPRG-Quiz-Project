<?php
session_start();
require "remember.php";
global $conn;

$gcat = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$gcat->execute();
$res = $gcat->get_result();

$quizzes = [];
if (isset($_GET['quizType']) && is_numeric($_GET['quizType'])) {
    $gQuiz = $conn->prepare("SELECT * FROM quizzes WHERE category_id = ?");
    $gQuiz->bind_param("i", $_GET['quizType']);
}
else {
    $gQuiz = $conn->prepare("SELECT * FROM quizzes");
}
$gQuiz->execute();
$qRes = $gQuiz->get_result();
while ($row = $qRes->fetch_assoc()) {
    $quizzes[] = $row;
}

$CatName = "";
if (isset($_GET['quizType']) && is_numeric($_GET['quizType'])) {
    $gCat = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $gCat->bind_param("i", $_GET['quizType']);
    $gCat->execute();
    $result = $gCat->get_result();
    if ($catRow = $result->fetch_assoc()) {
        $CatName = $catRow['name'];
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
    <link rel="stylesheet" href="styles/index.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include 'navbar.php' ?>
    <div class="container-fluid px-0">
        <div class="row g-0">
            <div class="col-md-2">
                <?php include 'aside.php'; ?>
            </div>
            <div class="col-md-10 px-4">
                <div class="container py-4">
                    <h2 class="mb-4">Available quizzes: <?php echo $CatName ?></h2>
                    <div class="row">
                        <?php if (empty($quizzes)): ?>
                            <p>No quizzes to display.</p>
                        <?php else: ?>
                            <?php foreach ($quizzes as $quiz): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <?php if (!empty($quiz['thumbnail_path']) && file_exists($quiz['thumbnail_path'])): ?>
                                            <img src="<?php echo $quiz['thumbnail_path'] ?>" class="card-img-top" alt="Thumbnail">
                                        <?php else: ?>
                                            <img src="uploads/placeholder.jpg" class="card-img-top" alt="No thumbnail">
                                        <?php endif; ?>
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?php echo $quiz['title'] ?></h5>
                                            <a href="solveQuiz.php?id=<?php echo $quiz['id'] ?>" class="btn btn-primary mt-auto">Solve</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php' ?>
</body>
</html>
