<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "supermarket";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM admins WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['isAuthenticated'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $loginError = "Неправильная почта или пароль";
    }

    $stmt->close();
}

// Check if user is authenticated
if (!isset($_SESSION['isAuthenticated']) || !$_SESSION['isAuthenticated']) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Логин | Система учета продуктового ассортимента в супермаркете</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <main>
        <div class="login container-sm">
            <h6>Система учета продуктового ассортимента в супермаркете</h6>
            <br>
            <h1>Вход</h1>
            <div id="ambiance" class=" p-0"></div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <?php if (isset($loginError)) { ?>
                    <div class="alert alert-danger"><?php echo $loginError; ?></div>
                <?php } ?>
                <div class="mb-3 mt-3 ">
                    <label for="email" class="form-label">Электронная почта</label>
                    <input type="email" class="form-control" id="email" placeholder="Введите вашу почту" name="email">
                </div>
                <div class="mb-1">
                    <label for="password" class="form-label">Пароль</label>
                    <input type="password" class="form-control" id="password" placeholder="Введите пароль" name="password">
                </div>
                <button type="submit" id="login_btn" class="btn btn-primary px-5 mt-4" name="login">Войти</button>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

// Handle form submissions for products
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (code for handling product submissions remains the same)
}

// Retrieve products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!-- HTML code for the admin panel remains the same -->

<?php
// Close the database connection
$conn->close();
?>