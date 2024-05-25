<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система учета продуктового ассортимента в супермаркете</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
                        <a class="nav-link" href="/aruzhan/">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/aruzhan/form.php">Оставить отзыв</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div id="products" class="container mt-5 d-flex gap-5 flex-wrap justify-content-center">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="product rounded">';
                    echo '<img src="' . $row['image'] . '" class="rounded" alt="" height="250">';
                    echo '<h2 class="mt-2">' . $row['name'] . '</h2>';
                    echo '<p>' . $row['description'] . '</p>';
                    echo '<button class="btn btn-primary mt-2"><h5 class="m-0">Цена: ' . $row['price'] . ' ₸</h5></button>';
                    echo '</div>';
                }
            } else {
                echo "No products found.";
            }
            ?>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>