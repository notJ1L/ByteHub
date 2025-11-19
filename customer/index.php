<?php 
include '../includes/db.php'; 
include '../includes/header.php';
?>

<div class="hero-section mb-0">
    <div class="hero-background-pattern"></div>
    <div class="container">
        <div class="row justify-content-center min-vh-75">
            <div class="col-lg-10 col-xl-9">
                <div class="hero-content text-center">
                    <div class="hero-badge mb-3 mx-auto">
                        <i class="bi bi-lightning-charge-fill me-2"></i>
                        <span>Premium Hardware Store</span>
                    </div>
                    <h1 class="hero-title mb-4">
                        Build Your Dream PC with
                        <span class="hero-title-highlight">ByteHub</span>
                    </h1>
                    <p class="hero-description mb-4 mx-auto">
                        Discover the latest and greatest in computer hardware. From high-performance CPUs to cutting-edge GPUs, we've got everything you need to build the perfect system.
                    </p>
                    <div class="hero-features mb-4 justify-content-center">
                        <div class="hero-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Premium Components</span>
                        </div>
                        <div class="hero-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Fast Shipping</span>
                        </div>
                        <div class="hero-feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Expert Support</span>
                        </div>
                    </div>
                    <div class="hero-actions justify-content-center">
                        <a href="#featured-products" class="btn btn-hero-primary">
                            <i class="bi bi-grid-3x3-gap me-2"></i>Shop Now
                        </a>
                        <a href="#featured-products" class="btn btn-hero-secondary">
                            <i class="bi bi-arrow-down me-2"></i>Explore
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div id="featured-products" class="mb-5">
        <div class="section-header mb-4">
            <h2 class="section-title">
                <i class="bi bi-star-fill text-warning me-2"></i>Featured Products
            </h2>
            <p class="section-subtitle text-muted">Handpicked premium selections</p>
        </div>
        <div class="row g-4">
        <?php
        $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND active = 1 LIMIT 6");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
        ?>
            <div class="col-lg-4 col-md-6">
                <div class="product-card-modern">
                    <div class="product-image-wrapper">
                        <a href="product.php?id=<?php echo $row['product_id']; ?>">
                            <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                 class="product-image" 
                                 alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        </a>
                        <?php if ($row['new_arrival']): ?>
                            <span class="badge-new">New</span>
                        <?php endif; ?>
                        <?php if ($row['featured']): ?>
                            <span class="badge-featured">Featured</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-body">
                        <h5 class="product-title">
                            <a href="product.php?id=<?php echo $row['product_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </a>
                        </h5>
                        <p class="product-price">₱<?php echo number_format($row['price'], 2); ?></p>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-view-details">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-info text-center">No featured products yet.</div></div>';
        }
        ?>
        </div>
    </div>

    <div class="mt-5 mb-5">
        <div class="section-header mb-4">
            <h2 class="section-title">
                <i class="bi bi-sparkles text-info me-2"></i>New Arrivals
            </h2>
            <p class="section-subtitle text-muted">Latest additions to our collection</p>
        </div>
        <div class="row g-4">
        <?php
        $stmt = $conn->prepare("SELECT * FROM products WHERE new_arrival = 1 AND active = 1 ORDER BY created_at DESC LIMIT 6");
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
        ?>
            <div class="col-lg-4 col-md-6">
                <div class="product-card-modern">
                    <div class="product-image-wrapper">
                        <a href="product.php?id=<?php echo $row['product_id']; ?>">
                            <img src="/bytehub/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                 class="product-image" 
                                 alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        </a>
                        <?php if ($row['new_arrival']): ?>
                            <span class="badge-new">New</span>
                        <?php endif; ?>
                        <?php if ($row['featured']): ?>
                            <span class="badge-featured">Featured</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-body">
                        <h5 class="product-title">
                            <a href="product.php?id=<?php echo $row['product_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($row['product_name']); ?>
                            </a>
                        </h5>
                        <p class="product-price">₱<?php echo number_format($row['price'], 2); ?></p>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-view-details">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-info text-center">No new arrivals yet.</div></div>';
        }
        ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.hero-section {
    background: linear-gradient(135deg, #004d26 0%, #1e7a34 50%, #2d8f47 100%);
    padding: 2rem 0 1.5rem 0;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.hero-background-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.06) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
    opacity: 0.6;
    animation: patternMove 20s ease-in-out infinite;
}

