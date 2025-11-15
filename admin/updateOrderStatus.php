<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer/src/Exception.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';

include '../includes/db.php';
include '../includes/functions.php';
include '../includes/config.php';
include '../includes/admin_header.php';

if (!isAdmin()) {
    header("Location: ../customer/index.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) die("Order not found.");

if (isset($_POST['update'])) {
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Send email notification
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = MAILTRAP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAILTRAP_USER;
        $mail->Password   = MAILTRAP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAILTRAP_PORT;

        //Recipients
        $user_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
        $user_stmt->bind_param("i", $order['user_id']);
        $user_stmt->execute();
        $user = $user_stmt->get_result()->fetch_assoc();

        $mail->setFrom('no-reply@bytehub.com', 'ByteHub');
        $mail->addAddress($user['email']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Order Status has been Updated';
        
        $order_items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $order_items_stmt->bind_param("i", $id);
        $order_items_stmt->execute();
        $order_items = $order_items_stmt->get_result();

        $email_body = "<h1>Your order #{$order['order_code']} is now {$status}</h1>";
        $email_body .= "<table border='1' cellpadding='5'><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        while ($item = $order_items->fetch_assoc()) {
            $email_body .= "<tr><td>{$item['name_snapshot']}</td><td>{$item['quantity']}</td><td>{$item['unit_price_snapshot']}</td><td>{$item['line_total']}</td></tr>";
        }
        $email_body .= "</table>";
        $email_body .= "<p>Subtotal: {$order['subtotal']}</p>";
        $email_body .= "<p>Tax: {$order['tax']}</p>";
        $email_body .= "<p><strong>Grand Total: {$order['total']}</strong></p>";

        $mail->Body    = $email_body;

        $mail->send();
    } catch (Exception $e) {
        // You can log the error here if needed
    }

    header("Location: orders.php?status_updated=1");
    exit();
}
?>

<div class="container mt-4">
    <h2>Update Order Status</h2>

    <form method="post" class="mt-3">

        <label>Status:</label>
        <select name="status" class="form-control">
            <option value="Pending"   <?php echo $order['status']=='Pending'?'selected':''; ?>>Pending</option>
            <option value="Processing" <?php echo $order['status']=='Processing'?'selected':''; ?>>Processing</option>
            <option value="Shipped"   <?php echo $order['status']=='Shipped'?'selected':''; ?>>Shipped</option>
            <option value="Completed" <?php echo $order['status']=='Completed'?'selected':''; ?>>Completed</option>
            <option value="Cancelled" <?php echo $order['status']=='Cancelled'?'selected':''; ?>>Cancelled</option>
        </select>

        <button name="update" class="btn btn-primary mt-3">Update Status</button>
        <a href="orders.php" class="btn btn-secondary mt-3">Cancel</a>
    </form>

</div>

<?php include '../includes/footer.php'; ?>
