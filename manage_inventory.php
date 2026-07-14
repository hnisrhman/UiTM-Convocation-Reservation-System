<?php
session_start();
include 'db_connect.php';
include 'header_admin.php';

// Add new product
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $type = $_POST['product_type'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $image_name = $_FILES['product_image']['name'];
    $image_tmp = $_FILES['product_image']['tmp_name'];
    $image_path = 'images/' . $image_name;

    move_uploaded_file($image_tmp, '../' . $image_path);

    // Insert product
    mysqli_query($conn, "INSERT INTO products (product_name, product_type, description, price, quantity, image_path) 
                         VALUES ('$name', '$type', '$desc', '$price', '$quantity', '$image_path')");
    echo "<script>alert('Product added successfully'); window.location='manage_inventory.php';</script>";
}


// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    echo "<script>alert('Product deleted.'); window.location='manage_inventory.php';</script>";
}

$result = mysqli_query($conn, "SELECT * FROM products");

echo "<h2>Manage Inventory</h2>";
echo "<table border='1'><tr><th>Name</th><th>Type</th><th>Price</th><th>Qty</th><th>Actions</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
<td>{$row['product_name']}</td>
<td>{$row['product_type']}</td>
<td>RM {$row['price']}</td>
<td>{$row['quantity']}</td>
<td>
  <a href='edit_product.php?id={$row['id']}'>Edit</a> | 
  <a href='manage_inventory.php?delete={$row['id']}'>Delete</a>
</td>
</tr>";

}
echo "</table>";
?>
<html>
    <head>
        <title>Add New Product</title>
    </head>
    <body>
        <h3>Add New Product</h3>
        <form method="post" enctype="multipart/form-data">
            Name: <input type="text" name="product_name" required><br>
            Type: <input type="text" name="product_type" required><br>
            Description: <input type="text" name="description" required><br>
            Price (RM): <input type="number" name="price" required><br>
            Quantity: <input type="number" name="quantity" required><br>
            Image: <input type="file" name="product_image" accept="image/*" required><br>
            <button type="submit" name="add_product">Add Product</button>
        </form> 
    </body>
</html>

