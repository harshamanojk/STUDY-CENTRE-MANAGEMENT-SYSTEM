<?php
session_start();
if (!isset($_SESSION['uid']) || empty($_SESSION['waitlist_pending'])) {
    header("Location: SLOT_BOOKING.php");
    exit();
}

include('INCLUDES/db.php');
$uid = $_SESSION['uid'];
$slot = $_SESSION['slot'];
$bookingDate = $_SESSION['booking_date'];
$amount = $_SESSION['amount'] ?? 500;

// Check if user already paid
$stmt = $conn->prepare("
    SELECT * FROM payments 
    WHERE UserID=? AND payment_status='Paid'
    ORDER BY created_at DESC LIMIT 1
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$prevPayment = $stmt->get_result()->fetch_assoc();
$stmt->close();

$skipPayment = !empty($prevPayment);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>WAITLIST CONFIRMATION | EduAxis</title>
<link rel="stylesheet" href="CSS/payment.css">
</head>
<body>
<div class="payment-container" style="text-align:center;">
    <h2>⚠ Waitlist Confirmation</h2>
    <p>
        Dear <strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>,<br>
        The slot <strong><?= htmlspecialchars($slot) ?></strong> on <strong><?= htmlspecialchars($bookingDate) ?></strong> is currently full.<br>
        <?php if($skipPayment): ?>
            ✅ Payment already done. Your waitlist spot is secured.
        <?php else: ?>
            To secure your waitlist spot, please pay ₹<?= $amount ?>.
        <?php endif; ?>
    </p>

    <?php if(!$skipPayment): ?>
    <form action="PAYMENT_PORTAL.php" method="POST">
        <input type="hidden" name="waitlist_payment" value="1">
        <button type="submit" id="payNowBtn">Proceed to Payment</button>
    </form>
    <?php else: ?>
    <a href="STUDENT_DASHBOARD.php">Back To Dashboard</a>
    <?php endif; ?>
</div>
</body>
</html>
