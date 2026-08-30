<?php
final class ClassModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM classes ORDER BY sort_order, name')->fetchAll();
    }

    public function save(array $data): void
    {
        if (!empty($data['id'])) {
            $this->db->prepare('UPDATE classes SET name=?, sort_order=? WHERE id=?')->execute([$data['name'], $data['sort_order'], $data['id']]);
            return;
        }
        $this->db->prepare('INSERT INTO classes (name, sort_order) VALUES (?, ?)')->execute([$data['name'], $data['sort_order']]);
    }

    public function delete(int $id): void
    {
        $this->db->prepare('DELETE FROM classes WHERE id=?')->execute([$id]);
    }
}
