<?php
declare(strict_types=1);

/**
 * Migration 013: Set default passwords for all existing student accounts to student surname (last_name, lowercase).
 */
return function (PDO $pdo): void {
    // 1. Fetch all student accounts along with applicant details
    $stmt = $pdo->query("
        SELECT sa.id AS account_id, sa.applicant_id, sa.username, a.last_name, a.first_name
        FROM `student_accounts` sa
        JOIN `applicants` a ON a.id = sa.applicant_id
    ");

    $accounts = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $updateStmt = $pdo->prepare("
        UPDATE `student_accounts`
        SET `password_hash` = ?,
            `reset_token` = NULL,
            `reset_expires` = NULL,
            `must_change_password` = 0
        WHERE `id` = ?
    ");

    foreach ($accounts as $row) {
        $lastName = trim((string) ($row['last_name'] ?? ''));
        $passPlain = $lastName !== '' ? strtolower($lastName) : 'student123';
        $hash = password_hash($passPlain, PASSWORD_BCRYPT);
        
        $updateStmt->execute([$hash, $row['account_id']]);
    }
};
