<?php

/**
 * core/product_module.php
 *
 * Handles product-related operations.
 */

require_once 'data_handler.php';

const PRODUCTS_FILE = 'products.json';

/**
 * Retrieves all products from the inventory.
 *
 * @return array An array of all products, or an empty array if there are no products or an error occurred.
 */
function get_all_products(): array
{
    $products = read_json_data(PRODUCTS_FILE);
    return $products ?? [];
}

/**
 * Retrieves a single product by its ID.
 *
 * @param string $product_id The ID of the product to retrieve.
 * @return array|null An associative array representing the product, or null if not found or an error occurred.
 */
function get_product_by_id(string $product_id): ?array
{
    $products = read_json_data(PRODUCTS_FILE);
    if ($products) {
        foreach ($products as $product) {
            if ($product['id'] === $product_id) {
                return $product;
            }
        }
    }
    return null;
}

/**
 * Adds a new product to the inventory.
 *
 * @param string $name The name of the product.
 * @param float $price The price of the product.
 * @param int $stock The initial stock quantity of the product.
 * @param string|null $id Optional product ID. If null, a unique ID will be generated.
 * @return bool True on success, false on error.
 */
function add_new_product(string $name, float $price, int $stock, ?string $id = null): bool
{
    $products = get_all_products();

    $new_product = [
        'id' => $id ?? uniqid('PROD_'),
        'name' => $name,
        'price' => $price,
        'stock' => $stock,
    ];

    $products[] = $new_product;
    return write_json_data(PRODUCTS_FILE, $products);
}

/**
 * Updates an existing product in the inventory.
 *
 * @param string $product_id The ID of the product to update.
 * @param string $name The new name of the product.
 * @param float $price The new price of the product.
 * @param int $stock The new stock quantity of the product.
 * @return bool True on success, false if the product is not found or an error occurred.
 */
function update_product(string $product_id, string $name, float $price, int $stock): bool
{
    $products = get_all_products();
    $updated = false;

    foreach ($products as &$product) {
        if ($product['id'] === $product_id) {
            $product['name'] = $name;
            $product['price'] = $price;
            $product['stock'] = $stock;
            $updated = true;
            break;
        }
    }

    if ($updated) {
        return write_json_data(PRODUCTS_FILE, $products);
    }

    return false;
}

/**
 * Updates the stock quantity of a product.
 *
 * @param string $product_id The ID of the product to update.
 * @param int $quantity_change The change in stock quantity (positive for adding, negative for selling).
 * @return bool True on success, false if the product is not found or the update failed.
 */
function update_stock(string $product_id, int $quantity_change): bool
{
    $product = get_product_by_id($product_id);
    if ($product) {
        $new_stock = max(0, $product['stock'] + $quantity_change); // Ensure stock doesn't go below 0
        return update_product($product_id, $product['name'], $product['price'], $new_stock);
    }
    return false;
}

?>