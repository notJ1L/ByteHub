<?php
// Start output buffering FIRST - before any includes
if (!ob_get_level()) {
    ob_start();
}

// Process POST request IMMEDIATELY (before any includes that might output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    // Include only what we need for POST processing
    include '../../includes/db.php';
    include '../../includes/functions.php';
    
    if (!isAdmin()) {
        if (ob_get_level()) ob_end_clean();
        redirect('../index.php');
    }
    
    $user_id = $_GET['id'] ?? 0;
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $active   = $_POST['active'];

    // Get current photo
    $res = $conn->query("SELECT photo FROM users WHERE user_id = $user_id");
    $current_user = $res->fetch_assoc();
    $photo = $current_user['photo'] ?? null;

    $newPhoto = $photo;

    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "../../uploads/users/" . $newPhoto);
    }

    $stmt = $conn->prepare("
        UPDATE users 
        SET username=?, email=?, role=?, active=?, photo=? 
        WHERE user_id=?
    ");
    $stmt->bind_param("sssisi", $username, $email, $role, $active, $newPhoto, $user_id);

    if ($stmt->execute()) {
        $stmt->close();
        if (ob_get_level()) ob_end_clean();
        redirect('users.php');
    }

    $stmt->close();
}

// Normal page load (GET request) - include files and display page
include '../../includes/db.php';
include '../../includes/functions.php';

if (!isAdmin()) {
    if (ob_get_level()) ob_end_clean();
    redirect('../index.php');
}

$user_id = $_GET['id'] ?? 0;
$res = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$user = $res->fetch_assoc();

if (!$user) {
    if (ob_get_level()) ob_end_clean();
    redirect('users.php?error=not_found');
}

$photo = $user['photo'];

// End output buffering and start output (only if buffer exists)
if (ob_get_level()) {
    ob_end_flush();
}

// Now include header (after all redirects are handled)
include '../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header mb-4">
            <div>
                <h2 class="page-title">
                    <i class="bi bi-pencil-square me-2"></i>Edit User
                </h2>
                <p class="text-muted mb-0">Update user information</p>
            </div>
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Users
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="user" <?php if ($user['role']=='user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if ($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="active" class="form-select" required>
                                <option value="1" <?php if ($user['active']==1) echo 'selected'; ?>>Active</option>
                                <option value="0" <?php if ($user['active']==0) echo 'selected'; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Profile Photo</label>
                            <?php if ($user['photo']): ?>
                                <div class="mb-2">
                                    <img src="../../uploads/users/<?php echo htmlspecialchars($user['photo']); ?>" 
                                         alt="Current photo" 
                                         class="img-thumbnail" 
                                         style="max-width: 150px; height: auto;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Leave blank to keep current photo</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary-green">
                                    <i class="bi bi-check-circle me-2"></i>Save Changes
                                </button>
                                <a href="users.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control,
.form-select {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 0.625rem 1rem;
    color: #212529 !important;
    background-color: #fff !important;
}

.form-control:focus,
.form-select:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 3px rgba(0, 77, 38, 0.1);
    outline: none;
    color: #212529 !important;
    background-color: #fff !important;
}

.btn-primary-green {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
    color: white;
    font-weight: 600;
}

.btn-primary-green:hover {
    background-color: var(--secondary-green);
    border-color: var(--secondary-green);
    color: white;
}
</style>

<?php include '../footer.php'; ?>
