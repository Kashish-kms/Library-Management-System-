<?php
include 'db.php';
session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // DEBUG: Show what you're searching for
    echo "<!-- DEBUG: Looking for username: $username -->";
    
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // DEBUG: Show if user was found
    echo "<!-- DEBUG: Rows found: " . $result->num_rows . " -->";
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        echo "<!-- DEBUG: User found: " . $user['username'] . " -->";
        echo "<!-- DEBUG: Password hash: " . $user['password'] . " -->";
        echo "<!-- DEBUG: Verify result: " . (password_verify($password, $user['password']) ? "TRUE" : "FALSE") . " -->";
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ User not found!";
    }
}
?>

<?php include 'heading.php'; ?>

<div class="form-container">
    <h1>🔐 Login</h1>
    
    <?php if ($error) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px;">Don't have an account? <a href="register.php" style="color: #667eea; text-decoration: none; font-weight: bold;">Register here</a></p>
</div>

<?php include 'footer.php'; ?>