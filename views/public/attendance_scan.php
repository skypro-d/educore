<?php
// $student is passed in from PublicController::attendanceScan()
$name = e(trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')));
$admNo = e($student['admission_number'] ?? '');
$class = e($student['class_name'] ?? '');
$photo = !empty($student['passport_photo']) ? url('uploads/' . $student['passport_photo']) : null;
$status = $student['scan_status'] ?? 'success'; // 'success', 'already', 'error'
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Attendance Scan – EduCore</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    min-height: 100vh;
    background: #060f22;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
    padding: 1.5rem;
}
.card {
    background: #0f1c35;
    border-radius: 20px;
    padding: 2.5rem 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    border: 1px solid rgba(255,255,255,.08);
    box-shadow: 0 25px 80px rgba(0,0,0,.6);
    animation: popIn .45s cubic-bezier(.16,1,.3,1) both;
}
@keyframes popIn {
    from { opacity:0; transform: scale(.88) translateY(20px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.4rem;
}
.icon-wrap.success { background: rgba(34,197,94,.15); border: 2px solid rgba(34,197,94,.4); }
.icon-wrap.already { background: rgba(251,191,36,.15); border: 2px solid rgba(251,191,36,.4); }
.icon-wrap.error   { background: rgba(239,68,68,.15);  border: 2px solid rgba(239,68,68,.4); }
.photo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 1.5rem;
    display: block;
    border: 3px solid rgba(255,255,255,.12);
}
.status-badge {
    display: inline-block;
    padding: .35rem 1rem;
    border-radius: 999px;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    margin-bottom: 1.2rem;
}
.badge-success { background: rgba(34,197,94,.2); color: #4ade80; }
.badge-already { background: rgba(251,191,36,.2); color: #fcd34d; }
.badge-error   { background: rgba(239,68,68,.2);  color: #f87171; }
.student-name {
    font-size: 1.5rem;
    font-weight: 900;
    color: #ffffff;
    margin-bottom: .4rem;
}
.student-meta {
    font-size: .85rem;
    color: rgba(255,255,255,.45);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}
.time-block {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.time-label { font-size: .7rem; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .08em; margin-bottom: .3rem; }
.time-value { font-size: 1.5rem; font-weight: 700; color: #ffffff; }
.date-value { font-size: .85rem; color: rgba(255,255,255,.5); margin-top: .2rem; }
.message-text { font-size: .92rem; color: rgba(255,255,255,.55); line-height: 1.6; }
.school-name { margin-top: 2rem; font-size: .72rem; color: rgba(255,255,255,.2); letter-spacing: .05em; }
</style>
</head>
<body>
<div class="card">

<?php if ($status === 'success'): ?>

    <?php if ($photo): ?>
        <img class="photo" src="<?= $photo ?>" alt="<?= $name ?>">
    <?php else: ?>
        <div class="icon-wrap success">✓</div>
    <?php endif; ?>
    <span class="status-badge badge-success">✓ Attendance Marked</span>
    <div class="student-name"><?= $name ?></div>
    <div class="student-meta">
        Admission No: <strong style="color:rgba(255,255,255,.7)"><?= $admNo ?></strong><br>
        Class: <strong style="color:rgba(255,255,255,.7)"><?= $class ?></strong>
    </div>
    <div class="time-block">
        <div class="time-label">Time In</div>
        <div class="time-value"><?= date('h:i A') ?></div>
        <div class="date-value"><?= date('l, d F Y') ?></div>
    </div>
    <p class="message-text">Your attendance has been successfully recorded for today.</p>

<?php elseif ($status === 'already'): ?>

    <?php if ($photo): ?>
        <img class="photo" src="<?= $photo ?>" alt="<?= $name ?>">
    <?php else: ?>
        <div class="icon-wrap already">⚠</div>
    <?php endif; ?>
    <span class="status-badge badge-already">Already Recorded</span>
    <div class="student-name"><?= $name ?></div>
    <div class="student-meta">
        Admission No: <strong style="color:rgba(255,255,255,.7)"><?= $admNo ?></strong><br>
        Class: <strong style="color:rgba(255,255,255,.7)"><?= $class ?></strong>
    </div>
    <div class="time-block">
        <div class="time-label">Today's Date</div>
        <div class="time-value"><?= date('h:i A') ?></div>
        <div class="date-value"><?= date('l, d F Y') ?></div>
    </div>
    <p class="message-text">Your attendance has already been recorded today. No duplicate entry needed.</p>

<?php elseif ($status === 'denied'): ?>

    <div class="icon-wrap error">✕</div>
    <span class="status-badge badge-error">Scan Denied</span>
    <div class="student-name"><?= $name ?></div>
    <div class="student-meta">
        Admission No: <strong style="color:rgba(255,255,255,.7)"><?= $admNo ?></strong><br>
        Class: <strong style="color:rgba(255,255,255,.7)"><?= $class ?></strong>
    </div>
    <div class="time-block">
        <div class="time-label">Time Scanned</div>
        <div class="time-value"><?= date('h:i A') ?></div>
        <div class="date-value"><?= date('l, d F Y') ?></div>
    </div>
    <p class="message-text">The attendance window for today is closed. Resumption is no longer allowed.</p>

<?php else: ?>

    <div class="icon-wrap error">✕</div>
    <span class="status-badge badge-error">Error</span>
    <div class="student-name">Student Not Found</div>
    <p class="message-text" style="margin-top:1rem">This QR code is invalid or the student record could not be found. Please contact administration.</p>

<?php endif; ?>

    <div class="school-name"><?= e(setting('school_name', APP_NAME)) ?> • Attendance System</div>
</div>
</body>
</html>
