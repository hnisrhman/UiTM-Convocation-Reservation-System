<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login_admin.php');
    exit();
}
include 'db_connect.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function redirectInventory() { header('Location: manage_inventory.php'); exit(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = trim($_POST['product_id'] ?? '');
    if ($id !== '') {
        $statement = mysqli_prepare($conn, 'DELETE FROM products WHERE id = ?');
        mysqli_stmt_bind_param($statement, 's', $id);
        mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
        $_SESSION['inventory_message'] = 'Product removed from inventory.';
    }
    redirectInventory();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name'] ?? '');
    $type = $_POST['product_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $allowedTypes = ['robe', 'hood', 'cap', 'package'];
    $imagePath = '';

    if ($name === '' || $description === '' || !in_array($type, $allowedTypes, true) || !is_numeric($price) || (float) $price < 0 || filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 0) {
        $error = 'Please provide a name, type, description, valid price and stock quantity.';
    } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'The product image could not be uploaded. Please try again.';
        } else {
            $imageInfo = @getimagesize($_FILES['product_image']['tmp_name']);
            $allowedMimeTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mimeType = $imageInfo['mime'] ?? '';
            if (!isset($allowedMimeTypes[$mimeType])) {
                $error = 'Please upload a JPG, PNG or WEBP image.';
            } else {
                $fileName = 'product_' . bin2hex(random_bytes(8)) . '.' . $allowedMimeTypes[$mimeType];
                $destination = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $fileName;
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                    $imagePath = 'images/' . $fileName;
                } else {
                    $error = 'The product image could not be saved. Please try again.';
                }
            }
        }
    }

    if ($error === '') {
        $productId = 'PRD' . strtoupper(uniqid());
        $formattedPrice = number_format((float) $price, 2, '.', '');
        $formattedQuantity = (string) (int) $quantity;
        $statement = mysqli_prepare($conn, 'INSERT INTO products (id, product_name, product_type, description, price, image_path, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'sssssss', $productId, $name, $type, $description, $formattedPrice, $imagePath, $formattedQuantity);
        $saved = mysqli_stmt_execute($statement);
        mysqli_stmt_close($statement);
        if ($saved) {
            $_SESSION['inventory_message'] = 'Product added to inventory successfully.';
            redirectInventory();
        }
        $error = 'Unable to add the product. Please try again.';
    }
}

