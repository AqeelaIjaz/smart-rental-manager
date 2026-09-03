<?php
/**
 * backend/cron/rent-reminder.php
 *
 * RENT REMINDER LOGIC (5 days before due date)
 * ---------------------------------------------------------------------
 * This script is designed to be run:
 *   1) Manually / via browser or Postman for testing:
 *        GET http://localhost/smart-rental-manager/backend/cron/rent-reminder.php
 *   2) Via a Windows Task Scheduler job or a cron-like scheduler that
 *      calls this URL/script once per day, e.g.:
 *        php C:\xampp\htdocs\smart-rental-manager\backend\cron\rent-reminder.php
 *
 * Logic:
 *   1. Loop through active agreements.
 *   2. Calculate days remaining until due_date.
 *   3. If exactly 5 days remain, create a "rent_reminder" notification
 *      for the tenant AND send them a WhatsApp reminder (Member 5).
 *   4. Duplicate notifications are prevented by checking whether a
 *      rent_reminder notification for this agreement's due date was
 *      already created today.
 *
 * NOTE: This is intentionally framework-free so it can be triggered
 * either via HTTP (for manual testing / Postman) or via CLI (php ...)
 * for a real scheduled task.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/notifier.php';

// Allow both CLI and HTTP execution. Only load the JSON response
// helper (which calls http_response_code) when running over HTTP.
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    require_once __DIR__ . '/../helpers/response.php';
}

/**
 * Runs the rent reminder check and creates notifications where needed.
 * Also sends a WhatsApp reminder to the tenant (dev-mode logs it if no
 * WhatsApp API credentials are configured).
 * Returns a summary array of what was created.
 *
 * @param PDO $pdo
 * @return array
 */
function runRentReminderCheck(PDO $pdo): array
{
    $created = [];
    $today = new DateTime('today');

    $stmt = $pdo->query(
        "SELECT a.id, a.tenant_id, a.due_date, a.rent_amount,
                u.name AS tenant_name, u.phone AS tenant_phone
         FROM agreements a
         JOIN users u ON u.id = a.tenant_id
         WHERE a.status = 'active'"
    );
    $agreements = $stmt->fetchAll();

    foreach ($agreements as $agreement) {
        $dueDate = new DateTime($agreement['due_date']);

        // Compare only the date portion; support recurring monthly
        // due dates by projecting the due day into the current/next month.
        $projectedDue = new DateTime($today->format('Y-m-') . $dueDate->format('d'));
        if ($projectedDue < $today) {
            $projectedDue->modify('+1 month');
        }

        $daysRemaining = (int) $today->diff($projectedDue)->format('%r%a');

        if ($daysRemaining === 5) {
            // Prevent duplicate: check if a reminder for this agreement
            // was already created today.
            $dupCheck = $pdo->prepare(
                "SELECT id FROM notifications
                 WHERE user_id = :user_id
                   AND type = 'rent_reminder'
                   AND message LIKE :agreement_marker
                   AND DATE(created_at) = CURDATE()
                 LIMIT 1"
            );
            $dupCheck->execute([
                'user_id' => $agreement['tenant_id'],
                'agreement_marker' => '%[agreement:' . $agreement['id'] . ']%',
            ]);

            if ($dupCheck->fetch()) {
                continue; // already reminded today
            }

            $message = "Your rent of {$agreement['rent_amount']} is due in 5 days "
                . "(on {$projectedDue->format('Y-m-d')}). [agreement:{$agreement['id']}]";

            $insert = $pdo->prepare(
                'INSERT INTO notifications (user_id, title, message, type, is_read)
                 VALUES (:user_id, :title, :message, "rent_reminder", 0)'
            );
            $insert->execute([
                'user_id' => $agreement['tenant_id'],
                'title'   => 'Rent Reminder',
                'message' => $message,
            ]);

            // Member 5: actual WhatsApp/SMS send, not just the in-app row.
            $waStatus = 'skipped (no phone on file)';
            if (!empty($agreement['tenant_phone'])) {
                $waMessage = "Hi {$agreement['tenant_name']}, this is a reminder that your rent of "
                    . "Rs. {$agreement['rent_amount']} is due on {$projectedDue->format('d M Y')} "
                    . "(in 5 days). Please pay on time to avoid penalty.";
                $waResult = sendWhatsAppMessage($agreement['tenant_phone'], $waMessage);
                $waStatus = $waResult['mode'] ?? 'unknown';
            }

            $created[] = [
                'agreement_id'   => (int) $agreement['id'],
                'tenant_id'      => (int) $agreement['tenant_id'],
                'due_date'       => $projectedDue->format('Y-m-d'),
                'whatsapp_status'=> $waStatus,
            ];
        }
    }

    return $created;
}

// If this script is executed directly (not included), run the check.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $pdo = getDbConnection();
    $result = runRentReminderCheck($pdo);

    $summary = [
        'reminders_created' => count($result),
        'details'            => $result,
    ];

    if ($isCli) {
        echo "Rent reminder check complete.\n";
        echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        sendSuccess('Rent reminder check complete.', $summary);
    }
}
