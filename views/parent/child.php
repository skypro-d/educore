<?php
$s = $student;
$dob = $s['date_of_birth'] ? date('M j, Y', strtotime($s['date_of_birth'])) : '—';
$age = $s['date_of_birth'] ? (int) date_diff(new DateTime($s['date_of_birth']), new DateTime())->y . ' years' : '—';
?>
<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-user" style="margin-right:8px;color:#d97706;"></i>Child Profile</div>
</div>
<div class="parent-content">
    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- Photo Card -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:28px;text-align:center;">
            <?php if ($s['passport_photo']): ?>
                <img src="<?= url('uploads/' . $s['passport_photo']) ?>" alt="Photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #eef4ff;margin-bottom:16px;">
            <?php else: ?>
                <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#0b3d91,#1a6dd8);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:40px;font-weight:700;color:#fff;"><?= strtoupper(substr($s['first_name'],0,1).substr($s['last_name'],0,1)) ?></div>
            <?php endif; ?>
            <div style="font-size:18px;font-weight:700;color:#1a2535;"><?= e($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'].' ' : '') . $s['last_name']) ?></div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px;"><?= e($s['class_name'] ?? '—') ?></div>
            <div style="margin-top:12px;display:inline-flex;align-items:center;gap:6px;padding:5px 14px;background:#f0fdf4;color:#16a34a;border-radius:20px;font-size:12px;font-weight:700;">
                <i class="ti ti-circle-check" style="font-size:14px;"></i><?= e($s['status']) ?>
            </div>
            <div style="margin-top:16px;padding:12px;background:#f9fafb;border-radius:10px;text-align:left;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:4px;">REG. NUMBER</div>
                <div style="font-size:13px;font-weight:700;color:#0b3d91;"><?= e($s['admission_number'] ?? $s['application_number']) ?></div>
            </div>
        </div>

        <!-- Details -->
        <div>
            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;margin-bottom:16px;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-info-circle" style="margin-right:7px;color:#0b3d91;"></i>Personal Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                    <?php $fields = [
                        ['Date of Birth', $dob], ['Age', $age],
                        ['Gender', $s['gender']], ['Blood Group', $s['blood_group'] ?: '—'],
                        ['State of Origin', $s['state_of_origin']], ['Nationality', $s['nationality']],
                        ['Religion', $s['religion'] ?: '—'], ['L.G.A.', $s['local_government'] ?: '—'],
                    ]; ?>
                    <?php foreach ($fields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;margin-bottom:16px;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-users" style="margin-right:7px;color:#0b3d91;"></i>Parent / Guardian</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                    <?php $pfields = [
                        ["Father's Name", $s['father_name'] ?: '—'], ["Mother's Name", $s['mother_name'] ?: '—'],
                        ['Guardian Name', $s['guardian_name'] ?: '—'], ['Phone', $s['parent_phone']],
                        ['Email', $s['parent_email']], ['Occupation', $s['parent_occupation'] ?: '—'],
                    ]; ?>
                    <?php foreach ($pfields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($s['emergency_name']): ?>
            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-alert-triangle" style="margin-right:7px;color:#dc2626;"></i>Emergency Contact</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;">
                    <?php $efields = [['Name',$s['emergency_name']],['Relationship',$s['emergency_relationship']],['Phone',$s['emergency_phone']]]; ?>
                    <?php foreach ($efields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
