<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testdb";

$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$createDatabaseQuery = "CREATE DATABASE IF NOT EXISTS $dbname";
$conn->query($createDatabaseQuery);

// Switch to the specified database
$conn->select_db($dbname);

// Create categories table if it doesn't exist
$createCategoriesTableQuery = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
)";
$conn->query($createCategoriesTableQuery);

// Create tasks table if it doesn't exist
$createTasksTableQuery = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    category_id INT,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)";
$conn->query($createTasksTableQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category'];

    $insertTaskQuery = "INSERT INTO tasks (title, description, category_id) VALUES ('$title', '$description', $category_id)";
    $conn->query($insertTaskQuery);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $name = $_POST['name'];

    $insertCategoryQuery = "INSERT INTO categories (name) VALUES ('$name')";
    $conn->query($insertCategoryQuery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <!-- Add Bootstrap CSS link -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container">

    <h1 class="mt-5">Task Management</h1>

    <h2>Tasks</h2>
    <?php
    $tasksResult = $conn->query("SELECT tasks.id, tasks.title, tasks.description, categories.name as category_name 
                                FROM tasks 
                                INNER JOIN categories ON tasks.category_id = categories.id");
    if ($tasksResult->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $tasksResult->fetch_assoc()) {
            echo "<li class='list-group-item'>{$row['title']} - {$row['description']} (Category: {$row['category_name']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No tasks found.</p>";
    }
    ?>

    <h2 class="mt-3">Categories</h2>
    <?php
    $categoriesResult = $conn->query("SELECT * FROM categories");
    if ($categoriesResult->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $categoriesResult->fetch_assoc()) {
            echo "<li class='list-group-item'>{$row['name']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No categories found.</p>";
    }
    ?>

    <h2 class="mt-3">Add Task</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <?php
            $categoriesResult = $conn->query("SELECT * FROM categories");
            if ($categoriesResult->num_rows > 0) {
                echo "<select name='category' class='form-control' required>";
                while ($row = $categoriesResult->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                }
                echo "</select>";
            } else {
                echo "<p>No categories found.</p>";
            }
            ?>
        </div>
        <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
    </form>

    <h2 class="mt-3">Add Category</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
    </form>

    <!-- Add Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
