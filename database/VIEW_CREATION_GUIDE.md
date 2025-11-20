# MySQL View Creation Guide - Order Transaction Details
## Database Design Assignment (20 Points)

---

## 📋 **STEP-BY-STEP INSTRUCTIONS**

### **STEP 1: Understand the Purpose**
A MySQL VIEW is a virtual table based on the result of a SQL statement. It allows you to:
- Combine data from multiple tables
- Simplify complex queries
- Provide a consistent interface for accessing data
- Improve security by controlling data access

### **STEP 2: Analyze Required Tables**
For order transaction details, we need data from:
- `orders` - Main order information
- `users` - Customer information
- `order_items` - Individual items in each order
- `products` - Product details (current)
- `brands` - Brand information
- `categories` - Category information

### **STEP 3: Open phpMyAdmin or MySQL Command Line**

**Option A: Using phpMyAdmin**
1. Open your browser and go to `http://localhost/phpmyadmin`
2. Select the `bytehub` database from the left sidebar
3. Click on the "SQL" tab at the top

**Option B: Using MySQL Command Line**
1. Open Command Prompt or Terminal
2. Navigate to your MySQL bin directory
3. Run: `mysql -u root -p`
4. Enter your password
5. Run: `USE bytehub;`

### **STEP 4: Create the View**

Copy and paste the SQL code from `create_order_transaction_view.sql` into:
- phpMyAdmin SQL tab, OR
- MySQL command line

**Key Components Explained:**

```sql
CREATE VIEW `order_transaction_details_view` AS
SELECT 
    -- Order Information
    o.order_id,
    o.order_code,
    o.status AS order_status,
    ...
FROM 
    orders o
    INNER JOIN users u ON o.user_id = u.user_id
    INNER JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.product_id
    ...
```

**What this does:**
- `INNER JOIN` - Gets only orders that have users and items
- `LEFT JOIN` - Gets product info even if product was deleted
- `AS` - Creates aliases for column names
- Calculated fields - Adds computed values like percentages

### **STEP 5: Execute the SQL**

Click "Go" in phpMyAdmin or press Enter in command line.

**Expected Result:**
```
Query OK, 0 rows affected
```

### **STEP 6: Verify the View**

**Test Query:**
```sql
SELECT * FROM order_transaction_details_view LIMIT 5;
```

You should see a combined result with data from all joined tables.

### **STEP 7: View Structure**

To see the view structure:
```sql
DESCRIBE order_transaction_details_view;
```

Or in phpMyAdmin:
- Click on "Views" in the left sidebar
- Click on `order_transaction_details_view`
- Click "Structure" tab

---

## 📊 **VIEW COLUMNS EXPLANATION**

| Column Name | Description | Source Table |
|------------|-------------|--------------|
| `order_id` | Unique order identifier | orders |
| `order_code` | Human-readable order code | orders |
| `order_status` | Current order status | orders |
| `payment_method` | How customer paid | orders |
| `order_date` | When order was placed | orders |
| `customer_name` | Customer's username | users |
| `customer_email` | Customer's email | users |
| `product_name` | Product name at time of order | order_items (snapshot) |
| `unit_price` | Price per unit at time of order | order_items (snapshot) |
| `quantity` | Number of items ordered | order_items |
| `item_subtotal` | Total for this line item | order_items |
| `order_subtotal` | Total before tax | orders |
| `order_tax` | Tax amount (12%) | orders |
| `order_total` | Final total amount | orders |
| `status_description` | Human-readable status | Calculated |
| `days_since_order` | Days since order placed | Calculated |

---

## 💻 **USING THE VIEW IN PHP**

### **Example 1: Get All Transaction Details**

```php
<?php
include '../includes/db.php';

$sql = "SELECT * FROM order_transaction_details_view 
        WHERE order_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $order_code);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "Order: " . $row['order_code'] . "<br>";
    echo "Product: " . $row['product_name'] . "<br>";
    echo "Quantity: " . $row['quantity'] . "<br>";
    echo "Total: ₱" . number_format($row['order_total'], 2) . "<br><br>";
}
?>
```

### **Example 2: Get Order Summary**

```php
<?php
$sql = "SELECT 
            order_code,
            customer_name,
            customer_email,
            order_status,
            order_subtotal,
            order_tax,
            order_total,
            order_date
        FROM order_transaction_details_view
        WHERE order_id = ?
        GROUP BY order_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
?>
```

### **Example 3: Get All Items for an Order**

```php
<?php
$sql = "SELECT 
            product_name,
            quantity,
            unit_price,
            item_subtotal
        FROM order_transaction_details_view
        WHERE order_code = ?
        ORDER BY orderItem_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $order_code);
$stmt->execute();
$items = $stmt->get_result();
?>
```

---

## ✅ **ADVANTAGES OF USING A VIEW**

1. **Simplified Queries** - No need to write complex JOINs every time
2. **Data Consistency** - Same logic used everywhere
3. **Performance** - MySQL can optimize view queries
4. **Security** - Can grant access to view without exposing base tables
5. **Maintainability** - Update view definition in one place

---

## 🔧 **MAINTENANCE**

### **Update the View:**
```sql
CREATE OR REPLACE VIEW order_transaction_details_view AS
SELECT ... -- your updated query
```

### **Drop the View:**
```sql
DROP VIEW IF EXISTS order_transaction_details_view;
```

### **Check View Definition:**
```sql
SHOW CREATE VIEW order_transaction_details_view;
```

---

## 📝 **ASSIGNMENT CHECKLIST**

- [x] View created successfully
- [x] View includes order information
- [x] View includes customer information
- [x] View includes order items
- [x] View includes financial details (subtotal, tax, total)
- [x] View uses appropriate JOINs
- [x] View includes calculated fields
- [x] View tested and working
- [x] View documented
- [x] View used in PHP code

---

## 🎯 **GRADING CRITERIA (20 Points)**

- **View Creation (5 pts)** - View created correctly
- **Data Completeness (5 pts)** - All relevant transaction data included
- **JOIN Usage (5 pts)** - Proper use of INNER/LEFT JOINs
- **Documentation (3 pts)** - Clear comments and documentation
- **Practical Usage (2 pts)** - View used in application code

---

## 📚 **ADDITIONAL RESOURCES**

- MySQL VIEW Documentation: https://dev.mysql.com/doc/refman/8.0/en/views.html
- JOIN Types Explained: https://www.w3schools.com/sql/sql_join.asp

---

**Created for ByteHub E-Commerce System**
**Date: 2025**

