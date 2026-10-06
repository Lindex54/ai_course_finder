<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * Chooses the programmes a student may be shown, from the programmes table.
 *
 * Rules:
 * - 10 UACE points or more: every active bachelor's degree.
 * - Under 10 UACE points: bachelor's degrees whose minimum points the student meets,
 *   plus diplomas related to the student's subjects.
 * - O-level students: diplomas related to their subjects.
 * - A-level students: a programme is dropped when their principal subjects do not meet
 *   its essential A-level subject requirement (from the official 2026/2027 document).
 * - IT-related students: the computing programmes are added, within the rules above.
 *
 * The AI chooses the final programmes from these candidates, and the results are saved here.
 */
final class ProgrammeRecommendationService
{
    public const UACE_MAX_POINTS = 20;

    // Students at or above this many UACE points are shown every bachelor's degree.
    public const HIGH_POINTS_FROM = 10;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{total_points: int|null, out_of: int, bachelors: list<array<string, mixed>>, diplomas: list<array<string, mixed>>}
     */
    public function recommend(int $sessionId): array
    {
        $level = $this->level($sessionId);

        // Every programme in the catalogue needs A-level entry, diplomas included, so
        // students without A-level subjects have no programmes to choose from.
        if ($level !== 'a_level') {
            return [
                'total_points' => null,
                'out_of' => self::UACE_MAX_POINTS,
                'bachelors' => [],
                'diplomas' => [],
            ];
        }

        $total = $this->uacePoints($sessionId);
        $subjects = $this->subjectSlugs($sessionId);
        $principals = $this->principalSlugs($sessionId);

        $bachelors = [];
        $diplomas = [];

        if ($total !== null && $total >= self::HIGH_POINTS_FROM) {
            $bachelors = $this->bachelors(null);
        } else {
            if ($total !== null) {
                $bachelors = $this->bachelors($total);
            }
            $diplomas = $this->relatedDiplomas($subjects);
        }

        // A-level students must meet each programme's essential subjects.
        if ($principals !== null) {
            $bachelors = $this->meetingRequirements($bachelors, $principals);
            $diplomas = $this->meetingRequirements($diplomas, $principals);
        }

        // Students whose answers point to technology always see the computing diplomas.
        if ($this->isInformationTechnologyStudent($sessionId)) {
            $diplomas = $this->addMissing($diplomas, $this->computingProgrammes('Diploma'));
            if ($principals !== null) {
                $diplomas = $this->meetingRequirements($diplomas, $principals);
            }
        }

        return [
            'total_points' => $total,
            'out_of' => self::UACE_MAX_POINTS,
            'bachelors' => $bachelors,
            'diplomas' => $diplomas,
        ];
    }

    /**
     * Every programme the rules allow for this student. The AI chooses from these.
     *
     * @return list<array<string, mixed>>
     */
    public function candidates(int $sessionId): array
    {
        $recommendation = $this->recommend($sessionId);
        return array_merge($recommendation['bachelors'], $recommendation['diplomas']);
    }

    /**
     * Saves the programmes the AI chose, replacing any earlier ones. Only codes from the
     * candidate list are kept, and each is saved once, in the order given.
     *
     * @param list<array{code: string, reason: string}> $picks
     */
    public function store(int $sessionId, array $picks): void
    {
        $candidates = $this->candidates($sessionId);
        $allowed = [];
        foreach ($candidates as $candidate) {
            $allowed[$candidate['code']] = (int) $candidate['id'];
        }

        $this->pdo->prepare('DELETE FROM recommendations WHERE session_id = ?')->execute([$sessionId]);

        $insert = $this->pdo->prepare(
            'INSERT INTO recommendations (session_id, programme_id, match_score, rank_position, explanation)
             VALUES (?, ?, 0, ?, ?)'
        );

        $rank = 0;
        $seen = [];
        foreach ($picks as $pick) {
            $programmeId = $allowed[$pick['code']] ?? null;
            if ($programmeId === null || isset($seen[$programmeId])) {
                continue;
            }

            $seen[$programmeId] = true;
            $insert->execute([$sessionId, $programmeId, ++$rank, $pick['reason'] !== '' ? $pick['reason'] : null]);
        }

        // IT-related students always get the computing programmes they are allowed,
        // even if the AI left them out.
        if ($this->isInformationTechnologyStudent($sessionId)) {
            foreach ($this->computingProgrammes(null) as $programme) {
                if (!isset($allowed[$programme['code']]) || isset($seen[$programme['id']])) {
                    continue;
                }

                $seen[$programme['id']] = true;
                $insert->execute([$sessionId, $programme['id'], ++$rank, null]);
            }
        }
    }

