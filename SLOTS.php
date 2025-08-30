<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: STUDENT_DASHBOARD.php');
    exit();
}

include('INCLUDES/db.php');
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$uid = $_SESSION['uid'];

// Function to delete a booking and promote from waitlist
function deleteSlot($conn, $bookingId) {
    $stmt = $conn->prepare("SELECT Slot, BookingDateChosen FROM slotbookings WHERE BookingID = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$booking) return false;

    $slot = $booking['Slot'];
    $date = $booking['BookingDateChosen'];

    // Delete booking
    $stmt = $conn->prepare("DELETE FROM slotbookings WHERE BookingID = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();

    // Promote from waitlist
    $stmt = $conn->prepare("
        SELECT w.id, w.UserID, u.name, u.email
        FROM waitlist w
        JOIN users u ON w.UserID = u.id
        WHERE w.Slot = ? AND w.BookingDateChosen = ?
        ORDER BY w.request_date ASC
        LIMIT 1
    ");
    $stmt->bind_param("ss", $slot, $date);
    $stmt->execute();
    $waitlistUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($waitlistUser) {
        $stmt = $conn->prepare("
            INSERT INTO slotbookings (UserID, name, email, Slot, BookingDateChosen, Status)
            VALUES (?, ?, ?, ?, ?, 'Booked')
        ");
        $stmt->bind_param("issss", $waitlistUser['UserID'], $waitlistUser['name'], $waitlistUser['email'], $slot, $date);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM waitlist WHERE id = ?");
        $stmt->bind_param("i", $waitlistUser['id']);
        $stmt->execute();
        $stmt->close();

        if (!empty($waitlistUser['email'])) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = ''; // Gmail
                $mail->Password = ''; // App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('no-reply@eduaxis.com', 'EduAxis');
                $mail->addAddress($waitlistUser['email'], $waitlistUser['name']);

                $mail->isHTML(false);
                $mail->Subject = "🎉 Your Waitlist Slot is Confirmed!";
                $mail->Body = "Hello " . $waitlistUser['name'] . ",\n\n" .
                              "Good news! A slot (" . $slot . " on " . $date . ") just opened up " .
                              "and has been booked for you.\n\n" .
                              "Thank you,\nEduAxis Team";

                $mail->send();
            } catch (Exception $e) {}
        }
    }
    return true;
}

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $bookingId = intval($_POST['bookingId']);
    if (deleteSlot($conn, $bookingId)) {
        header("Location: SLOTS.php?msg=success");
        exit();
    } else {
        $message = "Error deleting slot: " . $conn->error;
    }
}
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $message = "Slot deleted successfully";
}

$bookingSql = "
    SELECT b.BookingID, u.name AS FullName, u.email AS Email, u.contact AS ContactNumber,
           b.Slot, b.BookingDateChosen, b.Status, b.created_at AS BookingDate, s.Timings
    FROM slotbookings b
    JOIN users u ON b.UserID = u.id
    JOIN slots s ON b.Slot = s.Slot
    WHERE b.UserID = ?
    ORDER BY b.BookingDateChosen DESC
";

$waitlistSql = "
    SELECT w.id AS BookingID, u.name AS FullName, u.email AS Email, u.contact AS ContactNumber,
           w.Slot, w.BookingDateChosen, 'Waiting' AS Status, w.request_date AS BookingDate, s.Timings
    FROM waitlist w
    JOIN users u ON w.UserID = u.id
    JOIN slots s ON w.Slot = s.Slot
    LEFT JOIN slotbookings b 
        ON b.UserID = w.UserID 
        AND b.Slot = w.Slot 
        AND b.BookingDateChosen = w.BookingDateChosen
    WHERE w.UserID = ? 
      AND b.BookingID IS NULL
      AND w.id = (
          SELECT MIN(id) 
          FROM waitlist 
          WHERE UserID = w.UserID 
            AND Slot = w.Slot 
            AND BookingDateChosen = w.BookingDateChosen
      )
    ORDER BY w.request_date ASC
";

$allSlots = [];
$stmt = $conn->prepare($bookingSql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allSlots[] = $row;
}
$stmt->close();

$stmt = $conn->prepare($waitlistSql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allSlots[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MY SLOTS | EduAxis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { font-family: "DM Sans","sans-serif"; margin-top: 2px; }
.container { display: flex; justify-content: center; padding: 20px; }
.content { width: 95%; text-align: center; }
.logo img { width: 150px; max-width: 80vw; border-radius: 8px; margin-bottom: 20px; }
table th, table td { vertical-align: middle; text-align: center; }

/* Mobile view → stacked cards */
@media (max-width: 768px) {
    .table thead {
        display: none;
    }
    .table, .table tbody, .table tr, .table td {
        display: block;
        width: 100%;
    }
    .table tr {
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 0.5rem;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .table td {
        text-align: left;
        padding: 0.5rem;
        border: none;
        border-bottom: 1px solid #f1f1f1;
        position: relative;
    }
    .table td:last-child {
        border-bottom: none;
    }
    .table td::before {
        content: attr(data-label) " ";
        font-weight: bold;
        display: block;
        margin-bottom: 0.2rem;
        color: #333;
    }
}
</style>
</head>
<body>
<div class="container">
    <div class="content">
        <div class="logo">
            <img src="IMGS/LOGO.jpg" alt="Logo">
        </div>

        <h2><u>MY SLOTS</u></h2>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <p>Name: <?= htmlspecialchars($_SESSION['fullname']) ?></p>
        <p>Email ID: <?= htmlspecialchars($_SESSION['email']) ?></p>

        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>Full Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Slot</th>
                    <th>Booking Date Chosen</th>
                    <th>Booking Date</th>
                    <th>Timings</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($allSlots)): ?>
                <?php foreach ($allSlots as $row): ?>
                    <tr>
                        <td data-label="Full Name"><?= htmlspecialchars($row['FullName']) ?></td>
                        <td data-label="Contact Number"><?= htmlspecialchars($row['ContactNumber']) ?></td>
                        <td data-label="Email"><?= htmlspecialchars($row['Email']) ?></td>
                        <td data-label="Slot"><?= htmlspecialchars($row['Slot']) ?></td>
                        <td data-label="Booking Date Chosen"><?= htmlspecialchars($row['BookingDateChosen']) ?></td>
                        <td data-label="Booking Date"><?= htmlspecialchars($row['BookingDate']) ?></td>
                        <td data-label="Timings"><?= htmlspecialchars($row['Timings']) ?></td>
                        <td data-label="Status"><?= htmlspecialchars($row['Status']) ?></td>
                        <td data-label="Actions">
                            <?php if ($row['Status'] !== 'Waiting'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this slot?');">
                                    <input type="hidden" name="bookingId" value="<?= htmlspecialchars($row['BookingID']) ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Waitlist</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No slots found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
