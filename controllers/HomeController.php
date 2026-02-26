<?php

class HomeController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        try {
            // Fetch categories for menu/display
            $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
            $categories = $stmt->fetchAll();

            // Fetch featured products (random 8)
            $stmt = $this->db->query("SELECT * FROM products ORDER BY RAND() LIMIT 8");
            $products = $stmt->fetchAll();

            // Pass data to the view
            require __DIR__ . '/../views/index.php';
        } catch (Exception $e) {
            die("Error fetching home data: " . $e->getMessage());
        }
    }
}
