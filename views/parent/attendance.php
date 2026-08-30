<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-calendar-check" style="margin-right:8px;color:#0b3d91;"></i>Attendance &amp; Campus Movement Record</div>
    <div class="topbar-actions">
        <span style="font-size:13px;color:#6b7280;"><?= e($summary['Present'] ?? 0) ?> days present this month</span>
    </div>
</div>
<div class="parent-content">

    <!-- Month Filter -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:16px 20px;margin-bottom:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <form method="GET" action="<?= url('parent/attendance') ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="route" value="attendance">
            <select name="month" style="padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;">
                <?php foreach ($months as $m => $mName): ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= e($mName) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="year" value="<?= $year ?>" min="2020" max="<?= date('Y') + 1 ?>" style="width:90px;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;">
            <button type="submit" style="padding:8px 16px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Filter</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));gap:14px;margin-bottom:20px;">
        <?php $cards = [
            'Present' => ['#16a34a','#f0fdf4','ti-circle-check', $summary['Present'] ?? 0],
            'Late' => ['#d97706','#fef9ec','ti-clock', $summary['Late'] ?? 0],
            'Absent' => ['#dc2626','#fee2e2','ti-circle-x', $summary['Absent'] ?? 0],
            'Gate Exits' => ['#0284c7','#f0f9ff','ti-door-exit', $totalExits ?? 0],
            'Early Exits' => ['#ea580c','#fff7ed','ti-clock-exclamation', $earlyExits ?? 0]
        ]; ?>
        <?php foreach ($cards as $label => [$color, $bg, $icon, $val]): ?>
            <div style="background:#fff;border-radius:12px;border:1px solid #e8eef4;padding:16px;text-align:center;">
                <div style="width:36px;height:36px;border-radius:8px;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;">
                    <i class="ti <?= $icon ?>" style="font-size:18px;color:<?= $color ?>;"></i>
                </div>
                <div style="font-size:22px;font-weight:700;color:<?= $color ?>;"><?= $val ?></div>
                <div style="font-size:11px;color:#6b7280;"><?= $label ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Combined Daily Check-In & Gate Exit Audit Table -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;font-weight:600;color:#1a2535;font-size:14px;display:flex;justify-content:space-between;align-items:center;">
            <span>Daily Arrival &amp; Departure Log — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></span>
            <span class="badge bg-light text-muted fw-normal" style="font-size:11px;">Tracked via Gate QR Card</span>
        </div>
        <?php if ($records || $exitRecords): ?>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb;">
                    <th style="padding:12px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;">Date &amp; Day</th>
                    <th style="padding:12px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;">Morning Check-In</th>
                    <th style="padding:12px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;">Afternoon Departure</th>
                    <th style="padding:12px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;">Gate &amp; Collector</th>
                    <th style="padding:12px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;text-transform:uppercase;">Status / Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Merge unique dates
                $allDates = array_unique(array_merge(array_column($records, 'date'), array_column($exitRecords, 'exit_date')));
                rsort($allDates);

                $attByDate = [];
                foreach ($records as $r) {
                    $attByDate[$r['date']] = $r;
                }

                foreach ($allDates as $d):
                    $r = $attByDate[$d] ?? null;
                    $exit = $dailyExits[$d] ?? null;

                    $colors = ['Present'=>'#16a34a','Absent'=>'#dc2626','Late'=>'#d97706','Excused'=>'#7c3aed'];
                    $bgs    = ['Present'=>'#f0fdf4','Absent'=>'#fee2e2','Late'=>'#fef9ec','Excused'=>'#f5f0ff'];
                ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:14px 20px;font-weight:600;color:#1e293b;">
                        <?= date('M j, Y', strtotime($d)) ?>
                        <div style="font-size:11px;color:#64748b;font-weight:normal;"><?= date('l', strtotime($d)) ?></div>
                    </td>

                    <!-- Check-in column -->
                    <td style="padding:14px 20px;">
                        <?php if ($r): ?>
                            <?php $c = $colors[$r['status']] ?? '#6b7280'; $b = $bgs[$r['status']] ?? '#f9fafb'; ?>
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:<?= $b ?>;color:<?= $c ?>;font-size:11px;font-weight:700;">
                                <?= e($r['status']) ?>
                            </span>
                            <?php if (!empty($r['time_in'])): ?>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                    <i class="ti ti-clock" style="font-size:10px;"></i> <?= date('g:i A', strtotime($r['time_in'])) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Exit column -->
                    <td style="padding:14px 20px;">
                        <?php if ($exit): ?>
                            <?php if ($exit['exit_type'] === 'early'): ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:#fff7ed;color:#ea580c;font-size:11px;font-weight:700;">
                                    <i class="ti ti-clock-exclamation"></i> Early Departure
                                </span>
                            <?php else: ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:12px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:700;">
                                    <i class="ti ti-check"></i> Normal Dismissal
                                </span>
                            <?php endif; ?>
                            <div style="font-size:11px;color:#1e293b;font-weight:600;margin-top:2px;">
                                <i class="ti ti-door-exit" style="font-size:11px;color:#0284c7;"></i> <?= date('g:i A', strtotime($exit['exit_time'])) ?>
                            </div>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:12px;">No exit scan</span>
                        <?php endif; ?>
                    </td>

                    <!-- Gate & Pickup column -->
                    <td style="padding:14px 20px;">
                        <?php if ($exit): ?>
                            <div style="font-weight:600;color:#334155;font-size:12px;">
                                <?= e($exit['gate_name'] ?: 'School Gate') ?>
                            </div>
                            <?php if ($exit['pickup_person_name']): ?>
                                <div style="font-size:11px;color:#64748b;">
                                    <i class="ti ti-user-check" style="color:#16a34a;"></i> Collector: <?= e($exit['pickup_person_name']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color:#94a3b8;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Remarks / Reason column -->
                    <td style="padding:14px 20px;color:#64748b;font-size:12px;">
                        <?php if ($exit && $exit['exit_reason']): ?>
                            <div style="color:#1e293b;font-weight:500;"><?= e($exit['exit_reason']) ?></div>
                            <?php if ($exit['exit_reason_notes']): ?>
                                <div style="font-size:11px;color:#94a3b8;"><?= e($exit['exit_reason_notes']) ?></div>
                            <?php endif; ?>
                        <?php elseif ($r && $r['remark']): ?>
                            <?= e($r['remark']) ?>
                        <?php else: ?>
                            <span style="color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div style="text-align:center;padding:48px;color:#9ca3af;">
                <i class="ti ti-calendar-off" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                No attendance or exit records found for this period.
            </div>
        <?php endif; ?>
    </div>
</div>
