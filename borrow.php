<?php
include 'db.php';
session_start();

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = intval($_POST['book_id']);
    $due_days = intval($_POST['due_days']);

    // Check if book exists and has available copies
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $error = "Book not found!";
    } else {
        $book = $result->fetch_assoc();

        if ($book['available_copies'] <= 0) {
            $error = "This book is not available for borrowing!";
        } else {
            // Check if user already has this book borrowed
            $sql = "SELECT * FROM loans WHERE user_id = ? AND book_id = ? AND return_date IS NULL";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $book_id);
            $stmt->execute();
            $check = $stmt->get_result();

            if ($check->num_rows > 0) {
                $error = "You have already borrowed this book!";
            } else {
                // Create loan record
                $borrow_date = date('Y-m-d');
                $due_date = date('Y-m-d', strtotime("+$due_days days"));

                $sql = "INSERT INTO loans (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iiss", $user_id, $book_id, $borrow_date, $due_date);

                if ($stmt->execute()) {
                    // Update available copies
                    $new_copies = $book['available_copies'] - 1;
                    $sql = "UPDATE books SET available_copies = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $new_copies, $book_id);
                    $stmt->execute();

                    $success = "Book borrowed successfully! Due date: $due_date";
                    $book_id = 0;
                } else {
                    $error = "Failed to borrow book. Please try again.";
                }
            }
        }
    }
}

// Get book details if book_id is set
$book_details = null;
if ($book_id > 0) {
    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $book_details = $result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Borrow Book - Library Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <?php include 'heading.php'; ?>

    <div class="container">
        <div class="form-container">
            <h2 style="text-align: center; color: #667eea; margin-bottom: 30px;">➕ Borrow a Book</h2>

            <?php if ($error) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($success) { ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php } ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="book_id">Select Book:</label>
                    <select id="book_id" name="book_id" required onchange="this.form.submit();">
                        <option value="">-- Choose a Book --</option>
                        <?php
                        $sql = "SELECT * FROM books WHERE available_copies > 0 ORDER BY title ASC";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            $selected = $book_id == $row['id'] ? 'selected' : '';
                            echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['title']) . " by " . htmlspecialchars($row['author']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <?php if ($book_details) { ?>
                    <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                        <p><strong>Title:</strong> <?php echo htmlspecialchars($book_details['title']); ?></p>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($book_details['author']); ?></p>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book_details['isbn']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($book_details['category']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($book_details['description']); ?></p>
                        <p><strong>Available Copies:</strong> <?php echo $book_details['available_copies']; ?></p>
                    </div>

                    <div class="form-group">
                        <label for="due_days">Borrow Duration (days):</label>
                        <select id="due_days" name="due_days" required>
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="21">21 days</option>
                            <option value="30">30 days</option>
                        </select>
                    </div>

                    <button type="submit" class="btn" style="width: 100%;">Confirm Borrow</button>
                <?php } ?>
            </form>

            <hr style="margin: 30px 0;">

            <h3 style="color: #667eea; margin-bottom: 20px;">My Current Loans</h3>
            <?php
            $sql = "SELECT l.*, b.title, b.author FROM loans l 
                   JOIN books b ON l.book_id = b.id 
                   WHERE l.user_id = ? AND l.return_date IS NULL 
                   ORDER BY l.due_date ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $loans = $stmt->get_result();

            if ($loans->num_rows > 0) {
                echo "<div class='table-container'>";
                echo "<table>";
                echo "<thead><tr><th>Book Title</th><th>Author</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr></thead>";
                echo "<tbody>";

                while ($loan = $loans->fetch_assoc()) {
                    $due = new DateTime($loan['due_date']);
                    $today = new DateTime();
                    $status = $today > $due ? 'Overdue' : 'On Time';
                    $status_color = $status == 'Overdue' ? '#f8d7da' : '#d4edda';
                    $status_text = $status == 'Overdue' ? '#721c24' : '#155724';

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($loan['title']) . "</td>";
                    echo "<td>" . htmlspecialchars($loan['author']) . "</td>";
                    echo "<td>" . $loan['borrow_date'] . "</td>";
                    echo "<td>" . $loan['due_date'] . "</td>";
                    echo "<td><span style='background: $status_color; color: $status_text; padding: 5px 10px; border-radius: 3px;'>$status</span></td>";
                    echo "</tr>";
                }

                echo "</tbody></table></div>";
            } else {
                echo "<div class='alert alert-info'>You don't have any active loans.</div>";
            }
            ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Library Management System. All rights reserved.</p>
    </footer>
</body>
</html>
