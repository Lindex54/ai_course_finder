<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Builds the structured profile for one student session from the database.
 * This is the only input the AI receives, so it contains only what the student entered.
 */
final class StudentProfileService
{
    public const UACE_MAX_POINTS = 20;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function build(int $sessionId): array
    {
        $session = $this->pdo->prepare('SELECT highest_level FROM student_sessions WHERE id = ?');
        $session->execute([$sessionId]);
        $level = (string) ($session->fetchColumn() ?: '');

        return [
            'education' => $this->education($sessionId, $level),
            'interests' => $this->interests($sessionId),
            'skills' => $this->skills($sessionId),
            'career' => $this->career($sessionId),
            'student_words' => $this->freeText($sessionId),
        ];
    }

    /** @return array<string, mixed> */
    private function education(int $sessionId, string $level): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.name, ss.role, ss.performance, ss.grade, ss.points
             FROM student_subjects ss
             JOIN subjects s ON s.id = ss.subject_id
             WHERE ss.session_id = ?
             ORDER BY ss.id'
        );
        $stmt->execute([$sessionId]);

        if ($level === 'o_level') {
            $subjects = [];
            foreach ($stmt->fetchAll() as $row) {
                if ($row['role'] === 'ordinary') {
                    $subjects[] = ['subject' => $row['name'], 'performance' => $row['performance']];
                }
            }
            return ['level' => 'O-level', 'subjects' => $subjects];
        }

        $principal = [];
        $subsidiary = [];
        $general = [];
        $total = 0;

        foreach ($stmt->fetchAll() as $row) {
            $item = [
                'subject' => $row['name'],
                'grade' => $row['grade'],
                'points' => (int) $row['points'],
            ];
            $total += (int) $row['points'];

            if ($row['role'] === 'principal') {
                $principal[] = $item;
            } elseif ($row['role'] === 'subsidiary') {
                $subsidiary[] = $item;
            } elseif ($row['role'] === 'general') {
                $general[] = $item;
            }
        }

        return [
            'level' => 'A-level',
            'principal_subjects' => $principal,
            'subsidiary_subject' => $subsidiary[0] ?? null,
            'general_paper' => $general[0] ?? null,
            'uace_points' => $total,
            'uace_points_out_of' => self::UACE_MAX_POINTS,
        ];
    }

    /** @return array<string, list<string>> */
    private function interests(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT q.display_order, o.option_text
             FROM student_responses r
             JOIN questions q ON q.id = r.question_id
             JOIN question_options o ON o.id = r.option_id
             WHERE r.session_id = ? AND q.category = 'interests'
             ORDER BY q.display_order, o.display_order"
        );
        $stmt->execute([$sessionId]);

        $groups = ['subjects_enjoyed' => [], 'activities_enjoyed' => [], 'areas_of_interest' => []];
        $keys = [1 => 'subjects_enjoyed', 2 => 'activities_enjoyed', 3 => 'areas_of_interest'];

        foreach ($stmt->fetchAll() as $row) {
            $key = $keys[(int) $row['display_order']] ?? null;
            if ($key !== null) {
                $groups[$key][] = $row['option_text'];
            }
        }

        return $groups;
    }

    /** @return list<array{skill: string, rating: int, out_of: int}> */
    private function skills(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT q.question_text, r.numeric_response
             FROM student_responses r
             JOIN questions q ON q.id = r.question_id
             WHERE r.session_id = ? AND q.category = 'skills' AND r.numeric_response IS NOT NULL
             ORDER BY q.display_order"
        );
        $stmt->execute([$sessionId]);

        $skills = [];
        foreach ($stmt->fetchAll() as $row) {
            $skills[] = [
                'skill' => $row['question_text'],
                'rating' => (int) $row['numeric_response'],
                'out_of' => 5,
            ];
        }
        return $skills;
    }

    /** @return array<string, mixed> */
    private function career(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT q.display_order, o.option_text
             FROM student_responses r
             JOIN questions q ON q.id = r.question_id
             JOIN question_options o ON o.id = r.option_id
             WHERE r.session_id = ? AND q.category = 'career'
             ORDER BY q.display_order, o.display_order"
        );
        $stmt->execute([$sessionId]);

        $careers = [];
        $workPreference = null;
        $ambition = null;

        foreach ($stmt->fetchAll() as $row) {
            switch ((int) $row['display_order']) {
                case 1:
                    $careers[] = $row['option_text'];
                    break;
                case 2:
                    $workPreference = $row['option_text'];
                    break;
                case 3:
                    $ambition = $row['option_text'];
                    break;
            }
        }

        return [
            'careers_considered' => $careers,
            'preferred_work' => $workPreference,
            'future_ambition' => $ambition,
        ];
    }

    private function freeText(int $sessionId): ?string
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.text_response FROM student_responses r
             JOIN questions q ON q.id = r.question_id
             WHERE r.session_id = ? AND q.category = 'free_text' AND r.text_response IS NOT NULL
             ORDER BY r.id DESC LIMIT 1"
        );
        $stmt->execute([$sessionId]);
        $text = $stmt->fetchColumn();

        return is_string($text) && trim($text) !== '' ? trim($text) : null;
    }
}
