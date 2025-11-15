<?php 
include '../includes/db.php'; 
include '../includes/header.php';
?>

<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Welcome to ByteHub</h1>
        <p class="col-md-8 fs-4">Your one-stop shop for the latest and greatest in computer hardware.</p>
        <a href="#featured-products" class="btn btn-primary-green btn-lg">Shop Now</a>
    </div>
</div>

<div id="featured-products">
    <h2>Featured Products</h2>
    <div class="row">
    <?php
    $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND active = 1 LIMIT 6");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
    ?>
        <div class="col-md-4 mb-4">
            <div class="card product-card h-100">
                <img src="/bytehub/uploads/products/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['product_name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                    <p class="card-text">₱<?php echo number_format($row['price'], 2); ?></p>
                    <a href="product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary-green">View Details</a>
                </div>
            </div>
        </div>
    <?php
      }
    } else {
      echo "<p>No featured products yet.</p>";
    }
    ?>
    </div>
</div>

<div class="mt-5">
    <h2>New Arrivals</h2>
    <div class="row">
    <?php
    $stmt = $conn->prepare("SELECT * FROM products WHERE new_arrival = 1 AND active = 1 ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
    ?>
        <div class="col-md-4 mb-4">
            <div class="card product-card h-100">
                <img src="/bytehub/uploads/products/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['product_name']; ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['product_name']; ?></h5>
                    <p class="card-text">₱<?php echo number_format($row['price'], 2); ?></p>
                    <a href="product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-primary-green">View Details</a>
                </div>
            </div>
        </div>
    <?php
      }
    } else {
      echo "<p>No new arrivals yet.</p>";
    }
    ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

