<?php
// ----------------------------
// Database Configuration
// ----------------------------
$host     = getenv('DB_HOST');
$dbname   = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create table if not exists
$tableSql = "CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task VARCHAR(255) NOT NULL,
    is_done BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $tableSql);

// ----------------------------
// Handle Form Submissions
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $conn->prepare("INSERT INTO todos (task) VALUES (?)");
            $stmt->bind_param("s", $task);
            $stmt->execute();
            $stmt->close();
        }
    }

    if (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['toggle'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE todos SET is_done = NOT is_done WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF']); // Refresh page
    exit;
}

// ----------------------------
// Fetch To-Dos
// ----------------------------
$result = mysqli_query($conn, "SELECT * FROM todos ORDER BY created_at DESC");

$hostname = gethostname();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP To-Do List</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: #f3f6fa;
            color: #333;
            display: flex;
            justify-content: center;
            padding: 40px;
        }

        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 420px;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        form {
            display: flex;
            margin-bottom: 20px;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border-radius: 8px 0 0 8px;
            border: 1px solid #ccc;
            outline: none;
        }

        button {
            background: #0078d7;
            border: none;
            color: white;
            padding: 10px 16px;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #005fa3;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #f9fbfd;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .done {
            text-decoration: line-through;
            color: gray;
        }

        .actions form {
            display: inline;
        }

        .btn-small {
            background: none;
            border: none;
            color: #0078d7;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-small:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 To-Do List</h2>

    <form method="POST">
        <input type="text" name="task" placeholder="Add a new task..." required>
        <button type="submit" name="add">Add</button>
    </form>

    <ul>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <li>
                <span class="<?= $row['is_done'] ? 'done' : '' ?>">
                    <?= htmlspecialchars($row['task']) ?>
                </span>
                <div class="actions">
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button class="btn-small" type="submit" name="toggle">
                            <?= $row['is_done'] ? 'Undo' : 'Done' ?>
                        </button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button class="btn-small" type="submit" name="delete">Delete</button>
                    </form>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>

    <footer>
        Running on <strong><?= htmlspecialchars($hostname) ?></strong>
    </footer>
</div>
</body>
</html>

<?php
mysqli_close($conn);
?>
