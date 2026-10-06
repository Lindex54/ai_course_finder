<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/includes/session.php';
require __DIR__ . '/../src/autoload.php';

use App\Services\AssessmentAnalysisService;
use App\Services\GeminiService;

$pdo = require __DIR__ . '/../config/database.php';
$gemini = require __DIR__ . '/../config/gemini.php';

$esc = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$sessionId = ensureStudentSession($pdo);
$analysisService = new AssessmentAnalysisService(
    $pdo,
    new GeminiService($gemini['api_key'], $gemini['models'])
);

$notice = null;

// The student asked to try the AI analysis again after a failure.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $analysisService->run($sessionId);
        header('Location: results.php');
        exit;
    } catch (RuntimeException $exception) {
        $notice = $exception->getMessage();
    }
}

$analysis = $analysisService->latest($sessionId);
$result = $analysis['result'] ?? [];

$programmeService = new App\Services\ProgrammeRecommendationService($pdo);
$pointsSummary = $programmeService->recommend($sessionId);
$totalPoints = $pointsSummary['total_points'];
$pointsOutOf = $pointsSummary['out_of'];

$savedProgrammes = $programmeService->saved($sessionId);
$programmeGroups = [
    "Bachelor's degrees" => array_values(array_filter($savedProgrammes, static fn (array $p): bool => $p['award_type'] === "Bachelor's Degree")),
    'Diplomas' => array_values(array_filter($savedProgrammes, static fn (array $p): bool => $p['award_type'] === 'Diploma')),
    'Higher Education Access Certificates' => array_values(array_filter($savedProgrammes, static fn (array $p): bool => $p['award_type'] === 'Higher Education Access Certificate')),
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your results | AI Course Finder</title>
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
        <div class="container results">

            <header class="results-header">
                <p class="eyebrow">Your results</p>
                <h1>What your answers suggest</h1>
            </header>

            <?php if ($notice !== null): ?>
                <div class="alert alert-error" role="alert"><?= $esc($notice) ?></div>
            <?php endif; ?>

            <?php if ($analysis === null): ?>
                <p class="lead">There is no analysis yet. Finish the assessment to see your results.</p>
                <a class="btn btn-primary" href="index.php">Go to the assessment</a>

            <?php elseif ($analysis['status'] === 'failed'): ?>
                <div class="alert alert-error" role="alert">
                    <strong>We could not analyse your answers just now.</strong>
                    Your answers are saved. You can try again.
                    <?php if ($analysis['error'] !== null): ?>
                        <p class="form-help"><?= $esc($analysis['error']) ?></p>
                    <?php endif; ?>
                </div>
                <form method="post" action="results.php" data-busy-form>
                    <button type="submit" class="btn btn-primary">Try the analysis again</button>
                </form>

            <?php else: ?>

                <?php if ($totalPoints !== null): ?>
                    <p class="points-total">
                        Your UACE points: <strong><?= $totalPoints ?></strong> out of <?= $pointsOutOf ?>
                    </p>
                <?php endif; ?>

                <p class="results-summary"><?= $esc((string) ($result['summary'] ?? '')) ?></p>

                <?php if (!empty($result['career_directions'])): ?>
                    <section class="result-section">
                        <h2 class="result-heading">Career directions to explore</h2>
                        <ol class="result-list">
                            <?php foreach ($result['career_directions'] as $direction): ?>
                                <li>
                                    <h3><?= $esc($direction['title']) ?></h3>
                                    <p><?= $esc($direction['reason']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if ($savedProgrammes !== []): ?>
                    <section class="result-section">
                        <h2 class="result-heading">Programmes you could consider</h2>
                        <p class="text-secondary text-small">
                            From the 2026/2027 catalogue. Entry requirements are not yet verified for these programmes,
                            so check them with the Academic Registrar before you apply.
                        </p>

                        <?php foreach ($programmeGroups as $groupTitle => $programmes): ?>
                            <?php if ($programmes === []) { continue; } ?>
                            <h3 class="programme-group-title"><?= $esc($groupTitle) ?></h3>
                            <ul class="programme-list">
                                <?php foreach ($programmes as $programme): ?>
                                    <li class="programme-row">
                                        <span class="programme-code"><?= $esc($programme['code']) ?></span>
                                        <div class="programme-body">
                                            <h3><?= $esc($programme['name']) ?></h3>
                                            <p class="programme-meta">
                                                <?= $esc($programme['duration'] !== null ? $programme['duration'] . ' years' : '') ?>
                                            </p>
                                            <?php if ($programme['requirement'] !== null): ?>
                                                <p class="programme-requirement"><?= $esc($programme['requirement']) ?></p>
                                            <?php endif; ?>
                                            <?php if ($programme['reason'] !== null): ?>
                                                <p class="programme-reason"><?= $esc($programme['reason']) ?></p>
                                            <?php endif; ?>
                                            <p class="programme-status">Entry requirements to be checked</p>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

                <div class="results-two">
                    <?php if (!empty($result['strengths'])): ?>
                        <section class="result-section">
                            <h2 class="result-heading">Your strengths</h2>
                            <ul class="check-list">
                                <?php foreach ($result['strengths'] as $item): ?>
                                    <li><?= $esc($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($result['interest_themes'])): ?>
                        <section class="result-section">
                            <h2 class="result-heading">Themes in your interests</h2>
                            <div class="chip-grid">
                                <?php foreach ($result['interest_themes'] as $theme): ?>
                                    <span class="badge"><?= $esc($theme) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>

                <?php if (!empty($result['next_steps'])): ?>
                    <section class="result-section">
                        <h2 class="result-heading">Next steps</h2>
                        <ol class="step-list">
                            <?php foreach ($result['next_steps'] as $step): ?>
                                <li><?= $esc($step) ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <aside class="results-note">
                    <?php if (!empty($result['caveats'])): ?>
                        <p><strong>Please note</strong></p>
                        <ul>
                            <?php foreach ($result['caveats'] as $caveat): ?>
                                <li><?= $esc($caveat) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <p class="results-note-footer">
                        This is guidance only, generated by an AI service from your answers. It is not an admissions decision.
                    </p>
                </aside>
            <?php endif; ?>

            <div class="start-actions">
                <a class="btn btn-ghost" href="future.php">Back to your answer</a>
            </div>
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
        <strong>Trying again</strong>
        <p>We are reading your answers once more. This usually takes under a minute.</p>
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