$result = mysqli_query($conn, 'SELECT * FROM products ORDER BY product_type ASC, product_name ASC');
$products = [];
if ($result) while ($row = mysqli_fetch_assoc($result)) $products[] = $row;
$totalItems = count($products);
$totalUnits = array_sum(array_map(fn($product) => (int) $product['quantity'], $products));
$lowStock = count(array_filter($products, fn($product) => (int) $product['quantity'] <= 5));
$message = $_SESSION['inventory_message'] ?? '';
unset($_SESSION['inventory_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Inventory | UiTM RobeReserve</title>
  <style>
    :root { --navy:#062b55; --navy-dark:#031d3b; --gold:#f6bf19; --ink:#18273a; --muted:#607086; --line:#d9e2ec; --page:#f2f6fa; --success:#20744a; --warning:#9b6500; --danger:#a52828; }
    * { box-sizing:border-box; }body { margin:0; color:var(--ink); background:var(--page); font-family:"Segoe UI",Arial,sans-serif; }.site-header { background:var(--navy-dark); border-bottom:4px solid var(--gold); color:#fff; }.header-inner { width:min(1240px,calc(100% - 40px)); min-height:86px; margin:auto; display:flex; align-items:center; gap:18px; }.brand { display:flex; align-items:center; gap:13px; margin-right:auto; color:#fff; text-decoration:none; }.brand img { width:49px; height:49px; padding:4px; background:#fff; border-radius:8px; object-fit:contain; }.brand strong { display:block; font-size:1.08rem; }.brand span span { display:block; margin-top:2px; color:#cbd9e7; font-size:.8rem; }nav { display:flex; gap:3px; align-items:center; }nav a { padding:9px 11px; border-radius:6px; color:#e7eff8; font-size:.9rem; font-weight:650; text-decoration:none; }nav a:hover,nav a:focus-visible,nav a.active { color:var(--gold); background:rgba(255,255,255,.09); outline:none; }.logout { border:1px solid rgba(255,255,255,.28); }
    main { width:min(1240px,calc(100% - 40px)); margin:auto; padding:42px 0 70px; }.heading { display:flex; justify-content:space-between; align-items:end; gap:20px; margin-bottom:26px; }.eyebrow { margin:0 0 8px; color:#8b6505; font-size:.78rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }h1 { margin:0; color:var(--navy); font-size:clamp(2rem,4vw,2.7rem); }.heading p:last-child { margin:11px 0 0; color:var(--muted); line-height:1.55; }.dashboard-link { color:var(--navy); font-size:.92rem; font-weight:800; text-decoration:none; }.dashboard-link:hover { color:#8b6505; }.stats { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:26px; }.stat { padding:19px 21px; border-radius:11px; background:#fff; box-shadow:0 6px 17px rgba(22,48,77,.07); }.stat span { display:block; color:var(--muted); font-size:.86rem; font-weight:700; }.stat strong { display:block; margin-top:5px; color:var(--navy); font-size:1.6rem; }
    .layout { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(300px,.7fr); align-items:start; gap:24px; }.card { padding:26px; border-radius:12px; background:#fff; box-shadow:0 7px 20px rgba(22,48,77,.08); }.card h2 { margin:0; color:var(--navy); font-size:1.28rem; }.card > p { margin:6px 0 20px; color:var(--muted); }.table-wrap { overflow-x:auto; }.inventory-table { width:100%; min-width:690px; border-collapse:collapse; }.inventory-table th { padding:0 10px 12px; border-bottom:1px solid var(--line); color:#50647a; font-size:.73rem; letter-spacing:.05em; text-align:left; text-transform:uppercase; }.inventory-table td { padding:13px 10px; border-bottom:1px solid #eaf0f5; color:#42536a; font-size:.9rem; vertical-align:middle; }.inventory-table tr:last-child td { border-bottom:0; }.product-thumb,.image-fallback { width:48px; height:48px; border-radius:7px; object-fit:cover; background:#edf3f8; }.image-fallback { display:grid; place-items:center; color:var(--muted); font-size:.7rem; font-weight:800; }.product-name { color:var(--navy); font-weight:800; }.product-desc { display:block; max-width:190px; margin-top:3px; color:var(--muted); font-size:.79rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }.type { display:inline-block; padding:4px 7px; border-radius:14px; color:#7b5d0b; background:#fff0bf; font-size:.72rem; font-weight:800; text-transform:capitalize; }.stock { display:inline-block; padding:5px 8px; border-radius:15px; font-size:.75rem; font-weight:800; }.stock.good { color:var(--success); background:#e2f5e9; }.stock.low { color:var(--warning); background:#fff0cf; }.actions { display:flex; gap:7px; align-items:center; }.action { padding:7px 9px; border:1px solid #bdcad8; border-radius:5px; color:var(--navy); background:#fff; font:inherit; font-size:.79rem; font-weight:750; text-decoration:none; cursor:pointer; }.action:hover { background:#eef4fa; }.action.delete { color:var(--danger); }.action.delete:hover { background:#fff1f1; }
    .form-card { border-top:5px solid var(--gold); }.form-card h2 { margin-bottom:7px; }.form-card > p { margin-bottom:21px; line-height:1.5; }.field + .field { margin-top:16px; }label { display:block; margin-bottom:7px; color:#34465d; font-size:.9rem; font-weight:750; }input,select,textarea { width:100%; min-height:44px; padding:9px 11px; border:1px solid #bdcad8; border-radius:7px; color:var(--ink); background:#fff; font:inherit; }textarea { min-height:78px; resize:vertical; }input:focus,select:focus,textarea:focus { border-color:#2563a4; box-shadow:0 0 0 3px rgba(37,99,164,.13); outline:none; }.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }.file-help { margin:7px 0 0; color:var(--muted); font-size:.78rem; line-height:1.4; }.save-button { width:100%; min-height:47px; margin-top:22px; border:0; border-radius:7px; color:var(--navy-dark); background:var(--gold); font:inherit; font-weight:800; cursor:pointer; }.save-button:hover { background:#ffcf42; }.alert { margin-bottom:17px; padding:13px 15px; border-radius:8px; font-size:.91rem; font-weight:700; }.alert.success { color:var(--success); background:#e3f5e9; }.alert.error { color:var(--danger); background:#fde9e9; }.empty { padding:44px 20px; color:var(--muted); text-align:center; }
    @media(max-width:980px) { .layout { grid-template-columns:1fr; }.header-inner { min-height:auto; padding:15px 0; flex-wrap:wrap; }nav { width:100%; overflow-x:auto; } }.form-card { max-width:none; }@media(max-width:650px) { .header-inner,main { width:min(100% - 28px,1240px); }main { padding-top:29px; }.heading { align-items:start; flex-direction:column; }.stats { grid-template-columns:1fr; }.card { padding:21px; }.form-grid { grid-template-columns:1fr; } }
  </style>
</head>
<body>
  <header class="site-header"><div class="header-inner"><a class="brand" href="dashboard_admin.php"><img src="images/logo_uitm.png" alt="UiTM"><span><strong>UiTM RobeReserve</strong><span>Administration portal</span></span></a><nav aria-label="Admin navigation"><a href="dashboard_admin.php">Dashboard</a><a href="manage_reservations.php">Reservations</a><a class="active" href="manage_inventory.php">Inventory</a><a href="manage_users.php">Users</a><a href="profile_admin.php">Profile</a><a class="logout" href="logout.php">Logout</a></nav></div></header>
  <main>
    <section class="heading"><div><p class="eyebrow">Attire catalogue</p><h1>Manage inventory</h1><p>Keep attire products, prices and stock quantities accurate for graduates.</p></div><a class="dashboard-link" href="dashboard_admin.php">← Back to dashboard</a></section>
    <section class="stats" aria-label="Inventory totals"><div class="stat"><span>Product types</span><strong><?= e($totalItems) ?></strong></div><div class="stat"><span>Total items in stock</span><strong><?= e($totalUnits) ?></strong></div><div class="stat"><span>Low-stock products</span><strong><?= e($lowStock) ?></strong></div></section>
    <?php if ($message): ?><div class="alert success" role="status"><?= e($message) ?></div><?php endif; ?><?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <div class="layout"><section class="card"><h2>Current inventory</h2><p>Review products and take action when quantities need attention.</p><div class="table-wrap"><table class="inventory-table"><thead><tr><th>Product</th><th>Type</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php if ($products): foreach ($products as $product): $quantity = (int) $product['quantity']; ?><tr><td><?php if (!empty($product['image_path'])): ?><img class="product-thumb" src="<?= e($product['image_path']) ?>" alt=""><span style="display:inline-block;vertical-align:middle;margin-left:10px"><span class="product-name"><?= e($product['product_name']) ?></span><span class="product-desc"><?= e($product['description']) ?></span></span><?php else: ?><span class="image-fallback" style="display:inline-grid;vertical-align:middle">ITEM</span><span style="display:inline-block;vertical-align:middle;margin-left:10px"><span class="product-name"><?= e($product['product_name']) ?></span><span class="product-desc"><?= e($product['description']) ?></span></span><?php endif; ?></td><td><span class="type"><?= e($product['product_type']) ?></span></td><td><strong>RM <?= e($product['price']) ?></strong></td><td><span class="stock <?= $quantity <= 5 ? 'low' : 'good' ?>"><?= e($quantity) ?> <?= $quantity === 1 ? 'unit' : 'units' ?></span></td><td><div class="actions"><a class="action" href="edit_product.php?id=<?= urlencode($product['id']) ?>">Edit</a><form method="post" onsubmit="return confirm('Remove <?= e($product['product_name']) ?> from inventory?');"><input type="hidden" name="product_id" value="<?= e($product['id']) ?>"><button class="action delete" type="submit" name="delete_product">Delete</button></form></div></td></tr><?php endforeach; else: ?><tr><td class="empty" colspan="5">No products are available in inventory yet.</td></tr><?php endif; ?></tbody></table></div></section>
      <aside class="card form-card"><h2>Add a product</h2><p>Create a new attire or package option for graduates.</p><form method="post" enctype="multipart/form-data"><div class="field"><label for="productName">Product name</label><input id="productName" name="product_name" type="text" required></div><div class="field"><label for="productType">Product type</label><select id="productType" name="product_type" required><option value="">Select type</option><option value="robe">Robe</option><option value="hood">Hood</option><option value="cap">Cap</option><option value="package">Package</option></select></div><div class="field"><label for="description">Description</label><textarea id="description" name="description" required placeholder="Briefly describe what is included."></textarea></div><div class="form-grid"><div class="field"><label for="price">Price (RM)</label><input id="price" name="price" type="number" min="0" step="0.01" required></div><div class="field"><label for="quantity">Stock quantity</label><input id="quantity" name="quantity" type="number" min="0" step="1" required></div></div><div class="field"><label for="productImage">Product image <span style="color:var(--muted);font-weight:500">(optional)</span></label><input id="productImage" name="product_image" type="file" accept="image/jpeg,image/png,image/webp"><p class="file-help">JPG, PNG or WEBP. A clear product image helps graduates choose confidently.</p></div><button class="save-button" type="submit" name="add_product">Add product to inventory</button></form></aside>
    </div>
  </main>
</body>
</html>
