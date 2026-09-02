<?php
// views/admin/result_sheet.php — Official Terminal Report Card (Dynamic Subjects)
$subjectsList = $resultData['subjects'] ?? ($results ?? []);
$summary = $resultData['summary'] ?? [];
$termRemark = $resultData['term_remark'] ?? ($termRemark ?? []);
$attendance = $resultData['attendance'] ?? [
    'present' => $termRemark['times_present'] ?? '—',
    'absent'  => $termRemark['times_absent'] ?? '—',
];
$scaleRules = GradingService::getScale();
$components = ResultService::getAssessmentComponents();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Academic Report Card — <?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($term) ?> Term, <?= e($year) ?>)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 12.5px; background: #f8fafc; color: #1e293b; line-height: 1.4; }
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; padding: 16mm 18mm; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-radius: 4px; }
        .school-header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .school-header .logo { max-height: 65px; margin-bottom: 6px; }
        .school-header h1 { font-size: 21px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .school-header p { font-size: 11.5px; color: #64748b; margin-top: 1px; }
        .report-banner { text-align: center; background: #0f766e; color: #fff; padding: 6px 0; margin-bottom: 16px; font-size: 13.5px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; border-radius: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; background: #f8fafc; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 6px; }
        .info-row { display: flex; padding: 3px 0; font-size: 12px; }
        .info-label { font-weight: 700; color: #475569; width: 130px; flex-shrink: 0; }
        .info-val { color: #0f172a; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #0f172a; color: #fff; }
        th { padding: 8px 6px; font-size: 10.5px; text-align: center; font-weight: 700; border: 1px solid #cbd5e1; text-transform: uppercase; letter-spacing: 0.5px; }
        th:nth-child(2) { text-align: left; }
        td { padding: 6px 6px; font-size: 11.5px; text-align: center; border: 1px solid #e2e8f0; }
        td:nth-child(2) { text-align: left; font-weight: 600; color: #0f172a; }
        tr:nth-child(even) td { background: #fbfcfe; }
        .tfoot-row td { background: #f1f5f9; font-weight: 700; border-top: 2px solid #0f172a; font-size: 12px; }
        .grade-badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .remarks-box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; background: #f8fafc; font-size: 12px; }
        .remarks-box h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #475569; margin-bottom: 4px; letter-spacing: 0.5px; }
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 24px; }
        .sig-block { text-align: center; }
        .sig-line { border-bottom: 1px solid #0f172a; margin-bottom: 5px; height: 35px; }
        .sig-label { font-size: 11px; color: #475569; font-weight: 600; }
        .grading-key { border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 14px; background: #fff; }
        .grading-key h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; color: #475569; }
        .key-grid { display: grid; grid-template-columns: repeat(9, 1fr); gap: 4px; }
        .key-item { text-align: center; background: #f8fafc; border: 1px solid #e2e8f0; padding: 4px 2px; border-radius: 4px; font-size: 10.5px; }
        @media print {
            body { background: #fff; padding: 0; }
            .page { width: 100%; margin: 0; padding: 10mm 12mm; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print" style="padding:14px; background:#fff; border-bottom:1px solid #e2e8f0; display:flex; justify-content:center; gap:12px; position:sticky; top:0; z-index:100; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <button onclick="window.print()" style="padding:8px 24px; background:#0f766e; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
        <i class="ti ti-printer"></i> Print Report Card
    </button>
    <a href="javascript:history.back()" style="padding:8px 20px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; text-decoration:none; color:#334155; font-weight:600;">
        ← Back to Results
    </a>
</div>

<div class="page">
    <!-- School Header -->
    <div class="school-header">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img class="logo" src="<?= e($logoUrl) ?>" alt="Logo" onerror="this.style.display='none';">
        <?php endif; ?>
        <h1><?= e(setting('school_name', 'EduCore International Academy')) ?></h1>
        <p><?= e(setting('school_address', 'School Campus Address')) ?> &nbsp;|&nbsp; Tel: <?= e(setting('school_phone', '')) ?></p>
        <p><?= e(setting('school_email', '')) ?> &nbsp;|&nbsp; <?= e(setting('school_website', '')) ?></p>
    </div>

    <div class="report-banner">
        Official Terminal Academic Report &mdash; <?= e($term) ?> Term (<?= e($year) ?>)
    </div>

    <!-- Student Bio Grid -->
    <div class="info-grid">
        <div>
            <div class="info-row"><span class="info-label">Student Name:</span><span class="info-val"><?= e($student['last_name'] . ' ' . $student['first_name'] . (!empty($student['middle_name']) ? ' ' . $student['middle_name'] : '')) ?></span></div>
            <div class="info-row"><span class="info-label">Admission No:</span><span class="info-val font-monospace"><?= e($student['admission_number'] ?? $student['application_number']) ?></span></div>
            <div class="info-row"><span class="info-label">Class:</span><span class="info-val"><?= e($student['class_name'] ?? '—') ?></span></div>
            <div class="info-row"><span class="info-label">Gender:</span><span class="info-val"><?= e($student['gender'] ?? '—') ?></span></div>
        </div>
        <div>
            <div class="info-row"><span class="info-label">Position:</span><span class="info-val text-primary"><?= !empty($termRemark['position']) ? $termRemark['position'] . ' of ' . ($termRemark['class_size'] ?? '—') : '—' ?></span></div>
            <div class="info-row"><span class="info-label">Days Present:</span><span class="info-val text-success"><?= e((string)$attendance['present']) ?></span></div>
            <div class="info-row"><span class="info-label">Days Absent:</span><span class="info-val text-danger"><?= e((string)$attendance['absent']) ?></span></div>
            <div class="info-row"><span class="info-label">Session:</span><span class="info-val"><?= e($year) ?></span></div>
        </div>
    </div>

    <!-- Dynamic Subjects Results Table (ZERO hardcoded limits) -->
    <table>
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Subject</th>
                <th style="width:55px;">Test 1<br><small style="opacity:0.75;">(15)</small></th>
                <th style="width:55px;">Test 2<br><small style="opacity:0.75;">(10)</small></th>
                <th style="width:55px;">Assign<br><small style="opacity:0.75;">(10)</small></th>
                <th style="width:55px;">Mid<br><small style="opacity:0.75;">(10)</small></th>
                <th style="width:55px;">Exam<br><small style="opacity:0.75;">(55)</small></th>
                <th style="width:65px;">Total<br><small style="opacity:0.75;">(100)</small></th>
                <th style="width:55px;">Grade</th>
                <th style="width:90px;">Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1; 
            $totalSum = 0;
            $scoredCount = 0;
            foreach ($subjectsList as $r): 
                $total = isset($r['total']) && $r['total'] !== null ? (float)$r['total'] : null;
                if ($total !== null) {
                    $totalSum += $total;
                    $scoredCount++;
                }
                $subjName = $r['name'] ?? ($r['subject_name'] ?? 'Subject');
            ?>
            <tr>
                <td style="color:#64748b;"><?= $i++ ?></td>
                <td><?= e($subjName) ?></td>
                <td><?= isset($r['ca1']) && $r['ca1'] !== null ? number_format((float)$r['ca1'], 1) : '—' ?></td>
                <td><?= isset($r['ca2']) && $r['ca2'] !== null ? number_format((float)$r['ca2'], 1) : '—' ?></td>
                <td><?= isset($r['assignment']) && $r['assignment'] !== null ? number_format((float)$r['assignment'], 1) : '—' ?></td>
                <td><?= isset($r['mid_term']) && $r['mid_term'] !== null ? number_format((float)$r['mid_term'], 1) : '—' ?></td>
                <td><?= isset($r['exam']) && $r['exam'] !== null ? number_format((float)$r['exam'], 1) : '—' ?></td>
                <td style="font-weight:700; color:#0f766e;"><?= $total !== null ? number_format($total, 1) : '—' ?></td>
                <td style="font-weight:700;"><?= e($r['grade'] ?? '—') ?></td>
                <td style="font-size:11px;"><?= e($r['remark'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php 
            $actualSubjectCount = count($subjectsList);
            $finalAvg = $actualSubjectCount > 0 ? round($totalSum / $actualSubjectCount, 1) : 0.0;
            ?>
            <tr class="tfoot-row">
                <td colspan="7" style="text-align:right; padding-right:12px;">
                    TOTAL SCORE (<?= $actualSubjectCount ?> Subjects Offered) &nbsp;|&nbsp; <strong>AVERAGE PERCENTAGE</strong>:
                </td>
                <td style="color:#0f766e; font-size:13px;"><?= number_format($totalSum, 1) ?></td>
                <td colspan="2" style="font-size:12.5px; text-align:center; color:#0f766e;">
                    <?= $finalAvg ?>%
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Configurable Grading Scale Key -->
    <div class="grading-key">
        <h3>Grading Scale Key</h3>
        <div class="key-grid">
            <?php foreach ($scaleRules as $rule): ?>
                <div class="key-item">
                    <strong style="color:#0f766e;"><?= e($rule['label']) ?></strong><br>
                    <span style="font-size:9.5px; color:#64748b;"><?= round($rule['min']) ?>-<?= round($rule['max']) ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Teacher & Principal Remarks -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
        <div class="remarks-box">
            <h3>Class Teacher's Remark</h3>
            <p style="margin:0; font-style:italic;"><?= e($termRemark['class_teacher_remark'] ?: 'An encouraging and consistent academic performance.') ?></p>
        </div>
        <div class="remarks-box">
            <h3>Principal's Remark</h3>
            <p style="margin:0; font-style:italic;"><?= e($termRemark['principal_remark'] ?: 'Good effort. Keep striving for greater heights next term.') ?></p>
        </div>
    </div>

    <?php if (!empty($termRemark['next_term_begins'])): ?>
        <div style="text-align:center; border:1px solid #0f766e; border-radius:6px; padding:7px; margin-bottom:16px; font-size:12px; color:#0f766e; font-weight:700; background:#f0fdf4;">
            NEXT TERM RESUMPTION DATE: <?= date('l, F j, Y', strtotime($termRemark['next_term_begins'])) ?>
        </div>
    <?php endif; ?>

    <!-- Signature Line Blocks -->
    <div class="sig-row">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Class Teacher's Signature</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Principal's Signature & Stamp</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-label">Parent / Guardian Signature</div>
        </div>
    </div>

    <div style="text-align:center; margin-top:20px; font-size:10px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:10px;">
        This document is an authentic academic record generated by <?= e(setting('school_name', 'EduCore')) ?> &bull; Powered by SkySaving Tech Hub
    </div>
</div>

</body>
</html>
