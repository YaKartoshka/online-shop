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

// Check if user is authenticated
if (!isset($_SESSION['isAuthenticated']) || !$_SESSION['isAuthenticated']) {
    header("Location: login.php");
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_product') {
        $name = $_POST["name"];
        $category = $_POST["category"];
        $price = $_POST["price"];
        $description = $_POST["description"];

        $image_name = $_FILES["image"]["name"];
        $image_tmp = $_FILES["image"]["tmp_name"];
        $image_destination = "uploads/" . basename($image_name);

        // Move the uploaded image to the destination directory
        move_uploaded_file($image_tmp, $image_destination);

   
        $sql = "INSERT INTO products (name, category, price, description, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $category, $price, $description, $image_destination);

        if ($stmt->execute()) {
            echo "Product added successfully";
        } else {
            echo "Error adding product: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_product') {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $category = $_POST["category"];
        $price = $_POST["price"];
        $description = $_POST["description"];

        $image_name = $_FILES["image"]["name"];
        $image_tmp = $_FILES["image"]["tmp_name"];
        $image_destination = "uploads/" . basename($image_name);

        $sql = "UPDATE products SET name=?, category=?, price=?, description=?";
        $values = [$name, $category, $price, $description];

        if (!empty($image_name)) {
            // Move the uploaded image to the destination directory
            move_uploaded_file($image_tmp, $image_destination);

            $sql .= ", image=?";
            $values[] = $image_destination;
        }

        $sql .= " WHERE id=?";
        $values[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", ...$values);

        if ($stmt->execute()) {
            echo "Product updated successfully";
        } else {
            echo "Error updating product: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_product') {
        $id = $_POST["id"];

        $sql = "DELETE FROM products WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Product deleted successfully";
        } else {
            echo "Error deleting product: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'get_product') {
        $id = $_POST["id"];
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode($product);
        } else {
            echo json_encode(array('error' => 'Product not found'));
        }
    
        $stmt->close();
    }
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
    <title>Admin</title>
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
        <div class="container mt-5">
            <button class="btn btn-primary" onclick="showAddProductModal()">+ Добавить продукт</button>
        </div>
        <div id="products" class="container mt-5 d-flex pb-5 flex-wrap gap-5">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="admin-product rounded" id="product-' . $row['id'] . '">';
                    echo '<img src="' . $row['image'] . '" class="rounded" alt="" height="180">';
                    echo '<h3 class="mt-2">' . $row['name'] . '</h3>';
                    echo '<p>' . $row['description'] . '</p>';
                    echo '<h5>Цена: ' . $row['price'] . ' ₸</h5>';
                    echo '<div>';
                    echo '<button class="btn btn-success mt-2" onclick="showEditProductModal(' . $row['id'] . ')">Редактировать</button>';
                    echo '<button class="btn btn-danger mt-2" onclick="showDeleteProductModal(' . $row['id'] . ')">Удалить</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "No products found.";
            }
            ?>
        </div>
    </main>

    <div class="modal fade" id="add_product-modal" tabindex="-1" aria-labelledby="add_product-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="add_product-modalLabel">Добавить продукт</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add_product_form" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="new_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="new_category" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="text" class="form-control" id="new_price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="new_description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="new_image" name="image" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" form="add_product_form" name="add_product" onclick="addProduct()">Добавить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_product-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Редактировать продукт</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit_product_form">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="text" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" onclick="editProduct()">Обновить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="delete_product-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Удалить продукт</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="delete_product_form">
                <div class="modal-body">
                    <input type="hidden" id="delete_product_id" name="id"> <!-- Hidden input to store product ID -->
                    <p>Вы действительно хотите удалить продукт?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-md-4" onclick="deleteProduct()">Удалить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalProductId;

        function showAddProductModal() {
            $('#add_product-modal').modal('show');
        }

        function showEditProductModal(productId) {
            globalProductId = productId;
            var formData = new FormData();
            formData.append('action', 'get_product');
            formData.append('id', globalProductId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                             
                    var data = JSON.parse(xhr.responseText.split('}')[0]+"}");
                    document.getElementById('name').value = data.name;
                    document.getElementById('category').value = data.category;
                    document.getElementById('price').value = data.price;
                    document.getElementById('description').value = data.description;

                    $('#edit_product-modal').modal('show');
                } else {
                 
                }
            };
            xhr.onerror = function() {
                
            };
            xhr.send(formData);
               
                   
        }

        function showDeleteProductModal(productId) {
            globalProductId = productId;
            $('#delete_product_id').val(productId);
            $('#delete_product-modal').modal('show');
        }

        function addProduct() {
            var form = document.getElementById('add_product_form');
          
            if (!validateFormInputs(form)) {
                alert('Заполните все поля.');
                return; 
            }
            
            var formData = new FormData(form);
            formData.append('action', 'add_product');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Продукт добавлен');
                    window.location.reload();
                } else {
                    console.error('Error adding product:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error adding product:', xhr.statusText);
            };
            xhr.send(formData);
        }

        function validateFormInputs(form) {
            var inputs = form.querySelectorAll('input, select, textarea');
            for (var i = 0; i < inputs.length; i++) {
                console.log(isValidInput(inputs[i]))
                if (inputs[i].required && !isValidInput(inputs[i])) {
                    return false; 
                }
            }
            return true;
        }

        function isValidInput(input) {
         
            if (input.type === 'file') {
              
                return input.files.length > 0;
            } else {
             
                return input.value.trim() !== '';
            }
        }

        function editProduct() {
            var form = document.getElementById('edit_product_form');

            if (!validateFormInputs(form)) {
                alert('Заполните все поля и файл.');
                return; 
            }

            var formData = new FormData(form);
            formData.append('action', 'update_product');
            formData.append('id', globalProductId);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Продукт обновлен');
                    window.location.reload();
                } else {
                    console.error('Error updating product:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error updating product:', xhr.statusText);
            };
            xhr.send(formData);
        }

        function deleteProduct() {
            var form = document.getElementById('delete_product_form');
            var formData = new FormData(form);
            formData.append('action', 'delete_product');
            formData.append('id', globalProductId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'admin.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Продукт удален');
                    window.location.reload();
                } else {
                    console.error('Error deleting product:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error deleting product:', xhr.statusText);
            };
            xhr.send(formData);
        }


    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>