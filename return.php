<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

if (isset($_GET['loan_id'])) {
    $loan_id = intval($_GET['loan_id']);
    
    $sql = "SELECT * FROM loans WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $loan_id, $user_id);
    $stmt->execute();
    $loan_result = $stmt->get_result();
    
    if ($loan_result->num_rows > 0) {
        $loan = $loan_result->fetch_assoc();
        
        $return_date = date('Y-m-d');
        $sql = "UPDATE loans SET return_date = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $return_date, $loan_id);
        
        if ($stmt->execute()) {
            $sql = "UPDATE books SET available_copies = available_copies + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $loan['book_id']);
            $stmt->execute();
            
            $success = "✅ Book returned successfully!";
        } else {
            $error = "❌ Error: " . $conn->error;
        }
    } else {
        $error = "❌ Loan not found!";
    }
}

$loans_sql = "SELECT l.*, b.title, b.author FROM loans l 
              JOIN books b ON l.book_id = b.id 
              WHERE l.user_id = ? 
              ORDER BY l.due_date DESC";
$stmt = $conn->prepare($loans_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$loans_result = $stmt->get_result();
?>

<?php include 'heading.php'; ?>

<div class="container">
    <h1 style="color: white; margin-bottom: 30px;">↩️ Return Books</h1>
    
    <?php if ($success) { echo '<div class="alert alert-success">' . $success . '</div>'; } ?>
    <?php if ($error) { echo '<div class="alert alert-danger">' . $error . '</div>'; } ?>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($loan = $loans_result->fetch_assoc()) {
                    $status = "Active";
                    $action = '<a href="return.php?loan_id=' . $loan['id'] . '" class="btn" style="padding: 5px 10px;">Return</a>';
                    
                    if ($loan['return_date']) {
                        $status = "✅ Returned";
                        $action = "—";
                    } elseif (strtotime($loan['due_date']) < time()) {
                        $status = '<span style="color: red;">⚠️ OVERDUE</span>';
                    }
                    
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($loan['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($loan['author']) . '</td>';
                    echo '<td>' . $loan['borrow_date'] . '</td>';
                    echo '<td>' . $loan['due_date'] . '</td>';
                    echo '<td>' . $status . '</td>';
                    echo '<td>' . $action . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>