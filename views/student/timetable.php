<h1 class="h3 mb-3"><i class="ti ti-calendar-event text-primary" style="margin-right:8px"></i>My Weekly Class Schedule</h1>

<div class="row">
    <div class="col-12">
        <div class="st-card" style="justify-content:flex-start;">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center" style="font-size:13px;min-width:650px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:120px;">Day</th>
                            <th>Class Timetable Slots (Subject / Teacher / Time)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($days as $day): ?>
                        <tr>
                            <td class="table-light" style="font-weight:700;color:#334155;text-align:left;vertical-align:middle;padding-left:15px;">
                                <?= $day ?>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2" style="min-height:45px;align-items:center;">
                                <?php if (!empty($timetable[$day])): foreach ($timetable[$day] as $slot): ?>
                                    <div style="background:#f1f5f9;border-left:3px solid var(--student-primary);padding:8px 12px;border-radius:6px;text-align:left;min-width:180px;flex:1;">
                                        <div style="font-weight:700;color:#1e293b;font-size:12.5px;"><?= e($slot['subject_name']) ?></div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            <i class="ti ti-user" style="margin-right:3px"></i><?= e($slot['first_name'] . ' ' . $slot['last_name']) ?>
                                        </div>
                                        <div style="font-size:10.5px;font-weight:600;color:var(--student-accent);margin-top:4px;">
                                            <i class="ti ti-clock" style="margin-right:3px"></i><?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; else: ?>
                                    <span style="font-size:12px;color:#94a3b8;margin-left:10px;">No classes scheduled</span>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
