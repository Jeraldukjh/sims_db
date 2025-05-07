<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $product_id = trim($_POST["product_id"]);
        
        if ($_POST['action'] === 'update') {
            // Update product logic
            $product_name = trim($_POST["product_name"]);
            $stock = trim($_POST["stock"]);
            $category = trim($_POST["category"]);

            try {
                $stmt = $pdo->prepare("UPDATE products SET product_name = :product_name, stock = :stock, category = :category WHERE product_id = :product_id");
                $stmt->bindValue(':product_name', $product_name, PDO::PARAM_STR);
                $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
                $stmt->bindValue(':category', $category, PDO::PARAM_STR);
                $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $success = "Product updated successfully!";
                } else {
                    throw new Exception("Error: Could not update the product.");
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        } elseif ($_POST['action'] === 'delete') {
            // Delete product logic
            try {
                $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = :product_id");
                $stmt->bindValue(':product_id', $product_id, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $success = "Product deleted successfully!";
                } else {
                    throw new Exception("Error: Could not delete the product.");
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } else {
        // Insert new product logic
        $product_name = trim($_POST["product_name"]);
        $stock = trim($_POST["stock"]);
        $category = trim($_POST["category"]);
        $image_url = '';

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $target_dir = "uploads/";
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file; // Save image path
                } else {
                    $error = "Error uploading image.";
                }
            } else {
                $error = "Invalid image type.";
            }
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO products (product_name, stock, category, image_url) VALUES (:product_name, :stock, :category, :image_url)");
            $stmt->bindValue(':product_name', $product_name, PDO::PARAM_STR);
            $stmt->bindValue(':stock', $stock, PDO::PARAM_INT);
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
            $stmt->bindValue(':image_url', $image_url, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $success = "Product saved successfully!";
            } else {
                throw new Exception("Error: Could not save the product.");
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Fetch all products from the products table
$stmt = $pdo->prepare("SELECT product_id, product_name, stock, category, image_url FROM products");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Product Management</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
         body {
            font-family: 'Poppins', sans-serif;
            background-color: #ecf0f1;
            color: #2c3e50;
            margin: 0;
            display: flex;
        }
        h2 {
            text-align: center;
            font-weight: 600;
            color: #1abc9c;
        }
        .sidebar {
            width: 250px;
            background-color: #34495e;
            padding: 20px;
            color: white;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 0;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background-color: #1abc9c;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .container {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .input-container {
            position: relative;
            margin-bottom: 20px;
        }
        .input-container input, .input-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .input-container label {
            position: absolute;
            left: 10px;
            top: 10px;
            font-size: 16px;
            color: #aaa;
            transition: 0.2s ease all;
        }
        .input-container input:focus, .input-container select:focus {
            border-color: #1abc9c;
            outline: none;
        }
        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label,
        .input-container select:focus + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #1abc9c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #1abc9c;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #d1f2eb;
        }
        button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease-in-out;
            font-weight: 600;
        }
        button:hover {
            background: #16a085;
        }
        .logout {
            background: #e74c3c;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            float: right;
            margin-bottom: 20px;
        }
        .logout:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="Manage_request.php">Dashboard</a>
        <a href="CRUD.php">Manage Products</a>
        <a href="Approve.php">Students</a>
        <a href="Records.php">Records</a>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="logout">Logout</button>
        </form>
    </div>
    <div class="content">
        <div class="container">
            <h2>Product Management</h2>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form id="product-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="product_id" value="">
                <div class="input-container">
                    <input type="text" name="product_name" id="product_name" placeholder=" " required>
                    <label for="product_name">Product Name</label>
                </div>
                <div class="input-container">
                    <input type="number" name="stock" id="stock" placeholder=" " required>
                    <label for="stock">Stock</label>
                </div>
                <div class="input-container">
                    <select name="category" id="category" required>
                        <option value="" disabled selected hidden></option>
                        <option value="Input Devices">Input Devices</option>
                        <option value="Output Devices">Output Devices</option>
                        <option value="Storage Devices">Storage Devices</option>
                    </select>
                    <label for="category">Category</label>
                </div>
                <div class="input-container">
                    <input type="file" name="image" id="image">
                    <label for="image">Upload Image</label>
                </div>
                <button type="submit">Save Product</button>
            </form>

            <h3>Product List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product_id']); ?></td>
                            <td><?= htmlspecialchars($product['product_name']); ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($product['image_url']); ?>" alt="<?= htmlspecialchars($product['product_name']); ?>" style="max-width: 100px;">
                            </td>
                            <td><?= htmlspecialchars($product['stock']); ?></td>
                            <td><?= htmlspecialchars($product['category']); ?></td>
                            <td>
                                <div class="button-group">
                                    <button class="edit-product" data-id="<?= $product['product_id']; ?>" data-name="<?= $product['product_name']; ?>" data-stock="<?= $product['stock']; ?>" data-category="<?= $product['category']; ?>">Edit</button>
                                    <button class="delete-product" data-id="<?= $product['product_id']; ?>">Delete</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Edit product
            $('.edit-product').click(function() {
                const productId = $(this).data('id');
                const productName = $(this).data('name');
                const productStock = $(this).data('stock');
                const productCategory = $(this).data('category');
                $('#product_id').val(productId);
                $('#product_name').val(productName);
                $('#stock').val(productStock);
                $('#category').val(productCategory);
            });

            // Delete product
            $('.delete-product').click(function() {
                if (confirm("Are you sure you want to delete this product?")) {
                    const productId = $(this).data('id');
                    $.ajax({
                        url: '', // The same page
                        type: 'POST',
                        data: { action: 'delete', product_id: productId },
                        success: function(response) {
                            location.reload(); // Reload the page to see the changes
                        },
                        error: function() {
                            alert("Error deleting product.");
                        }
                    });
                }
            });
        });
    </script>
    <script>
    $(document).ready(function() {
        // Edit product
        $('.edit-product').click(function() {
            const productId = $(this).data('id');
</body>
</html>
