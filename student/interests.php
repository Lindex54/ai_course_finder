<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/includes/session.php';
$pdo = require __DIR__ . '/../config/database.php';

// Each interests question, by display order, and the most options a student may pick.
// Activities have no limit.
const INTEREST_LIMITS = [
    1 => 5,
    2 => null,
    3 => 5,
];

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$sessionId = ensureStudentSession($pdo);

// Load the three questions and their active options from the database.
$questions = $pdo->query(
    "SELECT id, question_text, display_order FROM questions
     WHERE category = 'interests' AND status = 'active' ORDER BY display_order"
)->fetchAll();

$optionsByQuestion = [];
$optionsStmt = $pdo->prepare(
    "SELECT id, option_text FROM question_options
     WHERE question_id = ? AND status = 'active' ORDER BY display_order, option_text"
);
foreach ($questions as $question) {
    $optionsStmt->execute([(int) $question['id']]);
    $optionsByQuestion[(int) $question['id']] = $optionsStmt->fetchAll();
}

$errors = [];
$saved = isset($_GET['saved']);
$chosen = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rows = [];

    foreach ($questions as $question) {
        $questionId = (int) $question['id'];
        $order = (int) $question['display_order'];
        $limit = INTEREST_LIMITS[$order] ?? null;

        $posted = array_values(array_unique(array_map('intval', (array) ($_POST['q' . $order] ?? []))));
        $chosen[$questionId] = $posted;

        $allowed = array_map(static fn (array $o): int => (int) $o['id'], $optionsByQuestion[$questionId]);
        $valid = array_values(array_intersect($posted, $allowed));

        if (count($valid) !== count($posted)) {
            $errors[] = 'One of your answers to "' . $question['question_text'] . '" is not a valid choice. Please try again.';
            continue;
        }

        if ($valid === []) {
            $errors[] = 'Choose at least one answer to "' . $question['question_text'] . '".';
            continue;
        }

        if ($limit !== null && count($valid) > $limit) {
            $errors[] = 'Choose no more than ' . $limit . ' answers to "' . $question['question_text'] . '".';
            continue;
        }

        foreach ($valid as $optionId) {
            $rows[] = ['question_id' => $questionId, 'option_id' => $optionId];
        }
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
                'INSERT INTO student_responses (session_id, question_id, option_id) VALUES (?, ?, ?)'
            );
            foreach ($rows as $row) {
                $insert->execute([$sessionId, $row['question_id'], $row['option_id']]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        header('Location: interests.php?saved=1');
        exit;
    }
} else {
    // Load what this student already chose.
    $existing = $pdo->prepare(
        'SELECT question_id, option_id FROM student_responses WHERE session_id = ? AND option_id IS NOT NULL'
    );
    $existing->execute([$sessionId]);
    foreach ($existing->fetchAll() as $row) {
        $chosen[(int) $row['question_id']][] = (int) $row['option_id'];
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interests | AI Course Finder</title>
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
            <p class="eyebrow">Step 2 of 5</p>
            <h1>Your interests</h1>
            <p class="lead">Tell us what you enjoy. Pick the options that feel most like you.</p>

            <?php if ($saved && $errors === []): ?>
                <div class="alert" role="status">
                    Your interests are saved.
                    <div class="alert-actions">
                        <a class="btn btn-primary" href="skills.php">Continue to skills</a>
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

            <form method="post" action="interests.php" novalidate>
                <?php foreach ($questions as $question): ?>
                    <?php
                    $questionId = (int) $question['id'];
                    $order = (int) $question['display_order'];
                    $limit = INTEREST_LIMITS[$order] ?? null;
                    $selected = $chosen[$questionId] ?? [];
                    ?>
                    <fieldset class="card subject-group">
                        <legend class="subject-legend"><?= $esc($question['question_text']) ?></legend>
                        <p class="text-secondary text-small">
                            <?= $limit !== null ? 'Choose up to ' . $limit . '.' : 'Choose as many as you like.' ?>
                        </p>

                        <div class="chip-grid">
                            <?php foreach ($optionsByQuestion[$questionId] as $option): ?>
                                <?php $optionId = (int) $option['id']; ?>
                                <label class="chip">
                                    <input type="checkbox" name="q<?= $order ?>[]" value="<?= $optionId ?>"
                                        <?= in_array($optionId, $selected, true) ? 'checked' : '' ?>>
                                    <span><?= $esc($option['option_text']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>

                <div class="start-actions">
                    <button type="submit" class="btn btn-primary">Save and continue</button>
                    <a class="btn btn-ghost" href="assessment.php">Back to academic background</a>
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
