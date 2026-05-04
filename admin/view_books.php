<?php
include 'db.php';
session_start();
?>

<?php include 'heading.php'; ?>

<div class="container">
    <h1 style="color: white; margin-bottom: 30px;">📖 Browse Our Collection</h1>
    
    <div class="card" style="margin-bottom: 30px;">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <input type="text" name="search" placeholder="Search by title or author..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
            
            <input type="text" name="category" placeholder="Filter by category..." value="<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : ''; ?>" style="padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
            
            <button type="submit" class="btn">🔍 Search</button>
        </form>
    </div>
    
    <?php
    $sql = "SELECT * FROM books WHERE available_copies > 0";
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $conn->real_escape_string($_GET['search']);
        $sql .= " AND (title LIKE '%$search%' OR author LIKE '%$search%')";
    }
    
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = $conn->real_escape_string($_GET['category']);
        $sql .= " AND category LIKE '%$category%'";
    }
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo '<div class="books-grid">';
        
        while ($book = $result->fetch_assoc()) {
            echo '<div class="book-card">';
            echo '<div class="book-image">📚</div>';
            echo '<div class="book-info">';
            echo '<div class="book-title">' . htmlspecialchars($book['title']) . '</div>';
            echo '<div class="book-author">' . htmlspecialchars($book['author']) . '</div>';
            echo '<div class="book-isbn">ISBN: ' . htmlspecialchars($book['isbn']) . '</div>';
            echo '<p style="font-size: 0.9em; color: #666; margin-bottom: 10px;">' . htmlspecialchars(substr($book['description'], 0, 100)) . '...</p>';
            echo '<p style="color: #667eea; font-weight: bold;">Available: ' . $book['available_copies'] . '</p>';
            
            if (isset($_SESSION['user_id'])) {
                echo '<a href="borrow.php?book_id=' . $book['id'] . '" class="btn" style="width: 100%; text-align: center; text-decoration: none;">Borrow</a>';
            }
            
            echo '</div></div>';
        }
        
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">No books found matching your criteria.</div>';
    }
    ?>
</div>

<?php include 'footer.php'; ?>