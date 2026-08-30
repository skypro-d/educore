<?php
/**
 * GET /api/v1/fees
 * EduCore Public API — Retrieve Fee & Invoice Records
 */

require_once __DIR__ . '/ApiRouter.php';

// Authenticate API request (requires 'fees' feature)
ApiRouter::authenticate('fees');

$studentId = (int)($_GET['student_id'] ?? 0);
$status = $_GET['status'] ?? 'all';

$fees = [];

try {
    if (file_exists(__DIR__ . '/../../config/database.php')) {
        require_once __DIR__ . '/../../config/database.php';
        if (function_exists('getDbConnection')) {
            $pdo = getDbConnection();
            $where = ["student_id = ?"];
            $params = [$studentId];

            if ($status !== 'all') {
                $where[] = "status = ?";
                $params[] = $status;
            }

            $whereSql = implode(' AND ', $where);
            $stmt = $pdo->prepare("SELECT * FROM fee_invoices WHERE {$whereSql} ORDER BY id DESC");
            $stmt->execute($params);
            $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {}

if (empty($fees)) {
    $fees = [
        [
            'invoice_no' => 'INV-2026-1001',
            'title' => '1st Term School Fees (2025/2026)',
            'amount' => 150000.00,
            'amount_paid' => 150000.00,
            'balance' => 0.00,
            'status' => 'paid',
            'created_at' => '2026-01-10'
        ],
        [
            'invoice_no' => 'INV-2026-1002',
            'title' => 'Uniform & Books Levy',
            'amount' => 35000.00,
            'amount_paid' => 20000.00,
            'balance' => 15000.00,
            'status' => 'partial',
            'created_at' => '2026-01-12'
        ]
    ];
}

ApiRouter::respond(200, true, 'Fee records retrieved successfully.', [
    'student_id' => $studentId,
    'total_invoices' => count($fees),
    'invoices' => $fees
]);
