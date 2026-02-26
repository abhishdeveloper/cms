<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function generateInvoice($orderId)
    {
        // 1. Fetch Data
        $stmt = $this->db->query("SELECT o.*, u.name, u.email, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?", [$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found");
        }

        $stmt = $this->db->query("SELECT oi.*, p.name as product_name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?", [$orderId]);
        $items = $stmt->fetchAll();

        // 2. Render HTML
        ob_start();
        require __DIR__ . '/../views/templates/invoice.php';
        $html = ob_get_clean();

        // 3. Generate PDF
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true); // Allow remote images if needed

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // 4. Save File
        $filename = "INV-{$orderId}.pdf";
        $path = __DIR__ . '/../storage/invoices/' . $filename;

        file_put_contents($path, $dompdf->output());

        return $path;
    }
}
