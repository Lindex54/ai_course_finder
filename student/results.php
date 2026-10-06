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
        <div class="container narrow">
            <p class="eyebrow">Your results</p>
            <h1>What your answers suggest</h1>

            <?php if ($notice !== null): ?>
                <div class="alert alert-error" role="alert"><?= $esc($notice) ?></div>
            <?php endif; ?>

            <?php if ($analysis === null): ?>
                <div class="card">
                    <p>There is no analysis yet. Finish the assessment to see your results.</p>
                    <a class="btn btn-primary" href="index.php">Go to the assessment</a>
                </div>

            <?php elseif ($analysis['status'] === 'failed'): ?>
                <div class="alert alert-error" role="alert">
                    <strong>We could not analyse your answers just now.</strong>
                    Your answers are saved. You can try again.
                    <?php if ($analysis['error'] !== null): ?>
                        <p class="form-help"><?= $esc($analysis['error']) ?></p>
                    <?php endif; ?>
                </div>
                <form method="post" action="results.php">
                    <button type="submit" class="btn btn-primary">Try the analysis again</button>
                </form>

            <?php else: ?>
                <?php $result = $analysis['result'] ?? []; ?>

                <div class="card result-summary">
                    <p class="result-summary-text"><?= $esc((string) ($result['summary'] ?? '')) ?></p>
                </div>

                <?php if (!empty($result['career_directions'])): ?>
                    <h2 class="result-heading">Career directions to explore</h2>
                    <div class="grid">
                        <?php foreach ($result['career_directions'] as $direction): ?>
                            <article class="card">
                                <h3 class="card-title"><?= $esc($direction['title']) ?></h3>
                                <p class="text-secondary"><?= $esc($direction['reason']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php $programmes = $analysisService->recommendations($sessionId); ?>
                <?php if ($programmes !== []): ?>
                    <h2 class="result-heading">Programmes you could consider</h2>
                    <p class="text-secondary text-small">
                        From the 2026/2027 catalogue. Entry requirements are not yet verified for these programmes,
                        so check them with the Academic Registrar before you apply.
                    </p>
                    <div class="grid">
                        <?php foreach ($programmes as $programme): ?>
                            <article class="card">
                                <p class="text-small text-secondary programme-code"><?= $esc($programme['code']) ?></p>
                                <h3 class="card-title"><?= $esc($programme['name']) ?></h3>
                                <p class="text-small text-secondary">
                                    <?= $esc(trim(($programme['award_type'] ?? '') . ' · ' . ($programme['duration'] !== null ? $programme['duration'] . ' years' : ''), ' ·')) ?>
                                </p>
                                <p class="text-secondary"><?= $esc($programme['reason']) ?></p>
                                <span class="badge">Entry requirements: needs review</span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['strengths'])): ?>
                    <h2 class="result-heading">Your strengths</h2>
                    <ul class="check-list">
                        <?php foreach ($result['strengths'] as $item): ?>
                            <li><?= $esc($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($result['interest_themes'])): ?>
                    <h2 class="result-heading">Themes in your interests</h2>
                    <div class="chip-grid">
                        <?php foreach ($result['interest_themes'] as $theme): ?>
                            <span class="badge"><?= $esc($theme) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['next_steps'])): ?>
                    <h2 class="result-heading">Next steps</h2>
                    <ul class="check-list">
                        <?php foreach ($result['next_steps'] as $step): ?>
                            <li><?= $esc($step) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="notice">
                    <?php if (!empty($result['caveats'])): ?>
                        <strong>Please note:</strong>
                        <ul class="error-list">
                            <?php foreach ($result['caveats'] as $caveat): ?>
                                <li><?= $esc($caveat) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    This is guidance only, generated by an AI service from your answers. It is not an admissions decision.
                </div>
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

</body>
</html>
