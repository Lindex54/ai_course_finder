<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Start your assessment | AI Course Finder</title>
    <meta name="description" content="Answer five short sections about your background, interests, strengths and goals to get programme recommendations.">
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
            <p class="eyebrow">Student assessment</p>
            <h1>Let's find your best-fit programmes</h1>
            <p class="lead">
                This takes about 10 minutes. You will answer five short sections, and your
                answers are saved as you go, so you can stop and return later.
            </p>

            <div class="start-actions">
                <a class="btn btn-primary" href="start.php">Start the assessment</a>
                <a class="btn btn-ghost" href="../index.php">Back to home</a>
            </div>

            <p class="text-small text-secondary start-note">
                Your written answer in section 5 is read by an AI service to understand your goals.
                Recommendations are guidance only. Confirm entry requirements with the Academic
                Registrar before you apply.
            </p>
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
