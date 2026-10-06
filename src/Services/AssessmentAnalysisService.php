<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Runs the AI analysis for a student session and records the outcome in ai_analyses.
 * The AI writes the career feedback. The programmes shown are chosen by
 * ProgrammeRecommendationService from the database, not by the AI.
 * Failures are recorded too, so the results page can explain what happened.
 */
final class AssessmentAnalysisService
{
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

        $programmes = new ProgrammeRecommendationService($this->pdo);
        $candidates = array_map(
            static fn (array $p): array => [
                'code' => $p['code'],
                'name' => $p['name'],
                'award_type' => $p['award_type'],
                'duration' => $p['duration'],
            ],
            $programmes->candidates($sessionId)
        );

        $profileJson = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        try {
            $result = $this->gemini->analyse($profile, $candidates);
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

            // Programmes are saved only when the analysis succeeded. A failed run leaves the previous choice in place.
            if ($result !== null) {
                $programmes->store($sessionId, $result['programme_suggestions']);
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
}
