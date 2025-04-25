<?php
// filepath: /Users/jw/Documents/hushandshine/admin/view_order.php
require_once '../_base.php';

// Ensure user is authorized as admin
auth('admin');

$_adminContext = true;

$_title = 'View Order';
include '../_head.php';

// Get order ID from URL
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    // Redirect if no order ID provided
    header('Location: admin_orders.php');
    exit;
}

$status_options = [
    'Confirmed' => 'Confirmed',
    'Processing' => 'Processing',
    'Shipped' => 'Shipped',
    'Delivered' => 'Delivered',
    'Cancelled' => 'Cancelled',
    'Refunded' => 'Refunded'
];

$status_weights = [
    'Confirmed' => 10,
    'Processing' => 20,
    'Shipped' => 30,
    'Delivered' => 40,
    'Cancelled' => 50,
    'Refunded' => 60
];

function getAvailableStatuses($currentStatus, $allStatuses, $status_weights) {
    if ($currentStatus === 'Cancelled') {
        return [
            'Cancelled' => 'Cancelled',
            'Refunded' => 'Refunded'
        ];
    }

    if ($currentStatus === 'Delivered') {
        return [
            'Delivered' => 'Delivered',
            'Refunded' => 'Refunded'
        ];
    }

    $currentWeight = $status_weights[$currentStatus] ?? 0;
    $available = [];

    foreach($allStatuses as $status => $label) {
        $statusWeight = $status_weights[$status] ?? 999;
        if ($statusWeight >= $currentWeight) {
            $available[$status] = $label;
        }
    }
    
    return $available;
    }

