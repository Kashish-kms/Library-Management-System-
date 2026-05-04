<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="header">
        <h1>📚 Library Management System</h1>
        <nav class="navbar">
            <ul>
                <?php
                if (isset($_SESSION['user_id'])) {
                    $role = $_SESSION['role'];
                    echo "<li><a href='index.php'>Home</a></li>";
                    echo "<li><a href='dashboard.php'>Dashboard</a></li>";
                    
                    if ($role == 'admin') {
                        echo "<li><a href='admin/manage_users.php'>👥 Manage Users</a></li>";
                        echo "<li><a href='admin/add_book.php'>➕ Add Book</a></li>";
                    } else {
                        echo "<li><a href='view_books.php'>📖 Browse Books</a></li>";
                        echo "<li><a href='borrow.php'>🎓 Borrow</a></li>";
                        echo "<li><a href='return.php'>↩️ Return</a></li>";
                    }
                    
                    echo "<li><a href='logout.php'>🚪 Logout (" . $_SESSION['username'] . ")</a></li>";
                } else {
                    echo "<li><a href='index.php'>Home</a></li>";
                    echo "<li><a href='login.php'>🔐 Login</a></li>";
                    echo "<li><a href='register.php'>📝 Register</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>