<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/includes/session.php';
$pdo = require __DIR__ . '/../config/database.php';

const MAX_ANSWER_LENGTH = 1500;
const MIN_ANSWER_WORDS = 5;

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$sessionId = ensureStudentSession($pdo);

$questionStmt = $pdo->query(
    "SELECT id, question_text FROM questions WHERE category = 'free_text' AND status = 'active' ORDER BY display_order LIMIT 1"
);
$question = $questionStmt->fetch();

if ($question === false) {
    http_response_code(500);
    exit('The free-text question is missing from the database. Please run the career seed.');
}

$questionId = (int) $question['id'];
$errors = [];
$saved = isset($_GET['saved']);
$answer = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answer = trim((string) ($_POST['answer'] ?? ''));
    $length = mb_strlen($answer);
    $words = count(preg_split('/\s+/u', $answer, -1, PREG_SPLIT_NO_EMPTY) ?: []);

    if ($answer === '') {
        $errors[] = 'Write a few sentences about the future you want.';
    } elseif ($words < MIN_ANSWER_WORDS) {
        $errors[] = 'Write at least ' . MIN_ANSWER_WORDS . ' words so we can understand what you mean.';
    } elseif ($length > MAX_ANSWER_LENGTH) {
        $errors[] = 'Your answer is too long. Keep it under ' . MAX_ANSWER_LENGTH . ' characters.';
    }

    if ($errors === []) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare('DELETE FROM student_responses WHERE session_id = ? AND question_id = ?')
                ->execute([$sessionId, $questionId]);

            $pdo->prepare(
                'INSERT INTO student_responses (session_id, question_id, text_response) VALUES (?, ?, ?)'
            )->execute([$sessionId, $questionId, $answer]);

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        // All five steps are complete, so run the AI analysis and show the results.
        // Failures are stored and shown on the results page, so the student is not blocked.
        require __DIR__ . '/../src/autoload.php';
        $gemini = require __DIR__ . '/../config/gemini.php';
        $analysisService = new App\Services\AssessmentAnalysisService(
            $pdo,
            new App\Services\GeminiService($gemini['api_key'], $gemini['models'])
        );
        $analysisService->run($sessionId);

        header('Location: results.php');
        exit;
    }
} else {
    $existing = $pdo->prepare(
        'SELECT text_response FROM student_responses WHERE session_id = ? AND question_id = ? ORDER BY id DESC LIMIT 1'
    );
    $existing->execute([$sessionId, $questionId]);
    $answer = (string) ($existing->fetchColumn() ?: '');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>In your words | AI Course Finder</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <a class="site-brand" href="../index.php">AI Course Finder</a>
        <nav class="site-nav" aria-label="Main">
            <a href="../index.php">Home</a>
            <a href="index.php" aria-current="page">Find a programme</a>
            <a href="../admin/index.php">Admin</a>
        </nav>
    </div>
</header>

<main>
    <section class="section">
        <div class="container narrow">
            <p class="eyebrow">Step 5 of 5</p>
            <h1>In your words</h1>
            <p class="lead"><?= $esc($question['question_text']) ?></p>

            <?php if ($saved && $errors === []): ?>
                <div class="alert" role="status">
                    Your answer is saved. The next section will open here once it is built.
                </div>
            <?php endif; ?>

            <?php if ($errors !== []): ?>
                <div class="alert alert-error" role="alert">
                    <strong>Please fix the following:</strong>
                    <ul class="error-list">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="future.php" novalidate data-busy-form>

                <div class="card subject-group">
                    <div class="form-group">
                        <label class="form-label" for="answer">Your answer</label>
                        <textarea class="form-control answer-box" id="answer" name="answer" rows="8"
                            maxlength="<?= MAX_ANSWER_LENGTH ?>"
                            placeholder="For example: I want to work in agriculture helping farmers grow more food, and maybe run my own business one day."><?= $esc($answer) ?></textarea>
                        <span class="form-help">A few sentences is enough. Up to <?= MAX_ANSWER_LENGTH ?> characters.</span>
                    </div>

                    <p class="notice">
                        Your answer is read by an AI service to understand your goals. It is used only to
                        suggest programmes, and recommendations are guidance only.
                    </p>
                </div>

                <div class="start-actions">
                    <button type="submit" class="btn btn-primary" data-busy-button>
                        <span data-busy-label>Save and continue</span>
                    </button>
                    <a class="btn btn-ghost" href="career.php">Back to career goals</a>
                </div>
            </form>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <span class="text-secondary">AI Course Finder</span>
        <span class="text-small text-secondary">Guidance only. Not an admissions decision.</span>
    </div>
</footer>

<div class="busy-overlay" data-busy-overlay hidden role="status" aria-live="polite">
    <div class="busy-card">
        <span class="spinner" aria-hidden="true"></span>
        <strong>Reading your answers</strong>
        <p>We are putting together your career directions and programme options. This usually takes under a minute.</p>
    </div>
</div>

<script>
(function () {
    var form = document.querySelector('[data-busy-form]');
    var overlay = document.querySelector('[data-busy-overlay]');
    if (!form || !overlay) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (form.getAttribute('data-submitted') === 'true') {
            event.preventDefault();
            return;
        }
        form.setAttribute('data-submitted', 'true');
        overlay.hidden = false;

        var button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
        }
    });

    // If the browser restores this page from its cache, hide the overlay again.
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            overlay.hidden = true;
            form.removeAttribute('data-submitted');
            var button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = false;
            }
        }
    });
})();
</script>
</body>
</html>
