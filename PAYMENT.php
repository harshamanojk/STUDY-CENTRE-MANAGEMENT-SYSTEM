<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: STUDENT_DASHBOARD.php');
    exit();
}

include('INCLUDES/db.php');

$uid = $_SESSION['uid'];

// Fetch payments for the logged-in user
$paymentSql = "
    SELECT p.id AS PaymentID, u.name AS FullName, u.email AS Email, u.contact AS ContactNumber,
           p.Slot, p.BookingDateChosen, p.amount, p.payment_status, p.created_at AS PaymentDate
    FROM payments p
    JOIN users u ON p.UserID = u.id
    WHERE p.UserID = ?
    ORDER BY p.created_at DESC
";

$allPayments = [];
$stmt = $conn->prepare($paymentSql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allPayments[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MY PAYMENTS | EduAxis</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body { 
    font-family: "DM Sans","sans-serif"; 
    margin-top: 2px; 
}
.container { 
    display: flex; 
    justify-content: center; 
    padding: 20px; 
}
.content { 
    width: 95%; 
    text-align: center; 
}
.logo img { 
    width: 150px; 
    max-width: 80vw; 
    border-radius: 8px; 
    margin-bottom: 20px; 
}
table th, table td { 
    vertical-align: middle; 
    text-align: center; 
}

/* Mobile view → stacked cards */
@media (max-width: 768px) {
    .table thead { display: none; }
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
    .table td:last-child { border-bottom: none; }
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

        <h2><u>MY PAYMENTS</u></h2>

        <p>Name: <?= htmlspecialchars($_SESSION['fullname']) ?></p>
        <p>Email ID: <?= htmlspecialchars($_SESSION['email']) ?></p>

        <!-- Responsive wrapper -->
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Full Name</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Slot</th>
                        <th>Booking Date Chosen</th>
                        <th>Payment Amount</th>
                        <th>Payment Status</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($allPayments)): ?>
                    <?php foreach ($allPayments as $row): ?>
                        <tr>
                            <td data-label="Full Name"><?= htmlspecialchars($row['FullName']) ?></td>
                            <td data-label="Contact Number"><?= htmlspecialchars($row['ContactNumber']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($row['Email']) ?></td>
                            <td data-label="Slot"><?= htmlspecialchars($row['Slot']) ?></td>
                            <td data-label="Booking Date Chosen"><?= htmlspecialchars($row['BookingDateChosen']) ?></td>
                            <td data-label="Payment Amount"><?= htmlspecialchars($row['amount']) ?></td>
                            <td data-label="Payment Status">
                                <?php if ($row['payment_status'] === 'Paid'): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Not Paid</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Payment Date"><?= htmlspecialchars($row['PaymentDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No payments found</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>

<?php $conn->close(); ?>
