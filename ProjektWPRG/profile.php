<?php
session_start();
require "remember.php";
global $conn;
?>
<?php
$gimage = $conn->prepare("SELECT profilePicture FROM userprofile u JOIN accounts a on u.id = a.userProfileId WHERE email = ?;");
$gimage->bind_param("s", $_SESSION['email']);
$gimage->execute();
$gimage = $gimage->get_result();
$resimage = $gimage->fetch_assoc()['profilePicture'];

$profilePicture = $resimage;
$nickname = $_SESSION['nickname'] ?? 'Guest';
$userStats = $conn->prepare("SELECT us.total_quizzes, us.total_answers, us.correct_answers FROM userstats us JOIN accounts a ON us.user_id = a.id WHERE a.email = ?");
$userStats->bind_param("s", $_SESSION['email']);
$userStats->execute();
$stats = $userStats->get_result()->fetch_assoc();

$totalQuizzes = $stats['total_quizzes'] ?? 0;
$totalAnswers = $stats['total_answers'] ?? 0;
$correctAnswers = $stats['correct_answers'] ?? 0;
$accuracy = $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 1) : 0;

$userStats->close();
$gimage->close();
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
    <link rel="stylesheet" href="styles/profile.css"
</head>
<body class="d-flex flex-column min-vh-100">
<?php include 'navbar.php' ?>
<div class="container my-5">
    <div class="profile-card text-center">
        <img src="<?php echo $profilePicture; ?>" alt="Profile picture" class="profile-pic mb-3">
        <h2 class="mb-4"><?php echo $nickname; ?></h2>
        <div class="row g-4 justify-content-center">
            <div class="col-12 col-sm-4">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $totalQuizzes;?></div>
                    <div class="stat-label">Completed quizes</div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $correctAnswers.'/'.$totalAnswers;?></div>
                    <div class="stat-label">Correct answeres</div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $accuracy.'%';?></div>
                    <div class="stat-label">Accuracy</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php' ?>
</body>
</html>
