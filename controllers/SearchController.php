<?php

class SearchController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        $query = $_GET['q'] ?? '';
        $categoryId = $_GET['category'] ?? '';
        $minPrice = $_GET['min_price'] ?? '';
        $maxPrice = $_GET['max_price'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';

        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        // Keyword Search
        if (!empty($query)) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        // Category Filter
        if (!empty($categoryId)) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }

        // Price Range
        if (!empty($minPrice)) {
            $sql .= " AND price >= ?";
            $params[] = $minPrice;
        }
        if (!empty($maxPrice)) {
            $sql .= " AND price <= ?";
            $params[] = $maxPrice;
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY price DESC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY created_at DESC";
                break;
        }

        $stmt = $this->db->query($sql, $params);
        $products = $stmt->fetchAll();

        // Fetch categories for sidebar
        $catStmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $catStmt->fetchAll();

        require __DIR__ . '/../views/search.php';
    }

    public function category()
    {
        $_GET['category'] = $_GET['id'] ?? '';
        $this->index();
    }
}
