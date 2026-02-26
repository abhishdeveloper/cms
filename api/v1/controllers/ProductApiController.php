<?php

class ProductApiController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index()
    {
        header('Content-Type: application/json');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock >= 0";
        $params = [];

        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        // PDO requires integers for LIMIT/OFFSET in prepared statements sometimes, or bindValue.
        // But Database::query uses execute($params) which treats everything as strings usually.
        // Let's rely on PDO handling it or disable emulate prepares in Database class (which is already done: ATTR_EMULATE_PREPARES => false).
        // If emulate prepares is false, types matter.

        try {
            // Need to bind params with types for LIMIT/OFFSET if emulate prepares is false
            $pdo = $this->db->getConnection();
            $stmt = $pdo->prepare($sql);

            $paramIndex = 1;
            if ($categoryId) {
                $stmt->bindValue($paramIndex++, $categoryId, PDO::PARAM_INT);
            }
            $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Parse gallery images
            foreach ($products as &$prod) {
                $prod['gallery_images'] = json_decode($prod['gallery_images'] ?? '[]', true);
            }

            echo json_encode(['success' => true, 'data' => $products, 'page' => $page, 'limit' => $limit]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        $stmt = $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?", [$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        $product['gallery_images'] = json_decode($product['gallery_images'] ?? '[]', true);

        echo json_encode(['success' => true, 'data' => $product]);
    }
}
