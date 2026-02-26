<?php

class QuotationController
{
    private $db;
    private $whatsapp;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->whatsapp = new WhatsAppService();
    }

    public function submit()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $productId = $input['product_id'] ?? 0;
        $name = strip_tags($input['name'] ?? '');
        $phone = strip_tags($input['phone'] ?? '');
        $email = strip_tags($input['email'] ?? '');
        $requirements = strip_tags($input['requirements'] ?? '');
        $budget = strip_tags($input['budget'] ?? '');

        if (empty($productId) || empty($name) || empty($phone) || empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }

        try {
            // Get Product Name
            $stmt = $this->db->query("SELECT name FROM products WHERE id = ?", [$productId]);
            $product = $stmt->fetch();
            $productName = $product ? $product['name'] : 'Unknown Product';

            // Insert Quotation
            $this->db->query(
                "INSERT INTO quotations (product_id, user_name, user_phone, user_email, requirements, budget_range) VALUES (?, ?, ?, ?, ?, ?)",
                [$productId, $name, $phone, $email, $requirements, $budget]
            );

            // Notify Admin
            $config = require __DIR__ . '/../config/services.php';
            $adminPhone = $config['whatsapp']['admin_phone'];

            if ($adminPhone) {
                $adminMsg = "🚨 New Lead! {$name} requested a quote for {$productName}.\nBudget: {$budget}\nRequirements: {$requirements}";
                try {
                    $this->whatsapp->sendMessage($adminPhone, $adminMsg);
                } catch (Exception $e) {
                    // Ignore notification error
                }
            }

            // Confirm to User
            $userMsg = "Hi {$name}, we received your quote request for {$productName}. Our team will review your requirements and get back to you shortly!";
            try {
                $this->whatsapp->sendMessage($phone, $userMsg);
            } catch (Exception $e) {
                // Ignore
            }

            echo json_encode(['status' => 'success', 'message' => 'Quote request submitted successfully']);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
        }
    }
}
