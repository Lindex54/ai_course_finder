<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/includes/session.php';
$pdo = require __DIR__ . '/../config/database.php';

// Starting the assessment always begins a new one, so answers from an earlier student
// on the same browser are not shown. The earlier session is kept in the database, marked as abandoned.
$previous = (int) ($_SESSION['student_session_id'] ?? 0);
if ($previous > 0) {
    $pdo->prepare("UPDATE student_sessions SET status = 'abandoned' WHERE id = ? AND status = 'in_progress'")
        ->execute([$previous]);
}

unset($_SESSION['student_session_id']);
ensureStudentSession($pdo);

header('Location: assessment.php');
exit;
