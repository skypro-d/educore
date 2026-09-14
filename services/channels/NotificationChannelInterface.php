<?php
declare(strict_types=1);

interface NotificationChannelInterface
{
    /**
     * Unique identifier of this channel (e.g. 'sms', 'email', 'whatsapp')
     */
    public function getName(): string;

    /**
     * Check if this channel is enabled globally in the school settings
     */
    public function isEnabled(): bool;

    /**
     * Check if parent has opted into receiving notifications on this channel
     */
    public function isParentOptedIn(array $student): bool;

    /**
     * Dispatch notification to recipient
     *
     * @param array  $student Applicant/student row
     * @param string $event   'check_in' or 'check_out'
     * @param array  $context Event parameters (time, date, status, attendance_id, exit_log_id, etc.)
     * @return array{success: bool, status: string, recipient: string, message: string, response: ?string, error: ?string}
     */
    public function send(array $student, string $event, array $context): array;
}
