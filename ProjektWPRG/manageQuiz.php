<?php
session_start();
require "remember.php";
global $conn;

if (!isset($_SESSION["accountType"]) || ($_SESSION["accountType"] != "admin" && $_SESSION["accountType"] != "moderator")) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_title'])) {
    $title = $_POST['quiz_title'];
    $category = $_POST['category'];
    $userEmail = $_SESSION['email'];

    if (isset($_FILES['thumbnail'])) {

        $tmpName = $_FILES['thumbnail']['tmp_name'];
        $originalName = basename($_FILES['thumbnail']['name']);
        $uploadDir = 'uploads/thumbnails/';
        $targetPath = $uploadDir . time() . '_' . $originalName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $thumbnailPath = $targetPath;
        }
    }

    $stmt = $conn->prepare("INSERT INTO quizzes (title, category_id, created_by, created_at, thumbnail_path) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("siss", $title, $category, $userEmail, $thumbnailPath);
    $stmt->execute();
    $quizId = $stmt->insert_id;

    for ($i = 0; isset($_POST["question_type_$i"]); $i++) {
        $type = $_POST["question_type_$i"];
        $question = $_POST["question_text_$i"];
        $imgPath = '';

        if (isset($_FILES["image_$i"])) {
            $imgName = basename($_FILES["image_$i"]["name"]);
            $targetDir = "uploads/";
            $targetFile = $targetDir . time() . "_" . $imgName;

            if (move_uploaded_file($_FILES["image_$i"]["tmp_name"], $targetFile)) {
                $imgPath = $targetFile;
            }
        }

        $insQ = $conn->prepare("INSERT INTO questions (quiz_id, question_text, question_type, image_path) VALUES (?, ?, ?, ?)");
        $insQ->bind_param("isss", $quizId, $question, $type, $imgPath);
        $insQ->execute();
        $questionId = $insQ->insert_id;

        if ($type === "single" || $type === "multi") {
            foreach ($_POST["answer_text_{$i}"] as $j => $ansText) {
                $isCorrect = in_array($j, $_POST["correct_{$i}"]) ? 1 : 0;
                $insA = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
                $insA->bind_param("isi", $questionId, $ansText, $isCorrect);
                $insA->execute();
            }
        }
        else if ($type === "image") {
            $textAnswer = $_POST["image_answer_$i"];
            $insA = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, 1)");
            $insA->bind_param("is", $questionId, $textAnswer);
            $insA->execute();
        }
    }
    $success = true;
}
$cats = $conn->query("SELECT id, name FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Quizzy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <h2 class="mb-4">Create quiz</h2>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Quiz has been created!</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="quiz_title" class="form-control" required>
            <label for="thumbnail" class="form-label mt-3">Thumbnail</label>
            <input type="file" name="thumbnail" id="thumbnail" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category</label>
            <select name="category" class="form-select" required>
                <?php while ($cat = $cats->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id'] ?>"><?php echo $cat['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div id="question-list"></div>
        <div class="d-flex gap-3 mt-4">
            <button type="button" class="btn btn-outline-primary" onclick="addQuestion()"><i class="bi bi-plus"></i> Add question</button>
            <button type="submit" class="btn btn-success"><i class="bi bi-plus"></i> Add quiz</button>
        </div>
    </form>
</div>
<script>
    let qIndex = 0;

    function addQuestion() {
        const container = document.getElementById('question-list');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'card my-4 p-3';

        questionBlock.innerHTML = `
            <label>Question ${qIndex + 1}</label>
            <label>Picture (optional):</label>
            <input type="file" name="image_${qIndex}" accept="image/*" class="form-control mb-2">
        <div class="mb-2">
            <label class="form-label">Question type:</label>
            <select name="question_type_${qIndex}" class="form-select" onchange="updateType(this, ${qIndex})">
                <option value="single">Single choise</option>
                <option value="multi">Multiple choise</option>
                <option value="image">From image</option>
            </select>
        </div>
        <div class="mb-2">
            <label class="form-label">Treść pytania:</label>
            <input type="text" name="question_text_${qIndex}" class="form-control" required>
        </div>
        <div id="options_${qIndex}">
        </div>
    `;
        container.appendChild(questionBlock);
        updateType(questionBlock.querySelector('select'), qIndex);
        qIndex++;
    }

    function updateType(select, index) {
        const optionsDiv = document.getElementById(`options_${index}`);
        const type = select.value;

        if (type === "single" || type === "multi") {
            let html = '';
            for (let i = 0; i < 4; i++) {
                html += `
                <div class="input-group mb-2">
                    <div class="input-group-text">
                        <input type="${type === 'multi' ? 'checkbox' : 'radio'}" name="correct_${index}[]" value="${i}">
                    </div>
                    <input type="text" class="form-control" name="answer_text_${index}[]" placeholder="Answer ${i + 1}" required>
                </div>
            `;
            }
            optionsDiv.innerHTML = html;
        }
        else if (type === "image") {
            optionsDiv.innerHTML = `
            <label class="form-label">Correct answer:</label>
            <input type="text" name="image_answer_${index}" class="form-control" required>
        `;
        }
    }
</script>
<?php include 'footer.php'; ?>
</body>
</html>
