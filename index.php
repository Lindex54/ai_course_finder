<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Course Finder</title>
    <meta name="description" content="Find a programme that fits you. Answer a short set of questions and get recommendations from the 2026/2027 programme catalogue.">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <a class="site-brand" href="index.php">AI Course Finder</a>
        <nav class="site-nav" aria-label="Main">
            <a href="index.php" aria-current="page">Home</a>
            <a href="student/index.php">Find a programme</a>
            <a href="admin/index.php">Admin</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-text">
                <p class="eyebrow">2026/2027 admissions</p>
                <h1>Find the programme that fits how you learn and what you want to do next.</h1>
                <p class="lead">
                    Answer a short set of questions about your interests, background and goals.
                    The finder compares your answers with the programme catalogue and shows the
                    options that match, with the reasons for each one.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="student/index.php">Start the assessment</a>
                    <a class="btn btn-ghost" href="#how-it-works">How it works</a>
                </div>
                <p class="text-small text-secondary hero-note">
                    Recommendations are guidance only. Confirm entry requirements with the
                    Academic Registrar before you apply.
                </p>
            </div>

            <aside class="card hero-panel" aria-label="What the finder covers">
                <h3>What the finder covers</h3>
                <ul class="level-list">
                    <li><span>Doctoral</span><span>PhD programmes</span></li>
                    <li><span>Master's</span><span>Masters programmes</span></li>
                    <li><span>Postgraduate</span><span>Diplomas</span></li>
                    <li><span>Bachelor's</span><span>Degree programmes</span></li>
                    <li><span>Diploma</span><span>Diploma programmes</span></li>
                    <li><span>Certificate</span><span>Certificate courses</span></li>
                </ul>
            </aside>
        </div>
    </section>

    <section class="section" id="how-it-works">
        <div class="container">
            <h2>How it works</h2>
            <p class="text-secondary section-intro">Three steps from first question to a shortlist.</p>

            <ol class="steps grid">
                <li class="card">
                    <span class="step-number">1</span>
                    <h3>Tell us about you</h3>
                    <p class="text-secondary">Share your current qualifications, the subjects you enjoy and the kind of work you are considering.</p>
                </li>
                <li class="card">
                    <span class="step-number">2</span>
                    <h3>Check the entry rules</h3>
                    <p class="text-secondary">The finder checks your answers against each programme's entry requirements and shows which ones you meet.</p>
                </li>
                <li class="card">
                    <span class="step-number">3</span>
                    <h3>Review your shortlist</h3>
                    <p class="text-secondary">Each recommendation includes the reasons it fits, so you can compare options before you decide.</p>
                </li>
            </ol>
        </div>
    </section>

    <section class="section section-muted">
        <div class="container split">
            <div>
                <h2>Built on the official catalogue</h2>
                <p class="text-secondary">
                    Programme names, codes and durations come from the Academic Registrar's
                    2026/2027 admissions call. Where a programme's details are unclear, the
                    admissions call takes priority over any other source.
                </p>
            </div>
            <div class="card">
                <h3>Before you apply</h3>
                <ul class="check-list">
                    <li>Check the specific entry requirements for your chosen programme.</li>
                    <li>Confirm the study mode and duration with the Academic Registrar.</li>
                    <li>Keep your academic certificates ready for the application.</li>
                </ul>
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
