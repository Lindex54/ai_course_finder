<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Runs the AI analysis for a student session and records the outcome in ai_analyses.
 * Programme suggestions are only saved when their codes exist in the programmes table.
 * Failures are recorded too, so the results page can explain what happened.
 */
final class AssessmentAnalysisService
{
    private const MAX_PROGRAMMES = 5;

    public function __construct(
        private readonly PDO $pdo,
        private readonly GeminiService $gemini,
    ) {
    }

    /**
     * Analyses the session and stores the result. Returns the id of the stored analysis row.
     */
    public function run(int $sessionId): int
    {
        $profile = (new StudentProfileService($this->pdo))->build($sessionId);

        if (($profile['student_words'] ?? null) === null) {
            throw new RuntimeException('Please write your answer in your own words before the analysis can run.');
        }

        $programmes = $this->activeProgrammes();
        $profileJson = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        try {
            $result = $this->gemini->analyse($profile, $programmes);
            $status = 'completed';
            $error = null;
        } catch (RuntimeException $exception) {
            $status = 'failed';
            $result = null;
            $error = $exception->getMessage();
        }

        $this->pdo->beginTransaction();
        try {
            $insert = $this->pdo->prepare(
                'INSERT INTO ai_analyses (session_id, model, status, input_profile, result_json, error_message)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $insert->execute([
                $sessionId,
                $this->gemini->lastModel(),
                $status,
                $profileJson,
                $result !== null ? json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null,
                $error,
            ]);
            $analysisId = (int) $this->pdo->lastInsertId();

            if ($result !== null) {
                $this->storeRecommendations($sessionId, $result['programme_suggestions']);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return $analysisId;
    }

    /**
     * The most recent analysis for a session, or null if there is none.
     *
     * @return array{id: int, status: string, result: array<string, mixed>|null, error: string|null, created_at: string}|null
     */
    public function latest(int $sessionId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, status, result_json, error_message, created_at
             FROM ai_analyses WHERE session_id = ? ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$sessionId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        $result = $row['result_json'] !== null ? json_decode((string) $row['result_json'], true) : null;

        return [
            'id' => (int) $row['id'],
            'status' => (string) $row['status'],
            'result' => is_array($result) ? $result : null,
            'error' => $row['error_message'] !== null ? (string) $row['error_message'] : null,
            'created_at' => (string) $row['created_at'],
        ];
    }

    /**
     * Saved programme recommendations for a session, best first, with the programme details.
     *
     * @return list<array{code: string, name: string, award_type: string|null, duration: string|null, reason: string}>
     */
    public function recommendations(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.code, p.name, p.award_type, p.duration, r.explanation
             FROM recommendations r
             JOIN programmes p ON p.id = r.programme_id
             WHERE r.session_id = ?
             ORDER BY r.rank_position'
        );
        $stmt->execute([$sessionId]);

        $list = [];
        foreach ($stmt->fetchAll() as $row) {
            $list[] = [
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
                'award_type' => $row['award_type'] !== null ? (string) $row['award_type'] : null,
                'duration' => $row['duration'] !== null ? rtrim(rtrim((string) $row['duration'], '0'), '.') : null,
                'reason' => (string) ($row['explanation'] ?? ''),
            ];
        }
        return $list;
    }

    /**
     * Active programmes the AI may choose from. Only these fields are sent to the model.
     *
     * @return list<array{code: string, name: string, award_type: string|null, duration: string|null}>
     */
    private function activeProgrammes(): array
    {
        $rows = $this->pdo->query(
            "SELECT code, name, award_type, duration FROM programmes
             WHERE status = 'active' AND code IS NOT NULL ORDER BY code"
        )->fetchAll();

        $programmes = [];
        foreach ($rows as $row) {
            $programmes[] = [
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
                'award_type' => $row['award_type'] !== null ? (string) $row['award_type'] : null,
                'duration' => $row['duration'] !== null ? rtrim(rtrim((string) $row['duration'], '0'), '.') : null,
            ];
        }
        return $programmes;
    }

    /**
     * Replaces this session's recommendations with the AI's suggestions that match a real programme code.
     *
     * @param list<array{code: string, reason: string}> $suggestions
     */
    private function storeRecommendations(int $sessionId, array $suggestions): void
    {
        $idByCode = [];
        $codes = $this->pdo->query("SELECT id, code FROM programmes WHERE status = 'active' AND code IS NOT NULL")->fetchAll();
        foreach ($codes as $row) {
            $idByCode[(string) $row['code']] = (int) $row['id'];
        }

        $this->pdo->prepare('DELETE FROM recommendations WHERE session_id = ?')->execute([$sessionId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO recommendations (session_id, programme_id, match_score, rank_position, explanation)
             VALUES (?, ?, ?, ?, ?)'
        );

        $rank = 0;
        $seen = [];
        foreach ($suggestions as $suggestion) {
            $programmeId = $idByCode[$suggestion['code']] ?? null;

            // Ignore any code the database does not know, and any repeat.
            if ($programmeId === null || isset($seen[$programmeId]) || $rank >= self::MAX_PROGRAMMES) {
                continue;
            }

            $seen[$programmeId] = true;
            $rank++;
            // No numeric match score is calculated yet, so the column holds 0 and the rank gives the order.
            $insert->execute([$sessionId, $programmeId, 0, $rank, $suggestion['reason']]);
        }
    }
}
