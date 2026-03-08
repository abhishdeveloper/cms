<?php

require_once __DIR__ . '/../autoload.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

AuthMiddleware::requireLogin();

$id = $_GET['id'] ?? 0;
$userId = $_SESSION['user_id'];

try {
    $db = Database::getInstance();

    // Check ownership
    $stmt = $db->query("SELECT id FROM orders WHERE id = ? AND user_id = ?", [$id, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        die("Unauthorized access to this invoice.");
    }

    $filename = "INV-{$id}.pdf";
    $path = __DIR__ . '/../storage/invoices/' . $filename;

    if (!file_exists($path)) {
        // Option: Regenerate if missing?
        // For now, assume it should exist if paid.
        // Let's regenerate on the fly if missing for robustness
        $invoiceService = new InvoiceService();
        $invoiceService->generateInvoice($id);
    }

    if (file_exists($path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    } else {
        http_response_code(404);
        die("Invoice file not found.");
    }

} catch (Exception $e) {
    http_response_code(500);
    die("Error: " . $e->getMessage());
}
