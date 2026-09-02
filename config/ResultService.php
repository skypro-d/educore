<?php
declare(strict_types=1);

/**
 * ResultService — Dynamic School Result Management Engine
 *
 * Handles dynamic class-subject resolution, student subject exceptions,
 * configurable assessment validation, grading evaluation, and terminal report calculations.
 *
 * @package EduCore
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/GradingService.php';
require_once __DIR__ . '/StaffAudit.php';

final class ResultService
{
    /**
     * Get all active subjects offered by a specific class.
     * ZERO hardcoding — strictly database-driven via class_subjects.
     *
     * @param int $classId
     * @param int|null $schoolId
     * @return array<int, array{id: int, name: string, code: string, is_compulsory: int, sort_order: int}>
     */
    public static function getSubjectsForClass(int $classId, ?int $schoolId = null): array
    {
        if ($classId <= 0) {
            return [];
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT s.id, s.name, s.code, cs.is_compulsory, cs.sort_order
                 FROM class_subjects cs
                 JOIN subjects s ON s.id = cs.subject_id
                 WHERE cs.class_id = ? AND cs.is_active = 1
                 ORDER BY cs.sort_order ASC, s.name ASC"
            );
            $stmt->execute([$classId]);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($subjects)) {
                return $subjects;
            }

            // Graceful fallback for backward compatibility: query subjects directly
            $stmtFb = $db->prepare(
                "SELECT s.id, s.name, s.code, 1 AS is_compulsory, 0 AS sort_order
                 FROM subjects s
                 WHERE (s.class_id = ? OR s.class_id IS NULL) AND s.is_active = 1
                 ORDER BY s.name ASC"
            );
            $stmtFb->execute([$classId]);
            return $stmtFb->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            Logger::error("ResultService::getSubjectsForClass failed", ['error' => $e->getMessage(), 'class_id' => $classId]);
            return [];
        }
    }

    /**
     * Get all subjects offered by a specific student.
     * Inherits from class subjects by default, supporting individual student exceptions.
     *
     * @param int $studentId
     * @param int $classId
     * @param string $academicYear
     * @param int|null $schoolId
     * @return array<int, array{id: int, name: string, code: string, is_compulsory: int}>
     */
    public static function getSubjectsForStudent(int $studentId, int $classId, string $academicYear, ?int $schoolId = null): array
    {
        if ($studentId <= 0 || $classId <= 0) {
            return [];
        }

        try {
            $db = Database::connect();
            // 1. Check for student-specific subject enrollments / electives
            $stmt = $db->prepare(
                "SELECT s.id, s.name, s.code, 1 AS is_compulsory
                 FROM student_subject_enrollments sse
                 JOIN subjects s ON s.id = sse.subject_id
                 WHERE sse.applicant_id = ? AND sse.academic_year = ? AND sse.is_exempt = 0
                 ORDER BY s.name ASC"
            );
            $stmt->execute([$studentId, $academicYear]);
            $customSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($customSubjects)) {
                return $customSubjects;
            }

            // 2. Inherit from class subjects
            return self::getSubjectsForClass($classId, $schoolId);
        } catch (Throwable $e) {
            Logger::error("ResultService::getSubjectsForStudent failed", ['error' => $e->getMessage(), 'student_id' => $studentId]);
            return self::getSubjectsForClass($classId, $schoolId);
        }
    }

    /**
     * Get active assessment components and their maximum score configurations.
     *
     * @param int|null $schoolId
     * @return array<int, array{id: int, name: string, code: string, max_score: float, weight_percent: float, sort_order: int}>
     */
    public static function getAssessmentComponents(?int $schoolId = null): array
    {
        try {
            $db = Database::connect();
            $stmt = $db->query(
                "SELECT id, name, code, max_score, weight_percent, sort_order
                 FROM assessment_components
                 WHERE is_active = 1
                 ORDER BY sort_order ASC, id ASC"
            );
            $components = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($components)) {
                return array_map(function ($c) {
                    $c['max_score'] = (float) $c['max_score'];
                    $c['weight_percent'] = (float) $c['weight_percent'];
                    return $c;
                }, $components);
            }
        } catch (Throwable $e) {
            // fallback
        }

        // Default standard components fallback
        return [
            ['id' => 1, 'name' => 'Test 1',      'code' => 'ca1',        'max_score' => 15.00, 'weight_percent' => 15.00, 'sort_order' => 1],
            ['id' => 2, 'name' => 'Test 2',      'code' => 'ca2',        'max_score' => 10.00, 'weight_percent' => 10.00, 'sort_order' => 2],
            ['id' => 3, 'name' => 'Assignment',  'code' => 'assignment', 'max_score' => 10.00, 'weight_percent' => 10.00, 'sort_order' => 3],
            ['id' => 4, 'name' => 'Mid-Term',    'code' => 'mid_term',   'max_score' => 10.00, 'weight_percent' => 10.00, 'sort_order' => 4],
            ['id' => 5, 'name' => 'Examination', 'code' => 'exam',       'max_score' => 55.00, 'weight_percent' => 55.00, 'sort_order' => 5],
        ];
    }

    /**
     * Validate an input score against a specific assessment component.
     *
     * @param mixed $score
     * @param float $maxScore
     * @param string $componentName
     * @return array{valid: bool, error: ?string, value: ?float}
     */
    public static function validateComponentScore(mixed $score, float $maxScore, string $componentName): array
    {
        if ($score === null || $score === '') {
            return ['valid' => true, 'error' => null, 'value' => null];
        }

        if (!is_numeric($score)) {
            return ['valid' => false, 'error' => "{$componentName} score must be numeric.", 'value' => null];
        }

        $num = (float) $score;
        if ($num < 0.0) {
            return ['valid' => false, 'error' => "{$componentName} score cannot be negative.", 'value' => null];
        }

        if ($num > $maxScore) {
            return ['valid' => false, 'error' => "{$componentName} score ({$num}) exceeds the maximum allowed ({$maxScore}).", 'value' => null];
        }

        return ['valid' => true, 'error' => null, 'value' => $num];
    }

    /**
     * Calculate a student's complete terminal result across all subjects.
     * Computes subject scores, totals, grades, overall average, and rank.
     *
     * @param int $studentId
     * @param int $classId
     * @param string $term
     * @param string $academicYear
     * @return array<string, mixed>
     */
    public static function calculateStudentResult(int $studentId, int $classId, string $term, string $academicYear): array
    {
        $db = Database::connect();

        // 1. Fetch Student Info
        $stmtStud = $db->prepare(
            "SELECT a.*, c.name AS class_name 
             FROM applicants a 
             LEFT JOIN classes c ON c.id = a.class_id 
             WHERE a.id = ? LIMIT 1"
        );
        $stmtStud->execute([$studentId]);
        $student = $stmtStud->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return [];
        }

        // 2. Fetch all subjects offered by the student
        $subjects = self::getSubjectsForStudent($studentId, $classId, $academicYear);
        $subjectIds = array_column($subjects, 'id');

        // 3. Fetch existing results
        $resultsMap = [];
        if (!empty($subjectIds)) {
            $inClause = implode(',', array_map('intval', $subjectIds));
            $stmtRes = $db->prepare(
                "SELECT sr.*, s.name AS subject_name, s.code AS subject_code
                 FROM student_results sr
                 JOIN subjects s ON s.id = sr.subject_id
                 WHERE sr.applicant_id = ? AND sr.class_id = ? AND sr.term = ? AND sr.academic_year = ?
                   AND sr.subject_id IN ({$inClause})
                 ORDER BY s.name ASC"
            );
            $stmtRes->execute([$studentId, $classId, $term, $academicYear]);
            foreach ($stmtRes->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $resultsMap[$r['subject_id']] = $r;
            }
        }

        // 4. Build Subject Breakdown
        $subjectRows = [];
        $totalScores = [];
        $completedCount = 0;

        foreach ($subjects as $sb) {
            $subId = (int) $sb['id'];
            $r = $resultsMap[$subId] ?? null;

            $hasScores = $r && ($r['ca1'] !== null || $r['ca2'] !== null || $r['assignment'] !== null || $r['mid_term'] !== null || $r['exam'] !== null);
            $totalVal = $r['total'] !== null ? (float) $r['total'] : null;

            if ($hasScores) {
                $completedCount++;
            }

            if ($totalVal !== null) {
                $totalScores[] = $totalVal;
                $eval = GradingService::evaluate($totalVal);
                $grade = $eval['grade'];
                $remark = $eval['remark'];
            } else {
                $grade = '—';
                $remark = '—';
            }

            $subjectRows[] = [
                'subject_id'     => $subId,
                'name'           => $sb['name'],
                'code'           => $sb['code'] ?? '',
                'ca1'            => $r['ca1'] ?? null,
                'ca2'            => $r['ca2'] ?? null,
                'assignment'     => $r['assignment'] ?? null,
                'mid_term'       => $r['mid_term'] ?? null,
                'exam'           => $r['exam'] ?? null,
                'total'          => $totalVal,
                'grade'          => $grade,
                'remark'         => $remark,
                'teacher_remark' => $r['teacher_remark'] ?? '',
                'status'         => $r['status'] ?? 'draft',
            ];
        }

        // 5. Compute Grand Total, Average & Overall Grade
        $subjectsCount = count($subjects);
        $grandTotal = array_sum($totalScores);
        // Average is calculated based on the actual number of subjects offered by the student
        $averageScore = $subjectsCount > 0 ? round($grandTotal / $subjectsCount, 2) : 0.0;
        $overallEval = GradingService::evaluate($averageScore);

        // 6. Fetch Term Remarks & Attendance
        $stmtTerm = $db->prepare(
            "SELECT * FROM term_remarks 
             WHERE applicant_id = ? AND term = ? AND academic_year = ? LIMIT 1"
        );
        $stmtTerm->execute([$studentId, $term, $academicYear]);
        $termRemark = $stmtTerm->fetch(PDO::FETCH_ASSOC) ?: [];

        // Attendance stats
        $stmtAtt = $db->prepare(
            "SELECT 
                COUNT(*) AS total_days,
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS days_present,
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS days_absent,
                SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) AS days_late
             FROM attendance 
             WHERE applicant_id = ? AND date >= ? AND date <= ?"
        );
        // Approximation for current term range if not set
        $stmtAtt->execute([$studentId, date('Y-01-01'), date('Y-12-31')]);
        $attData = $stmtAtt->fetch(PDO::FETCH_ASSOC) ?: ['days_present' => 0, 'days_absent' => 0];

        return [
            'student'      => $student,
            'subjects'     => $subjectRows,
            'summary'      => [
                'total_subjects'    => $subjectsCount,
                'completed_subjects'=> $completedCount,
                'grand_total'       => $grandTotal,
                'average_score'     => $averageScore,
                'overall_grade'     => $overallEval['grade'],
                'overall_remark'    => $overallEval['remark'],
            ],
            'term_remark'  => $termRemark,
            'attendance'   => [
                'present' => (int) ($termRemark['times_present'] ?? $attData['days_present'] ?? 0),
                'absent'  => (int) ($termRemark['times_absent'] ?? $attData['days_absent'] ?? 0),
            ],
        ];
    }

    /**
     * Recalculate class rankings, totals, and per-student averages.
     * Uses each student's actual required subjects, never hardcoded counts or class size.
     *
     * @param int $classId
     * @param string $term
     * @param string $academicYear
     */
    public static function recalculateClassPositions(int $classId, string $term, string $academicYear): void
    {
        if ($classId <= 0) {
            return;
        }

        $db = Database::connect();

        // 1. Fetch all active enrolled students in this class
        $stmtStudents = $db->prepare(
            "SELECT id FROM applicants 
             WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'"
        );
        $stmtStudents->execute([$classId]);
        $studentIds = $stmtStudents->fetchAll(PDO::FETCH_COLUMN);

        if (empty($studentIds)) {
            return;
        }

        $studentPerformances = [];

        foreach ($studentIds as $sId) {
            $sId = (int) $sId;
            $subjects = self::getSubjectsForStudent($sId, $classId, $academicYear);
            $subjectCount = max(1, count($subjects));

            // Sum student totals for this class, term, year
            $stmtSum = $db->prepare(
                "SELECT SUM(total) AS grand_total, COUNT(total) AS scored_subjects
                 FROM student_results 
                 WHERE applicant_id = ? AND class_id = ? AND term = ? AND academic_year = ?"
            );
            $stmtSum->execute([$sId, $classId, $term, $academicYear]);
            $sumRow = $stmtSum->fetch(PDO::FETCH_ASSOC);

            $grandTotal = (float) ($sumRow['grand_total'] ?? 0.0);
            $average = round($grandTotal / $subjectCount, 2);

            $studentPerformances[$sId] = [
                'student_id'   => $sId,
                'grand_total'  => $grandTotal,
                'average'      => $average,
                'subject_count'=> $subjectCount,
            ];
        }

        // 2. Sort students descending by average score (standard school ranking)
        uasort($studentPerformances, function ($a, $b) {
            if ($b['average'] == $a['average']) {
                return $b['grand_total'] <=> $a['grand_total'];
            }
            return $b['average'] <=> $a['average'];
        });

        $classSize = count($studentPerformances);
        $rank = 1;
        $prevAverage = null;
        $actualPosition = 1;

        $stmtUpsert = $db->prepare(
            "INSERT INTO term_remarks (applicant_id, class_id, term, academic_year, total_score, average, position, class_size, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
               total_score = VALUES(total_score),
               average = VALUES(average),
               position = VALUES(position),
               class_size = VALUES(class_size),
               updated_at = NOW()"
        );

        foreach ($studentPerformances as $sId => $perf) {
            if ($prevAverage !== null && $perf['average'] < $prevAverage) {
                $actualPosition = $rank;
            }
            $prevAverage = $perf['average'];

            $stmtUpsert->execute([
                $sId,
                $classId,
                $term,
                $academicYear,
                $perf['grand_total'],
                $perf['average'],
                $actualPosition,
                $classSize
            ]);

            $rank++;
        }
    }

    /**
     * Get class-level result submission and approval status overview.
     * Returns a matrix of all subjects offered by the class, assigned teacher,
     * submission status, graded students vs enrolled students.
     *
     * @param int $classId
     * @param string $term
     * @param string $academicYear
     * @return array<string, mixed>
     */
    public static function getClassResultOverview(int $classId, string $term, string $academicYear): array
    {
        if ($classId <= 0) {
            return [];
        }

        $db = Database::connect();

        // 1. Get all subjects offered by this class
        $subjects = self::getSubjectsForClass($classId);

        // 2. Get total enrolled students in this class
        $stmtCount = $db->prepare("SELECT COUNT(*) FROM applicants WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'");
        $stmtCount->execute([$classId]);
        $totalStudents = (int) $stmtCount->fetchColumn();

        // 3. For each subject, query assigned teacher and result status
        $overview = [];
        $totalCompletedSubjects = 0;

        foreach ($subjects as $sb) {
            $subId = (int) $sb['id'];

            // Find assigned subject teacher
            $stmtTeacher = $db->prepare(
                "SELECT s.id, s.first_name, s.last_name 
                 FROM staff_class_assignments sca
                 JOIN staff s ON s.id = sca.staff_id
                 WHERE sca.class_id = ? AND sca.subject_id = ? AND sca.academic_year = ? LIMIT 1"
            );
            $stmtTeacher->execute([$classId, $subId, $academicYear]);
            $teacher = $stmtTeacher->fetch(PDO::FETCH_ASSOC);
            $teacherName = $teacher ? ($teacher['first_name'] . ' ' . $teacher['last_name']) : 'Unassigned';

            // Find result status and count of graded students
            $stmtRes = $db->prepare(
                "SELECT 
                    COUNT(*) AS graded_count,
                    COALESCE(MAX(status), 'draft') AS status,
                    MAX(submitted_at) AS submitted_at,
                    MAX(approved_at) AS approved_at,
                    MAX(published_at) AS published_at
                 FROM student_results 
                 WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
            );
            $stmtRes->execute([$classId, $subId, $term, $academicYear]);
            $resData = $stmtRes->fetch(PDO::FETCH_ASSOC) ?: ['graded_count' => 0, 'status' => 'draft'];

            $gradedCount = (int) ($resData['graded_count'] ?? 0);
            $status = $resData['status'] ?? 'draft';

            if (in_array($status, ['submitted', 'approved', 'published'], true)) {
                $totalCompletedSubjects++;
            }

            $overview[] = [
                'subject_id'     => $subId,
                'subject_name'   => $sb['name'],
                'subject_code'   => $sb['code'] ?? '',
                'is_compulsory'  => (bool) ($sb['is_compulsory'] ?? 1),
                'teacher_name'   => $teacherName,
                'total_students' => $totalStudents,
                'graded_count'   => $gradedCount,
                'missing_count'  => max(0, $totalStudents - $gradedCount),
                'status'         => $status,
                'submitted_at'   => $resData['submitted_at'] ?? null,
                'approved_at'    => $resData['approved_at'] ?? null,
                'published_at'   => $resData['published_at'] ?? null,
            ];
        }

        $totalSubjectsCount = count($subjects);
        $completionRate = $totalSubjectsCount > 0 ? round(($totalCompletedSubjects / $totalSubjectsCount) * 100, 1) : 0.0;

        return [
            'class_id'             => $classId,
            'total_students'       => $totalStudents,
            'total_subjects'       => $totalSubjectsCount,
            'completed_subjects'   => $totalCompletedSubjects,
            'completion_rate'      => $completionRate,
            'subjects'             => $overview,
        ];
    }
}
