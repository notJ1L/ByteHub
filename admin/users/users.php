<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? ORDER BY user_id DESC";
$stmt = $conn->prepare($sql);
$search_param = "%$search%";
$stmt->bind_param('ss', $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Management</h3>
        <div class="card-tools">
            <a href="addUser.php" class="btn btn-primary-green">+ Add User</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search for users..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($u = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $u['user_id']; ?></td>
                        <td>
                            <?php if ($u['photo']): ?>
                                <img src="/bytehub/uploads/users/<?php echo $u['photo']; ?>" width="50" height="50" class="rounded-circle">
                            <?php else: ?>
                                <span class="badge bg-secondary">No Photo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge bg-info"><?php echo htmlspecialchars($u['role']); ?></span></td>
                        <td><span class="badge bg-<?php echo $u['active'] ? 'success' : 'danger'; ?>"><?php echo $u['active'] ? 'Active' : 'Inactive'; ?></span></td>
                        <td><?php echo date('Y-m-d', strtotime($u['created_at'])); ?></td>
                        <td>
                            <a href="editUser.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <a href="deleteUser.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
