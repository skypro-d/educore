<?php
final class ActivityLog
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function record(string $action, string $details = ''): void
    {
        $adminId = $_SESSION['admin']['id'] ?? null;
        $stmt = $this->db->prepare('INSERT INTO activity_logs (admin_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([$adminId, $action, $details, $_SERVER['REMOTE_ADDR'] ?? '']);
    }

    public function recent(int $limit = 10): array
    {
        $stmt = $this->db->prepare('SELECT l.*, a.name AS admin_name FROM activity_logs l LEFT JOIN admins a ON a.id=l.admin_id ORDER BY l.created_at DESC LIMIT ?');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
