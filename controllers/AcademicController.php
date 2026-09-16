<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/ResultService.php';
require_once __DIR__ . '/../config/GradingService.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';

final class AcademicController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── Master Subjects ─────────────────────────────────── */

    public function subjects(): void
    {
        require_admin();
        $classes  = (new ClassModel($this->db))->all();
        $subjects = $this->db->query(
            "SELECT s.*, c.name AS class_name,
                    (SELECT COUNT(*) FROM class_subjects cs WHERE cs.subject_id = s.id) AS classes_count
             FROM subjects s
             LEFT JOIN classes c ON c.id = s.class_id
             ORDER BY s.name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        render('admin/subjects', compact('subjects', 'classes'), 'admin');
    }

    public function saveSubject(): void
    {
        require_admin();
        verify_csrf();
        $id       = (int) ($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $code     = strtoupper(trim($_POST['code'] ?? ''));
        $classId  = !empty($_POST['class_id']) ? (int) $_POST['class_id'] : null;

        if ($name === '') {
            flash('danger', 'Subject name is required.');
            redirect('admin/subjects');
        }

        if ($id > 0) {
            $this->db->prepare("UPDATE subjects SET name = ?, code = ?, class_id = ? WHERE id = ?")
                ->execute([$name, $code ?: null, $classId, $id]);
            flash('success', 'Subject updated successfully.');
        } else {
            $this->db->prepare("INSERT INTO subjects (name, code, class_id) VALUES (?, ?, ?)")
                ->execute([$name, $code ?: null, $classId]);
            flash('success', 'New subject added successfully.');
        }

        redirect('admin/subjects');
    }

    public function deleteSubject(int $id): void
    {
        require_admin();
        verify_csrf();
        $this->db->prepare("DELETE FROM subjects WHERE id = ?")->execute([$id]);
        flash('success', 'Subject deleted.');
        redirect('admin/subjects');
    }

    /* ─── Class Subjects Allocation ──────────────────────── */

    public function classSubjects(): void
    {
        require_admin();
        $classes = (new ClassModel($this->db))->all();
        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));

        $selectedClass = null;
        foreach ($classes as $c) {
            if ((int)$c['id'] === $classId) {
                $selectedClass = $c;
                break;
            }
        }

        // All active master subjects
        $allSubjects = $this->db->query("SELECT id, name, code FROM subjects WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Currently assigned subjects for this class
        $assignedSubjectIds = [];
        if ($classId > 0) {
            $stmt = $this->db->prepare("SELECT subject_id FROM class_subjects WHERE class_id = ? AND is_active = 1");
            $stmt->execute([$classId]);
            $assignedSubjectIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        }

        // Summary counts per class for sidebar badge
        $classCounts = [];
        $stmtCounts = $this->db->query("SELECT class_id, COUNT(*) AS count FROM class_subjects WHERE is_active = 1 GROUP BY class_id");
        foreach ($stmtCounts->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $classCounts[$row['class_id']] = (int) $row['count'];
        }

        render('admin/class_subjects', compact('classes', 'classId', 'selectedClass', 'allSubjects', 'assignedSubjectIds', 'classCounts'), 'admin');
    }

    public function saveClassSubjects(): void
    {
        require_admin();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        $selectedSubjectIds = array_map('intval', $_POST['subject_ids'] ?? []);

        if ($classId <= 0) {
            flash('danger', 'Invalid class selected.');
            redirect('admin/class-subjects');
        }

        $this->db->beginTransaction();
        try {
            // Delete subjects no longer selected for this class
            if (!empty($selectedSubjectIds)) {
                $inClause = implode(',', $selectedSubjectIds);
                $stmtDel = $this->db->prepare("DELETE FROM class_subjects WHERE class_id = ? AND subject_id NOT IN ({$inClause})");
                $stmtDel->execute([$classId]);
            } else {
                $this->db->prepare("DELETE FROM class_subjects WHERE class_id = ?")->execute([$classId]);
            }

            // Insert newly selected subjects
            $stmtIns = $this->db->prepare(
                "INSERT INTO class_subjects (class_id, subject_id, is_compulsory, sort_order, is_active)
                 VALUES (?, ?, 1, ?, 1)
                 ON DUPLICATE KEY UPDATE is_active = 1, sort_order = VALUES(sort_order)"
            );

            foreach ($selectedSubjectIds as $order => $subId) {
                $stmtIns->execute([$classId, $subId, $order + 1]);
            }

            $this->db->commit();
            flash('success', "Class subjects saved successfully (" . count($selectedSubjectIds) . " subjects configured).");
        } catch (Throwable $e) {
            $this->db->rollBack();
            Logger::error("Failed to save class subjects", ['error' => $e->getMessage(), 'class_id' => $classId]);
            flash('danger', 'Failed to update class subjects: ' . $e->getMessage());
        }

        redirect('admin/class-subjects?class_id=' . $classId);
    }

    /* ─── Configurable Assessment Components & Grading ───── */

    public function assessmentComponents(): void
    {
        require_admin();
        $components = ResultService::getAssessmentComponents();
        render('admin/assessment_components', compact('components'), 'admin');
    }

    public function saveAssessmentComponents(): void
    {
        require_admin();
        verify_csrf();

        $ids       = $_POST['id'] ?? [];
        $names     = $_POST['name'] ?? [];
        $codes     = $_POST['code'] ?? [];
        $maxScores = $_POST['max_score'] ?? [];
        $weights   = $_POST['weight_percent'] ?? [];

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO assessment_components (id, name, code, max_score, weight_percent, sort_order, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE name = VALUES(name), max_score = VALUES(max_score), weight_percent = VALUES(weight_percent)"
            );

            foreach ($codes as $idx => $code) {
                $compCode = trim($code);
                $compName = trim($names[$idx] ?? $compCode);
                $maxVal   = (float) ($maxScores[$idx] ?? 10.0);
                $weightVal= (float) ($weights[$idx] ?? 10.0);
                $compId   = !empty($ids[$idx]) ? (int)$ids[$idx] : null;

                if ($compCode !== '') {
                    $stmt->execute([$compId, $compName, $compCode, $maxVal, $weightVal, $idx + 1]);
                }
            }

            $this->db->commit();
            flash('success', 'Assessment components updated successfully.');
        } catch (Throwable $e) {
            $this->db->rollBack();
            flash('danger', 'Failed to update assessment components: ' . $e->getMessage());
        }

        redirect('admin/assessment-components');
    }

    public function gradingRules(): void
    {
        require_admin();
        $rules = GradingService::getRules();
        render('admin/grading_rules', compact('rules'), 'admin');
    }

    public function saveGradingRules(): void
    {
        require_admin();
        verify_csrf();

        $minScores = $_POST['min_score'] ?? [];
        $maxScores = $_POST['max_score'] ?? [];
        $grades    = $_POST['grade'] ?? [];
        $remarks   = $_POST['remark'] ?? [];
        $points    = $_POST['grade_point'] ?? [];

        $this->db->beginTransaction();
        try {
            $this->db->exec("DELETE FROM grading_rules");
            $stmt = $this->db->prepare(
                "INSERT INTO grading_rules (min_score, max_score, grade, remark, grade_point, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($grades as $idx => $grade) {
                $g = trim($grade);
                if ($g !== '') {
                    $stmt->execute([
                        (float) ($minScores[$idx] ?? 0),
                        (float) ($maxScores[$idx] ?? 100),
                        $g,
                        trim($remarks[$idx] ?? ''),
                        (float) ($points[$idx] ?? 0.0),
                        $idx + 1
                    ]);
                }
            }

            $this->db->commit();
            GradingService::resetCache();
            flash('success', 'Grading scale rules updated successfully.');
        } catch (Throwable $e) {
            $this->db->rollBack();
            flash('danger', 'Failed to update grading rules: ' . $e->getMessage());
        }

        redirect('admin/grading-rules');
    }

    /* ─── Results Management & Entry ─────────────────────── */

    public function results(): void
    {
        require_permission('results');
        $classes    = (new ClassModel($this->db))->all();
        $year       = $_GET['year'] ?? current_academic_year();
        $term       = $_GET['term'] ?? current_term();
        $classId    = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));

        $students = [];
        if ($classId > 0) {
            $stmt = $this->db->prepare(
                "SELECT a.id, a.first_name, a.last_name, a.application_number, a.admission_number, a.passport_photo
                 FROM applicants a 
                 WHERE a.class_id = ? AND a.status = 'Enrolled' AND a.student_status = 'Active'
                 ORDER BY a.last_name, a.first_name"
            );
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Dynamically get ONLY the subjects offered by this specific class!
        $subjects = $classId > 0 ? ResultService::getSubjectsForClass($classId) : [];

        // Class Overview stats
        $classOverview = $classId > 0 ? ResultService::getClassResultOverview($classId, $term, $year) : [];

        $components = ResultService::getAssessmentComponents();

        render('admin/results', compact('classes', 'students', 'subjects', 'year', 'term', 'classId', 'classOverview', 'components'), 'admin');
    }

    public function enterResults(): void
    {
        require_permission('results');
        verify_csrf();
        $applicantId  = (int) ($_POST['applicant_id'] ?? 0);
        $year         = trim($_POST['academic_year'] ?? current_academic_year());
        $term         = $_POST['term'] ?? current_term();
        $classId      = (int) ($_POST['class_id'] ?? 0);
        $scores       = $_POST['scores'] ?? [];

        $components = ResultService::getAssessmentComponents();
        $maxScores = [];
        foreach ($components as $c) {
            $maxScores[$c['code']] = $c['max_score'];
        }

        foreach ($scores as $subjectId => $s) {
            $subjectId = (int) $subjectId;
            $ca1       = ($s['ca1'] ?? '') !== '' ? (float) $s['ca1'] : null;
            $ca2       = ($s['ca2'] ?? '') !== '' ? (float) $s['ca2'] : null;
            $assign    = ($s['assignment'] ?? '') !== '' ? (float) $s['assignment'] : null;
            $midTerm   = ($s['mid_term'] ?? '') !== '' ? (float) $s['mid_term'] : null;
            $exam      = ($s['exam'] ?? '') !== '' ? (float) $s['exam'] : null;

            // Validate bounds
            if ($ca1 !== null && $ca1 > ($maxScores['ca1'] ?? 15.0)) $ca1 = $maxScores['ca1'] ?? 15.0;
            if ($ca2 !== null && $ca2 > ($maxScores['ca2'] ?? 10.0)) $ca2 = $maxScores['ca2'] ?? 10.0;
            if ($assign !== null && $assign > ($maxScores['assignment'] ?? 10.0)) $assign = $maxScores['assignment'] ?? 10.0;
            if ($midTerm !== null && $midTerm > ($maxScores['mid_term'] ?? 10.0)) $midTerm = $maxScores['mid_term'] ?? 10.0;
            if ($exam !== null && $exam > ($maxScores['exam'] ?? 55.0)) $exam = $maxScores['exam'] ?? 55.0;

            $total = null;
            $grade = null;
            $remark = null;

            if ($ca1 !== null || $ca2 !== null || $assign !== null || $midTerm !== null || $exam !== null) {
                $total = ($ca1 ?? 0) + ($ca2 ?? 0) + ($assign ?? 0) + ($midTerm ?? 0) + ($exam ?? 0);
                $eval = GradingService::evaluate($total);
                $grade = $eval['grade'];
                $remark = $eval['remark'];
            }

            $this->db->prepare(
                "INSERT INTO student_results
                 (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                 ca1 = VALUES(ca1), ca2 = VALUES(ca2), assignment = VALUES(assignment), mid_term = VALUES(mid_term),
                 exam = VALUES(exam), total = VALUES(total), grade = VALUES(grade), remark = VALUES(remark), updated_at = NOW()"
            )->execute([$applicantId, $subjectId, $classId, $term, $year, $ca1, $ca2, $assign, $midTerm, $exam, $total, $grade, $remark]);
        }

        // Recalculate positions based on student's actual required subjects
        ResultService::recalculateClassPositions($classId, $term, $year);

        flash('success', 'Results saved and positions recalculated successfully.');
        redirect('admin/results?class_id=' . $classId . '&term=' . urlencode($term) . '&year=' . urlencode($year));
    }

    public function resultSheet(int $applicantId): void
    {
        require_admin();
        $year  = $_GET['year'] ?? current_academic_year();
        $term  = $_GET['term'] ?? current_term();

        $student = (new Applicant($this->db))->find($applicantId);
        if (!$student) {
            flash('warning', 'Student not found.');
            redirect('admin/results');
        }

        $classId = (int) ($student['class_id'] ?? 0);
        $resultData = ResultService::calculateStudentResult($applicantId, $classId, $term, $year);

        render('admin/result_sheet', compact('student', 'resultData', 'year', 'term', 'classId'), 'admin');
    }

    public function saveTermRemark(): void
    {
        require_admin();
        verify_csrf();
        $applicantId = (int) ($_POST['applicant_id'] ?? 0);
        $classId     = (int) ($_POST['class_id'] ?? 0);
        $year        = trim($_POST['academic_year'] ?? current_academic_year());
        $term        = trim($_POST['term'] ?? current_term());

        $this->db->prepare(
            "INSERT INTO term_remarks (applicant_id, class_id, term, academic_year, class_teacher_remark, principal_remark, next_term_begins, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE class_teacher_remark = VALUES(class_teacher_remark), principal_remark = VALUES(principal_remark),
             next_term_begins = VALUES(next_term_begins), updated_at = NOW()"
        )->execute([
            $applicantId, $classId, $term, $year,
            trim($_POST['class_teacher_remark'] ?? ''),
            trim($_POST['principal_remark'] ?? ''),
            $_POST['next_term_begins'] ?: null,
        ]);

        flash('success', 'Terminal remarks saved successfully.');
        redirect('admin/result-sheet/' . $applicantId . '?year=' . urlencode($year) . '&term=' . urlencode($term));
    }

    /* ─── Class Timetable Management ──────────────────────── */

    public function timetable(): void
    {
        require_admin();
        $classes = (new ClassModel($this->db))->all();
        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));

        $selectedClass = null;
        foreach ($classes as $c) {
            if ((int)$c['id'] === $classId) {
                $selectedClass = $c;
                break;
            }
        }

        // Active subjects and active teaching staff
        $subjects = $this->db->query("SELECT id, name, code FROM subjects WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $teachers = $this->db->query("SELECT id, first_name, last_name, staff_id, phone FROM staff WHERE status = 'Active' ORDER BY first_name ASC, last_name ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Fetch timetable schedule for this class
        $schedule = [];
        if ($classId > 0) {
            $stmt = $this->db->prepare(
                "SELECT t.*, s.name AS subject_name, s.code AS subject_code, 
                        st.first_name, st.last_name, st.staff_id AS teacher_staff_code
                 FROM timetables t
                 JOIN subjects s ON s.id = t.subject_id
                 LEFT JOIN staff st ON st.id = t.teacher_id
                 WHERE t.class_id = ?
                 ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time ASC"
            );
            $stmt->execute([$classId]);
            $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetableByDay = [];
        foreach ($days as $d) {
            $timetableByDay[$d] = [];
        }
        foreach ($schedule as $row) {
            $day = ucfirst(strtolower($row['day_of_week']));
            if (!isset($timetableByDay[$day])) {
                $timetableByDay[$day] = [];
            }
            $timetableByDay[$day][] = $row;
        }

        // Summary counts per class for sidebar/tabs
        $classCounts = [];
        $stmtCounts = $this->db->query("SELECT class_id, COUNT(*) AS total_slots FROM timetables GROUP BY class_id");
        foreach ($stmtCounts->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $classCounts[(int)$row['class_id']] = (int)$row['total_slots'];
        }

        render('admin/timetable', compact('classes', 'selectedClass', 'classId', 'subjects', 'teachers', 'timetableByDay', 'days', 'classCounts', 'schedule'), 'admin');
    }

    public function saveTimetableSlot(): void
    {
        require_admin();
        verify_csrf();

        $id        = (int) ($_POST['id'] ?? 0);
        $classId   = (int) ($_POST['class_id'] ?? 0);
        $dayOfWeek = trim($_POST['day_of_week'] ?? '');
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $teacherId = !empty($_POST['teacher_id']) ? (int) $_POST['teacher_id'] : null;
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime   = trim($_POST['end_time'] ?? '');

        if ($classId <= 0 || $subjectId <= 0 || $dayOfWeek === '' || $startTime === '' || $endTime === '') {
            flash('danger', 'Please provide Class, Day, Subject, Start Time, and End Time.');
            redirect('admin/timetable?class_id=' . $classId);
            return;
        }

        if (strtotime($endTime) <= strtotime($startTime)) {
            flash('danger', 'Period End Time must be later than Start Time.');
            redirect('admin/timetable?class_id=' . $classId);
            return;
        }

        if ($id > 0) {
            $stmt = $this->db->prepare(
                "UPDATE timetables SET 
                    class_id = ?, day_of_week = ?, subject_id = ?, teacher_id = ?, start_time = ?, end_time = ?
                 WHERE id = ?"
            );
            $stmt->execute([$classId, $dayOfWeek, $subjectId, $teacherId, $startTime, $endTime, $id]);
            flash('success', 'Timetable period updated successfully.');
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO timetables (class_id, day_of_week, subject_id, teacher_id, start_time, end_time)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$classId, $dayOfWeek, $subjectId, $teacherId, $startTime, $endTime]);
            flash('success', 'New period added to ' . $dayOfWeek . ' schedule.');
        }

        redirect('admin/timetable?class_id=' . $classId);
    }

    public function deleteTimetableSlot(int $id): void
    {
        require_admin();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId <= 0) {
            $stmt = $this->db->prepare("SELECT class_id FROM timetables WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            $classId = (int) ($row['class_id'] ?? 0);
        }

        $this->db->prepare("DELETE FROM timetables WHERE id = ?")->execute([$id]);
        flash('success', 'Timetable period deleted.');
        redirect('admin/timetable?class_id=' . $classId);
    }

    public function copyClassTimetable(): void
    {
        require_admin();
        verify_csrf();

        $fromClassId = (int) ($_POST['from_class_id'] ?? 0);
        $toClassId   = (int) ($_POST['to_class_id'] ?? 0);
        $overwrite   = !empty($_POST['overwrite']);

        if ($fromClassId <= 0 || $toClassId <= 0) {
            flash('danger', 'Please select source and destination classes.');
            redirect('admin/timetable?class_id=' . $toClassId);
            return;
        }

        if ($fromClassId === $toClassId) {
            flash('warning', 'Source and target class cannot be the same.');
            redirect('admin/timetable?class_id=' . $toClassId);
            return;
        }

        // Fetch source slots
        $stmtSrc = $this->db->prepare("SELECT * FROM timetables WHERE class_id = ?");
        $stmtSrc->execute([$fromClassId]);
        $slots = $stmtSrc->fetchAll(PDO::FETCH_ASSOC);

        if (empty($slots)) {
            flash('warning', 'Source class has no timetable slots to copy.');
            redirect('admin/timetable?class_id=' . $fromClassId);
            return;
        }

        if ($overwrite) {
            $this->db->prepare("DELETE FROM timetables WHERE class_id = ?")->execute([$toClassId]);
        }

        $stmtIns = $this->db->prepare(
            "INSERT INTO timetables (class_id, day_of_week, subject_id, teacher_id, start_time, end_time)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $copied = 0;
        foreach ($slots as $s) {
            $stmtIns->execute([$toClassId, $s['day_of_week'], $s['subject_id'], $s['teacher_id'], $s['start_time'], $s['end_time']]);
            $copied++;
        }

        flash('success', "Successfully copied {$copied} timetable periods to destination class.");
        redirect('admin/timetable?class_id=' . $toClassId);
    }

    public function clearClassTimetable(): void
    {
        require_admin();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        if ($classId > 0) {
            $this->db->prepare("DELETE FROM timetables WHERE class_id = ?")->execute([$classId]);
            flash('success', 'All timetable periods cleared for the selected class.');
        }
        redirect('admin/timetable?class_id=' . $classId);
    }
}
