<?php
declare(strict_types=1);

/**
 * GradingService — Dynamic Grading Scale & Evaluation Manager
 *
 * Evaluates student scores using the school's configured grading rules from the database,
 * avoiding hardcoded grading logic across controllers and views.
 *
 * @package EduCore
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

final class GradingService
{
    private static ?array $cachedRules = null;

    /**
     * Clear cached rules (e.g. after rule update).
     */
    public static function resetCache(): void
    {
        self::$cachedRules = null;
    }

    /**
     * Load active grading rules for the current tenant school.
     *
     * @return array<int, array{min_score: float, max_score: float, grade: string, remark: string, grade_point: float}>
     */
    public static function getRules(): array
    {
        if (self::$cachedRules !== null) {
            return self::$cachedRules;
        }

        try {
            $db = Database::connect();
            $stmt = $db->query(
                "SELECT min_score, max_score, grade, remark, grade_point 
                 FROM grading_rules 
                 ORDER BY min_score DESC"
            );
            $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rules)) {
                self::$cachedRules = array_map(function ($r) {
                    return [
                        'min_score'   => (float) $r['min_score'],
                        'max_score'   => (float) $r['max_score'],
                        'grade'       => (string) $r['grade'],
                        'remark'      => (string) $r['remark'],
                        'grade_point' => (float) $r['grade_point'],
                    ];
                }, $rules);

                return self::$cachedRules;
            }
        } catch (Throwable $e) {
            // fallback below
        }

        // Fallback to setting() or standard scale
        self::$cachedRules = [
            ['min_score' => (float) setting('grade_a1_min', '75'), 'max_score' => 100.0, 'grade' => setting('grade_a1_label', 'A1'), 'remark' => 'Excellent', 'grade_point' => 4.0],
            ['min_score' => (float) setting('grade_b2_min', '70'), 'max_score' => 74.99, 'grade' => setting('grade_b2_label', 'B2'), 'remark' => 'Very Good', 'grade_point' => 3.5],
            ['min_score' => (float) setting('grade_b3_min', '65'), 'max_score' => 69.99, 'grade' => setting('grade_b3_label', 'B3'), 'remark' => 'Good',      'grade_point' => 3.0],
            ['min_score' => (float) setting('grade_c4_min', '60'), 'max_score' => 64.99, 'grade' => setting('grade_c4_label', 'C4'), 'remark' => 'Credit',    'grade_point' => 2.5],
            ['min_score' => (float) setting('grade_c5_min', '55'), 'max_score' => 59.99, 'grade' => setting('grade_c5_label', 'C5'), 'remark' => 'Credit',    'grade_point' => 2.0],
            ['min_score' => (float) setting('grade_c6_min', '50'), 'max_score' => 54.99, 'grade' => setting('grade_c6_label', 'C6'), 'remark' => 'Credit',    'grade_point' => 1.5],
            ['min_score' => (float) setting('grade_d7_min', '45'), 'max_score' => 49.99, 'grade' => setting('grade_d7_label', 'D7'), 'remark' => 'Pass',      'grade_point' => 1.0],
            ['min_score' => (float) setting('grade_e8_min', '40'), 'max_score' => 44.99, 'grade' => setting('grade_e8_label', 'E8'), 'remark' => 'Pass',      'grade_point' => 0.5],
            ['min_score' => 0.0,                                   'max_score' => 39.99, 'grade' => setting('grade_f9_label', 'F9'), 'remark' => 'Fail',      'grade_point' => 0.0],
        ];

        return self::$cachedRules;
    }

    /**
     * Evaluate a numeric total score and return the corresponding grade and remark.
     *
     * @param float $total
     * @return array{grade: string, remark: string, grade_point?: float}
     */
    public static function evaluate(float $total): array
    {
        $rules = self::getRules();

        foreach ($rules as $r) {
            if ($total >= $r['min_score']) {
                return [
                    'grade'       => $r['grade'],
                    'remark'      => $r['remark'],
                    'grade_point' => $r['grade_point'] ?? 0.0,
                ];
            }
        }

        return [
            'grade'       => setting('grade_f9_label', 'F9'),
            'remark'      => 'Fail',
            'grade_point' => 0.0,
        ];
    }

    /**
     * Get the active grading scale table for presentation.
     *
     * @return array<int, array{min: int|float, max: int|float, label: string, remark: string, grade_point: float}>
     */
    public static function getScale(): array
    {
        $rules = self::getRules();
        $scale = [];
        foreach ($rules as $r) {
            $scale[] = [
                'min'         => $r['min_score'],
                'max'         => $r['max_score'],
                'label'       => $r['grade'],
                'remark'      => $r['remark'],
                'grade_point' => $r['grade_point'] ?? 0.0,
            ];
        }
        return $scale;
    }
}
