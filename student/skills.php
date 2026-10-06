<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/includes/session.php';
$pdo = require __DIR__ . '/../config/database.php';

const RATING_MIN = 1;
const RATING_MAX = 5;

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$sessionId = ensureStudentSession($pdo);

// The four skill questions, from the database.
$questions = $pdo->query(
    "SELECT id, question_text, display_order FROM questions
     WHERE category = 'skills' AND status = 'active' ORDER BY display_order"
)->fetchAll();

$errors = [];
$saved = isset($_GET['saved']);
$ratings = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedRatings = is_array($_POST['rating'] ?? null) ? $_POST['rating'] : [];
    $rows = [];
    $missing = [];

    foreach ($questions as $question) {
        $questionId = (int) $question['id'];
        $value = $postedRatings[$questionId] ?? '';

        if (!is_string($value) && !is_int($value)) {
            $missing[] = $question['question_text'];
            continue;
        }

        $rating = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => RATING_MIN, 'max_range' => RATING_MAX],
        ]);

        if ($rating === false) {
            $missing[] = $question['question_text'];
            continue;
        }

        $ratings[$questionId] = $rating;
        $rows[] = ['question_id' => $questionId, 'rating' => $rating];
    }

    if ($missing !== []) {
        $errors[] = 'Rate every skill from 1 to 5. Still needed: ' . implode(' ', $missing);
    }

    if ($errors === []) {
        try {
            $pdo->beginTransaction();

            $questionIds = array_map(static fn (array $q): int => (int) $q['id'], $questions);
            $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

            $delete = $pdo->prepare(
                "DELETE FROM student_responses WHERE session_id = ? AND question_id IN ($placeholders)"
            );
            $delete->execute(array_merge([$sessionId], $questionIds));

            $insert = $pdo->prepare(
                'INSERT INTO student_responses (session_id, question_id, numeric_response) VALUES (?, ?, ?)'
            );
            foreach ($rows as $row) {
                $insert->execute([$sessionId, $row['question_id'], $row['rating']]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        header('Location: skills.php?saved=1');
        exit;
    }
} else {
    // Load ratings this student already gave.
    $existing = $pdo->prepare(
        'SELECT question_id, numeric_response FROM student_responses
         WHERE session_id = ? AND numeric_response IS NOT NULL'
    );
    $existing->execute([$sessionId]);
    foreach ($existing->fetchAll() as $row) {
        $ratings[(int) $row['question_id']] = (int) $row['numeric_response'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Skills and strengths | AI Course Finder</title>
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
            <p class="eyebrow">Step 3 of 5</p>
            <h1>Skills and strengths</h1>
            <p class="lead">Rate how well each of these describes you, from 1 (not really) to 5 (very much so).</p>

            <?php if ($saved && $errors === []): ?>
                <div class="alert" role="status">
                    Your skills are saved.
                    <div class="alert-actions">
                        <a class="btn btn-primary" href="career.php">Continue to career goals</a>
                    </div>
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

            <form method="post" action="skills.php" novalidate>
                <fieldset class="card subject-group">
                    <legend class="subject-legend">Your skills</legend>
                    <div class="scale-head" aria-hidden="true">
                        <span></span>
                        <span class="scale-ends"><span>Not really</span><span>Very much so</span></span>
                    </div>

                    <?php foreach ($questions as $question): ?>
                        <?php
                        $questionId = (int) $question['id'];
                        $selected = $ratings[$questionId] ?? null;
                        ?>
                        <div class="scale-row" role="radiogroup" aria-labelledby="skill-<?= $questionId ?>">
                            <span class="scale-label" id="skill-<?= $questionId ?>"><?= $esc($question['question_text']) ?></span>
                            <div class="scale-options">
                                <?php for ($value = RATING_MIN; $value <= RATING_MAX; $value++): ?>
                                    <label class="scale-option">
                                        <input type="radio" name="rating[<?= $questionId ?>]" value="<?= $value ?>"
                                            aria-label="<?= $value ?> out of <?= RATING_MAX ?>"
                                            <?= $selected === $value ? 'checked' : '' ?>>
                                        <span><?= $value ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <div class="start-actions">
                    <button type="submit" class="btn btn-primary">Save and continue</button>
                    <a class="btn btn-ghost" href="interests.php">Back to interests</a>
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

</body>
</html>
