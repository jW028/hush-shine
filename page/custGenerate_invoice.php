<?php
require_once '../_base.php';
require_once '../lib/TCPDF/tcpdf.php';

// Ensure the user is logged in
if (!isset($_SESSION['cust_id'])) {
    header('Location: ../login.php');
    exit;
}

$cust_id = $_SESSION['cust_id'];
$order_id = $_GET['id'] ?? $_SESSION['order_id'] ?? null;

if (!$order_id) {
    header('Location: mypurchase.php');
    exit;
}

try {
    // Fetch order details and ensure the order belongs to the logged-in customer
    $stmt = $_db->prepare("
        SELECT o.*, c.cust_name, c.cust_email, c.cust_contact as cust_phone
        FROM orders o
        LEFT JOIN customer c ON o.cust_id = c.cust_id
        WHERE o.order_id = ? AND o.cust_id = ?
    ");
    $stmt->execute([$order_id, $cust_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['message'] = "Order not found or you are not authorized to view this order.";
        $_SESSION['message_type'] = "error";
        header('Location: mypurchase.php');
        exit;
    }

    // Fetch order items
    $stmt = $_db->prepare("
        SELECT oi.*, p.prod_name, p.image
        FROM order_items oi
        LEFT JOIN product p ON oi.prod_id = p.prod_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate subtotal, tax, and total
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $tax = $subtotal * 0.06; // 6% tax
    $rewardPointsApplied = $order['reward_used'] ?? 0;
    $total = $subtotal + $tax - $rewardPointsApplied;

    // Ensure the total is not less than the minimum payable amount
    if ($total < 0.01) {
        $total = 0.01;
    }

    // Generate the invoice PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('Hush & Shine');
    $pdf->SetAuthor('Hush & Shine');
    $pdf->SetTitle('Invoice #' . $order_id);
    $pdf->SetSubject('Invoice #' . $order_id);

    $pdf->SetHeaderData('', 0, 'Hush & Shine', 'Invoice #' . $order_id);

    $pdf->SetMargins(15, 20, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    $pdf->SetAutoPageBreak(TRUE, 25);

    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Invoice header
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Invoice #: ' . $order_id, 0, 1);
    $pdf->Cell(0, 5, 'Date: ' . date('F j, Y', strtotime($order['order_date'])), 0, 1);
    $pdf->Cell(0, 5, 'Status: ' . $order['status'], 0, 1);
    $pdf->Cell(0, 5, 'Payment Status: ' . $order['payment_status'], 0, 1);
    $pdf->Ln(5);

    // Customer info
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Customer Information', 0, 1);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Name: ' . $order['cust_name'], 0, 1);
    $pdf->Cell(0, 5, 'Email: ' . $order['cust_email'], 0, 1);
    $pdf->Cell(0, 5, 'Phone: ' . $order['cust_phone'], 0, 1);
    $pdf->MultiCell(0, 5, 'Address: ' . $order['shipping_address'], 0, 'L');
    $pdf->Ln(5);

    // Order items
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Order Items', 0, 1);

    // Table header
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(15, 7, 'Qty', 1, 0, 'C', 1);
    $pdf->Cell(100, 7, 'Product', 1, 0, 'L', 1);
    $pdf->Cell(35, 7, 'Price', 1, 0, 'R', 1);
    $pdf->Cell(35, 7, 'Total', 1, 1, 'R', 1);

    // Table rows
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(255, 255, 255);
    foreach ($items as $item) {
        $pdf->Cell(15, 7, $item['quantity'], 1, 0, 'C', 1);
        $pdf->Cell(100, 7, $item['prod_name'], 1, 0, 'L', 1);
        $pdf->Cell(35, 7, 'RM ' . number_format($item['price'], 2), 1, 0, 'R', 1);
        $pdf->Cell(35, 7, 'RM ' . number_format($item['price'] * $item['quantity'], 2), 1, 1, 'R', 1);
    }

    // Totals
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(150, 7, 'Subtotal', 1, 0, 'R', 0);
    $pdf->Cell(35, 7, 'RM ' . number_format($subtotal, 2), 1, 1, 'R', 0);

    $pdf->Cell(150, 7, 'Tax (6%)', 1, 0, 'R', 0);
    $pdf->Cell(35, 7, 'RM ' . number_format($tax, 2), 1, 1, 'R', 0);

    if ($rewardPointsApplied > 0) {
        $pdf->Cell(150, 7, 'Reward Points Applied', 1, 0, 'R', 0);
        $pdf->Cell(35, 7, '-RM ' . number_format($rewardPointsApplied, 2), 1, 1, 'R', 0);
    }

    if (isset($order['shipping_fee']) && $order['shipping_fee'] > 0) {
        $pdf->Cell(150, 7, 'Shipping', 1, 0, 'R', 0);
        $pdf->Cell(35, 7, 'RM ' . number_format($order['shipping_fee'], 2), 1, 1, 'R', 0);
    }

    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(150, 7, 'TOTAL', 1, 0, 'R', 1);
    $pdf->Cell(35, 7, 'RM ' . number_format($order['total_amount'], 2), 1, 1, 'R', 1);

    // Save the PDF
    $invoiceDir = __DIR__ . '/../invoices/';
    if (!file_exists($invoiceDir)) {
        mkdir($invoiceDir, 0755, true);
    }
    $pdfFilePath = $invoiceDir . '/invoice_' . $order_id . '.pdf';
    $pdf->Output($pdfFilePath, 'F');

    // Handle email or download
    $send_email = $_GET['email'] ?? false;
    if ($send_email) {
        try {
            $m = get_mail();
            $m->addAddress($order['cust_email']);
            $m->Subject = "Your Hush & Shine Invoice #" . $order_id;

            $emailBody = "
            <h2>Thank you for your order!</h2>
            <p>Dear {$order['cust_name']},</p>
            <p>Please find attached your invoice for order #{$order_id}.</p>
            <p>Order Status: {$order['status']}</p>
            <p>Payment Status: {$order['payment_status']}</p>
            <p>Order Total: RM " . number_format($order['total_amount'], 2) . "</p>
            <p>If you have any questions, please contact our customer service.</p>
            <p>Thank you for shopping with Hush & Shine!</p>
            ";

            $m->Body = $emailBody;
            $m->AltBody = strip_tags($emailBody);
            $m->addAttachment($pdfFilePath);

            if ($m->send()) {
                $_SESSION['message'] = "Invoice sent to your email successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to send invoice email: " . $m->ErrorInfo;
                $_SESSION['message_type'] = "error";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Email error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }

        header("Location: mypurchase.php");
        exit;
    } else {
        // Download the PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice_' . $order_id . '.pdf"');
        readfile($pdfFilePath);
        unlink($pdfFilePath); // Delete the file after sending
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    header('Location: mypurchase.php');
    exit;
}