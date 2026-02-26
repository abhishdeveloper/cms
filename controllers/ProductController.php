<?php

class ProductController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function show()
    {
        $id = $_GET['id'] ?? 0;

        // Fetch Product
        $stmt = $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?", [$id]);
        $product = $stmt->fetch();

        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            return;
        }

        // Parse Gallery Images
        $gallery = !empty($product['gallery_images']) ? json_decode($product['gallery_images'], true) : [];
        if (!is_array($gallery)) $gallery = [];
        // Add main image to start of gallery if not empty
        if ($product['image_url']) array_unshift($gallery, $product['image_url']);
        // Ensure unique
        $gallery = array_unique($gallery);

        // Fetch Reviews
        $stmt = $this->db->query("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC", [$id]);
        $reviews = $stmt->fetchAll();

        // Calculate Average Rating
        $avgRating = 0;
        if (count($reviews) > 0) {
            $sum = array_reduce($reviews, fn($carry, $item) => $carry + $item['rating'], 0);
            $avgRating = round($sum / count($reviews), 1);
        }

        // Check if user can review
        $canReview = false;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            // Check if user purchased this product and it is delivered
            $stmt = $this->db->query("
                SELECT count(*) as count
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'completed'
            ", [$userId, $id]);
            $purchaseCount = $stmt->fetch()['count'];

            // Check if already reviewed
            $stmt = $this->db->query("SELECT count(*) as count FROM reviews WHERE user_id = ? AND product_id = ?", [$userId, $id]);
            $reviewCount = $stmt->fetch()['count'];

            if ($purchaseCount > 0 && $reviewCount == 0) {
                $canReview = true;
            }
        }

        // Related Products
        $related = [];
        if ($product['category_id']) {
            $stmt = $this->db->query("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4", [$product['category_id'], $id]);
            $related = $stmt->fetchAll();
        }

        require __DIR__ . '/../views/product_detail.php';
    }

    public function storeReview()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $productId = $_POST['product_id'];
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        // Server-side validation for eligibility can be repeated here for security

        $this->db->query(
            "INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)",
            [$userId, $productId, $rating, $comment]
        );

        header("Location: /product?id=$productId");
    }
}
