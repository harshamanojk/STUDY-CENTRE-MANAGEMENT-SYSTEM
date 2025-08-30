<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header("Location: HOME PAGE.php");
    exit();
}

include('INCLUDES/db.php');
$uid = $_SESSION['uid'];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, contact FROM users WHERE id=?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all slots with capacity info
$slots = [];
$result = $conn->query("SELECT * FROM slots");
while ($row = $result->fetch_assoc()) {
    $slots[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot = $_POST['Slot'] ?? '';
    $bookingDate = $_POST['BookingDateChosen'] ?? '';
    $contact = $_POST['phone'] ?? '';

    if (!$slot || !$bookingDate || !$contact) {
        $error = "Please fill all required fields.";
    } else {
        // Get current bookings count for this slot and date
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS cnt, s.capacity 
            FROM slots s 
            LEFT JOIN slotbookings b 
            ON s.Slot = b.Slot AND b.BookingDateChosen = ? 
            WHERE s.Slot = ? 
            GROUP BY s.Slot
        ");
        $stmt->bind_param("ss", $bookingDate, $slot);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $booked_count = $res['cnt'] ?? 0;
        $capacity = $res['capacity'] ?? 1;

        // Save session info for confirmation/payment
        $_SESSION['slot'] = $slot;
        $_SESSION['booking_date'] = $bookingDate;
        $_SESSION['fullname'] = $userData['name'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['amount'] = 500; // fixed amount

        if ($booked_count < $capacity) {
            // Slot available: go to confirmation page
            $_SESSION['waitlist_pending'] = false;
            header("Location: BOOKING_CONFIRM.php");
            exit();
        } else {
            // Slot full: automatically add to waitlist
            $stmt = $conn->prepare("
                INSERT INTO waitlist (UserID, Slot, BookingDateChosen) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("iss", $uid, $slot, $bookingDate);
            $stmt->execute();
            $stmt->close();

            $_SESSION['waitlist_pending'] = true;
            header("Location: WAITLIST_CONFIRM.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SLOT BOOKING | EduAxis</title>
<link rel="stylesheet" href="CSS/form.css">
</head>
<body>
<div class="slot-container">
<div style="text-align: center; margin-bottom: 20px;">
    <img src="IMGS/LOGO.jpg" alt="Logo" style="width: 150px; max-width: 80vw; height: auto; border-radius: 8px;">
</div>
<h2>SLOT BOOKING</h2>

<?php if(!empty($error)): ?>
    <p class="text-danger"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form action="" method="POST">
    <label>Name:</label>
    <input type="text" name="FullName" value="<?= htmlspecialchars($userData['name'] ?? '') ?>" readonly>

    <label>Email:</label>
    <input type="email" name="Email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" readonly>

    <label>Contact:</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($userData['contact'] ?? '') ?>" required>

    <label>Choose Date:</label>
    <input type="date" name="BookingDateChosen" required min="<?= date('Y-m-d') ?>">

    <label>Select Slot:</label>
    <div class="slots-container">
        <?php foreach ($slots as $slotData): ?>
            <label class="slot-box">
                <input type="radio" name="Slot" value="<?= htmlspecialchars($slotData['Slot']) ?>" required>
                <div class="slot-content">
                    <strong><?= htmlspecialchars($slotData['Slot']) ?></strong>
                    <span><?= htmlspecialchars($slotData['Timings']) ?></span>
                    <small>Capacity: <?= $slotData['capacity'] ?></small>
                </div>
            </label>
        <?php endforeach; ?>
    </div>

    <button type="submit" id="bookSlotBtn" name="bookSlotBtn">Book Slot</button>
</form>
</div>
</body>
</html>