@keyframes patternMove {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-20px, -20px); }
}

.hero-section .container {
    position: relative;
    z-index: 2;
    margin-bottom: 0;
    padding-bottom: 0;
}

.hero-section .row {
    margin-bottom: 0;
}

.hero-section .col-lg-10 {
    margin-bottom: 0;
    padding-bottom: 0;
}

.min-vh-75 {
    min-height: auto;
    padding: 0;
}

.hero-content {
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.hero-badge i {
    color: #ffd700;
    font-size: 1.1rem;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.2;
    color: white;
    margin-bottom: 1.5rem;
    letter-spacing: -0.02em;
}

.hero-title-highlight {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
    position: relative;
}

.hero-description {
    font-size: 1.25rem;
    line-height: 1.7;
    color: rgba(255, 255, 255, 0.9);
    max-width: 700px;
    font-weight: 400;
    margin-bottom: 1.5rem;
}

.hero-features {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-top: 2rem;
    margin-bottom: 1.5rem;
}

.hero-feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
    font-weight: 500;
    font-size: 1rem;
}

.hero-feature-item i {
    color: #4ade80;
    font-size: 1.2rem;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2.5rem;
    flex-wrap: wrap;
}

.btn-hero-primary {
    background: white;
    color: var(--primary-green);
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-hero-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    color: var(--primary-green);
    background: #f8f9fa;
}

.btn-hero-secondary {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    padding: 1rem 2.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-hero-secondary:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    transform: translateY(-3px);
}

.hero-visual {
    position: relative;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeInRight 1s ease-out;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.hero-icon-wrapper {
    position: relative;
    width: 300px;
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.hero-main-icon {
    font-size: 10rem;
    color: rgba(255, 255, 255, 0.15);
    position: relative;
    z-index: 3;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.hero-icon-circle {
    position: absolute;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    animation: pulse 4s ease-in-out infinite;
}

.hero-icon-circle-1 {
    width: 240px;
    height: 240px;
    animation-delay: 0s;
}

.hero-icon-circle-2 {
    width: 270px;
    height: 270px;
    animation-delay: 1s;
}

.hero-icon-circle-3 {
    width: 300px;
    height: 300px;
    animation-delay: 2s;
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 0.5rem;
}

.section-subtitle {
    font-size: 1.1rem;
}

.product-card-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.product-image-wrapper {
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
    padding: 1.5rem;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-card-modern:hover .product-image {
    transform: scale(1.05);
}

.badge-new,
.badge-featured {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 2;
}

.badge-new {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.badge-featured {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: white;
}

.product-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #212529;
    line-height: 1.4;
}

.product-title a {
    color: inherit;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: var(--primary-green);
}

.product-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-green);
    margin-bottom: 1rem;
}

.product-actions {
    margin-top: auto;
}

.btn-view-details {
    width: 100%;
    background: var(--primary-green);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-view-details:hover {
    background: var(--secondary-green);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 77, 38, 0.3);
}

@media (max-width: 992px) {
    .hero-section {
        min-height: auto;
        padding: 4rem 0;
    }
    
    .min-vh-75 {
        min-height: auto;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-description {
        font-size: 1.1rem;
        max-width: 100%;
    }
    
    .hero-icon-wrapper {
        width: 300px;
        height: 300px;
        margin: 2rem auto 0;
    }
    
    .hero-main-icon {
        font-size: 8rem;
    }
    
    .hero-icon-circle-1 {
        width: 220px;
        height: 220px;
    }
    
    .hero-icon-circle-2 {
        width: 260px;
        height: 260px;
    }
    
    .hero-icon-circle-3 {
        width: 300px;
        height: 300px;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 3rem 0;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-description {
        font-size: 1rem;
    }
    
    .hero-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-hero-primary,
    .btn-hero-secondary {
        width: 100%;
        justify-content: center;
    }
    
    .hero-features {
        flex-direction: column;
        gap: 1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}
</style>

<?php include '../includes/footer.php'; ?>
