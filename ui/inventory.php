<?php

/**
 * ui/inventory.php
 *
 * Interface for managing product inventory.
 */

require_once '../core/product_module.php';

$products = get_all_products();
$error_message = '';
$success_message = '';

// Handle adding a new product
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    if (!empty($name) && $price >= 0 && $stock >= 0) {
        if (add_new_product($name, $price, $stock)) {
            $success_message = 'Product added successfully!';
            $products = get_all_products(); // Reload products after adding
        } else {
            $error_message = 'Error adding product.';
        }
    } else {
        $error_message = 'Please fill in all fields with valid values.';
    }
}

// Handle updating an existing product
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);

    if (!empty($name) && $price >= 0 && $stock >= 0 && !empty($product_id)) {
        if (update_product($product_id, $name, $price, $stock)) {
            $success_message = 'Product updated successfully!';
            $products = get_all_products(); // Reload products after updating
        } else {
            $error_message = 'Error updating product or product not found.';
        }
    } else {
        $error_message = 'Please fill in all fields with valid values for updating.';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-md p-6">
        <h1 class="text-2xl font-semibold mb-4">Inventory Management</h1>

        <?php if ($error_message): ?>
            <div class="bg-red-200 text-red-800 py-2 px-4 rounded mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="bg-green-200 text-green-800 py-2 px-4 rounded mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Add New Product</h2>
            <form method="post" class="grid grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                    <input type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price:</label>
                    <input type="number" name="price" id="price" step="0.01" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <label for="stock" class="block text-gray-700 text-sm font-bold mb-2">Stock:</label>
                    <input type="number" name="stock" id="stock" min="0" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                </div>
                <div>
                    <button type="submit" name="add_product" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Product</button>
                </div>
            </form>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-2">Current Inventory</h2>
            <?php if (!empty($products)): ?>
                <table class="w-full border-collapse border border-gray-300">
                    <thead>
                        <tr>
                            <th class="border border-gray-300 p-2">ID</th>
                            <th class="border border-gray-300 p-2">Name</th>
                            <th class="border border-gray-300 p-2">Price</th>
                            <th class="border border-gray-300 p-2">Stock</th>
                            <th class="border border-gray-300 p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($product['id']); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td class="border border-gray-300 p-2">$<?php echo number_format($product['price'], 2); ?></td>
                                <td class="border border-gray-300 p-2"><?php echo $product['stock']; ?></td>
                                <td class="border border-gray-300 p-2">
                                    <details>
                                        <summary class="text-blue-500 hover:underline cursor-pointer">Edit</summary>
                                        <form method="post" class="mt-2 grid grid-cols-1 gap-2">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <div>
                                                <label for="edit_name_<?php echo $product['id']; ?>" class="block text-gray-700 text-sm font-bold mb-1">Name:</label>
                                                <input type="text" name="name" id="edit_name_<?php echo $product['id']; ?>" value="<?php echo htmlspecialchars($product['name']); ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <div>
                                                <label for="edit_price_<?php echo $product['id']; ?>" class="block text-gray-700 text-sm font-bold mb-1">Price:</label>
                                                <input type="number" name="price" id="edit_price_<?php echo $product['id']; ?>" step="0.01" value="<?php echo $product['price']; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <div>
                                                <label for="edit_stock_<?php echo $product['id']; ?>" class="block text-gray-700 text-sm font-bold mb-1">Stock:</label>
                                                <input type="number" name="stock" id="edit_stock_<?php echo $product['id']; ?>" min="0" value="<?php echo $product['stock']; ?>" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                            </div>
                                            <button type="submit" name="update_product" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update</button>
                                        </form>
                                    </details>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No products in inventory.</p>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <a href="index.php" class="text-blue-500 hover:underline">Back to POS</a> |
            <a href="reports.php" class="text-blue-500 hover:underline">View Reports</a>
        </div>
    </div>
</body>
</html>