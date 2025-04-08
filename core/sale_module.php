<?php

/**
 * core/sale_module.php
 *
 * Handles sale transaction operations.
 */

require_once 'data_handler.php';
require_once 'product_module.php';

const SALES_FILE = 'sales.json';

/**
 * Records a new sale transaction.
 *
 * @param array $items An array of items in the sale, where each item is an associative array
 * with 'product_id' and 'quantity'.
 * @param float $total_amount The total amount of the sale.
 * @param string $payment_method The payment method used.
 * @param float|null $cash_received The amount of cash received (if applicable).
 * @return bool True on success, false on error.
 */
function record_sale(array $items, float $total_amount, string $payment_method, ?float $cash_received = null): bool
{
    $sales = read_json_data(SALES_FILE) ?? [];

    $sale_record = [
        'transaction_id' => uniqid('TRANS_'),
        'timestamp' => date('Y-m-d H:i:s'),
        'items' => [],
        'total_amount' => $total_amount,
        'payment_method' => $payment_method,
        'cash_received' => $cash_received,
        'change_due' => ($payment_method === 'Cash' && $cash_received !== null) ? round($cash_received - $total_amount, 2) : null,
    ];

    foreach ($items as $item_data) {
        $product = get_product_by_id($item_data['product_id']);
        if ($product) {
            $sale_record['items'][] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
                'quantity' => $item_data['quantity'],
                'price' => $product['price'],
            ];
            // Update the stock quantity
            update_stock($product['id'], -$item_data['quantity']);
        } else {
            error_log("Error: Product not found during sale recording: " . $item_data['product_id']);
            return false; // Abort sale if a product is not found
        }
    }

    $sales[] = $sale_record;
    return write_json_data(SALES_FILE, $sales);
}

/**
 * Retrieves all recorded sales transactions.
 *
 * @return array An array of all sales transactions, or an empty array if there are no sales or an error occurred.
 */
function get_all_sales(): array
{
    $sales = read_json_data(SALES_FILE);
    return $sales ?? [];
}

/**
 * Retrieves a specific sale transaction by its ID.
 *
 * @param string $transaction_id The ID of the sale transaction to retrieve.
 * @return array|null An associative array representing the sale transaction, or null if not found or an error occurred.
 */
function get_sale_by_id(string $transaction_id): ?array
{
    $sales = get_all_sales();
    if ($sales) {
        foreach ($sales as $sale) {
            if ($sale['transaction_id'] === $transaction_id) {
                return $sale;
            }
        }
    }
    return null;
}

?>