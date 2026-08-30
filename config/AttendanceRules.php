<?php
/**
 * AttendanceRules — EduCore Attendance Time Rules Engine
 *
 * Reads configured time windows from school_settings and determines
 * the correct attendance status for a given check-in time.
 *
 * Time windows (all from school_settings):
 *   attendance_open_time    — school gate opens       (e.g. 07:00)
 *   attendance_ontime_until — last on-time minute     (e.g. 07:30)
 *   attendance_late_from    — late arrival starts      (e.g. 07:31)
 *   attendance_close_time   — attendance window closes (e.g. 09:00)
 *
 * Status resolution:
 *   Before open_time              → 'Present'  (early bird, treat as on-time)
 *   open_time  .. ontime_until    → 'Present'
 *   late_from  .. close_time      → 'Late'
 *   After close_time              → 'Denied'   (attendance closed)
 */
final class AttendanceRules
{
    // Cached settings (loaded once per request)
    private static ?array $times = null;

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Resolve attendance status based on current server time.
     *
     * @return string  'Present' | 'Late' | 'Denied'
     */
    public static function resolveCurrentStatus(): string
    {
        return self::resolveStatus(date('H:i'));
    }

    /**
     * Resolve attendance status for a specific time string (HH:MM).
     *
     * @param string $timeIn  e.g. '07:15', '08:45'
     * @return string  'Present' | 'Late' | 'Denied'
     */
    public static function resolveStatus(string $timeIn): string
    {
        $times = self::loadTimes();

        $scanMins  = self::toMinutes($timeIn);
        $openMins  = self::toMinutes($times['open']);
        $ontimeMins = self::toMinutes($times['ontime_until']);
        $closeMins = self::toMinutes($times['close']);

        // After attendance window closes → Denied
        if ($scanMins > $closeMins) {
            return 'Denied';
        }

        // On time (including early arrivals before gate opens)
        if ($scanMins <= $ontimeMins) {
            return 'Present';
        }

        // Between late_from and close → Late
        return 'Late';
    }

    /**
     * Is the current time still within the attendance window?
     * (i.e., before or at attendance_close_time)
     */
    public static function isWindowOpen(): bool
    {
        $times = self::loadTimes();
        return self::toMinutes(date('H:i')) <= self::toMinutes($times['close']);
    }

    /**
     * Is it past the attendance closing time?
     * Used to trigger auto-absent processing.
     */
    public static function isWindowClosed(): bool
    {
        return !self::isWindowOpen();
    }

    /**
     * Get all configured time values as an associative array.
     *
     * @return array{open:string, ontime_until:string, late_from:string, close:string, school_close:string}
     */
    public static function getTimes(): array
    {
        return self::loadTimes();
    }

    /**
     * Format a time string for display (e.g. '07:30' → '7:30 AM').
     */
    public static function format(string $time): string
    {
        if ($time === '' || $time === '00:00') {
            return '—';
        }
        return date('g:i A', strtotime($time));
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private static function loadTimes(): array
    {
        if (self::$times !== null) {
            return self::$times;
        }

        $get = fn(string $key, string $default): string =>
            function_exists('setting') ? setting($key, $default) : $default;

        self::$times = [
            'open'         => $get('attendance_open_time',    '07:00'),
            'ontime_until' => $get('attendance_ontime_until', '07:30'),
            'late_from'    => $get('attendance_late_from',    '07:31'),
            'close'        => $get('attendance_close_time',   '09:00'),
            'school_close' => $get('school_close_time',       '14:30'),
        ];

        return self::$times;
    }

    /** Convert HH:MM to total minutes since midnight. */
    private static function toMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time . ':00');
        return ((int) $h * 60) + (int) $m;
    }
}
