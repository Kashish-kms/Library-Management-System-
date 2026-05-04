<?php
include '../db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $total_copies = intval($_POST['total_copies']);
    
    $sql = "INSERT INTO books (title, author, isbn, category, description, total_copies, available_copies) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssii", $title, $author, $isbn, $category, $description, $total_copies, $total_copies);
    
    if ($stmt->execute()) {
        $success = "✅ Book added successfully!";
    } else {
        if ($conn->errno == 1062) {
            $error = "❌ ISBN already exists!";
        } else {
            $error = "❌ Error: " . $conn->error;
        }
    }
}
?>

<?php include '../heading.php'; ?>

<div class="form-container">
    <h1>➕ Add New Book</h1>
    
    <?php if ($success) { echo '<div class="alert alert-success">' . $success . '</div>'; } ?>
    <?php if ($error) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="title">Book Title:</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>
        </div>
        
        <div class="form-group">
            <label for="isbn">ISBN:</label>
            <input type="text" id="isbn" name="isbn" required>
        </div>
        
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-group">
            <label for="total_copies">Total Copies:</label>
            <input type="number" id="total_copies" name="total_copies" min="1" required>
        </div>
        
        <button type="submit" class="btn" style="width: 100%;">Add Book</button>
    </form>
</div>

<?php include '../footer.php'; ?>