<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Result Sheet — <?= e($student['first_name'].' '.$student['last_name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; background: #fff; color: #1a1a1a; }
        .page { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 16mm 18mm; }
        .school-header { text-align: center; border-bottom: 3px double #0b3d91; padding-bottom: 14px; margin-bottom: 16px; }
        .school-header .logo { max-height: 70px; margin-bottom: 6px; }
        .school-header h1 { font-size: 20px; font-weight: 700; color: #0b3d91; text-transform: uppercase; letter-spacing: 1px; }
        .school-header p { font-size: 12px; color: #555; margin-top: 2px; }
        .report-title { text-align: center; background: #0b3d91; color: #fff; padding: 6px 0; margin-bottom: 16px; font-size: 14px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 14px; }
        .info-row { display: flex; border-bottom: 1px dotted #ccc; padding: 3px 0; font-size: 12px; }
        .info-label { font-weight: 700; color: #555; width: 130px; flex-shrink: 0; }
        .info-val { color: #1a1a1a; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead tr { background: #0b3d91; color: #fff; }
        th { padding: 7px 8px; font-size: 11px; text-align: center; font-weight: 700; border: 1px solid #0b3d91; }
        th:first-child { text-align: left; }
        td { padding: 6px 8px; font-size: 12px; text-align: center; border: 1px solid #ddd; }
        td:first-child { text-align: left; font-weight: 600; }
        tr:nth-child(even) td { background: #f5f8ff; }
        .tfoot-row td { background: #e8eef9; font-weight: 700; border-top: 2px solid #0b3d91; }
        .grade-chip { display: inline-block; padding: 1px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .grade-pass { background: #d1fae5; color: #065f46; }
        .grade-fail { background: #fee2e2; color: #991b1b; }
        .remarks-box { border: 1px solid #ddd; border-radius: 4px; padding: 12px 14px; margin-bottom: 14px; background: #fafafa; font-size: 12px; }
        .remarks-box h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 6px; }
        .sig-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-top: 24px; }
        .sig-block { text-align: center; }
        .sig-line { border-bottom: 1px solid #333; margin-bottom: 5px; height: 40px; }
        .sig-label { font-size: 11px; color: #555; }
        .grading-key { border: 1px solid #ddd; padding: 8px; margin-bottom: 14px; font-size: 11px; }
        .grading-key h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .grading-key .key-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px; }
        .key-item { text-align: center; background: #f9fafb; padding: 3px; border-radius: 3px; }
        .stamp-box { border: 2px dashed #0b3d91; display: inline-block; width: 80px; height: 80px; text-align: center; vertical-align: middle; color: #0b3d91; font-size: 9px; padding-top: 30px; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
<div class="no-print" style="padding:16px;background:#f0f4f8;display:flex;justify-content:center;gap:12px;margin-bottom:0;">
    <button onclick="window.print()" style="padding:8px 20px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;"><i class="ti ti-printer"></i> Print</button>
    <a href="javascript:history.back()" style="padding:8px 20px;background:#fff;border:1px solid #ddd;border-radius:8px;font-size:13px;text-decoration:none;color:#374151;">← Back</a>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="page">
    <!-- Header -->
    <div class="school-header">
        <?php if (setting('school_logo')): ?>
            <img class="logo" src="<?= url('uploads/'.setting('school_logo')) ?>" alt="Logo">
        <?php endif; ?>
        <h1><?= e(setting('school_name', 'School Name')) ?></h1>
        <p><?= e(setting('school_address', '')) ?> &nbsp;|&nbsp; <?= e(setting('school_phone','')) ?></p>
        <p><?= e(setting('school_email','')) ?></p>
    </div>

    <div class="report-title"><?= e($term) ?> Term Academic Report — <?= e($year) ?></div>

    <!-- Student Info -->
    <div class="info-grid">
        <div>
            <div class="info-row"><span class="info-label">Student Name:</span><span class="info-val"><?= e($student['first_name'].' '.($student['middle_name']?' '.$student['middle_name'].' ':'').$student['last_name']) ?></span></div>
            <div class="info-row"><span class="info-label">Class:</span><span class="info-val"><?= e($student['class_name'] ?? '—') ?></span></div>
            <div class="info-row"><span class="info-label">Admission No.:</span><span class="info-val"><?= e($student['admission_number'] ?? $student['application_number']) ?></span></div>
        </div>
        <div>
            <div class="info-row"><span class="info-label">Position:</span><span class="info-val"><?= $termRemark['position'] ?? '—' ?> of <?= $termRemark['class_size'] ?? '—' ?></span></div>
            <div class="info-row"><span class="info-label">Times Present:</span><span class="info-val"><?= $termRemark['times_present'] ?? '—' ?></span></div>
            <div class="info-row"><span class="info-label">Times Absent:</span><span class="info-val"><?= $termRemark['times_absent'] ?? '—' ?></span></div>
        </div>
    </div>

    <!-- Results Table -->
    <?php
    $totalAll = 0; $subCount = count($results);
    $ca1Max   = (int) setting('ca1_max', 10);
    $ca2Max   = (int) setting('ca2_max', 10);
    $assnMax  = (int) setting('assignment_max', 10);
    $midMax   = (int) setting('mid_term_max', 10);
    $examMax  = (int) setting('exam_max', 60);
    $grandMax = $ca1Max + $ca2Max + $assnMax + $midMax + $examMax;
    ?>
    <table>
        <thead>
            <tr>
                <th style="text-align:left;width:180px;">SUBJECT</th>
                <th>CA1<br>(<?= $ca1Max ?>)</th>
                <th>CA2<br>(<?= $ca2Max ?>)</th>
                <th>Assgn<br>(<?= $assnMax ?>)</th>
                <th>Mid<br>(<?= $midMax ?>)</th>
                <th>Exam<br>(<?= $examMax ?>)</th>
                <th>TOTAL<br>(<?= $grandMax ?>)</th>
                <th>GRADE</th>
                <th>REMARK</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $r):
                $total = (float) ($r['total'] ?? 0);
                $totalAll += $total;
                $passed = $total >= 50;
            ?>
            <tr>
                <td><?= e($r['subject_name']) ?></td>
                <td><?= $r['ca1'] !== null ? number_format((float)$r['ca1'],1) : '—' ?></td>
                <td><?= $r['ca2'] !== null ? number_format((float)$r['ca2'],1) : '—' ?></td>
                <td><?= $r['assignment'] !== null ? number_format((float)$r['assignment'],1) : '—' ?></td>
                <td><?= $r['mid_term'] !== null ? number_format((float)$r['mid_term'],1) : '—' ?></td>
                <td><?= $r['exam'] !== null ? number_format((float)$r['exam'],1) : '—' ?></td>
                <td style="font-weight:700;"><?= number_format($total,1) ?></td>
                <td><span class="grade-chip <?= $passed ? 'grade-pass' : 'grade-fail' ?>"><?= e($r['grade'] ?? '—') ?></span></td>
                <td><?= e($r['remark'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="6">TOTAL SCORE / AVERAGE</td>
                <td><?= number_format($totalAll, 1) ?></td>
                <td colspan="2"><?= $subCount ? round($totalAll / $subCount, 1) : '—' ?> avg</td>
            </tr>
        </tfoot>
    </table>

    <!-- Grading Key -->
    <div class="grading-key">
        <h3>Grading Scale</h3>
        <div class="key-grid">
            <?php
            $grades = [
                [setting('grade_a1_label','A1'), setting('grade_a1_min','75').'-100'],
                [setting('grade_b2_label','B2'), setting('grade_b2_min','70').'-74'],
                [setting('grade_b3_label','B3'), setting('grade_b3_min','65').'-69'],
                [setting('grade_c4_label','C4'), setting('grade_c4_min','60').'-64'],
                [setting('grade_c5_label','C5'), setting('grade_c5_min','55').'-59'],
                [setting('grade_c6_label','C6'), setting('grade_c6_min','50').'-54'],
                [setting('grade_d7_label','D7'), setting('grade_d7_min','45').'-49'],
                [setting('grade_e8_label','E8'), setting('grade_e8_min','40').'-44'],
                [setting('grade_f9_label','F9'), '0-39'],
            ];
            foreach ($grades as [$g,$r]):
            ?>
                <div class="key-item"><strong><?= e($g) ?></strong><br><?= e($r) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Remarks -->
    <?php if (!empty($termRemark['class_teacher_remark']) || !empty($termRemark['principal_remark'])): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div class="remarks-box">
            <h3>Class Teacher's Remark</h3>
            <p><?= e($termRemark['class_teacher_remark'] ?: '—') ?></p>
        </div>
        <div class="remarks-box">
            <h3>Principal's Remark</h3>
            <p><?= e($termRemark['principal_remark'] ?: '—') ?></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($termRemark['next_term_begins'])): ?>
    <div style="text-align:center;border:1px solid #0b3d91;border-radius:4px;padding:8px;margin-bottom:16px;font-size:12px;color:#0b3d91;font-weight:700;">
        NEXT TERM BEGINS: <?= date('D, F j Y', strtotime($termRemark['next_term_begins'])) ?>
    </div>
    <?php endif; ?>

    <!-- Signatures -->
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

    <div style="text-align:center;margin-top:20px;font-size:10px;color:#9ca3af;border-top:1px solid #eee;padding-top:10px;">
        This result is computer-generated by <?= e(setting('school_name','')) ?> School Management System &nbsp;|&nbsp; Powered by SkySaving Tech Hub
    </div>
</div>
</body>
</html>
