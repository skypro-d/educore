<?php
$studentName = trim($application['first_name'] . ' ' . $application['last_name']);
$admissionNumber = $application['admission_number'] ?: ('SCH/' . date('Y') . '/' . str_pad((string) $application['id'], 5, '0', STR_PAD_LEFT));
$letterValues = [
    '{student_name}' => $studentName,
    '{first_name}' => $application['first_name'],
    '{last_name}' => $application['last_name'],
    '{class_name}' => $application['class_name'] ?? '',
    '{school_name}' => setting('school_name', 'our school'),
    '{application_number}' => $application['application_number'],
    '{admission_number}' => $admissionNumber,
    '{date}' => date('F j, Y'),
];
$renderLetterText = static fn (string $text): string => nl2br(e(strtr($text, $letterValues)));
$letterTitle = setting('admission_letter_title', 'Offer of Admission');
$letterBody = setting('admission_letter_body', 'We are pleased to inform you that you have been offered admission into {class_name} at {school_name}.');
$letterInstruction = setting('admission_letter_instruction', 'Please report to the school office with original copies of your submitted documents.');
$letterClosing = setting('admission_letter_closing', 'Congratulations, and welcome to our academic community.');
?>
<div class="letter-sheet mx-auto">
    <div class="letter-head">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?><img class="nav-logo" src="<?= e($logoUrl) ?>" alt="Logo" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='inline-grid';"><span class="logo-mark" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></span><?php else: ?><span class="logo-mark"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></span><?php endif; ?>
        <div>
            <h1><?= e(setting('school_name', 'Bluefield International School')) ?></h1>
            <p><?= e(setting('school_address', 'No. 1 Excellence Avenue, Lagos, Nigeria')) ?></p>
        </div>
    </div>
    <hr>
    <p class="text-end"><?= date('F j, Y') ?></p>
    <h2 class="text-center"><?= e($letterTitle) ?></h2>
    <p>Dear <?= e($studentName) ?>,</p>
    <p><?= $renderLetterText($letterBody) ?></p>
    <p>Your admission number is <strong><?= e($admissionNumber) ?></strong>. <?= $renderLetterText($letterInstruction) ?></p>
    <div class="d-flex justify-content-between align-items-end mt-4">
        <div>
            <strong>QR Verification</strong><br>
            <img alt="QR Verification" width="120" height="120" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode(url('track?number=' . $application['application_number'])) ?>">
        </div>
        <div class="text-center">
            <div style="width:140px;height:80px;border:1px dashed #111827;display:grid;place-items:center;">School Stamp</div>
        </div>
    </div>
    <p><?= $renderLetterText($letterClosing) ?></p>
    <div class="signature">
        <?php if ($sig = setting('principal_signature', '')): ?>
            <img src="<?= url('uploads/' . $sig) ?>" alt="Signature" style="max-height:45px;max-width:140px;object-fit:contain;margin-bottom:-10px;">
        <?php else: ?>
            <span></span>
        <?php endif; ?>
        <strong><?= e(setting('principal_name', 'The Principal')) ?></strong>
        <small><?= e(setting('admission_letter_signature_title', setting('principal_signature_title', 'Principal'))) ?></small>
    </div>
    <div class="no-print text-center mt-4 d-flex justify-content-center align-items-center gap-2 flex-wrap">
        <button class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer"></i> Print / PDF Download</button>
        <?php if (!empty($application['parent_email'])): ?>
            <form method="post" action="<?= url('admin/letter/' . $application['id'] . '/send') ?>" style="display:inline;" onsubmit="return confirm('Email this admission letter to <?= e($application['parent_email']) ?>?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success"><i class="ti ti-mail-forward"></i> Email to Parent</button>
            </form>
        <?php endif; ?>
        <a class="btn btn-outline-secondary" href="<?= e($backUrl ?? url('admin/applications/' . $application['id'])) ?>"><i class="ti ti-arrow-left"></i> Back to Application</a>
    </div>
</div>
