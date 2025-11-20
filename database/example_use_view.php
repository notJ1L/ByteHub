<?php
include '../includes/db.php';

function getOrderDetails($order_code) {
    global $conn;
    
    $sql = "SELECT * FROM order_transaction_details_view 
            WHERE order_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    return $stmt->get_result();
}

function getOrderSummary($order_id) {
    global $conn;
    
    $sql = "SELECT 
                order_id,
                order_code,
                customer_name,
                customer_email,
                order_status,
                payment_method,
                order_subtotal,
                order_tax,
                order_total,
                order_date,
                status_description
            FROM order_transaction_details_view
            WHERE order_id = ?
            GROUP BY order_id
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getOrderItems($order_code) {
    global $conn;
    
    $sql = "SELECT 
                orderItem_id,
                product_name,
                current_product_name,
                product_model,
                brand_name,
                category_name,
                quantity,
                unit_price,
                item_subtotal,
                item_percentage_of_order
            FROM order_transaction_details_view
            WHERE order_code = ?
            ORDER BY orderItem_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    return $stmt->get_result();
}

function getCustomerOrders($user_id) {
    global $conn;
    
    $sql = "SELECT DISTINCT
                order_id,
                order_code,
                order_status,
                order_total,
                order_date,
                status_description,
                days_since_order
            FROM order_transaction_details_view
            WHERE user_id = ?
            ORDER BY order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getSalesByStatus() {
    global $conn;
    
    $sql = "SELECT 
                order_status,
                status_description,
                COUNT(DISTINCT order_id) AS total_orders,
                SUM(order_subtotal) AS total_subtotal,
                SUM(order_tax) AS total_tax,
                SUM(order_total) AS total_revenue,
                AVG(order_total) AS average_order_value
            FROM order_transaction_details_view
            GROUP BY order_status, status_description
            ORDER BY total_revenue DESC";
    $result = $conn->query($sql);
    return $result;
}

function displayOrderTable($order_code) {
    global $conn;
    
    $summary_sql = "SELECT DISTINCT
                        order_code,
                        customer_name,
                        order_status,
                        order_subtotal,
                        order_tax,
                        order_total,
                        order_date
                    FROM order_transaction_details_view
                    WHERE order_code = ?
                    LIMIT 1";
    $stmt = $conn->prepare($summary_sql);
    $stmt->bind_param('s', $order_code);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
    
    $items = getOrderItems($order_code);
    
    echo '<div class="order-details">';
    echo '<h3>Order: ' . htmlspecialchars($summary['order_code']) . '</h3>';
    echo '<p>Customer: ' . htmlspecialchars($summary['customer_name']) . '</p>';
    echo '<p>Date: ' . date('F j, Y g:i A', strtotime($summary['order_date'])) . '</p>';
    echo '<p>Status: ' . htmlspecialchars($summary['order_status']) . '</p>';
    
    echo '<table class="table">';
    echo '<thead><tr>';
    echo '<th>Product</th>';
    echo '<th>Quantity</th>';
    echo '<th>Unit Price</th>';
    echo '<th>Subtotal</th>';
    echo '</tr></thead>';
    echo '<tbody>';
    
    while ($item = $items->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
        echo '<td>' . $item['quantity'] . '</td>';
        echo '<td>₱' . number_format($item['unit_price'], 2) . '</td>';
        echo '<td>₱' . number_format($item['item_subtotal'], 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr><td colspan="3"><strong>Subtotal:</strong></td>';
    echo '<td><strong>₱' . number_format($summary['order_subtotal'], 2) . '</strong></td></tr>';
    echo '<tr><td colspan="3"><strong>Tax (12%):</strong></td>';
    echo '<td><strong>₱' . number_format($summary['order_tax'], 2) . '</strong></td></tr>';
    echo '<tr><td colspan="3"><strong>Total:</strong></td>';
    echo '<td><strong>₱' . number_format($summary['order_total'], 2) . '</strong></td></tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';
}

function viewExists() {
    global $conn;
    
    $sql = "SELECT COUNT(*) as count 
            FROM information_schema.views 
            WHERE table_schema = DATABASE() 
            AND table_name = 'order_transaction_details_view'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

?>

