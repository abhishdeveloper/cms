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
        $stmt = $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
        $products = $stmt->fetchAll();
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();
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

    public function categories()
    {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/categories.php';
    }

    public function storeCategory()
    {
        $name = $_POST['name'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $imageUrl = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('cat_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
                $imageUrl = '/uploads/' . $filename;
            }
        }

        $this->db->query("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)", [$name, $slug, $imageUrl]);
        header('Location: /admin/categories');
    }

    public function deleteCategory()
    {
        $id = $_POST['id'];
        $this->db->query("DELETE FROM categories WHERE id = ?", [$id]);
        header('Location: /admin/categories');
    }

    public function editCategory()
    {
        $id = $_GET['id'] ?? 0;
        $stmt = $this->db->query("SELECT * FROM categories WHERE id = ?", [$id]);
        $category = $stmt->fetch();

        if (!$category) {
            header('Location: /admin/categories');
            exit;
        }

        require __DIR__ . '/../views/admin/category_edit.php';
    }

    public function updateCategory()
    {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        // Fetch existing image
        $stmt = $this->db->query("SELECT image_url FROM categories WHERE id = ?", [$id]);
        $imageUrl = $stmt->fetch()['image_url'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('cat_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
                $imageUrl = '/uploads/' . $filename;
            }
        }

        $this->db->query("UPDATE categories SET name=?, slug=?, image_url=? WHERE id=?", [$name, $slug, $imageUrl, $id]);
        header('Location: /admin/categories');
    }

    public function updateOrderStatus()
    {
        $id = $_POST['id'];
        $status = $_POST['status'];

        $this->db->query("UPDATE orders SET order_status = ? WHERE id = ?", [$status, $id]);

        if ($status === 'shipped') {
            $stmt = $this->db->query("SELECT u.phone, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?", [$id]);
            $user = $stmt->fetch();
            if ($user) {
                $this->whatsapp->sendMessage($user['phone'], "Hello {$user['name']}, your order #{$id} has been shipped!");

                if (!empty($user['email'])) {
                    $emailService = new EmailService();
                    $emailService->sendOrderUpdate($user['email'], $user['name'], "Order #{$id} Shipped", "<p>Good news! Your order #{$id} has been shipped.</p>");
                }
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
        $categoryId = $_POST['category_id'] ?: null;
        $isQuoteOnly = isset($_POST['is_quote_only']) ? 1 : 0;
        $imageUrl = '';
        $galleryImages = [];

        // Main Image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
                $imageUrl = '/uploads/' . $filename;
            }
        }

        // Gallery Images
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            foreach ($_FILES['gallery']['name'] as $key => $val) {
                if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                        $filename = uniqid('prod_gal_') . '.' . $ext;
                        move_uploaded_file($_FILES['gallery']['tmp_name'][$key], __DIR__ . '/../public/uploads/' . $filename);
                        $galleryImages[] = '/uploads/' . $filename;
                    }
                }
            }
        }

        $galleryJson = json_encode($galleryImages);

        $this->db->query(
            "INSERT INTO products (name, price, description, stock, image_url, category_id, gallery_images, is_quote_only) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [$name, $price, $description, $stock, $imageUrl, $categoryId, $galleryJson, $isQuoteOnly]
        );

        header('Location: /admin/products');
    }

    public function editProduct()
    {
        $id = $_GET['id'] ?? 0;
        $stmt = $this->db->query("SELECT * FROM products WHERE id = ?", [$id]);
        $product = $stmt->fetch();

        if (!$product) {
            header('Location: /admin/products');
            exit;
        }

        $product['gallery_images'] = json_decode($product['gallery_images'] ?? '[]', true);

        $stmt = $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll();

        require __DIR__ . '/../views/admin/product_edit.php';
    }

    public function updateProduct()
    {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $stock = $_POST['stock'];
        $categoryId = $_POST['category_id'] ?: null;
        $isQuoteOnly = isset($_POST['is_quote_only']) ? 1 : 0;

        // Fetch existing images
        $stmt = $this->db->query("SELECT image_url, gallery_images FROM products WHERE id = ?", [$id]);
        $current = $stmt->fetch();
        $imageUrl = $current['image_url'];
        $galleryImages = json_decode($current['gallery_images'] ?? '[]', true);

        // Update Main Image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = uniqid('prod_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . $filename);
                $imageUrl = '/uploads/' . $filename;
            }
        }

        // Add to Gallery
        if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
            foreach ($_FILES['gallery']['name'] as $key => $val) {
                if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['gallery']['name'][$key], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp'])) {
                        $filename = uniqid('prod_gal_') . '.' . $ext;
                        move_uploaded_file($_FILES['gallery']['tmp_name'][$key], __DIR__ . '/../public/uploads/' . $filename);
                        $galleryImages[] = '/uploads/' . $filename;
                    }
                }
            }
        }

        $galleryJson = json_encode($galleryImages);

        $this->db->query(
            "UPDATE products SET name=?, price=?, description=?, stock=?, category_id=?, is_quote_only=?, image_url=?, gallery_images=? WHERE id=?",
            [$name, $price, $description, $stock, $categoryId, $isQuoteOnly, $imageUrl, $galleryJson, $id]
        );

        header('Location: /admin/products');
    }

    public function quotations()
    {
        $stmt = $this->db->query("SELECT q.*, p.name as product_name FROM quotations q JOIN products p ON q.product_id = p.id ORDER BY q.created_at DESC");
        $quotations = $stmt->fetchAll();
        require __DIR__ . '/../views/admin/quotations.php';
    }

    public function updateQuotationStatus()
    {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $this->db->query("UPDATE quotations SET status = ? WHERE id = ?", [$status, $id]);
        header('Location: /admin/quotations');
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

    public function editCoupon()
    {
        $id = $_GET['id'] ?? 0;
        $stmt = $this->db->query("SELECT * FROM coupons WHERE id = ?", [$id]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            header('Location: /admin/coupons');
            exit;
        }

        require __DIR__ . '/../views/admin/coupon_edit.php';
    }

    public function updateCoupon()
    {
        $id = $_POST['id'];
        $code = $_POST['code'];
        $type = $_POST['type'];
        $value = $_POST['value'];
        $min = $_POST['min_cart_value'];
        $expiry = $_POST['expires_at'];
        $limit = $_POST['usage_limit'] ?: null;

        $this->db->query(
            "UPDATE coupons SET code=?, type=?, discount_value=?, min_cart_value=?, expires_at=?, usage_limit=? WHERE id=?",
            [$code, $type, $value, $min, $expiry, $limit, $id]
        );

        header('Location: /admin/coupons');
    }
}
