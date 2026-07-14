<?php
session_start();
include 'db_connect.php';
include 'header_admin.php';

$id = $_GET['id'];
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['product_name'];
    $type = $_POST['product_type'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Check if a new image was uploaded
    if (!empty($_FILES['product_image']['name'])) {
        $image_name = $_FILES['product_image']['name'];
        $image_tmp = $_FILES['product_image']['tmp_name'];
        $image_path = 'images/' . $image_name;
        move_uploaded_file($image_tmp, '../' . $image_path);

        // Update product with new image
        mysqli_query($conn, "UPDATE products SET 
            product_name='$name', 
            product_type='$type', 
            description='$desc', 
            price='$price', 
            quantity='$quantity', 
            image_path='$image_path'
            WHERE id=$id");
    } else {
        // Update without changing image
        mysqli_query($conn, "UPDATE products SET 
            product_name='$name', 
            product_type='$type', 
            description='$desc', 
            price='$price', 
            quantity='$quantity'
            WHERE id=$id");
    }

    echo "<script>alert('Product updated successfully'); window.location='manage_inventory.php';</script>";
}

?>

<html>
    <head>
        <title>Edit Product</title>
    </head>
    <body>
       <h2>Edit Product</h2>
        <form method="post" enctype="multipart/form-data">
        Name: <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required><br>
        Type: <input type="text" name="product_type" value="<?= $product['product_type'] ?>" required><br>
        Description: <input type="text" name="description" value="<?= $product['description'] ?>" required><br>
        Price (RM): <input type="number" name="price" value="<?= $product['price'] ?>" required><br>
        Quantity: <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required><br>

        Current Image: <br>
        <img src="<?= $product['image_path'] ?>" width="100"><br><br>

        Change Image: <input type="file" name="product_image" accept="image/*"><br>
        <button type="submit">Save Changes</button>
        </form> 
    </body>
</html>


