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
    if (isset($_POST['action']) && $_POST['action'] == 'add_supplier') {
        $name = $_POST["name"];
        $category = $_POST["category"];
       

  
   
        $sql = "INSERT INTO suppliers (name, category) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $category);

        if ($stmt->execute()) {
            echo "supplier added successfully";
        } else {
            echo "Error adding supplier: " . $stmt->error;
        }

        $stmt->close();
    } elseif (isset($_POST['action']) && $_POST['action'] == 'delete_supplier') {
        $id = $_POST["id"];

        $sql = "DELETE FROM suppliers WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "supplier deleted successfully";
        } else {
            echo "Error deleting supplier: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Retrieve suppliers from the database
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Поставщики</title>
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
            <button class="btn btn-primary" onclick="showAddSupplierModal()">+ Добавить Поставщика</button>
        </div>
        <div id="suppliers" class="container mt-5 d-flex pb-5 flex-wrap gap-5">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="admin-supplier bg-white p-4 rounded" id="supplier-' . $row['id'] . '">';
                    echo '<h3 class="mt-2">' . $row['name'] . '</h3>';
                    echo '<p>' . $row['category'] . '</p>';
                    echo '<div>';
              
                    echo '<button class="btn btn-danger mt-2" onclick="showDeletesupplierModal(' . $row['id'] . ')">Удалить</button>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "No suppliers found.";
            }
            ?>
        </div>
    </main>

    <div class="modal fade" id="add_supplier-modal" tabindex="-1" aria-labelledby="add_supplier-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="add_supplier-modalLabel">Добавить поставщика</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add_supplier_form" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Название</label>
                            <input type="text" class="form-control" id="new_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Категория</label>
                            <input type="text" class="form-control" id="new_category" name="category" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success px-md-4" form="add_supplier_form" name="add_supplier" onclick="addsupplier()">Добавить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="delete_supplier-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Удалить продукт</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="delete_supplier_form">
                <div class="modal-body">
                    <input type="hidden" id="delete_supplier_id" name="id"> <!-- Hidden input to store supplier ID -->
                    <p>Вы действительно хотите удалить продукт?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger px-md-4" onclick="deletesupplier()">Удалить</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отменить</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var globalsupplierId;

        function showAddSupplierModal() {
            $('#add_supplier-modal').modal('show');
        }

        

        function showDeletesupplierModal(supplierId) {
            globalsupplierId = supplierId;
            $('#delete_supplier_id').val(supplierId);
            $('#delete_supplier-modal').modal('show');
        }

        function addsupplier() {
            var form = document.getElementById('add_supplier_form');
          
            if (!validateFormInputs(form)) {
                alert('Заполните все поля.');
                return; 
            }
            
            var formData = new FormData(form);
            formData.append('action', 'add_supplier');
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'suppliers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Продукт добавлен');
                    console.log(xhr.responseText)
                    window.location.reload();
                } else {
                    console.error('Error adding supplier:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error adding supplier:', xhr.statusText);
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

     

        function deletesupplier() {
            var form = document.getElementById('delete_supplier_form');
            var formData = new FormData(form);
            formData.append('action', 'delete_supplier');
            formData.append('id', globalsupplierId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'suppliers.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    alert('Продукт удален');
                    window.location.reload();
                } else {
                    console.error('Error deleting supplier:', xhr.statusText);
                }
            };
            xhr.onerror = function() {
                console.error('Error deleting supplier:', xhr.statusText);
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