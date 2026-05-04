<?php
include '../db.php';
session_start();

// Redirect if user is not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

// Handle delete
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account!";
    } else {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "User deleted successfully!";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

// Handle role update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];

    // Don't allow changing your own role
    if ($user_id == $_SESSION['user_id']) {
        $error = "You cannot change your own role!";
    } else {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $role, $user_id);

        if ($stmt->execute()) {
            $success = "User role updated successfully!";
        } else {
            $error = "Failed to update user role.";
        }
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY username ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - Library Management System</title>
    <link rel="stylesheet" type="text/css" href="../style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include '../heading.php'; ?>

    <div class="container">
        <h2 style="color: #667eea; margin: 30px 0;">👥 Manage Users</h2>

        <?php if ($error) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>

        <?php if ($success) { ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php } ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="POST" action="" style="display: flex; gap: 5px;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" style="padding: 5px;" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="btn" style="padding: 5px 10px;" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>Update</button>
                                </form>
                            </td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']) { ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; text-decoration: none; display: inline-block;" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php } else { ?>
                                    <span style="color: #999;">(Your Account)</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>
</body>
</html>
