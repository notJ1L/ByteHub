<?php
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    redirect('../customer/index.php');
}

include '../../includes/admin_header.php';

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$sql = "SELECT * FROM expenses WHERE (title LIKE ?)";
$params = ['s', "%$search%"];

if ($category_filter) {
    $sql .= " AND category = ?";
    $params[0] .= 's';
    $params[] = $category_filter;
}

if ($start_date && $end_date) {
    $sql .= " AND created_at BETWEEN ? AND ?";
    $params[0] .= 'ss';
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql .= " ORDER BY expenses_id DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

// Calculate total expenses
$total_expenses = 0;
$result->data_seek(0);
while ($exp = $result->fetch_assoc()) {
    $total_expenses += $exp['amount'];
}
$result->data_seek(0);
?>

<div class="admin-content" style="max-width: 100%; box-sizing: border-box; overflow-x: hidden;">
    <!-- Success/Error Messages -->
    <?php if (isset($_GET['added']) && $_GET['added'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Expense has been added successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Expense has been updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Success!</strong> Expense has been deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php if ($_GET['error'] == 'invalid_id'): ?>
                <strong>Error!</strong> Invalid expense ID.
            <?php elseif ($_GET['error'] == 'not_found'): ?>
                <strong>Error!</strong> Expense not found.
            <?php else: ?>
                <strong>Error!</strong> An error occurred while processing your request.
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header mb-4">
        <div>
            <h1 class="page-title">Expenses Management</h1>
            <p class="page-subtitle">Track and manage business expenses</p>
        </div>
        <div>
            <a href="addExpense.php" class="btn btn-primary-green">
                <i class="bi bi-plus-circle me-2"></i>Add Expense
            </a>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-card-danger">
                <div class="stat-icon">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Expenses</div>
                    <div class="stat-value">₱<?php echo number_format($total_expenses, 2); ?></div>
                    <div class="stat-change text-muted">
                        <i class="bi bi-calendar"></i> All time
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-list-check"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Records</div>
                    <div class="stat-value"><?php echo $result->num_rows; ?></div>
                    <div class="stat-change text-muted">
                        Expense entries
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-funnel me-2"></i>Filters & Search
                </h5>
                <a href="expenses.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by title..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input type="text" 
                               name="category" 
                               class="form-control" 
                               placeholder="Filter by category..." 
                               value="<?php echo htmlspecialchars($category_filter); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary-green w-100">
                            <i class="bi bi-funnel-fill me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Expenses Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="bi bi-receipt me-2"></i>Expenses List
            </h5>
            <span class="badge bg-primary-green"><?php echo $result->num_rows; ?> expense(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Notes</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4"><?php echo $row['expenses_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                </td>
                                <td>
                                    <strong class="text-danger">₱<?php echo number_format($row['amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($row['category'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars(substr($row['notes'] ?? '', 0, 50)); ?>
                                        <?php echo strlen($row['notes'] ?? '') > 50 ? '...' : ''; ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                    </small>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="editExpense.php?id=<?php echo $row['expenses_id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="deleteExpense.php?id=<?php echo $row['expenses_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this expense?');"
                                           title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No expenses found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    border: 1px solid #e9ecef;
    height: 100%;
    box-sizing: border-box;
    overflow: hidden;
    word-wrap: break-word;
    max-width: 100%;
    width: 100%;
}

.stat-content {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

.stat-card-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
}

.stat-card-danger .stat-icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.stat-value {
    word-break: break-word;
    overflow-wrap: break-word;
    max-width: 100%;
    overflow: hidden;
}
</style>

<?php include '../footer.php'; ?>
