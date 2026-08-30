<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';

final class AcademicController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── Subjects ───────────────────────────────────────── */

    public function subjects(): void
    {
        require_admin();
        $classes  = (new ClassModel($this->db))->all();
        $subjects = $this->db->query(
            "SELECT s.*, c.name AS class_name FROM subjects s
             LEFT JOIN classes c ON c.id=s.class_id
             ORDER BY s.name ASC"
        )->fetchAll();
        render('admin/subjects', compact('subjects', 'classes'), 'admin');
    }

    public function saveSubject(): void
    {
        require_admin();
        verify_csrf();
        $id       = (int) ($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $code     = trim($_POST['code'] ?? '');
        $classId  = $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;

        if ($id > 0) {
            $this->db->prepare("UPDATE subjects SET name=?, code=?, class_id=? WHERE id=?")
                ->execute([$name, $code ?: null, $classId, $id]);
        } else {
            $this->db->prepare("INSERT INTO subjects (name, code, class_id) VALUES (?, ?, ?)")
                ->execute([$name, $code ?: null, $classId]);
        }
        flash('success', 'Subject saved.');
        redirect('admin/subjects');
    }

    public function deleteSubject(int $id): void
    {
        require_admin();
        verify_csrf();
        $this->db->prepare("DELETE FROM subjects WHERE id=?")->execute([$id]);
        flash('success', 'Subject deleted.');
        redirect('admin/subjects');
    }

    /* ─── Results ────────────────────────────────────────── */

    public function results(): void
    {
        require_permission('results');
        $classes    = (new ClassModel($this->db))->all();
        $year       = $_GET['year'] ?? setting('academic_year', '');
        $term       = $_GET['term'] ?? setting('current_term', 'First');
        $classId    = (int) ($_GET['class_id'] ?? 0);

        $students = [];
        if ($classId) {
            $stmt = $this->db->prepare(
                "SELECT a.id, a.first_name, a.last_name, a.application_number
                 FROM applicants a WHERE a.class_id=? AND a.status='Enrolled' ORDER BY a.last_name, a.first_name"
            );
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll();
        }

        $subjects = $this->db->query("SELECT * FROM subjects WHERE is_active=1 ORDER BY name")->fetchAll();
        render('admin/results', compact('classes', 'students', 'subjects', 'year', 'term', 'classId'), 'admin');
    }

    public function enterResults(): void
    {
        require_permission('results');
        verify_csrf();
        $applicantId  = (int) ($_POST['applicant_id'] ?? 0);
        $year         = trim($_POST['academic_year'] ?? '');
        $term         = $_POST['term'] ?? 'First';
        $classId      = (int) ($_POST['class_id'] ?? 0);
        $scores       = $_POST['scores'] ?? [];

        foreach ($scores as $subjectId => $s) {
            $subjectId = (int) $subjectId;
            $ca1       = $s['ca1'] !== '' ? (float) $s['ca1'] : null;
            $ca2       = $s['ca2'] !== '' ? (float) $s['ca2'] : null;
            $assignment= $s['assignment'] !== '' ? (float) $s['assignment'] : null;
            $midTerm   = $s['mid_term'] !== '' ? (float) $s['mid_term'] : null;
            $exam      = $s['exam'] !== '' ? (float) $s['exam'] : null;

            $total = array_sum(array_filter([$ca1, $ca2, $assignment, $midTerm, $exam], fn($v) => $v !== null));
            [$grade, $remark] = $this->gradeScore($total);

            $this->db->prepare(
                "INSERT INTO student_results
                 (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                 ca1=VALUES(ca1), ca2=VALUES(ca2), assignment=VALUES(assignment), mid_term=VALUES(mid_term),
                 exam=VALUES(exam), total=VALUES(total), grade=VALUES(grade), remark=VALUES(remark), updated_at=NOW()"
            )->execute([$applicantId, $subjectId, $classId, $term, $year, $ca1, $ca2, $assignment, $midTerm, $exam, $total, $grade, $remark]);
        }

        // Recalculate positions for this class/term
        $this->recalcPositions($classId, $term, $year);

        flash('success', 'Results saved successfully.');
        redirect('admin/results?class_id=' . $classId . '&term=' . urlencode($term) . '&year=' . urlencode($year));
    }

    public function resultSheet(int $applicantId): void
    {
        require_admin();
        $year  = $_GET['year'] ?? setting('academic_year', '');
        $term  = $_GET['term'] ?? setting('current_term', 'First');

        $student = (new Applicant($this->db))->find($applicantId);
        if (!$student) {
            flash('warning', 'Student not found.');
            redirect('admin/results');
        }

        $stmt = $this->db->prepare(
            "SELECT sr.*, s.name AS subject_name, s.code
             FROM student_results sr JOIN subjects s ON s.id=sr.subject_id
             WHERE sr.applicant_id=? AND sr.academic_year=? AND sr.term=?
             ORDER BY s.name"
        );
        $stmt->execute([$applicantId, $year, $term]);
        $results = $stmt->fetchAll();

        $remark = $this->db->prepare(
            "SELECT * FROM term_remarks WHERE applicant_id=? AND academic_year=? AND term=? LIMIT 1"
        );
        $remark->execute([$applicantId, $year, $term]);
        $termRemark = $remark->fetch() ?: [];

        render('admin/result_sheet', compact('student', 'results', 'termRemark', 'year', 'term'), 'auth');
    }

    public function saveTermRemark(): void
    {
        require_admin();
        verify_csrf();
        $applicantId = (int) ($_POST['applicant_id'] ?? 0);
        $classId     = (int) ($_POST['class_id'] ?? 0);
        $year        = trim($_POST['academic_year'] ?? '');
        $term        = trim($_POST['term'] ?? '');

        $this->db->prepare(
            "INSERT INTO term_remarks (applicant_id, class_id, term, academic_year, class_teacher_remark, principal_remark, next_term_begins, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE class_teacher_remark=VALUES(class_teacher_remark), principal_remark=VALUES(principal_remark),
             next_term_begins=VALUES(next_term_begins), updated_at=NOW()"
        )->execute([
            $applicantId, $classId, $term, $year,
            trim($_POST['class_teacher_remark'] ?? ''),
            trim($_POST['principal_remark'] ?? ''),
            $_POST['next_term_begins'] ?: null,
        ]);

        flash('success', 'Remarks saved.');
        redirect('admin/result-sheet/' . $applicantId . '?year=' . urlencode($year) . '&term=' . urlencode($term));
    }

    /* ─── Grading helpers ────────────────────────────────── */

    private function gradeScore(float $total): array
    {
        $thresholds = [
            (int) setting('grade_a1_min', '75') => [setting('grade_a1_label', 'A1'), 'Excellent'],
            (int) setting('grade_b2_min', '70') => [setting('grade_b2_label', 'B2'), 'Very Good'],
            (int) setting('grade_b3_min', '65') => [setting('grade_b3_label', 'B3'), 'Good'],
            (int) setting('grade_c4_min', '60') => [setting('grade_c4_label', 'C4'), 'Credit'],
            (int) setting('grade_c5_min', '55') => [setting('grade_c5_label', 'C5'), 'Credit'],
            (int) setting('grade_c6_min', '50') => [setting('grade_c6_label', 'C6'), 'Credit'],
            (int) setting('grade_d7_min', '45') => [setting('grade_d7_label', 'D7'), 'Pass'],
            (int) setting('grade_e8_min', '40') => [setting('grade_e8_label', 'E8'), 'Pass'],
        ];
        krsort($thresholds);
        foreach ($thresholds as $min => $info) {
            if ($total >= $min) {
                return $info;
            }
        }
        return [setting('grade_f9_label', 'F9'), 'Fail'];
    }

    private function recalcPositions(int $classId, string $term, string $year): void
    {
        $stmt = $this->db->prepare(
            "SELECT sr.applicant_id, SUM(sr.total) AS grand_total
             FROM student_results sr
             WHERE sr.class_id=? AND sr.term=? AND sr.academic_year=?
             GROUP BY sr.applicant_id
             ORDER BY grand_total DESC"
        );
        $stmt->execute([$classId, $term, $year]);
        $rows      = $stmt->fetchAll();
        $classSize = count($rows);

        foreach ($rows as $pos => $row) {
            $position = $pos + 1;
            $this->db->prepare(
                "UPDATE term_remarks SET position=?, class_size=?, total_score=?, average=?, updated_at=NOW()
                 WHERE applicant_id=? AND term=? AND academic_year=?"
            )->execute([
                $position,
                $classSize,
                $row['grand_total'],
                $classSize ? round($row['grand_total'] / max(1, $classSize), 2) : 0,
                $row['applicant_id'],
                $term,
                $year,
            ]);
        }
    }
}
