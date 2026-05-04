<?php
include 'db.php';
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search = '';
$filter = '';

if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}

// Build query
$sql = "SELECT * FROM books WHERE 1=1";
$params = array();
$types = '';

if ($search) {
    $search_term = "%$search%";
    $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $params = array($search_term, $search_term, $search_term);
    $types = 'sss';
}

if ($filter && $filter != 'all') {
    $sql .= " AND category = ?";
    $params[] = $filter;
    $types .= 's';
}

$sql .= " ORDER BY title ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Books - Library Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'heading.php'; ?>

    <div class="container">
        <h2 style="color: #667eea; margin: 30px 0;">📚 Browse Books</h2>

        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <form method="GET" action="" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search by title, author, or ISBN..." 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       style="flex: 1; min-width: 200px; padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px;">
                
                <select name="filter" style="padding: 12px; border: 2px solid #e0e0e0; border-radius: 5px;">
                    <option value="all">All Categories</option>
                    <option value="Fiction" <?php echo $filter == 'Fiction' ? 'selected' : ''; ?>>Fiction</option>
                    <option value="Non-Fiction" <?php echo $filter == 'Non-Fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
                    <option value="Science" <?php echo $filter == 'Science' ? 'selected' : ''; ?>>Science</option>
                    <option value="Technology" <?php echo $filter == 'Technology' ? 'selected' : ''; ?>>Technology</option>
                    <option value="History" <?php echo $filter == 'History' ? 'selected' : ''; ?>>History</option>
                    <option value="Biography" <?php echo $filter == 'Biography' ? 'selected' : ''; ?>>Biography</option>
                </select>

                <button type="submit" class="btn">Search</button>
                <a href="view_books.php" class="btn" style="text-decoration: none; display: flex; align-items: center;">Clear</a>
            </form>
        </div>

        <?php if ($result->num_rows > 0) { ?>
            <div class="books-grid">
                <?php while ($book = $result->fetch_assoc()) { ?>
                    <div class="book-card">
                        <div class="book-image">📖</div>
                        <div class="book-info">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></div>
                            <div style="color: #999; font-size: 0.85em; margin-bottom: 10px;">
                                <?php echo htmlspecialchars($book['category']); ?>
                            </div>
                            <div style="margin-bottom: 10px;">
                                <span style="background: <?php echo $book['available_copies'] > 0 ? '#d4edda' : '#f8d7da'; ?>; 
                                            color: <?php echo $book['available_copies'] > 0 ? '#155724' : '#721c24'; ?>; 
                                            padding: 5px 10px; border-radius: 3px; font-size: 0.9em;">
                                    <?php echo $book['available_copies'] > 0 ? 'Available (' . $book['available_copies'] . ')' : 'Not Available'; ?>
                                </span>
                            </div>
                            <?php if ($book['available_copies'] > 0) { ?>
                                <a href="borrow.php?book_id=<?php echo $book['id']; ?>" class="btn" style="display: block; text-align: center; text-decoration: none;">
                                    Borrow
                                </a>
                            <?php } else { ?>
                                <button class="btn" disabled style="width: 100%; opacity: 0.5;">Not Available</button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="alert alert-info" style="text-align: center;">
                No books found matching your search criteria.
            </div>
        <?php } ?>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>
</body>
</html>
