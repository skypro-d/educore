<?php
declare(strict_types=1);

require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/../config/helpers.php';

final class AttendanceService
{
    private static ?NotificationService $notificationService = null;

    public static function getNotificationService(?PDO $db = null): NotificationService
    {
        if (self::$notificationService === null) {
            self::$notificationService = new NotificationService($db);
        }
        return self::$notificationService;
    }

    /**
     * Dispatch all configured check-in notifications (SMS, Email, WhatsApp)
     *
     * @param array  $student      Student record (from applicants table)
     * @param string $timeIn       Scan time (e.g. '07:45')
     * @param string $status       Resolved status ('Present', 'Late')
     * @param int    $attendanceId ID of newly recorded attendance entry
     */
    public static function dispatchCheckinNotification(
        array $student,
        string $timeIn,
        string $status,
        int $attendanceId
    ): void {
        $context = [
            'time'                => $timeIn,
            'time_formatted'      => date('g:i A', strtotime($timeIn)),
            'date_formatted'      => date('F j, Y'),
            'status'              => $status,
            'attendance_id'       => $attendanceId,
            'template_identifier' => 'check_in'
        ];

        self::dispatchAsync(function() use ($student, $context): void {
            self::getNotificationService()->sendAttendanceNotification($student, 'check_in', $context);
        });
    }

    /**
     * Dispatch all configured check-out notifications (SMS, Email, WhatsApp)
     *
     * @param array $student   Student record
     * @param array $exitData  Exit details (exit_type, exit_date, exit_time, exit_reason, pickup_person_name)
     * @param int   $exitLogId ID of newly created student_exit_logs entry
     */
    public static function dispatchCheckoutNotification(
        array $student,
        array $exitData,
        int $exitLogId
    ): void {
        $context = [
            'time'                => $exitData['exit_time'] ?? date('H:i:s'),
            'time_formatted'      => date('g:i A', strtotime($exitData['exit_time'] ?? date('H:i:s'))),
            'date_formatted'      => date('F j, Y', strtotime($exitData['exit_date'] ?? date('Y-m-d'))),
            'status'              => 'Checked Out',
            'exit_log_id'         => $exitLogId,
            'exit_data'           => $exitData,
            'template_identifier' => 'check_out'
        ];

        self::dispatchAsync(function() use ($student, $context): void {
            self::getNotificationService()->sendAttendanceNotification($student, 'check_out', $context);
        });
    }

    /**
     * Dispatches callback without delaying attendance response to the scanner.
     */
    private static function dispatchAsync(callable $callback): void
    {
        // If FastCGI is active and output buffering can be flushed, do so
        if (function_exists('fastcgi_finish_request')) {
            // Register for execution right after fastcgi_finish_request
            register_shutdown_function(function() use ($callback): void {
                try {
                    $callback();
                } catch (Throwable $e) {
                    error_log('[AttendanceService] Async notification error: ' . $e->getMessage());
                }
            });
            return;
        }

        // Standard shutdown function execution
        register_shutdown_function(function() use ($callback): void {
            try {
                $callback();
            } catch (Throwable $e) {
                error_log('[AttendanceService] Shutdown notification error: ' . $e->getMessage());
            }
        });
    }
}
