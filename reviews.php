<?php
// Start the session
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['isAuthenticated']) || !$_SESSION['isAuthenticated']) {
    // Redirect to the login page
    header("Location: /aruzhan/login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/style.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-sm bg-white navbar-light">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/aruzhan/">Supermarket</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="/aruzhan/admin.php">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/aruzhan/reviews.php">Отзывы</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/aruzhan/suppliers.php">Поставщики</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="reviews" class="container mt-5 d-flex gap-5 flex-wrap justify-content-center">
            <?php
            // Database connection details
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "supermarket";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get reviews
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $sql = "SELECT * FROM reviews";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="review rounded w-75 bg-white p-3">';
                        echo '<h3 class="mt-2">' . $row['name'] . '</h3>';
                        echo '<h5>Почта: ' . $row['email'] . '</h5>';
                        echo '<p>Описание: ' . $row['description'] . '</p>';
                        echo '<div>';
                        echo '<button class="btn btn-danger mt-2" onclick="showDeleteReviewModal(' . $row['id'] . ')">Удалить</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "Нет отзывов";
                }
            }

            // Delete review
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $reviewId = $_POST['id'];
                $sql = "DELETE FROM reviews WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $reviewId);

                if ($stmt->execute()) {
                    echo "Review deleted successfully";
                } else {
                    echo "Error deleting review: " . $stmt->error;
                }

                $stmt->close();
            }

            $conn->close();
            ?>
        </div>
    </main>

    <div class="modal fade" id="delete_review-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Удалить отзыв</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Вы действительно хотите удалить?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-md-4" onclick="deleteReview()">Удалить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalReviewId;

        function showDeleteReviewModal(reviewId) {
            globalReviewId = reviewId;
            $('#delete_review-modal').modal('show');
        }

        function deleteReview() {
            $.ajax({
                url: '', // Set the correct URL for the PHP file
                type: 'POST',
                data: { 'id': globalReviewId },
                success: function(response) {
                    alert('отзыв удален');
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting review:', error);
                }
            });
        }
    </script>
</body>

</html>