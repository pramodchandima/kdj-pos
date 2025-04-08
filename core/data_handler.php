<?php

/**
 * core/data_handler.php
 *
 * Handles reading and writing data to JSON files.
 */

/**
 * Reads data from a JSON file.
 *
 * @param string $filename The name of the JSON file (relative to the data directory).
 * @return array|null Returns an associative array of the data, or null on error.
 */
function read_json_data(string $filename): ?array
{
    $filepath = __DIR__ . '/../data/' . $filename;

    if (!file_exists($filepath)) {
        error_log("Error: File not found: $filepath");
        return null;
    }

    $json_data = file_get_contents($filepath);

    if ($json_data === false) {
        error_log("Error: Could not read file: $filepath");
        return null;
    }

    $data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error: JSON decode error in file: $filepath - " . json_last_error_msg());
        return null;
    }

    return $data;
}

/**
 * Writes data to a JSON file.
 *
 * @param string $filename The name of the JSON file (relative to the data directory).
 * @param array $data The data to write to the file.
 * @return bool True on success, false on error.
 */
function write_json_data(string $filename, array $data): bool
{
    $filepath = __DIR__ . '/../data/' . $filename;

    $json_encoded_data = json_encode($data, JSON_PRETTY_PRINT);

    if ($json_encoded_data === false) {
        error_log("Error: JSON encode error for file: $filepath - " . json_last_error_msg());
        return false;
    }

    $result = file_put_contents($filepath, $json_encoded_data);

    if ($result === false) {
        error_log("Error: Could not write to file: $filepath");
        return false;
    }

    return true;
}

?>