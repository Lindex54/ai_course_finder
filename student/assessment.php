<?php

declare(strict_types=1);

session_start();

$pdo = require __DIR__ . '/../config/database.php';

// UACE: three principal subjects, plus a subsidiary subject and the General Paper.
const PRINCIPAL_COUNT = 3;
const UACE_MAX_POINTS = 20;

$performanceOptions = [
    'strong' => 'Strong',
    'average' => 'Average',
    'weak' => 'Weak',
];

// UACE principal grades and the points each one gives.
$principalGrades = [
    'A' => ['label' => 'A (6 points)', 'points' => 6],
    'B' => ['label' => 'B (5 points)', 'points' => 5],
    'C' => ['label' => 'C (4 points)', 'points' => 4],
    'D' => ['label' => 'D (3 points)', 'points' => 3],
    'E' => ['label' => 'E (2 points)', 'points' => 2],
    'O' => ['label' => 'O (1 point, subsidiary pass)', 'points' => 1],
    'F' => ['label' => 'F (0 points, fail)', 'points' => 0],
];

// Subsidiary papers (General Paper, Subsidiary Mathematics or ICT) give 1 point for a pass.
$subsidiaryGrades = [
    'PASS' => ['label' => 'Pass (1 point)', 'points' => 1],
    'FAIL' => ['label' => 'Fail (0 points)', 'points' => 0],
];

$levelLabels = [
    'o_level' => 'O-level (UCE)',
    'a_level' => 'A-level (UACE)',
];

require __DIR__ . '/includes/session.php';

$sessionId = ensureStudentSession($pdo);

// Subjects come from the database. Only active subjects are offered.
$subjects = $pdo->query(
    "SELECT id, name, slug, level, category FROM subjects WHERE status = 'active' ORDER BY level, display_order, name"
)->fetchAll();

$oLevelSubjects = [];
$aPrincipalSubjects = [];
$aSubsidiarySubjects = [];
$aGeneralPaper = [];
$subjectNames = [];

foreach ($subjects as $subject) {
    $id = (int) $subject['id'];
    $subjectNames[$id] = $subject['name'];

    if ($subject['level'] === 'o_level') {
        $oLevelSubjects[] = $subject;
    } elseif ($subject['slug'] === 'general-paper') {
        $aGeneralPaper[] = $subject;
    } elseif ($subject['category'] === 'General and subsidiary') {
        $aSubsidiarySubjects[] = $subject;
    } else {
        $aPrincipalSubjects[] = $subject;
    }
}

