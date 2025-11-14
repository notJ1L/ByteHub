<?php
include '../includes/db.php';
include '../includes/functions.php';
include '../includes/header.php';

if (!is_logged_in()) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders 
        WHERE user_id = $user_id 
        ORDER BY order_id DESC";

$result = $conn->query($sql);
?>

<div class="orders-page">
  <h2>My Orders</h2>

  <?php if ($result->num_rows > 0): ?>
    <table class="table">
      <thead>
        <tr>
          <th>Order Code</th>
          <th>Total</th>
          <th>Status</th>
          <th>Date</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $row['order_code']; ?></td>
          <td>$<?php echo number_format($row['total'], 2); ?></td>
          <td><?php echo $row['status']; ?></td>
          <td><?php echo $row['created_at']; ?></td>

          <td>
            <a href="myorder_view.php?id=<?php echo $row['order_id']; ?>" 
               class="btn btn-sm btn-primary">View</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>

    </table>

  <?php else: ?>
    <p>You have no orders yet.</p>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
