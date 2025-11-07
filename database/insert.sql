USE bytehub;

INSERT INTO users (username, email, password_hash)
VALUES 
('John Doe', 'john@example.com', MD5('password123')),
('Jane Smith', 'jane@example.com', MD5('mypassword'));

INSERT INTO admin (email, password_hash)
VALUES 
('admin@bytehub.com', MD5('admin123'));

INSERT INTO categories (name, slug, active)
VALUES
('CPUs', 'cpus', 1),
('GPUs', 'gpus', 1),
('Motherboards', 'motherboards', 1),
('RAM', 'ram', 1),
('Storage', 'storage', 1),
('Peripherals', 'peripherals', 1);

INSERT INTO brands (name, slug, active)
VALUES
('Intel', 'intel', 1),
('AMD', 'amd', 1),
('NVIDIA', 'nvidia', 1),
('Kingston', 'kingston', 1),
('Corsair', 'corsair', 1),
('ASUS', 'asus', 1),
('MSI', 'msi', 1);

INSERT INTO products (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id)
VALUES
('Intel Core i5-12400F', 'i5-12400F', 180.00, 10, 'cpu1.jpg', 1, 1, 1, 
 (SELECT category_id FROM categories WHERE slug='cpus'),
 (SELECT brand_id FROM brands WHERE slug='intel')),
('AMD Ryzen 5 5600', 'R5-5600', 160.00, 15, 'cpu2.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='cpus'),
 (SELECT brand_id FROM brands WHERE slug='amd')),
('NVIDIA RTX 3060', 'RTX3060', 299.00, 8, 'gpu1.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='gpus'),
 (SELECT brand_id FROM brands WHERE slug='nvidia')),
('MSI B550 Tomahawk', 'B550', 140.00, 12, 'mobo1.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='motherboards'),
 (SELECT brand_id FROM brands WHERE slug='msi')),
('ASUS TUF Z690', 'Z690', 250.00, 5, 'mobo2.jpg', 0, 1, 1,
 (SELECT category_id FROM categories WHERE slug='motherboards'),
 (SELECT brand_id FROM brands WHERE slug='asus')),
('Corsair Vengeance 16GB DDR4', 'CMK16', 89.99, 20, 'ram1.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='ram'),
 (SELECT brand_id FROM brands WHERE slug='corsair')),
('Kingston Fury 32GB DDR5', 'KF432', 129.00, 15, 'ram2.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='ram'),
 (SELECT brand_id FROM brands WHERE slug='kingston')),
('Samsung 970 EVO Plus 1TB', 'MZ-V7S1T0', 99.00, 25, 'storage1.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='storage'),
 (SELECT brand_id FROM brands WHERE slug='corsair')),
('Logitech G Pro X Headset', 'GPROX', 89.00, 10, 'peripheral1.jpg', 1, 1, 1,
 (SELECT category_id FROM categories WHERE slug='peripherals'),
 (SELECT brand_id FROM brands WHERE slug='asus'));

INSERT INTO expenses (title, amount, category, notes, created_at)
VALUES
('Website Hosting', 120.00, 'Operations', 'Yearly server costs', NOW()),
('Advertising Campaign', 250.00, 'Marketing', 'Facebook ads', NOW()),
('Office Supplies', 75.00, 'Admin', 'Printer ink, paper', NOW());

INSERT INTO orders (user_id, order_code, payment_method, subtotal, tax, total, status)
VALUES
(1, 'ORDER-1001', 'Cash', 300.00, 36.00, 336.00, 'Pending'),
(2, 'ORDER-1002', 'Credit Card', 250.00, 30.00, 280.00, 'Shipped');

INSERT INTO order_items (name_snapshot, unit_price_snapshot, quantity, line_total, order_id, product_id)
VALUES
('Intel Core i5-12400F', 180.00, 1, 180.00, 1, 1),
('NVIDIA RTX 3060', 299.00, 1, 299.00, 2, 3);
