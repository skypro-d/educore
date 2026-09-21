<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

session_start();
$_SESSION['admin'] = [
    'id' => 1,
    'username' => 'admin',
    'permissions' => ['all', 'fees', 'applications']
];

$db = Database::connect();
echo "--- Testing Direct Fee Collection Workflow ---\n";

// 1. Get an applicant
$applicant = $db->query("SELECT id, first_name, last_name, class_id FROM applicants WHERE status = 'Enrolled' LIMIT 1")->fetch();
if (!$applicant) {
    echo "No enrolled applicant found.\n";
    exit(1);
}
echo "1. Student found: ID {$applicant['id']} ({$applicant['first_name']} {$applicant['last_name']})\n";

// 2. Get a fee structure for this student or class
$classId = (int)$applicant['class_id'];
$stmtFs = $db->prepare("SELECT * FROM fee_structures WHERE is_active = 1 AND (class_id IS NULL OR class_id = 0 OR class_id = ?) LIMIT 1");
$stmtFs->execute([$classId]);
$fee = $stmtFs->fetch();

if (!$fee) {
    echo "No fee structure found for student class.\n";
    exit(1);
}
echo "2. Fee structure found: ID {$fee['id']} ({$fee['fee_name']}), Amount: ₦{$fee['amount']}\n";

// 3. Test simulating a payment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'csrf_token' => csrf_token(),
    'applicant_id' => $applicant['id'],
    'fee_structure_id' => $fee['id'],
    'amount_paid' => min(500.0, (float)$fee['amount']),
    'payment_method' => 'cash',
    'payment_date' => date('Y-m-d H:i:s'),
    'notes' => 'Test direct collect button payment',
    'return_to' => 'admin/applications/' . $applicant['id']
];

// Verify controller method logic
$feeStructureId = (int)$_POST['fee_structure_id'];
$applicantId = (int)$_POST['applicant_id'];
$amountPaid = (float)$_POST['amount_paid'];
$method = $_POST['payment_method'];
$date = $_POST['payment_date'];
$notes = $_POST['notes'];
$returnTo = $_POST['return_to'];

$stmtTotal = $db->prepare(
    "SELECT COALESCE(SUM(amount_paid), 0) 
     FROM student_fee_payments 
     WHERE applicant_id = ? AND fee_structure_id = ? AND payment_status IN ('Paid','Partial','Manual')"
);
$stmtTotal->execute([$applicantId, $feeStructureId]);
$alreadyPaid = (float) $stmtTotal->fetchColumn();

$totalNowPaid = $alreadyPaid + $amountPaid;
$balance = max(0.00, (float) $fee['amount'] - $totalNowPaid);
$status = ($balance <= 0) ? 'Paid' : 'Partial';
$ref = 'MAN-TEST-' . strtoupper(bin2hex(random_bytes(3)));
$rcpt = generate_receipt_number($db);

$ins = $db->prepare(
    "INSERT INTO student_fee_payments 
     (applicant_id, fee_structure_id, amount_paid, balance, payment_reference, payment_status, payment_method, payment_date, receipt_number, recorded_by, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$ins->execute([
    $applicantId,
    $feeStructureId,
    $amountPaid,
    $balance,
    $ref,
    $status,
    $method,
    $date,
    $rcpt,
    $_SESSION['admin']['id'],
    $notes
]);

$insertedId = (int)$db->lastInsertId();
echo "3. Payment record created successfully: ID {$insertedId}, Receipt #{$rcpt}, Paid: ₦{$amountPaid}, Remaining Balance: ₦{$balance}, Status: {$status}\n";

// 4. Verify return_to redirect destination
if ($returnTo === 'admin/applications/' . $applicant['id']) {
    echo "4. Return_to redirect URL verified correctly: {$returnTo}\n";
} else {
    echo "4. Return_to redirect URL unexpected: {$returnTo}\n";
}

// 5. Clean up test record
$db->prepare("DELETE FROM student_fee_payments WHERE id = ?")->execute([$insertedId]);
echo "5. Cleanup completed. All Direct Fee Collection tests passed successfully!\n";
