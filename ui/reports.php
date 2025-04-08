 <?php

/**
 * ui/reports.php
 *
 * Interface for viewing basic sales reports.
 */

require_once '../core/report_module.php';

$daily_sales = get_daily_sales_report();
$sales_history = get_sales_history();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-md p-6">
        <h1 class="text-2xl font-semibold mb-4">Sales Reports</h1>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Daily Sales</h2>
            <div class="bg-gray-200 rounded-md p-4">
                <?php if ($daily_sales): ?>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($daily_sales['date']); ?></p>
                    <p><strong>Total Sales:</strong> $<?php echo number_format($daily_sales['total_sales'], 2); ?></p>
                <?php else: ?>
                    <p>No sales recorded for today.</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <h2 class="text-lg font-semibold mb-2">Sales History</h2>
            <?php if (!empty($sales_history)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-300">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 p-2">Transaction ID</th>
                                <th class="border border-gray-300 p-2">Timestamp</th>
                                <th class="border border-gray-300 p-2">Items Sold</th>
                                <th class="border border-gray-300 p-2">Total Amount</th>
                                <th class="border border-gray-300 p-2">Payment Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_history as $sale): ?>
                                <tr>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($sale['transaction_id']); ?></td>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($sale['timestamp']); ?></td>
                                    <td class="border border-gray-300 p-2">
                                        <ul>
                                            <?php foreach ($sale['items'] as $item): ?>
                                                <li><?php echo htmlspecialchars($item['name']) . ' x ' . $item['quantity']; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td class="border border-gray-300 p-2">$<?php echo number_format($sale['total_amount'], 2); ?></td>
                                    <td class="border border-gray-300 p-2"><?php echo htmlspecialchars($sale['payment_method']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No sales history available.</p>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <a href="index.php" class="text-blue-500 hover:underline">Back to POS</a> |
            <a href="inventory.php" class="text-blue-500 hover:underline">Manage Inventory</a>
        </div>
    </div>
</body>
</html>
