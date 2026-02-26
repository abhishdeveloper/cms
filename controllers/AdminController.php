<?php

class AdminController
{
    private $db;
    private $whatsapp;

    public function __construct()
    {
        AuthMiddleware::requireAdmin();
        $this->db = Database::getInstance();
        $this->whatsapp = new WhatsAppService();
    }

    public function dashboard()
    {
        $stmt = $this->db->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'paid'");
        $revenue = $stmt->fetch()['total_revenue'] ?? 0;

        $stmt = $this->db->query("SELECT COUNT(*) as pending_count FROM orders WHERE order_status = 'pending'");
        $pending = $stmt->fetch()['pending_count'] ?? 0;

        $stmt = $this->db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
        $recentOrders = $stmt->fetchAll();

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function products()
    {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY created_at DESC");
        $products = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/products.php';
    }

    public function orders()
    {
        $stmt = $this->db->query("SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/orders.php';
    }

    public function orderDetails()
    {
        $id = $_GET['id'] ?? 0;

        $stmt = $this->db->query("SELECT o.*, u.name as user_name, u.phone as user_phone, u.email as user_email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?", [$id]);
        $order = $stmt->fetch();

        if (!$order) {
            header('Location: /admin/orders');
            exit;
        }

        $stmt = $this->db->query("SELECT oi.*, p.name as product_name, p.image_url FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$id]);
        $items = $stmt->fetchAll();

        require __DIR__ . '/../views/admin/order_details.php';
    }

    public function coupons()
    {
        $stmt = $this->db->query("SELECT * FROM coupons ORDER BY created_at DESC");
        $coupons = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/coupons.php';
    }

    public function updateOrderStatus()
    {
        $id = $_POST['id'];
        $status = $_POST['status'];

        $this->db->query("UPDATE orders SET order_status = ? WHERE id = ?", [$status, $id]);

        if ($status === 'shipped') {
            $stmt = $this->db->query("SELECT u.phone, u.name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?", [$id]);
            $user = $stmt->fetch();
            if ($user) {
                $this->whatsapp->sendMessage($user['phone'], "Hello {$user['name']}, your order #{$id} has been shipped!");
            }
        }

        header('Location: /admin/orders');
    }

    public function storeProduct()
    {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $stock = $_POST['stock'];
        $imageUrl = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
                $imageUrl = '/uploads/' . $filename;
            }
        }

        $this->db->query(
            "INSERT INTO products (name, price, description, stock, image_url) VALUES (?, ?, ?, ?, ?)",
            [$name, $price, $description, $stock, $imageUrl]
        );

        header('Location: /admin/products');
    }

    public function deleteProduct()
    {
        $id = $_POST['id'];
        $this->db->query("DELETE FROM products WHERE id = ?", [$id]);
        header('Location: /admin/products');
    }

    public function storeCoupon()
    {
        $code = $_POST['code'];
        $type = $_POST['type'];
        $value = $_POST['value'];
        $min = $_POST['min_cart_value'];
        $expiry = $_POST['expires_at'];
        $limit = $_POST['usage_limit'] ?: null;

        $this->db->query(
            "INSERT INTO coupons (code, type, discount_value, min_cart_value, expires_at, usage_limit) VALUES (?, ?, ?, ?, ?, ?)",
            [$code, $type, $value, $min, $expiry, $limit]
        );

        header('Location: /admin/coupons');
    }

    public function deleteCoupon()
    {
        $id = $_POST['id'];
        $this->db->query("DELETE FROM coupons WHERE id = ?", [$id]);
        header('Location: /admin/coupons');
    }
}
