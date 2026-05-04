<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$user_loans = $conn->query("SELECT COUNT(*) as count FROM loans WHERE user_id = $user_id AND return_date IS NULL")->fetch_assoc()['count'];
$overdue = $conn->query("SELECT COUNT(*) as count FROM loans WHERE user_id = $user_id AND return_date IS NULL AND due_date < CURDATE()")->fetch_assoc()['count'];
?>

<?php include 'heading.php'; ?>

<div class="container">
    <h1 style="color: white; margin-bottom: 30px;">📊 Dashboard</h1>
    
    <div class="dashboard-grid">
        <div class="stat-card">
            <h3>📚</h3>
            <p>Total Books</p>
            <h3 style="color: #667eea; font-size: 2.5em;"><?php echo $total_books; ?></h3>
        </div>
        
        <div class="stat-card">
            <h3>📖</h3>
            <p>My Active Loans</p>
            <h3 style="color: #764ba2; font-size: 2.5em;"><?php echo $user_loans; ?></h3>
        </div>
        
        <div class="stat-card">
            <h3>⏰</h3>
            <p>Overdue Books</p>
            <h3 style="color: #f5576c; font-size: 2.5em;"><?php echo $overdue; ?></h3>
        </div>
    </div>
    
    <h2 style="color: white; margin-top: 40px; margin-bottom: 20px;">📋 My Current Loans</h2>
    
    <?php
    $loans_sql = "SELECT l.*, b.title, b.author FROM loans l 
                  JOIN books b ON l.book_id = b.id 
                  WHERE l.user_id = $user_id 
                  ORDER BY l.due_date";
    $loans_result = $conn->query($loans_sql);
    
    if ($loans_result->num_rows > 0) {
        echo '<div class="table-container">';
        echo '<table>';
        echo '<thead><tr><th>Book Title</th><th>Author</th><th>Due Date</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        
        while ($loan = $loans_result->fetch_assoc()) {
            $status = "Active";
            if ($loan['return_date']) {
                $status = "✅ Returned";
            } elseif (strtotime($loan['due_date']) < time()) {
                $status = '⚠️ OVERDUE';
            }
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($loan['title']) . '</td>';
            echo '<td>' . htmlspecialchars($loan['author']) . '</td>';
            echo '<td>' . $loan['due_date'] . '</td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></div>';
    } else {
        echo '<div class="alert alert-info">You have no active loans.</div>';
    }
    ?>
</div>

<?php include 'footer.php'; ?>