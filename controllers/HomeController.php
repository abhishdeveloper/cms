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
            $stmt = $this->db->query("SELECT * FROM products");
            $products = $stmt->fetchAll();

            // Pass products to the view
            require __DIR__ . '/../views/index.php';
        } catch (Exception $e) {
            die("Error fetching products: " . $e->getMessage());
        }
    }
}
