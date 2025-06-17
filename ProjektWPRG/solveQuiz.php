<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Incorrect quiz id.");
}

if (!isset($_SESSION['email'])) {
    die("No account detected.");
}

$quizId = intval($_GET['id']);
$quiz = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$quiz->bind_param("i", $quizId);
$quiz->execute();
$quizRes = $quiz->get_result()->fetch_assoc();

if (!$quizRes) {
    die("Quiz does not exist.");
}

$qGet = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$qGet->bind_param("i", $quizId);
$qGet->execute();
$questionsRes = $qGet->get_result();

$questions = [];
while ($q = $questionsRes->fetch_assoc()) {
    $q['answers'] = [];

    if ($q['question_type'] === 'single' || $q['question_type'] === 'multi') {
        $aGet = $conn->prepare("SELECT * FROM answers WHERE question_id = ?");
        $aGet->bind_param("i", $q['id']);
        $aGet->execute();
        $ares = $aGet->get_result();

        while ($a = $ares->fetch_assoc()) {
            $q['answers'][] = $a;
        }
    }

    $questions[] = $q;
}

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
$totalAnswers = 0;
$correctPerQuestion = [];

if ($submitted) {
    foreach ($questions as $q) {
        $userAnswer = $_POST['question'][$q['id']] ?? null;

        if ($q['question_type'] === 'single' || $q['question_type'] === 'multi') {
            if (!is_array($userAnswer)) $userAnswer = [$userAnswer];

            $correctAnswers = array_filter($q['answers'], function ($a) {
                return $a['is_correct'];
            });
            $correctIds = array_column($correctAnswers, 'id');

            $correct = (count(array_diff($userAnswer, $correctIds)) === 0) && (count(array_diff($correctIds, $userAnswer)) === 0);
            $correctPerQuestion[$q['id']] = $correct;

            $totalAnswers += count($userAnswer);
        }
        else if ($q['question_type'] === 'image') {
            $userText = trim($userAnswer);
            $correctStmt = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ? AND is_correct = 1");
            $correctStmt->bind_param("i", $q['id']);
            $correctStmt->execute();
            $correctText = trim($correctStmt->get_result()->fetch_assoc()['answer_text']);

            $correct = strcasecmp($userText, $correctText) === 0;
            $correctPerQuestion[$q['id']] = $correct;

            if (!empty($userText)) {
                $totalAnswers += 1;
            }
        }
    }

    $score = array_sum(array_map(function ($c) {
        return $c ? 1 : 0;
    }, $correctPerQuestion));

    if (isset($_SESSION['email'])) {
        $getUserId = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
        $getUserId->bind_param("s", $_SESSION['email']);
        $getUserId->execute();
        $userId = $getUserId->get_result()->fetch_assoc()['id'];

        $update = $conn->prepare("INSERT INTO userstats (user_id, total_quizzes, total_answers, correct_answers)VALUES (?, 1, ?, ?)
        ON DUPLICATE KEY UPDATE
        total_quizzes = total_quizzes + 1,
        total_answers = total_answers + VALUES(total_answers),
        correct_answers = correct_answers + VALUES(correct_answers)
");
        $update->bind_param("iii", $userId, $totalAnswers, $score);
        $update->execute();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $quizRes['title'] ?> | Quizzy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="styles/solveQuiz.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
<div class="container py-5">
    <h2 class="mb-4"><?php echo $quizRes['title'] ?></h2>
    <?php if ($submitted): ?>
        <?php foreach ($questions as $q): ?>
            <div class="card mb-4 p-3">
                <h5><?php echo $q['question_text'] ?></h5>
                <?php if (!empty($q['image_path']) && file_exists($q['image_path'])): ?>
                    <img src="<?php echo $q['image_path'] ?>" alt="Picture" class="quiz-image img-fluid mb-2 rounded">
                <?php endif; ?>

                <?php if ($q['question_type'] === 'single' || $q['question_type'] === 'multi'): ?>
                    <?php
                    $userAnswers = $_POST['question'][$q['id']] ?? [];
                    if (!is_array($userAnswers)) $userAnswers = [$userAnswers];
                    ?>

                    <?php foreach ($q['answers'] as $a): ?>
                        <?php
                        $checked = in_array($a['id'], $userAnswers);
                        $isCorrect = $a['is_correct'];
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="<?php echo $q['question_type'] === 'multi' ? 'checkbox' : 'radio' ?>" disabled <?php echo $checked ? 'checked' : '' ?>>
                            <label class="form-check-label <?php echo $isCorrect ? 'text-success' : ($checked ? 'text-danger' : '') ?>">
                                <?php echo $a['answer_text'] ?>
                                <?php echo $isCorrect ? '(✓)' : ($checked ? '(✕)' : '') ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($q['question_type'] === 'image'): ?>
                    <?php
                    $userText = trim($_POST['question'][$q['id']] ?? '');
                    $correctStmt = $conn->prepare("SELECT answer_text FROM answers WHERE question_id = ? AND is_correct = 1");
                    $correctStmt->bind_param("i", $q['id']);
                    $correctStmt->execute();
                    $correctText = trim($correctStmt->get_result()->fetch_assoc()['answer_text']);
                    ?>
                    <p><strong>Your answer:</strong> <?php echo $userText ?></p>
                    <p><strong>Correct answer:</strong> <?php echo $correctText ?>
                        <?php echo strcasecmp($userText, $correctText) === 0 ? '✓' : '✕' ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="alert alert-info">
            <h4>Your score: <?php echo $score ?>/<?php echo count($questions) ?></h4>
        </div>
        <a href="index.php" class="btn btn-secondary">Go back to home</a>
    <?php else: ?>
        <form method="post">
            <?php foreach ($questions as $q): ?>
                <div class="card mb-4 p-3">
                    <h5><?php echo $q['question_text'] ?></h5>
                    <?php if (!empty($q['image_path']) && file_exists($q['image_path'])): ?>
                        <img src="<?php echo $q['image_path'] ?>" alt="Image" class="quiz-image img-fluid mb-2 rounded">
                    <?php endif; ?>

                    <?php if ($q['question_type'] === 'single' || $q['question_type'] === 'multi'): ?>
                        <?php foreach ($q['answers'] as $a): ?>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="<?php echo $q['question_type'] === 'multi' ? 'checkbox' : 'radio' ?>"
                                       name="question[<?php echo $q['id'] ?>]<?php echo $q['question_type'] === 'multi' ? '[]' : '' ?>"
                                       value="<?php echo $a['id'] ?>">
                                <label class="form-check-label"><?php echo $a['answer_text'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($q['question_type'] === 'image'): ?>
                        <input type="text" name="question[<?php echo $q['id'] ?>]" class="form-control" required>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Finish quiz</button>
        </form>
    <?php endif; ?>
</div>
<?php include 'footer.php' ?>
</body>
</html>
