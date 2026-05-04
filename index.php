<?php include 'heading.php'; ?>

<div class="container">
    <div class="card" style="text-align: center; padding: 60px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin: 30px 0;">
        <h1 style="font-size: 48px; margin-bottom: 20px;">📚 Welcome to Our Library</h1>
        <p style="font-size: 20px; margin-bottom: 30px;">Your gateway to a world of knowledge</p>
        
        <?php
        if (!isset($_SESSION['user_id'])) {
            echo '<div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">';
            echo '<a href="login.php" class="btn">Login</a>';
            echo '<a href="register.php" class="btn">Register</a>';
            echo '</div>';
        } else {
            echo '<p style="font-size: 18px;">Welcome back, ' . htmlspecialchars($_SESSION['username']) . '! 👋</p>';
        }
        ?>
    </div>

    <div class="dashboard-grid" style="margin-top: 50px;">
        <div class="card">
            <h3>📖 Browse Books</h3>
            <p>Explore our collection of books across different categories and find your next read.</p>
            <a href="view_books.php" class="btn" style="width: 100%;">View Books</a>
        </div>
        
        <div class="card">
            <h3>🎓 Borrow Books</h3>
            <p>Borrow books quickly and manage your loans easily with our simple interface.</p>
            <?php echo isset($_SESSION['user_id']) ? '<a href="borrow.php" class="btn" style="width: 100%;">Borrow Now</a>' : '<p style="color: #999;">Login to borrow books</p>'; ?>
        </div>
        
        <div class="card">
            <h3>⏰ Track Returns</h3>
            <p>Never miss a due date with our return tracking and notification system.</p>
            <?php echo isset($_SESSION['user_id']) ? '<a href="return.php" class="btn" style="width: 100%;">My Returns</a>' : '<p style="color: #999;">Login to view returns</p>'; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>