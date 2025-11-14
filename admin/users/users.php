<?php
include '../../includes/db.php';
include '../../includes/functions.php';
include '../../includes/admin_header.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$sql = "SELECT * FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);
?>

<h2>User Management</h2>
<a href="addUser.php" class="btn btn-success mb-3">Add User</a>

<table class="table table-bordered">
    <thead>
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
                    <img src="../../uploads/users/<?php echo $u['photo']; ?>" width="50" height="50">
                <?php else: ?>
                    No Photo
                <?php endif; ?>
            </td>

            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td><?php echo $u['active'] ? 'Active' : 'Inactive'; ?></td>
            <td><?php echo $u['created_at']; ?></td>

            <td>
                <a href="editUser.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="deleteUser.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Delete this user?')">Delete</a>
            </td>

        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