$errors = [];
$level = '';
$oChecked = [];
$oPerformance = [];
$aPrincipalIds = array_fill(0, PRINCIPAL_COUNT, 0);
$aPrincipalGrades = array_fill(0, PRINCIPAL_COUNT, '');
$aSubsidiaryId = 0;
$aSubsidiaryGrade = '';
$aGeneralGrade = '';
$saved = isset($_GET['saved']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = (string) ($_POST['highest_level'] ?? '');

    if (!isset($levelLabels[$level])) {
        $errors[] = 'Choose the highest level of education you completed.';
    } elseif ($level === 'o_level') {
        $postedIds = array_values(array_unique(array_map('intval', (array) ($_POST['subject_ids'] ?? []))));
        $postedPerformance = is_array($_POST['performance'] ?? null) ? $_POST['performance'] : [];
        $allowedIds = array_map(static fn (array $s): int => (int) $s['id'], $oLevelSubjects);

        if ($postedIds === []) {
            $errors[] = 'Choose at least one O-level subject you studied.';
        }

        $rows = [];
        $missing = [];
        foreach ($postedIds as $subjectId) {
            if (!in_array($subjectId, $allowedIds, true)) {
                $errors[] = 'One of the O-level subjects was not recognised. Please refresh the page and try again.';
                break;
            }

            $value = $postedPerformance[$subjectId] ?? '';
            if (!is_string($value) || !isset($performanceOptions[$value])) {
                $missing[] = $subjectNames[$subjectId];
                continue;
            }

            $rows[] = ['subject_id' => $subjectId, 'role' => 'ordinary', 'performance' => $value, 'grade' => null, 'points' => null];
        }

        if ($missing !== []) {
            $errors[] = 'Choose how well you did in: ' . implode(', ', $missing) . '.';
        }

        $oChecked = array_fill_keys($postedIds, true);
        $oPerformance = array_intersect_key(
            array_filter($postedPerformance, static fn ($v): bool => is_string($v) && isset($performanceOptions[$v])),
            $oChecked
        );
    } else {
        // A-level: three principal subjects with a grade each, a subsidiary subject
        // with a pass/fail result, and the General Paper with a pass/fail result.
        $rows = [];
        $postedPrincipals = is_array($_POST['principal_subject'] ?? null) ? $_POST['principal_subject'] : [];
        $postedGrades = is_array($_POST['principal_grade'] ?? null) ? $_POST['principal_grade'] : [];
        $principalAllowed = array_map(static fn (array $s): int => (int) $s['id'], $aPrincipalSubjects);
        $subsidiaryAllowed = array_map(static fn (array $s): int => (int) $s['id'], $aSubsidiarySubjects);
        $generalAllowed = array_map(static fn (array $s): int => (int) $s['id'], $aGeneralPaper);
        $seen = [];

        for ($i = 0; $i < PRINCIPAL_COUNT; $i++) {
            $chosen = (int) ($postedPrincipals[$i] ?? 0);
            $grade = (string) ($postedGrades[$i] ?? '');
            $aPrincipalIds[$i] = $chosen;
            $aPrincipalGrades[$i] = $grade;

            if ($chosen === 0) {
                $errors[] = 'Choose principal subject ' . ($i + 1) . '.';
            } elseif (!in_array($chosen, $principalAllowed, true)) {
                $errors[] = 'Principal subject ' . ($i + 1) . ' is not a valid choice. Please pick it from the list.';
            } elseif (isset($seen[$chosen])) {
                $errors[] = '"' . $subjectNames[$chosen] . '" is chosen more than once. Each principal subject must be different.';
            } elseif (!isset($principalGrades[$grade])) {
                $errors[] = 'Choose your grade in ' . $subjectNames[$chosen] . '.';
            } else {
                $seen[$chosen] = true;
                $rows[] = [
                    'subject_id' => $chosen,
                    'role' => 'principal',
                    'performance' => null,
                    'grade' => $grade,
                    'points' => $principalGrades[$grade]['points'],
                ];
            }
        }

        $aSubsidiaryId = (int) ($_POST['subsidiary_subject'] ?? 0);
        $aSubsidiaryGrade = (string) ($_POST['subsidiary_grade'] ?? '');
        if ($aSubsidiaryId === 0) {
            $errors[] = 'Choose your subsidiary subject.';
        } elseif (!in_array($aSubsidiaryId, $subsidiaryAllowed, true)) {
            $errors[] = 'Your subsidiary subject is not a valid choice. Please pick it from the list.';
        } elseif (!isset($subsidiaryGrades[$aSubsidiaryGrade])) {
            $errors[] = 'Choose whether you passed ' . $subjectNames[$aSubsidiaryId] . '.';
        } else {
            $rows[] = [
                'subject_id' => $aSubsidiaryId,
                'role' => 'subsidiary',
                'performance' => null,
                'grade' => $aSubsidiaryGrade,
                'points' => $subsidiaryGrades[$aSubsidiaryGrade]['points'],
            ];
        }

        // General Paper is compulsory for every UACE candidate, so it is always saved.
        $aGeneralGrade = (string) ($_POST['general_paper_grade'] ?? '');
        if ($generalAllowed === []) {
            $errors[] = 'General Paper is missing from the subject list. Please contact the administrator.';
        } elseif (!isset($subsidiaryGrades[$aGeneralGrade])) {
            $errors[] = 'Choose whether you passed the General Paper.';
        } else {
            $rows[] = [
                'subject_id' => $generalAllowed[0],
                'role' => 'general',
                'performance' => null,
                'grade' => $aGeneralGrade,
                'points' => $subsidiaryGrades[$aGeneralGrade]['points'],
            ];
        }
    }

    if ($errors === []) {
        try {
            $pdo->beginTransaction();

            $pdo->prepare('UPDATE student_sessions SET highest_level = ? WHERE id = ?')
                ->execute([$level, $sessionId]);

            $pdo->prepare('DELETE FROM student_subjects WHERE session_id = ?')->execute([$sessionId]);

            $insertSubject = $pdo->prepare(
                'INSERT INTO student_subjects (session_id, subject_id, role, performance, grade, points)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            foreach ($rows as $row) {
                $insertSubject->execute([
                    $sessionId,
                    $row['subject_id'],
                    $row['role'],
                    $row['performance'],
                    $row['grade'],
                    $row['points'],
                ]);
            }

            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        header('Location: assessment.php?saved=1');
        exit;
    }
} else {
    // Load what this student already entered.
    $current = $pdo->prepare('SELECT highest_level FROM student_sessions WHERE id = ?');
    $current->execute([$sessionId]);
    $level = (string) ($current->fetchColumn() ?: '');

    $existing = $pdo->prepare(
        'SELECT subject_id, role, performance, grade FROM student_subjects WHERE session_id = ? ORDER BY id'
    );
    $existing->execute([$sessionId]);

    $principalIndex = 0;
    foreach ($existing->fetchAll() as $row) {
        $subjectId = (int) $row['subject_id'];

        if ($row['role'] === 'ordinary') {
            $oChecked[$subjectId] = true;
            if ($row['performance'] !== null) {
                $oPerformance[$subjectId] = $row['performance'];
            }
        } elseif ($row['role'] === 'principal' && $principalIndex < PRINCIPAL_COUNT) {
            $aPrincipalIds[$principalIndex] = $subjectId;
            $aPrincipalGrades[$principalIndex] = (string) $row['grade'];
            $principalIndex++;
        } elseif ($row['role'] === 'subsidiary') {
            $aSubsidiaryId = $subjectId;
            $aSubsidiaryGrade = (string) $row['grade'];
        } elseif ($row['role'] === 'general') {
            $aGeneralGrade = (string) $row['grade'];
        }
    }
}

// Total UACE points for the summary shown after saving.
$uacePoints = null;
if ($saved && $level === 'a_level' && $errors === []) {
    $total = $pdo->prepare(
        "SELECT COALESCE(SUM(points), 0) FROM student_subjects WHERE session_id = ? AND role IN ('principal', 'subsidiary', 'general')"
    );
    $total->execute([$sessionId]);
    $uacePoints = (int) $total->fetchColumn();
}

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// Renders <option> tags for a list of subjects, marking the one that is selected.
$renderSubjectOptions = static function (array $list, int $selectedId) use ($esc): string {
    $html = '<option value="">Choose a subject</option>';
    foreach ($list as $subject) {
        $id = (int) $subject['id'];
        $html .= sprintf(
            '<option value="%d"%s>%s</option>',
            $id,
            $id === $selectedId ? ' selected' : '',
            $esc($subject['name'])
        );
    }
    return $html;
};

// Renders <option> tags for a grade list, marking the one that is selected.
$renderGradeOptions = static function (array $grades, string $selected) use ($esc): string {
    $html = '<option value="">Choose a grade</option>';
    foreach ($grades as $value => $grade) {
        $html .= sprintf(
            '<option value="%s"%s>%s</option>',
            $esc((string) $value),
            (string) $value === $selected ? ' selected' : '',
            $esc($grade['label'])
        );
    }
    return $html;
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic background | AI Course Finder</title>
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
            <p class="eyebrow">Step 1 of 5</p>
            <h1>Academic background</h1>
            <p class="lead">Tell us your highest level of education, then the subjects you studied at that level.</p>

            <?php if ($saved && $errors === []): ?>
                <div class="alert" role="status">
                    <?php if ($uacePoints !== null): ?>
                        Your UACE points total is <strong><?= $uacePoints ?> out of <?= UACE_MAX_POINTS ?></strong>.
                    <?php endif; ?>
                    Your academic background is saved.
                    <div class="alert-actions">
                        <a class="btn btn-primary" href="interests.php">Continue to interests</a>
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

            <form method="post" action="assessment.php" novalidate data-level-form>
                <fieldset class="card subject-group">
                    <legend class="subject-legend">Highest level of education completed</legend>
                    <div class="level-options">
                        <?php foreach ($levelLabels as $key => $label): ?>
                            <label class="level-option">
                                <input type="radio" name="highest_level" value="<?= $key ?>" <?= $level === $key ? 'checked' : '' ?>>
                                <span><?= $esc($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <!-- O-level: tick the subjects studied and rate each one -->
                <fieldset class="card subject-group" data-level-section="o_level" <?= $level === 'a_level' ? 'hidden' : '' ?>>
                    <legend class="subject-legend">O-level subjects</legend>
                    <p class="text-secondary text-small">Tick each subject you studied, then say how well you did in it.</p>

                    <div class="subject-head" aria-hidden="true">
                        <span>Subject</span>
                        <span>How well did you do?</span>
                    </div>

                    <?php foreach ($oLevelSubjects as $subject): ?>
                        <?php
                        $subjectId = (int) $subject['id'];
                        $inputId = 'o-subject-' . $subjectId;
                        $selectedPerformance = $oPerformance[$subjectId] ?? '';
                        ?>
                        <div class="subject-row">
                            <label class="subject-name" for="<?= $inputId ?>">
                                <input type="checkbox" id="<?= $inputId ?>" name="subject_ids[]" value="<?= $subjectId ?>"
                                    <?= isset($oChecked[$subjectId]) ? 'checked' : '' ?>>
                                <span><?= $esc($subject['name']) ?></span>
                            </label>
                            <label class="visually-hidden" for="<?= $inputId ?>-performance">
                                How well did you do in <?= $esc($subject['name']) ?>?
                            </label>
                            <select class="form-control subject-performance" id="<?= $inputId ?>-performance" name="performance[<?= $subjectId ?>]">
                                <option value="">Choose</option>
                                <?php foreach ($performanceOptions as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $selectedPerformance === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </fieldset>

                <!-- A-level: dropdowns filled from the database, with a grade for each paper -->
                <fieldset class="card subject-group" data-level-section="a_level" <?= $level === 'o_level' || $level === '' ? 'hidden' : '' ?>>
                    <legend class="subject-legend">A-level subjects</legend>
                    <p class="text-secondary text-small">
                        Choose your three principal subjects and your subsidiary subject, then enter the grade you got in each.
                    </p>

                    <?php for ($i = 0; $i < PRINCIPAL_COUNT; $i++): ?>
                        <div class="form-group">
                            <label class="form-label" for="principal-<?= $i ?>">Principal subject <?= $i + 1 ?></label>
                            <select class="form-control" id="principal-<?= $i ?>" name="principal_subject[<?= $i ?>]">
                                <?= $renderSubjectOptions($aPrincipalSubjects, $aPrincipalIds[$i]) ?>
                            </select>
                            <select class="form-control" id="principal-grade-<?= $i ?>" name="principal_grade[<?= $i ?>]" aria-label="Grade for principal subject <?= $i + 1 ?>">
                                <?= $renderGradeOptions($principalGrades, $aPrincipalGrades[$i]) ?>
                            </select>
                        </div>
                    <?php endfor; ?>

                    <div class="form-group">
                        <label class="form-label" for="subsidiary-subject">Subsidiary subject</label>
                        <select class="form-control" id="subsidiary-subject" name="subsidiary_subject">
                            <?= $renderSubjectOptions($aSubsidiarySubjects, $aSubsidiaryId) ?>
                        </select>
                        <select class="form-control" id="subsidiary-grade" name="subsidiary_grade" aria-label="Result in your subsidiary subject">
                            <?= $renderGradeOptions($subsidiaryGrades, $aSubsidiaryGrade) ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <span class="form-label"><?= $esc($aGeneralPaper[0]['name'] ?? 'General Paper') ?></span>
                        <p class="form-help">
                            Compulsory for every A-level candidate, so it is recorded automatically.
                        </p>
                        <select class="form-control" id="general-paper-grade" name="general_paper_grade" aria-label="Result in the General Paper">
                            <?= $renderGradeOptions($subsidiaryGrades, $aGeneralGrade) ?>
                        </select>
                    </div>

                    <p class="form-help">UACE points are out of <?= UACE_MAX_POINTS ?>: three principal subjects (up to 6 each) plus two subsidiary papers (1 each).</p>
                </fieldset>

                <div class="start-actions">
                    <button type="submit" class="btn btn-primary">Save and continue</button>
                    <a class="btn btn-ghost" href="index.php">Back</a>
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

<script src="../assets/js/app.js"></script>
</body>
</html>
