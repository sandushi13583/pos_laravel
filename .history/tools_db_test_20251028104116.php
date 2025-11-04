<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ultimate_pos', 'root', '');
    echo "DB_OK\n";
} catch (Exception $e) {
    echo "DB_ERR: " . $e->getMessage() . "\n";
}
