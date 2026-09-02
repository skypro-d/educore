<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Result Broadsheet — <?= e($class['name'] ?? 'Class') ?> | <?= e($subject['name'] ?? 'Subject') ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/bootstrap.min.css') ?>">
    <style>
        body {
            background: #fff;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding: 30px;
            font-size: 13px;
        }
        .header-box {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .school-title {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .doc-title {
            font-size: 15px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .meta-table td {
            padding: 4px 8px;
            font-size: 12.5px;
        }
        .table-broadsheet {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-broadsheet th, .table-broadsheet td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: middle;
        }
        .table-broadsheet th {
            background-color: #f8fafc;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            color: #1e293b;
        }
        .sign-box {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sign-line {
            width: 220px;
            border-top: 1px solid #000;
            text-align: center;
            padding-top: 6px;
            font-size: 11.5px;
            font-weight: 600;
        }
        @media print {
            body {
                padding: 10px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light border rounded">
        <div>
            <strong>Official Broad Sheet Preview</strong> &mdash; Press Print to print or export as PDF.
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold">
                Print Broadsheet
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">
                Close Window
            </button>
        </div>
    </div>

    <div class="header-box">
        <div class="school-title"><?= e(setting('school_name', 'EduCore International Academy')) ?></div>
        <div style="font-size:12px; color:#64748b;"><?= e(setting('school_address', 'School Campus Address')) ?> | <?= e(setting('school_phone', '')) ?></div>
        <div class="doc-title mt-2">Term Academic Result Broadsheet</div>
    </div>

    <table class="w-100 mb-3 meta-table">
        <tr>
            <td style="width:15%;"><strong>Class:</strong> <?= e($class['name'] ?? '—') ?></td>
            <td style="width:25%;"><strong>Subject:</strong> <?= e($subject['name'] ?? '—') ?> (<?= e($subject['code'] ?? '') ?>)</td>
            <td style="width:20%;"><strong>Academic Session:</strong> <?= e($academicYear) ?></td>
            <td style="width:15%;"><strong>Term:</strong> <?= e($term) ?> Term</td>
            <td style="width:25%;"><strong>Date Generated:</strong> <?= date('F j, Y') ?></td>
        </tr>
    </table>

    <table class="table-broadsheet">
        <thead>
            <tr>
                <th style="width:40px;" class="text-center">#</th>
                <th style="width:110px;">Adm No.</th>
                <th>Student Full Name</th>
                <th style="width:60px;" class="text-center">Gender</th>
                <th style="width:65px;" class="text-center">Test 1</th>
                <th style="width:65px;" class="text-center">Test 2</th>
                <th style="width:65px;" class="text-center">Assign</th>
                <th style="width:65px;" class="text-center">Exam</th>
                <th style="width:70px;" class="text-center">Total</th>
                <th style="width:60px;" class="text-center">Grade</th>
                <th style="width:90px;" class="text-center">Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $count = 1;
            $allTotals = [];
            foreach ($students as $st): 
                $r = $results[$st['id']] ?? [];
                if (isset($r['total']) && $r['total'] !== null) {
                    $allTotals[] = (float)$r['total'];
                }
            ?>
                <tr>
                    <td class="text-center text-muted"><?= $count++ ?></td>
                    <td class="font-monospace" style="font-size:11.5px;"><?= e($st['admission_number']) ?></td>
                    <td class="fw-bold"><?= e($st['last_name'] . ' ' . $st['first_name']) ?></td>
                    <td class="text-center"><?= substr(e($st['gender'] ?? 'M'), 0, 1) ?></td>
                    <td class="text-center"><?= isset($r['ca1']) && $r['ca1'] !== null ? number_format((float)$r['ca1'], 1) : '—' ?></td>
                    <td class="text-center"><?= isset($r['ca2']) && $r['ca2'] !== null ? number_format((float)$r['ca2'], 1) : '—' ?></td>
                    <td class="text-center"><?= isset($r['assignment']) && $r['assignment'] !== null ? number_format((float)$r['assignment'], 1) : '—' ?></td>
                    <td class="text-center"><?= isset($r['exam']) && $r['exam'] !== null ? number_format((float)$r['exam'], 1) : '—' ?></td>
                    <td class="text-center fw-bold"><?= isset($r['total']) && $r['total'] !== null ? number_format((float)$r['total'], 1) : '—' ?></td>
                    <td class="text-center fw-bold"><?= e($r['grade'] ?? '—') ?></td>
                    <td class="text-center" style="font-size:11.5px;"><?= e($r['remark'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-4 p-3 border rounded bg-light" style="font-size:12px;">
        <div class="row">
            <div class="col-3"><strong>Total Students:</strong> <?= count($students) ?></div>
            <div class="col-3"><strong>Graded Students:</strong> <?= count($allTotals) ?></div>
            <div class="col-3"><strong>Class Average:</strong> <?= count($allTotals) > 0 ? round(array_sum($allTotals) / count($allTotals), 1) : 0 ?>%</div>
            <div class="col-3"><strong>Highest / Lowest:</strong> <?= count($allTotals) > 0 ? max($allTotals) : 0 ?>% / <?= count($allTotals) > 0 ? min($allTotals) : 0 ?>%</div>
        </div>
    </div>

    <div class="sign-box">
        <div>
            <div class="sign-line">Subject / Class Teacher Signature</div>
        </div>
        <div>
            <div class="sign-line">HOD / Principal Signature & Stamp</div>
        </div>
    </div>

</body>
</html>
