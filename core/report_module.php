 <?php

/**
 * core/report_module.php
 *
 * Handles basic reporting functionalities.
 */

require_once 'sale_module.php';

/**
 * Generates a basic daily sales report.
 *
 * @return array An associative array containing the total sales for the current day,
 * or an empty array if there are no sales today or an error occurred.
 */
function get_daily_sales_report(): array
{
    $total_sales = 0;
    $sales = get_all_sales();
    $today = date('Y-m-d');

    foreach ($sales as $sale) {
        $sale_date = substr($sale['timestamp'], 0, 10); // Extract the date part
        if ($sale_date === $today) {
            $total_sales += $sale['total_amount'];
        }
    }

    return ['date' => $today, 'total_sales' => round($total_sales, 2)];
}

/**
 * Retrieves all sales transactions (potentially for displaying a sales history).
 * This is essentially a wrapper around get_all_sales() for clarity in the reporting context.
 *
 * @return array An array of all sales transactions.
 */
function get_sales_history(): array
{
    return get_all_sales();
}

?>
