<?php

require_once __DIR__ . '/../autoload.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<h1>E-Commerce Backend Initialized</h1>";
    echo "<p>Database connection established successfully.</p>";
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>Failed to connect to the database: " . htmlspecialchars($e->getMessage()) . "</p>";
}
