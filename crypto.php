<?php
// crypto.php

// Определяем мастер‑ключ из переменной окружения (или используйте библиотеку dotenv)
if (!defined('MASTER_KEY')) {
    define('MASTER_KEY', getenv('MASTER_KEY') ?: 'default_master_key');
}

/**
 * Генерация симметричного ключа на основе пароля и соли.
 */
function generateEncryptionKey($password, $salt) {
    $iterations = 10000;
    return openssl_pbkdf2($password, $salt, 32, $iterations, 'sha256');
}

/**
 * Шифрование данных с использованием AES-256-CBC.
 * Результат – base64‑кодированная строка вида IV:encrypted_data.
 */
function encryptData($data, $key) {
    $method = "AES-256-CBC";
    $ivLength = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLength);
    $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
    return base64_encode($iv . ':' . $encrypted);
}

/**
 * Дешифрование данных с использованием AES-256-CBC.
 * Требует передачи ключа.
 */
function decryptData($data, $key) {
    $decoded = base64_decode($data);
    
    // IV должен быть первых 16 байтов
    $ivLength = openssl_cipher_iv_length("AES-256-CBC");
    $iv = substr($decoded, 0, $ivLength);
    $encryptedData = substr($decoded, $ivLength);

    return openssl_decrypt($encryptedData, "AES-256-CBC", $key, 0, $iv);
}

?>
