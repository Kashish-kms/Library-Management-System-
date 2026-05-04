<?php
include 'db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error = "❌ Password must be at least 6 characters!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $success = "✅ Registration successful! <a href='login.php' style='color: #667eea;'>Login here</a>";
        } else {
            if ($conn->errno == 1062) {
                $error = "❌ Username or email already exists!";
            } else {
                $error = "❌ Error: " . $conn->error;
            }
        }
    }
}
?>

<?php include 'heading.php'; ?>

<div class="form-container">
    <h1>📝 Register</h1>
    
    <?php if ($error) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
    <?php if ($success) { echo '<div class="alert alert-success">' . $success . '</div>'; } ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">Already have an account? <a href="login.php" style="color: #667eea; text-decoration: none; font-weight: bold;">Login here</a></p>
</div>

<?php include 'footer.php'; ?>