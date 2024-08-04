<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to generate navbar
function generateNavbar() {
    include 'navbar.php'; // Include the navbar
}

// Function to generate footer
function generateFooter() {
    include 'footer.php'; // Include the footer
}

// Initialize message variables
$message = "";
$message_type = "";

// Process form submission for updating a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    if (!empty($category_id) && !empty($category_name)) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE id = ?");
        $stmt->bind_param("si", $category_name, $category_id);
        if ($stmt->execute()) {
            $message = "Category updated successfully";
            $message_type = "success";
        } else {
            $message = "Error updating category: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields";
        $message_type = "error";
    }
}

// Process form submission for deleting a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];

    if (!empty($category_id)) {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        if ($stmt->execute()) {
            $message = "Category deleted successfully";
            $message_type = "success";
        } else {
            $message = "Error deleting category: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Invalid category ID";
        $message_type = "error";
    }
}

// Fetch categories from the database
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);
if ($result) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Manage Categories</h2>

        <!-- Display success or error messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- List of Categories -->
        <h3 class="mt-4">Existing Categories</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['id']); ?></td>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td>
                                <!-- Trigger update modal -->
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#updateModal" data-id="<?php echo htmlspecialchars($category['id']); ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                    Update
                                </button>
                                <!-- Trigger delete confirmation -->
                                <form action="manage_categories.php" method="post" class="d-inline">
                                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['id']); ?>">
                                    <input type="hidden" name="delete_category" value="1">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No categories found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Update Category Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">Update Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="manage_categories.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="category_id" name="category_id">
                        <div class="form-group">
                            <label for="category_name">Category Name:</label>
                            <input type="text" id="category_name" name="category_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <input type="hidden" name="update_category" value="1">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php generateFooter(); ?>

    <script>
        // Populate the update modal with category data
        $('#updateModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var categoryId = button.data('id'); // Extract info from data-* attributes
            var categoryName = button.data('name');

            var modal = $(this);
            modal.find('#category_id').val(categoryId);
            modal.find('#category_name').val(categoryName);
        });
    </script>
</body>
</html>
