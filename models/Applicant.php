<?php
final class Applicant
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO applicants (
            application_number, admission_type, first_name, middle_name, last_name, gender, date_of_birth,
            state_of_origin, local_government, nationality, religion, home_address, parent_name, parent_phone,
            parent_email, father_name, mother_name, guardian_name, parent_occupation, class_id,
            previous_school, previous_class, blood_group, allergies, special_needs,
            emergency_name, emergency_relationship, emergency_phone, passport_photo, birth_certificate,
            previous_result, testimonial, recommendation_letter, status, created_at
        ) VALUES (
            :application_number, :admission_type, :first_name, :middle_name, :last_name, :gender, :date_of_birth,
            :state_of_origin, :local_government, :nationality, :religion, :home_address, :parent_name, :parent_phone,
            :parent_email, :father_name, :mother_name, :guardian_name, :parent_occupation, :class_id,
            :previous_school, :previous_class, :blood_group, :allergies, :special_needs,
            :emergency_name, :emergency_relationship, :emergency_phone, :passport_photo, :birth_certificate,
            :previous_result, :testimonial, :recommendation_letter, 'Submitted', NOW()
        )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['q'])) {
            $where[] = "(a.application_number LIKE :q OR a.first_name LIKE :q OR a.last_name LIKE :q OR a.parent_phone LIKE :q)";
            $params['q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['class_id'])) {
            $where[] = "a.class_id = :class_id";
            $params['class_id'] = $filters['class_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = "a.status = :status";
            $params['status'] = $filters['status'];
        }

        $sql = "SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY a.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT a.*, c.name AS class_name, l.admission_number FROM applicants a LEFT JOIN classes c ON c.id = a.class_id LEFT JOIN admission_letters l ON l.applicant_id = a.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByNumber(string $number): ?array
    {
        $stmt = $this->db->prepare("SELECT a.*, c.name AS class_name, l.admission_number FROM applicants a LEFT JOIN classes c ON c.id = a.class_id LEFT JOIN admission_letters l ON l.applicant_id = a.id WHERE a.application_number = ?");
        $stmt->execute([$number]);
        return $stmt->fetch() ?: null;
    }

    public function updateStatus(int $id, string $status): void
    {
        switch ($status) {
            case 'Approved':
                $admissionStatus = 'Offered';
                break;
            case 'Rejected':
                $admissionStatus = 'Rejected';
                break;
            case 'Terminated':
                $admissionStatus = 'Terminated';
                break;
            case 'Enrolled':
                $admissionStatus = 'Enrolled';
                break;
            default:
                $admissionStatus = null;
                break;
        }
        $enrollmentStatus = $status === 'Enrolled' ? 'Completed' : null;

        if ($admissionStatus !== null && $enrollmentStatus !== null) {
            $this->db->prepare("UPDATE applicants SET status = ?, admission_status = ?, enrollment_status = ?, enrolled_at = NOW(), updated_at = NOW() WHERE id = ?")
                ->execute([$status, $admissionStatus, $enrollmentStatus, $id]);
            $this->ensureAdmissionNumber($id);
            return;
        }

        if ($admissionStatus !== null) {
            $this->db->prepare("UPDATE applicants SET status = ?, admission_status = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$status, $admissionStatus, $id]);
            return;
        }

        $this->db->prepare("UPDATE applicants SET status = ?, updated_at = NOW() WHERE id = ?")
            ->execute([$status, $id]);
    }

    private function ensureAdmissionNumber(int $id): void
    {
        $stmt = $this->db->prepare('SELECT admission_number FROM admission_letters WHERE applicant_id = ? LIMIT 1');
        $stmt->execute([$id]);
        if ($stmt->fetchColumn()) {
            return;
        }

        $admissionNumber = 'SCH/' . date('Y') . '/' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
        $stmt = $this->db->prepare('INSERT INTO admission_letters (applicant_id, admission_number, generated_at) VALUES (?, ?, NOW())');
        $stmt->execute([$id, $admissionNumber]);
    }

    public function stats(): array
    {
        return [
            'total' => (int) $this->db->query("SELECT COUNT(*) FROM applicants")->fetchColumn(),
            'pending' => (int) $this->db->query("SELECT COUNT(*) FROM applicants WHERE status IN ('Pending','Submitted','Under Review','Awaiting Exam','Interview Scheduled')")->fetchColumn(),
            'approved' => (int) $this->db->query("SELECT COUNT(*) FROM applicants WHERE status='Approved'")->fetchColumn(),
            'rejected' => (int) $this->db->query("SELECT COUNT(*) FROM applicants WHERE status='Rejected'")->fetchColumn(),
            'enrolled' => (int) $this->db->query("SELECT COUNT(*) FROM applicants WHERE status='Enrolled'")->fetchColumn(),
            'revenue' => (float) $this->db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='Paid'")->fetchColumn(),
        ];
    }

    public function chartByClass(): array
    {
        return $this->db->query("SELECT COALESCE(c.name, 'Unknown') AS label, COUNT(*) AS total FROM applicants a LEFT JOIN classes c ON c.id=a.class_id GROUP BY label ORDER BY total DESC")->fetchAll();
    }

    public function chartByMonth(): array
    {
        return $this->db->query("SELECT DATE_FORMAT(created_at, '%b %Y') AS label, COUNT(*) AS total FROM applicants GROUP BY YEAR(created_at), MONTH(created_at), label ORDER BY MIN(created_at)")->fetchAll();
    }
}
