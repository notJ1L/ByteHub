<?php
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

include '../../includes/admin_header.php';

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM brands WHERE (name LIKE ? OR slug LIKE ?)";
$params = ['ss', "%$search%", "%$search%"];

if ($status_filter) {
    if ($status_filter == 'active') {
        $sql .= " AND active = 1";
    } elseif ($status_filter == 'inactive') {
        $sql .= " AND active = 0";
    }
}

$sql .= " ORDER BY brand_id DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
?>

<div class="admin-content">
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Brands Management</h1>
            <p class="page-subtitle">Manage product brands</p>
        </div>
        <div>
            <a href="addBrand.php" class="btn btn-primary-green">
                <i class="bi bi-plus-circle me-2"></i>Add Brand
            </a>
        </div>
    </div>

    <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Brand has been added successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Brand has been updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Brand has been deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php if ($_GET['error'] == 'in_use'): ?>
                <strong>Cannot Delete!</strong> This brand is being used by <?php echo htmlspecialchars($_GET['count'] ?? 0); ?> product(s). Please remove or reassign those products first.
            <?php elseif ($_GET['error'] == 'invalid_id'): ?>
                <strong>Error!</strong> Invalid brand ID.
            <?php elseif ($_GET['error'] == 'not_found'): ?>
                <strong>Error!</strong> Brand not found.
            <?php else: ?>
                <strong>Error!</strong> An error occurred while processing your request.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters & Search
                </h5>
                <a href="brands.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by name or slug..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary-green flex-fill">Filter</button>
                        <a href="brands.php" class="btn btn-outline-secondary flex-fill">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-building me-2"></i>Brands List
            </h5>
            <span class="badge bg-primary-green"><?php echo $result->num_rows; ?> brand(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?php echo $row['brand_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                </td>
                                <td>
                                    <code class="text-muted"><?php echo htmlspecialchars($row['slug']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['active'] ? 'success' : 'danger'; ?>">
                                        <?php echo $row['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="editBrand.php?id=<?php echo $row['brand_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="deleteBrand.php?id=<?php echo $row['brand_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this brand?');"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No brands found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<?php include '../footer.php'; ?>
