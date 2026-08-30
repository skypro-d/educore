<?php
$totalScore  = array_sum(array_column($results, 'total'));
$subjectCount = count($results);
$average     = $subjectCount ? round($totalScore / $subjectCount, 1) : 0;
?>
<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-report-analytics" style="margin-right:8px;color:#7c3aed;"></i>Academic Results</div>
    <div class="topbar-actions">
        <a href="<?= url('admin/result-sheet/' . ($student['id'] ?? 0) . '?year=' . urlencode($year) . '&term=' . urlencode($term)) ?>" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#0b3d91;color:#fff;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;"><i class="ti ti-printer"></i> Print Result</a>
    </div>
</div>
<div class="parent-content">

    <!-- Filters -->
    <form method="GET" action="<?= url('parent/results') ?>" style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:16px 20px;margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="route" value="results">
        <div>
            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">ACADEMIC YEAR</label>
            <input type="text" name="year" value="<?= e($year) ?>" placeholder="2024/2025" style="padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;width:110px;">
        </div>
        <div>
            <label style="font-size:11px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">TERM</label>
            <select name="term" style="padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;">
                <?php foreach ($terms as $t): ?>
                    <option value="<?= e($t) ?>" <?= $t === $term ? 'selected' : '' ?>><?= e($t) ?> Term</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="align-self:flex-end;">
            <button type="submit" style="padding:9px 20px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">View</button>
        </div>
    </form>

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
        <div style="background:#fff;border-radius:12px;border:1px solid #e8eef4;padding:18px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#0b3d91;"><?= $subjectCount ?></div>
            <div style="font-size:12px;color:#6b7280;margin-top:4px;">Subjects</div>
        </div>
        <div style="background:#fff;border-radius:12px;border:1px solid #e8eef4;padding:18px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#7c3aed;"><?= $average ?></div>
            <div style="font-size:12px;color:#6b7280;margin-top:4px;">Average Score</div>
        </div>
        <div style="background:#fff;border-radius:12px;border:1px solid #e8eef4;padding:18px;text-align:center;">
            <div style="font-size:28px;font-weight:800;color:#16a34a;"><?= $termRemark['position'] ?? '—' ?></div>
            <div style="font-size:12px;color:#6b7280;margin-top:4px;">Position in Class</div>
        </div>
    </div>

    <!-- Results Table -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;margin-bottom:20px;">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;font-weight:600;color:#1a2535;font-size:14px;">
            <?= e($term) ?> Term Results — <?= e($year) ?>
        </div>
        <?php if ($results): ?>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;">SUBJECT</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">CA1</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">CA2</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">ASSGN</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">EXAM</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">TOTAL</th>
                    <th style="padding:10px 12px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">GRADE</th>
                    <th style="padding:10px 12px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;">REMARK</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r):
                    $total = (float) ($r['total'] ?? 0);
                    $gradeColor = $total >= 50 ? '#16a34a' : '#dc2626';
                    $gradeBg = $total >= 50 ? '#f0fdf4' : '#fee2e2';
                ?>
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:12px 16px;font-size:13px;font-weight:500;color:#374151;"><?= e($r['subject_name']) ?></td>
                    <td style="padding:12px 12px;text-align:center;font-size:13px;color:#6b7280;"><?= $r['ca1'] !== null ? number_format((float)$r['ca1'],1) : '—' ?></td>
                    <td style="padding:12px 12px;text-align:center;font-size:13px;color:#6b7280;"><?= $r['ca2'] !== null ? number_format((float)$r['ca2'],1) : '—' ?></td>
                    <td style="padding:12px 12px;text-align:center;font-size:13px;color:#6b7280;"><?= $r['assignment'] !== null ? number_format((float)$r['assignment'],1) : '—' ?></td>
                    <td style="padding:12px 12px;text-align:center;font-size:13px;color:#6b7280;"><?= $r['exam'] !== null ? number_format((float)$r['exam'],1) : '—' ?></td>
                    <td style="padding:12px 12px;text-align:center;font-size:15px;font-weight:700;color:#1a2535;"><?= number_format($total,1) ?></td>
                    <td style="padding:12px 12px;text-align:center;"><span style="padding:3px 9px;border-radius:20px;background:<?= $gradeBg ?>;color:<?= $gradeColor ?>;font-size:12px;font-weight:700;"><?= e($r['grade'] ?? '—') ?></span></td>
                    <td style="padding:12px 12px;font-size:12px;color:#6b7280;"><?= e($r['remark'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e8eef4;">
                    <td colspan="5" style="padding:12px 16px;font-size:13px;font-weight:700;color:#374151;">TOTAL / AVERAGE</td>
                    <td style="padding:12px 12px;text-align:center;font-size:16px;font-weight:800;color:#0b3d91;"><?= number_format($totalScore, 1) ?></td>
                    <td colspan="2" style="padding:12px 12px;font-size:13px;color:#6b7280;">Avg: <?= $average ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
        <?php else: ?>
            <div style="text-align:center;padding:48px;color:#9ca3af;">
                <i class="ti ti-report-analytics" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                No results found for this term. Please check back later.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($termRemark): ?>
    <!-- Teacher & Principal Remarks -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:20px;">
        <div style="font-weight:600;color:#1a2535;font-size:14px;margin-bottom:16px;"><i class="ti ti-message" style="margin-right:8px;color:#0b3d91;"></i>Remarks</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="padding:16px;background:#f9fafb;border-radius:10px;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:6px;">CLASS TEACHER</div>
                <div style="font-size:13px;color:#374151;line-height:1.6;"><?= e($termRemark['class_teacher_remark'] ?: 'No remarks') ?></div>
            </div>
            <div style="padding:16px;background:#f9fafb;border-radius:10px;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:6px;">PRINCIPAL</div>
                <div style="font-size:13px;color:#374151;line-height:1.6;"><?= e($termRemark['principal_remark'] ?: 'No remarks') ?></div>
            </div>
        </div>
        <?php if ($termRemark['next_term_begins']): ?>
        <div style="margin-top:14px;padding:10px 16px;background:#eef4ff;border-radius:8px;font-size:13px;color:#0b3d91;"><i class="ti ti-calendar" style="margin-right:6px;"></i>Next term begins: <strong><?= date('D, M j Y', strtotime($termRemark['next_term_begins'])) ?></strong></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
