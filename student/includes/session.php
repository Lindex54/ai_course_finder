<?php

declare(strict_types=1);

/**
 * Returns the id of the student's in-progress session, creating one if needed.
 * The id is kept in the PHP session. If the stored session no longer exists in the
 * database (for example, after a reset), a new one is created.
 */
function ensureStudentSession(PDO $pdo): int
{
    $sessionId = (int) ($_SESSION['student_session_id'] ?? 0);

    if ($sessionId > 0) {
        $check = $pdo->prepare("SELECT id FROM student_sessions WHERE id = ? AND status = 'in_progress'");
        $check->execute([$sessionId]);
        if ($check->fetchColumn() !== false) {
            return $sessionId;
        }
    }

    $insert = $pdo->prepare('INSERT INTO student_sessions (session_token) VALUES (?)');
    $insert->execute([bin2hex(random_bytes(32))]);
    $sessionId = (int) $pdo->lastInsertId();
    $_SESSION['student_session_id'] = $sessionId;

    return $sessionId;
}
