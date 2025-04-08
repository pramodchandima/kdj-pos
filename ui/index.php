 <?php

/**
 * ui/index.php
 *
 * Main POS interface for processing sales transactions.
 */

require_once '../core/product_module.php';
require_once '../core/sale_module.php';

// Initialize cart as an array in the session if it doesn't exist
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$products = get_all_products();
$error_message = '';
$success_message = '';

// Handle adding items to the cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    if ($quantity > 0) {
        $product = get_product_by_id($product_id);
        if ($product) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                ];
            }
        } else {
            $error_message = 'Product not found.';
        }
    } else {
        $error_message = 'Quantity must be greater than zero.';
    }
}

// Handle updating cart quantities
if (isset($_POST['update_cart']) && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        $quantity = intval($quantity);
        if ($quantity > 0) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
        } elseif ($quantity === 0 && isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// Handle removing item from cart
if (isset($_POST['remove_from_cart']) && isset($_POST['remove_product_id'])) {
    $product_id_to_remove = $_POST['remove_product_id'];
    if (isset($_SESSION['cart'][$product_id_to_remove])) {
        unset($_SESSION['cart'][$product_id_to_remove]);
    }
}

// Handle processing the sale
if (isset($_POST['process_sale'])) {
    if (!empty($_SESSION['cart'])) {
        $total_amount = 0;
        $items_to_sell = [];
        foreach ($_SESSION['cart'] as $item) {
            $total_amount += $item['price'] * $item['quantity'];
            $items_to_sell[] = ['product_id' => $item['id'], 'quantity' => $item['quantity']];
        }

        $payment_method = $_POST['payment_method'];
        $cash_received = ($payment_method === 'Cash' && isset($_POST['cash_received'])) ? floatval($_POST['cash_received']) : null;

        if ($payment_method === 'Cash' && $cash_received < $total_amount) {
            $error_message = 'Cash received is less than the total amount.';
        } else {
            if (record_sale($items_to_sell, $total_amount, $payment_method, $cash_received)) {
                $_SESSION['cart'] = []; // Clear the cart after a successful sale
                $success_message = 'Sale processed successfully!';
            } else {
                $error_message = 'Error processing the sale.';
            }
        }
    } else {
        $error_message = 'Cart is empty.';
    }
}

// Calculate total for the cart
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-md p-6">
        <h1 class="text-2xl font-semibold mb-4">Point of Sale</h1>

        <?php if ($error_message): ?>
            <div class="bg-red-200 text-red-800 py-2 px-4 rounded mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="bg-green-200 text-green-800 py-2 px-4 rounded mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Add Product</h2>
            <form method="post" class="flex items-center space-x-2">
                <select name="product_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']) . ' ($' . number_format($product['price'], 2) . ')'; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantity" value="1" min="1" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-24 sm:text-sm border-gray-300 rounded-md">
                <button type="submit" name="add_to_cart" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add to Cart</button>
            </form>
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold mb-2">Shopping Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="post">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 p-2">Product</th>
                                <th class="border border-gray-300 p-2">Price</th>
                                <th class="border border-gray-300 p-2">Quantity</th>
                                <th class="border border-gray-300 p-2">Total</th>
                                <th class="border border-gray-300 p-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <tr>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="border border-gray-300 p-2">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="border border-gray-300 p-2">
                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0" class="w-20 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                                    </td>
                                    <td class="border border-gray-300 p-2">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    <td class="border border-gray-300 p-2">
                                        <button type="submit" name="remove_from_cart" value="Remove" class="text-red-600 hover:text-red-800 focus:outline-none">Remove</button>
                                        <input type="hidden" name="remove_product_id" value="<?php echo $item['id']; ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="border border-gray-300 p-2 font-semibold text-right">Total:</td>
                                <td class="border border-gray-300 p-2 font-semibold">$<?php echo number_format($cart_total, 2); ?></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="border-t border-gray-300 p-2 text-right">
                                    <button type="submit" name="update_cart" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mr-2">Update Cart</button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-2">Process Sale</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <form method="post">
                    <div class="mb-2">
                        <label for="payment_method" class="block text-gray-700 text-sm font-bold mb-2">Payment Method:</label>
                        <select name="payment_method" id="payment_method" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="Cash">Cash</option>
                            <option value="Card">Card</option>
                        </select>
                    </div>
                    <div id="cash_payment_fields" class="mb-2">
                        <label for="cash_received" class="block text-gray-700 text-sm font-bold mb-2">Cash Received:</label>
                        <input type="number" name="cash_received" step="0.01" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <button type="submit" name="process_sale" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Process Sale</button>
                </form>
                <script>
                    const paymentMethodSelect = document.getElementById('payment_method');
                    const cashPaymentFields = document.getElementById('cash_payment_fields');

                    paymentMethodSelect.addEventListener('change', function() {
                        if (this.value === 'Cash') {
                            cashPaymentFields.style.display = 'block';
                        } else {
                            cashPaymentFields.style.display = 'none';
                        }
                    });

                    // Initially hide cash payment fields if 'Card' is selected
                    if (paymentMethodSelect.value !== 'Cash') {
                        cashPaymentFields.style.display = 'none';
                    }
                </script>
            <?php else: ?>
                <p>Please add items to the cart to process a sale.</p>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <a href="inventory.php" class="text-blue-500 hover:underline">Manage Inventory</a> |
            <a href="reports.php" class="text-blue-500 hover:underline">View Reports</a>
        </div>
    </div>
</body>
</html>
