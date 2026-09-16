<?php
$s = $student;
$dob = $s['date_of_birth'] ? date('M j, Y', strtotime($s['date_of_birth'])) : '—';
$age = $s['date_of_birth'] ? (int) date_diff(new DateTime($s['date_of_birth']), new DateTime())->y . ' years' : '—';
$children = $children ?? parent_linked_children();
$activeChildId = (int) ($s['id'] ?? 0);
?>
<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-user" style="margin-right:8px;color:#d97706;"></i>Child Profile</div>
    <?php if (count($children) > 1): ?>
    <div class="topbar-actions">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
            <i class="ti ti-users me-1"></i> <?= count($children) ?> Linked Children
        </span>
    </div>
    <?php endif; ?>
</div>

<div class="parent-content">

    <?php if (count($children) > 1): ?>
    <!-- Multi-Children Switcher Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.6px;">
                <i class="ti ti-switch-horizontal me-1 text-primary"></i> Your Registered Children (<?= count($children) ?>)
            </div>
            <span class="small text-muted">Click any child to switch and view their portal records</span>
        </div>
        <div class="row g-3">
            <?php foreach ($children as $c): 
                $isActive = (int)$c['id'] === $activeChildId;
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?= url('parent/switch-child?id=' . $c['id']) ?>" class="text-decoration-none">
                    <div class="card p-3 rounded-3 h-100 transition-all border <?= $isActive ? 'border-primary shadow-sm bg-primary-subtle' : 'border-light-subtle bg-light-subtle hover-shadow' ?>" style="transition: transform .15s ease-in-out;">
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($c['passport_photo'])): ?>
                                <img src="<?= url('uploads/' . $c['passport_photo']) ?>" alt="Photo" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                            <?php else: ?>
                                <div style="width:48px;height:48px;border-radius:50%;background:#0b3d91;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;">
                                    <?= strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">
                                    <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">Active</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted text-truncate"><?= e($c['class_name'] ?: 'Enrolled') ?> &bull; <?= e($c['admission_number'] ?: $c['application_number']) ?></div>
                            </div>
                            <?php if ($isActive): ?>
                                <i class="ti ti-check-circle-filled text-primary fs-5"></i>
                            <?php else: ?>
                                <i class="ti ti-chevron-right text-muted"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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