// Handle status update if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    try {
        $stmt = $_db->prepare("SELECT status FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
        $current_status = $stmt->fetchColumn();

        $available_statuses = getAvailableStatuses($current_status, $status_options, $status_weights);
        if (!array_key_exists($new_status, $available_statuses)) {
            $error_message = "Invalid status update. Order status can only progress forward.";
            header('Location: view_order.php?id=' . $order_id);
            exit;
        }
        // Begin transaction
        $_db->beginTransaction();
        
        // Update order status
        $stmt = $_db->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        // Store shipping info in shipping_address field if status is "Shipped"
        if ($new_status === 'Shipped' && !empty($_POST['tracking_number'])) {
            $tracking_number = $_POST['tracking_number'];
            $courier = $_POST['courier'] ?? '';
            
            // Get current shipping address
            $stmt = $_db->prepare("SELECT shipping_address FROM orders WHERE order_id = ?");
            $stmt->execute([$order_id]);
            $currentAddress = $stmt->fetchColumn();
            
            // Append tracking info to shipping address
            $trackingInfo = "\n\n--TRACKING INFO--\nCourier: " . $courier . "\nTracking Number: " . $tracking_number;
            $updatedAddress = $currentAddress . $trackingInfo;
            
            $stmt = $_db->prepare("UPDATE orders SET shipping_address = ? WHERE order_id = ?");
            $stmt->execute([$updatedAddress, $order_id]);
        }
        
        // Commit transaction
        $_db->commit();
        
        // Set success message
        $success_message = "Order status updated successfully";
        
        // Optionally send email notification to customer about status change
        if (isset($_POST['notify_customer']) && $_POST['notify_customer'] == 1) {
            // Get customer email
            $stmt = $_db->prepare("SELECT c.cust_email, c.cust_name FROM orders o JOIN customer c ON o.cust_id = c.cust_id WHERE o.order_id = ?");
            $stmt->execute([$order_id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($customer && !empty($customer['cust_email'])) {
                // Send status update email
                $subject = "Your Order #$order_id Status Update";
                $message = "Dear " . htmlspecialchars($customer['cust_name']) . ",\n\n";
                $message .= "Your order #$order_id has been updated to: " . $new_status . "\n\n";
                
                if ($new_status === 'Shipped' && !empty($_POST['tracking_number'])) {
                    $message .= "Tracking Information:\n";
                    $message .= "Courier: " . htmlspecialchars($_POST['courier']) . "\n";
                    $message .= "Tracking Number: " . htmlspecialchars($_POST['tracking_number']) . "\n\n";
                }
                
                $message .= "Thank you for shopping with us!\n\n";
                $message .= "Best regards,\nHush & Shine Team";
                
                // Send email
                mail($customer['cust_email'], $subject, $message);
                
                $success_message .= " and customer has been notified.";
            }
        }
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $_db->rollBack();
        $error_message = "Error updating order: " . $e->getMessage();
    }
}

// Fetch order details
try {
    // Get order information
    $stmt = $_db->prepare("
        SELECT o.*, c.cust_name, c.cust_email, c.cust_contact
        FROM orders o
        LEFT JOIN customer c ON o.cust_id = c.cust_id
        WHERE o.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        // Redirect if order not found
        header('Location: admin_orders.php');
        exit;
    }
    
    // Parse tracking information from shipping_address if available
    $trackingInfo = [
        'courier' => '',
        'tracking_number' => ''
    ];
    
    if (!empty($order['shipping_address']) && strpos($order['shipping_address'], '--TRACKING INFO--') !== false) {
        $parts = explode('--TRACKING INFO--', $order['shipping_address']);
        $order['shipping_address'] = trim($parts[0]);
        
        // Extract tracking info
        if (isset($parts[1])) {
            if (preg_match('/Courier:\s*(.*?)\s*\n/i', $parts[1], $match)) {
                $trackingInfo['courier'] = $match[1];
            }
            if (preg_match('/Tracking Number:\s*(.*?)\s*(\n|$)/i', $parts[1], $match)) {
                $trackingInfo['tracking_number'] = $match[1];
            }
        }
    }
    
    // Get order items
    $stmt = $_db->prepare("
        SELECT oi.*, p.prod_name, p.image
        FROM order_items oi
        JOIN product p ON oi.prod_id = p.prod_id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "Error fetching order details: " . $e->getMessage();
}
?>

<div class="admin-container">
    
    <div class="admin-main">
        <div class="content-header">
            <h2>Order #<?= $order_id ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="admin_orders.php">Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Order #<?= $order_id ?></li>
                </ol>
            </nav>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="order-view-container">
            <!-- Order Status Section -->
            <div class="order-status-section">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3>Order Status</h3>
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="view_order.php?id=<?= $order_id ?>" class="view-order" method="POST">
                            <div class="form-group">
                                <label for="status">Update Status:</label>
                                <select name="status" id="status" class="form-control">
                                    <?php
                                    $available_statuses = getAvailableStatuses($order['status'], $status_options, $status_weights);

                                    foreach ($available_statuses as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $order['status'] === $value ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (count($available_statuses) < count($status_options)): ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Order status can only progress forward in the workflow.
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <div id="shipping-info" class="mt-3 <?= $order['status'] === 'Shipped' ? '' : 'd-none' ?>">
                                <div class="form-group">
                                    <label for="courier">Courier Service:</label>
                                    <input type="text" name="courier" id="courier" class="form-control" value="<?= htmlspecialchars($trackingInfo['courier']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tracking_number">Tracking Number:</label>
                                    <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="<?= htmlspecialchars($trackingInfo['tracking_number']) ?>">
                                </div>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label for="admin_notes">Admin Notes:</label>
                                <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3"></textarea>
                                <small class="form-text text-muted">For internal reference only. Not stored in database.</small>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input type="checkbox" name="notify_customer" id="notify_customer" value="1" class="form-check-input" checked>
                                <label for="notify_customer" class="form-check-label">Notify customer of status change</label>
                            </div>
                            
                            <div class="mt-3">
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Information and Items -->
            <div class="order-details-grid">
                <!-- Customer Information -->
                <div class="card">
                    <div class="card-header">
                        <h3>Customer Information</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Customer ID:</strong> <?= htmlspecialchars($order['cust_id']) ?></p>
                        <?php if (!empty($order['cust_name'])): ?>
                            <p><strong>Name:</strong> <?= htmlspecialchars($order['cust_name']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['cust_email'])): ?>
                            <p><strong>Email:</strong> <?= htmlspecialchars($order['cust_email']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['cust_contact'])): ?>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($order['cust_contact']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order['order_date'])) ?></p>
                        <?php if (!empty($order['payment_status'])): ?>
                            <p>
                                <strong>Payment Status:</strong> 
                                <span class="payment-badge payment-<?= strtolower($order['payment_status']) ?>">
                                    <?= htmlspecialchars($order['payment_status']) ?>
                                </span>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($order['payment_id'])): ?>
                            <p><strong>Payment ID:</strong> <?= htmlspecialchars($order['payment_id']) ?></p>
                        <?php endif; ?>
                        <p><strong>Total Amount:</strong> RM <?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                </div>
                
                <!-- Shipping Address -->
                <div class="card">
                    <div class="card-header">
                        <h3>Shipping Address</h3>
                    </div>
                    <div class="card-body">
                        <address>
                            <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'No shipping address provided')) ?>
                        </address>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card order-items-card">
                    <div class="card-header">
                        <h3>Order Items</h3>
                    </div>
                    <div class="card-body">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal = 0;
                                foreach ($order_items as $item): 
                                    $subtotal += $item['price'] * $item['quantity'];
                                ?>
                                    <?php 
                                        $productImage = '../images/no-image.png'; // Default image
                                        if (!empty($item['image'])) {
                                            $imageData = json_decode($item['image'], true);
                                            if ($imageData) {
                                                $imageFile = is_array($imageData) ? $imageData[0] : $imageData;
                                                $productImage = '../images/products/' . $imageFile;
                                                if (!file_exists($productImage)) {
                                                    $productImage = '../images/no-image.png';
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td class="product-cell">
                                            <div class="product-info">
                                                <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($item['prod_name']) ?>" class="product-thumb">
                                                <span><?= htmlspecialchars($item['prod_name']) ?></span>
                                            </div>
                                        </td>
                                        <td>RM <?= number_format($item['price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td>RM <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                    <td>RM <?= number_format($subtotal, 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Tax:</strong></td>
                                    <td>RM <?= number_format($subtotal*0.06, 2) ?></td>
                                </tr>
                                
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                    <td><strong>RM <?= number_format($order['total_amount'], 2) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <!-- Order Status History -->
                <div class="card order-history-card">
                    <div class="card-header">
                        <h3>Order Timeline</h3>
                    </div>
                    <div class="card-body">
                        <ul class="timeline">
                            <li class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">
                                        Order <?= htmlspecialchars($order['status']) ?>
                                        <span class="timeline-date">
                                            Current Status
                                        </span>
                                    </h4>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">
                                        Order Placed
                                        <span class="timeline-date">
                                            <?= date('M j, Y g:i A', strtotime($order['order_date'])) ?>
                                        </span>
                                    </h4>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="order-actions">
                    <a href="generate_invoice.php?id=<?= $order_id ?>" class="admin-submit-btn" target="_blank">
                        <i class="fas fa-file-pdf"></i> Download Invoice
                    </a>

                    <a href="generate_invoice.php?id=<?= $order_id ?>&email=1" class="admin-submit-btn secondary">
                        <i class="fas fa-envelope"></i> Send Invoice to Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentStatus = '<?= $order['status'] ?>';
    const statuses = ['Confirmed', 'Processing', 'Shipped', 'Delivered'];
    const statusIndices = {
        'Confirmed': 0,
        'Processing': 1,
        'Shipped': 2,
        'Delivered': 3,
        'Cancelled': -1,
        'Refunded': -1
    };
    
    // Create status progression visualization
    const container = document.createElement('div');
    container.className = 'status-progression-container';
    container.innerHTML = `
        <div class="status-track">
            ${statuses.map((status, index) => {
                const isActive = statusIndices[currentStatus] >= index;
                return `
                <div class="status-step ${isActive ? 'active' : ''}">
                    <div class="status-dot"></div>
                    <div class="status-label">${status}</div>
                </div>`;
            }).join('<div class="status-line"></div>')}
        </div>
    `;
    
    // Special statuses
    if (currentStatus === 'Cancelled' || currentStatus === 'Refunded') {
        const specialStatus = document.createElement('div');
        specialStatus.className = 'special-status ' + currentStatus.toLowerCase();
        specialStatus.innerHTML = `<i class="fas fa-exclamation-circle"></i> This order has been ${currentStatus.toLowerCase()}`;
        container.appendChild(specialStatus);
    }
    
    // Insert before the form
    const formElement = document.querySelector('.order-status-section .card-body form');
    formElement.parentNode.insertBefore(container, formElement);
    
    // Show shipping info when status is Shipped
    document.getElementById('status').addEventListener('change', function() {
        const shippingInfo = document.getElementById('shipping-info');
        if (this.value === 'Shipped') {
            shippingInfo.classList.remove('d-none');
        } else {
            shippingInfo.classList.add('d-none');
        }
    });
});
    
</script>

<?php include '../_foot.php'; ?>