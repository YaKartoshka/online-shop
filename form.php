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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $description = $_POST["description"];

    // Check if the email already exists in the clients table
    $sql_check = "SELECT COUNT(*) AS count FROM clients WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();
    $existing_count = $row['count'];
    $stmt_check->close();

  
    $sql_review = "INSERT INTO reviews (name, email, description) VALUES (?, ?, ?)";
    $stmt_review = $conn->prepare($sql_review);
    $stmt_review->bind_param("sss", $name, $email, $description);

    
    if ($stmt_review->execute()) {
        $stmt_review->close(); 
        if ($existing_count > 0) {
             echo "Отзыв добавлен";
        } else {
            
            $sql_client = "INSERT INTO clients (client_name, email) VALUES (?, ?)";
            $stmt_client = $conn->prepare($sql_client);
            $stmt_client->bind_param("ss", $name, $email);

            
            if ($stmt_client->execute()) {
                $stmt_client->close(); 
                echo "Отзыв и новый клиент добавлены";
            } else {
                echo "Error adding client: " . $stmt_client->error;
            }
        }
    } else {
        echo "Error adding review: " . $stmt_review->error;
    }
    
}
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
                        <a class="nav-link active" href="/aruzhan">Supermarket</a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="/aruzhan">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/aruzhan/form.php">Оставить отзыв</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main>
        <div class="container mt-5">
            <h2>Оставить отзыв</h2>
            <form id="add_review_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Ваше Имя</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Отзыв</label>
                    <textarea class="form-control" id="description" name="description" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check if form submission is in progress
        if (sessionStorage.getItem('form')) {
            alert('Ваш запрос еще в обработке');
            window.location.href = '/aruzhan';
        }

        $(document).ready(function() {
            $('#add_review_form').submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                // Send AJAX POST request
                $.ajax({
                    type: 'POST',
                    url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>',
                    data: $(this).serialize(),
                    success: function(response) {
                        console.log('Review added successfully:', response);
                        alert('Ваш отзыв в обработке');
                        window.location.href = '/aruzhan';
                        sessionStorage.setItem('form', 'send');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding review:', error);
                        // Handle error response here, e.g., show an error message
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>