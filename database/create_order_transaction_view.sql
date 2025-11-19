SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0;
DROP VIEW IF EXISTS `order_transaction_details_view`;
SET SQL_NOTES=@OLD_SQL_NOTES;

CREATE VIEW `order_transaction_details_view` AS
SELECT 
    o.order_id,
    o.order_code,
    o.status AS order_status,
    o.payment_method,
    o.created_at AS order_date,
    DATE_FORMAT(o.created_at, '%Y-%m-%d %H:%i:%s') AS formatted_order_date,
    u.user_id,
    u.username AS customer_name,
    u.email AS customer_email,
    oi.orderItem_id,
    oi.name_snapshot AS product_name,
    oi.unit_price_snapshot AS unit_price,
    oi.quantity,
    oi.line_total AS item_subtotal,
    oi.product_id,
    p.product_name AS current_product_name,
    p.model AS product_model,
    p.price AS current_price,
    b.name AS brand_name,
    c.name AS category_name,
    o.subtotal AS order_subtotal,
    o.tax AS order_tax,
    o.total AS order_total,
    CASE 
        WHEN o.subtotal > 0 THEN ROUND((oi.line_total / o.subtotal) * 100, 2)
        ELSE 0
    END AS item_percentage_of_order,
    CASE 
        WHEN o.status = 'Completed' THEN 'Fulfilled'
        WHEN o.status = 'Shipped' THEN 'In Transit'
        WHEN o.status = 'Pending' THEN 'Processing'
        WHEN o.status = 'Cancelled' THEN 'Cancelled'
        ELSE o.status
    END AS status_description,
    TIMESTAMPDIFF(DAY, o.created_at, NOW()) AS days_since_order,
    CASE 
        WHEN o.status = 'Completed' THEN 'Yes'
        ELSE 'No'
    END AS is_completed
FROM 
    orders o
    INNER JOIN users u ON o.user_id = u.user_id
    INNER JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    LEFT JOIN brands b ON p.brand_id = b.brand_id
    LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY 
    o.created_at DESC, 
    o.order_id DESC, 
    oi.orderItem_id ASC;