    /**
     * The programmes saved for this student, in the order the AI gave them.
     *
     * @return list<array{code: string, name: string, award_type: string, duration: string|null, reason: string|null, requirement: string|null}>
     */
    public function saved(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.code, p.name, p.award_type, p.duration, p.a_level_requirement, r.explanation
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
                'award_type' => (string) $row['award_type'],
                'duration' => $row['duration'] !== null ? rtrim(rtrim((string) $row['duration'], '0'), '.') : null,
                'reason' => $row['explanation'] !== null ? (string) $row['explanation'] : null,
                'requirement' => $row['a_level_requirement'] !== null ? (string) $row['a_level_requirement'] : null,
            ];
        }
        return $list;
    }

    private function level(int $sessionId): string
    {
        $stmt = $this->pdo->prepare('SELECT highest_level FROM student_sessions WHERE id = ?');
        $stmt->execute([$sessionId]);
        return (string) ($stmt->fetchColumn() ?: '');
    }

    private function uacePoints(int $sessionId): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(points), 0) FROM student_subjects
             WHERE session_id = ? AND role IN ('principal', 'subsidiary', 'general')"
        );
        $stmt->execute([$sessionId]);
        return (int) $stmt->fetchColumn();
    }

    /** @return list<string> */
    private function subjectSlugs(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT s.slug FROM student_subjects ss
             JOIN subjects s ON s.id = ss.subject_id WHERE ss.session_id = ?'
        );
        $stmt->execute([$sessionId]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return list<string> Slugs of the principal subjects only. */
    private function principalSlugs(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.slug FROM student_subjects ss
             JOIN subjects s ON s.id = ss.subject_id
             WHERE ss.session_id = ? AND ss.role = 'principal'"
        );
        $stmt->execute([$sessionId]);
        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Bachelor's degrees. With points given, only those whose recorded minimum the student meets.
     *
     * @return list<array<string, mixed>>
     */
    private function bachelors(?int $points): array
    {
        $sql = "SELECT id, code, name, award_type, duration, a_level_rules, a_level_requirement
                FROM programmes WHERE status = 'active' AND award_type = ?";
        $params = ["Bachelor's Degree"];

        if ($points !== null) {
            $sql .= ' AND minimum_points IS NOT NULL AND minimum_points <= ?';
            $params[] = $points;
        }

        $stmt = $this->pdo->prepare($sql . ' ORDER BY name');
        $stmt->execute($params);
        return $this->shape($stmt->fetchAll());
    }

    /**
     * Diplomas whose related subjects include at least one of the student's subjects.
     *
     * @param list<string> $subjects
     * @return list<array<string, mixed>>
     */
    private function relatedDiplomas(array $subjects): array
    {
        if ($subjects === []) {
            return [];
        }

        $stmt = $this->pdo->prepare(
            "SELECT id, code, name, award_type, duration, related_subjects, a_level_rules, a_level_requirement
             FROM programmes
             WHERE status = 'active' AND award_type = 'Diploma' AND related_subjects IS NOT NULL ORDER BY name"
        );
        $stmt->execute();

        $matches = [];
        foreach ($stmt->fetchAll() as $row) {
            $related = array_map('trim', explode(',', (string) $row['related_subjects']));
            if (array_intersect($related, $subjects) !== []) {
                $matches[] = $row;
            }
        }

        return $this->shape($matches);
    }

    /**
     * Active computing programmes, optionally limited to one award type.
     *
     * @return list<array<string, mixed>>
     */
    private function computingProgrammes(?string $awardType): array
    {
        $sql = "SELECT id, code, name, award_type, duration, a_level_rules, a_level_requirement
                FROM programmes WHERE status = 'active' AND field_of_study = 'Computing'";
        $params = [];

        if ($awardType !== null) {
            $sql .= ' AND award_type = ?';
            $params[] = $awardType;
        }

        $stmt = $this->pdo->prepare($sql . ' ORDER BY name');
        $stmt->execute($params);
        return $this->shape($stmt->fetchAll());
    }

    /**
     * Keeps the programmes that have an A-level entry route in the official document and
     * whose essential subjects the student's principal subjects meet. Programmes that only
     * take diploma holders have no A-level route, so they are not kept.
     *
     * @param list<array<string, mixed>> $programmes
     * @param list<string> $principals
     * @return list<array<string, mixed>>
     */
    private function meetingRequirements(array $programmes, array $principals): array
    {
        return array_values(array_filter(
            $programmes,
            fn (array $programme): bool => $programme['rules'] !== null
                && $this->satisfies($programme['rules'], $principals)
        ));
    }

    /**
     * True when the principal subjects can fill every group, each subject used once.
     * Groups with a list of subjects are filled first, so a subject that is needed in
     * two places is not counted twice.
     *
     * @param list<array{subjects: list<string>|null, count: int}> $groups
     * @param list<string> $principals
     */
    private function satisfies(array $groups, array $principals): bool
    {
        usort($groups, static fn (array $a, array $b): int
            => ($a['subjects'] === null) <=> ($b['subjects'] === null));

        $available = $principals;
        foreach ($groups as $group) {
            $needed = (int) $group['count'];
            $picked = [];

            foreach ($available as $index => $subject) {
                if ($needed === 0) {
                    break;
                }
                if ($group['subjects'] === null || in_array($subject, $group['subjects'], true)) {
                    $picked[] = $index;
                    $needed--;
                }
            }

            if ($needed > 0) {
                return false;
            }

            foreach ($picked as $index) {
                unset($available[$index]);
            }
        }

        return true;
    }

    /**
     * True when the student's answers point to technology: an IT or engineering career,
     * an information technology or engineering interest, or an ICT or technology subject.
     */
    private function isInformationTechnologyStudent(int $sessionId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM student_responses r
             JOIN question_options o ON o.id = r.option_id
             JOIN questions q ON q.id = r.question_id
             WHERE r.session_id = ?
               AND ((q.category = 'career' AND o.option_value IN ('software-it', 'engineer'))
                 OR (q.category = 'interests' AND o.option_value IN ('information-technology', 'engineering-construction')))"
        );
        $stmt->execute([$sessionId]);

        if ((int) $stmt->fetchColumn() > 0) {
            return true;
        }

        $subjects = $this->pdo->prepare(
            "SELECT COUNT(*) FROM student_subjects ss
             JOIN subjects s ON s.id = ss.subject_id
             WHERE ss.session_id = ? AND s.slug IN ('ict', 'subsidiary-ict', 'technology-and-design')"
        );
        $subjects->execute([$sessionId]);
        return (int) $subjects->fetchColumn() > 0;
    }

    /**
     * Adds programmes that are not already in the list, keeping the original order first.
     *
     * @param list<array<string, mixed>> $list
     * @param list<array<string, mixed>> $extra
     * @return list<array<string, mixed>>
     */
    private function addMissing(array $list, array $extra): array
    {
        $ids = array_column($list, 'id');
        foreach ($extra as $programme) {
            if (!in_array($programme['id'], $ids, true)) {
                $list[] = $programme;
            }
        }
        return $list;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function shape(array $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $rules = null;
            if (!empty($row['a_level_rules'])) {
                $decoded = json_decode((string) $row['a_level_rules'], true);
                $rules = is_array($decoded) ? $decoded : null;
            }

            $list[] = [
                'id' => (int) $row['id'],
                'code' => (string) $row['code'],
                'name' => (string) $row['name'],
                'award_type' => (string) $row['award_type'],
                'duration' => $row['duration'] !== null ? rtrim(rtrim((string) $row['duration'], '0'), '.') : null,
                'requirement' => $row['a_level_requirement'] !== null ? (string) $row['a_level_requirement'] : null,
                'rules' => $rules,
            ];
        }
        return $list;
    }
}
