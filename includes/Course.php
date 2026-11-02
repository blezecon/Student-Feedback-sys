<?php
/**
 * Course catalog data access for admin/courses/*.
 */
class Course
{
    public function __construct(private PDO $pdo) {}

    // shortcut: assumes one teacher per course (the admin form enforces it);
    // upgrade to GROUP_CONCAT/aggregate if the schema ever allows co-teaching.
    /** Courses with their assigned teacher, optionally filtered. */
    public function all(string $search = '', string $department = '', string $semester = ''): array
    {
        $sql = "SELECT c.*, u.name AS teacher_name
                FROM courses c
                LEFT JOIN course_assignments ca ON ca.course_id = c.course_id
                LEFT JOIN users u ON u.user_id = ca.teacher_id
                WHERE 1 = 1";
        $params = [];

        if ($search !== '') {
            $sql .= ' AND (c.course_code LIKE ? OR c.course_name LIKE ?)';
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($department !== '') {
            $sql .= ' AND c.department = ?';
            $params[] = $department;
        }
        if ($semester !== '') {
            $sql .= ' AND c.semester = ?';
            $params[] = $semester;
        }

        $stmt = $this->pdo->prepare($sql . ' ORDER BY c.department, c.semester, c.course_code');
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, ca.teacher_id
             FROM courses c
             LEFT JOIN course_assignments ca ON ca.course_id = c.course_id
             WHERE c.course_id = ?'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    /** Verified teachers available for assignment. */
    public function teachers(): array
    {
        return $this->pdo->query(
            "SELECT user_id, name, employee_id FROM users
             WHERE role = 'teacher' AND is_verified = 1
             ORDER BY name"
        )->fetchAll();
    }

    public function departments(): array
    {
        return $this->pdo->query('SELECT DISTINCT department FROM courses ORDER BY department')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function exists(string $code, string $semester, string $section, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM courses WHERE course_code = ? AND semester = ? AND section = ?';
        $params = [$code, $semester, $section];

        if ($excludeId !== null) {
            $sql .= ' AND course_id != ?';
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    /** @return string[] validation errors, empty when valid */
    public function validate(array $data, ?int $id = null): array
    {
        $errors = [];
        $labels = [
            'course_code' => 'Course code',
            'course_name' => 'Course name',
            'department'  => 'Department',
            'semester'    => 'Semester',
            'section'     => 'Section',
        ];

        foreach ($labels as $field => $label) {
            if (($data[$field] ?? '') === '') {
                $errors[] = $label . ' is required.';
            }
        }

        if (!$errors && $this->exists($data['course_code'], $data['semester'], $data['section'], $id)) {
            $errors[] = 'That course code already exists for this semester and section.';
        }

        return $errors;
    }

    /** Insert or update a course and its (single) teacher assignment. */
    public function save(array $data, ?int $id = null): int
    {
        $this->pdo->beginTransaction();

        if ($id === null) {
            $this->pdo->prepare(
                'INSERT INTO courses (course_code, course_name, department, semester, section)
                 VALUES (?, ?, ?, ?, ?)'
            )->execute([
                $data['course_code'], $data['course_name'], $data['department'], $data['semester'], $data['section'],
            ]);
            $id = (int) $this->pdo->lastInsertId();
        } else {
            $this->pdo->prepare(
                'UPDATE courses SET course_code = ?, course_name = ?, department = ?, semester = ?, section = ?
                 WHERE course_id = ?'
            )->execute([
                $data['course_code'], $data['course_name'], $data['department'], $data['semester'], $data['section'], $id,
            ]);
        }

        $this->pdo->prepare('DELETE FROM course_assignments WHERE course_id = ?')->execute([$id]);
        if (($data['teacher_id'] ?? '') !== '') {
            $this->pdo->prepare('INSERT INTO course_assignments (course_id, teacher_id) VALUES (?, ?)')
                ->execute([$id, (int) $data['teacher_id']]);
        }

        $this->pdo->commit();

        return $id;
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM courses WHERE course_id = ?')->execute([$id]);
    }
}
