<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';

$number = trim($_GET['number'] ?? '');
if ($number === '') {
    flash('warning', 'Application number is required.');
    redirect('student');
}

$application = (new Applicant(Database::connect()))->findByNumber($number);
if (!$application) {
    flash('warning', 'Application not found.');
    redirect('student');
}

$isEnrolled = $application['status'] === 'Enrolled' || (($application['enrollment_status'] ?? '') === 'Completed');
if (!$isEnrolled) {
    flash('warning', 'Admission letter is available after enrollment is completed.');
    redirect('student?number=' . urlencode($number));
}

$backUrl = url('student?number=' . urlencode($number));
render('admin/letter', compact('application', 'backUrl'), 'auth');
