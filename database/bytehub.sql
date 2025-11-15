-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema bytehub
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema bytehub
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `bytehub` DEFAULT CHARACTER SET utf8 ;
USE `bytehub` ;

-- -----------------------------------------------------
-- Table `bytehub`.`users`
-- -----------------------------------------------------
CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(125) NOT NULL,
  email VARCHAR(125) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  photo VARCHAR(125),
  active TINYINT DEFAULT 1,
  role VARCHAR(45) DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bytehub`.`categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bytehub`.`categories` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(125) NOT NULL,
  `slug` VARCHAR(125) NOT NULL,
  `active` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`category_id`),
  UNIQUE INDEX `slug_UNIQUE` (`slug` ASC)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bytehub`.`brands`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bytehub`.`brands` (
  `brand_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `active` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`brand_id`),
  UNIQUE INDEX `slug_UNIQUE` (`slug` ASC)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bytehub`.`products`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bytehub`.`products` (
  `product_id` INT NOT NULL AUTO_INCREMENT,
  `product_name` VARCHAR(145) NOT NULL,
  `model` VARCHAR(45) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL,
  `image` VARCHAR(125) NOT NULL,
  `featured` TINYINT NOT NULL,
  `new_arrival` TINYINT NOT NULL,
  `active` TINYINT NOT NULL,
  `description` TEXT,
  `specifications` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `category_id` INT NOT NULL,
  `brand_id` INT NOT NULL,
  PRIMARY KEY (`product_id`),
  INDEX `fk_products_categories_idx` (`category_id` ASC),
  INDEX `fk_products_brands1_idx` (`brand_id` ASC),
  CONSTRAINT `fk_products_categories`
    FOREIGN KEY (`category_id`)
    REFERENCES `bytehub`.`categories` (`category_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_products_brands1`
    FOREIGN KEY (`brand_id`)
    REFERENCES `bytehub`.`brands` (`brand_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bytehub`.`product_images`
-- -----------------------------------------------------
CREATE TABLE product_images (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  filename VARCHAR(125) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bytehub`.`admin`
-- -----------------------------------------------------
CREATE TABLE admin (
  admin_id INT NOT NULL AUTO_INCREMENT,
  email VARCHAR(165) NOT NULL,
  password_hash VARCHAR(165) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (admin_id),
  UNIQUE INDEX email_UNIQUE (email ASC)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bytehub`.`orders`
-- -----------------------------------------------------
CREATE TABLE orders (
  order_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_code VARCHAR(45) NOT NULL,
  payment_method VARCHAR(45) NOT NULL,
  subtotal DECIMAL(10,2),
  tax DECIMAL(10,2),
  total DECIMAL(10,2),
  status VARCHAR(45) NOT NULL DEFAULT 'Pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bytehub`.`order_items`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bytehub`.`order_items` (
  `orderItem_id` INT NOT NULL AUTO_INCREMENT,
  `name_snapshot` VARCHAR(200) NOT NULL,
  `unit_price_snapshot` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `line_total` DECIMAL(10,2) NOT NULL,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  PRIMARY KEY (`orderItem_id`),
  INDEX `fk_order_items_orders1_idx` (`order_id` ASC),
  INDEX `fk_order_items_products1_idx` (`product_id` ASC),
  CONSTRAINT `fk_order_items_orders1`
    FOREIGN KEY (`order_id`)
    REFERENCES `bytehub`.`orders` (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_items_products1`
    FOREIGN KEY (`product_id`)
    REFERENCES `bytehub`.`products` (`product_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bytehub`.`reviews`
-- -----------------------------------------------------
CREATE TABLE reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bytehub`.`expenses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bytehub`.`expenses` (
  `expenses_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(160) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `category` VARCHAR(120) NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`expenses_id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- View `bytehub`.`order_details_view`
-- -----------------------------------------------------
CREATE VIEW order_details_view AS
SELECT
    o.order_id,
    o.order_code,
    o.status,
    o.created_at AS order_date,
    u.username,
    u.email,
    p.product_name,
    oi.quantity,
    oi.unit_price_snapshot,
    oi.line_total
FROM orders o
JOIN users u ON o.user_id = u.user_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- INSERT DATA
-- -----------------------------------------------------

INSERT INTO users (username, email, password_hash)
VALUES 
('John Doe', 'john@example.com', '$2y$10$E.qA.U5eA.e6Z.w8d.w8c.e6Z.w8d.w8c.e6Z.w8d.w8c.e'),
('Jane Smith', 'jane@example.com', '$2y$10$E.qA.U5eA.e6Z.w8d.w8c.e6Z.w8d.w8c.e6Z.w8d.w8c.e');

INSERT INTO admin (email, password_hash)
VALUES 
('admin@bytehub.com', '$2y$10$E.qA.U5eA.e6Z.w8d.w8c.e6Z.w8d.w8c.e6Z.w8d.w8c.e');

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
('MSI', 'msi', 1),
('Samsung', 'samsung', 1),
('Logitech', 'logitech', 1);

INSERT INTO products (product_name, model, price, stock, image, featured, new_arrival, active, category_id, brand_id, description, specifications)
VALUES
('Intel Core i9-13900K', 'i9-13900K', 32395.00, 10, 'cpu1.jpg', 1, 1, 1, 1, 1, 'The flagship Intel Core i9-13900K processor, delivering unparalleled performance for gaming and content creation.', 'Cores: 24 (8P + 16E)\nThreads: 32\nMax Turbo Frequency: 5.8 GHz\nCache: 36 MB Intel Smart Cache'),
('AMD Ryzen 9 7950X', 'R9-7950X', 30195.00, 12, 'cpu2.jpg', 1, 1, 1, 1, 2, 'Experience the power of AMD with the Ryzen 9 7950X, the ultimate processor for enthusiasts.', 'Cores: 16\nThreads: 32\nMax Boost Clock: 5.7 GHz\nCache: 80 MB'),
('NVIDIA GeForce RTX 4090', 'RTX 4090', 87945.00, 5, 'gpu1.jpg', 1, 1, 1, 2, 3, 'The NVIDIA GeForce RTX 4090 is the ultimate GPU, delivering a quantum leap in performance, efficiency, and AI-powered graphics.', 'CUDA Cores: 16384\nBoost Clock: 2.52 GHz\nMemory: 24 GB GDDR6X\nMemory Interface: 384-bit'),
('AMD Radeon RX 7900 XTX', 'RX7900XTX', 54945.00, 8, 'gpu2.jpg', 1, 1, 1, 2, 2, 'Built on the groundbreaking AMD RDNA 3 architecture, the Radeon RX 7900 XTX delivers next-generation performance, visuals, and efficiency.', 'Stream Processors: 6144\nGame Clock: 2.3 GHz\nMemory: 24 GB GDDR6\nMemory Interface: 384-bit'),
('ASUS ROG Maximus Z790 Hero', 'Z790 Hero', 34595.00, 7, 'mobo1.jpg', 1, 1, 1, 3, 6, 'The ASUS ROG Maximus Z790 Hero is the perfect foundation for a high-end gaming build, featuring robust power delivery and extensive connectivity.', 'Chipset: Intel Z790\nMemory: 4x DDR5, 128GB Max\nExpansion Slots: 2x PCIe 5.0 x16\nStorage: 5x M.2 slots, 6x SATA 6Gb/s'),
('MSI MEG X670E ACE', 'X670E ACE', 38445.00, 6, 'mobo2.jpg', 1, 1, 1, 3, 7, 'The MSI MEG X670E ACE is a premium motherboard for AMD Ryzen 7000 series processors, offering extreme performance and a wealth of features.', 'Chipset: AMD X670E\nMemory: 4x DDR5, 128GB Max\nExpansion Slots: 2x PCIe 5.0 x16\nStorage: 6x M.2 slots, 8x SATA 6Gb/s'),
('Corsair Vengeance 32GB DDR5', 'CMK32GX5', 7699.45, 25, 'ram1.jpg', 1, 1, 1, 4, 5, 'Corsair Vengeance DDR5 memory delivers the higher frequencies and greater capacities of DDR5 technology in a high-quality, compact module.', 'Capacity: 32GB (2x16GB)\nSpeed: 5600MHz\nCAS Latency: 36\nVoltage: 1.25V'),
('Kingston Fury Beast 32GB DDR5', 'KF560C40', 8249.45, 20, 'ram2.jpg', 1, 1, 1, 4, 4, 'Kingston FURY Beast DDR5 memory brings the latest, cutting-edge technology for next-gen gaming platforms.', 'Capacity: 32GB (2x16GB)\nSpeed: 6000MHz\nCAS Latency: 40\nVoltage: 1.35V'),
('Samsung 980 Pro 2TB NVMe SSD', 'MZ-V8P2T0', 9349.45, 30, 'storage1.jpg', 1, 1, 1, 5, 8, 'The Samsung 980 PRO delivers read speeds up to 7,000 MB/s, pushing the limits of what SSDs can do.', 'Capacity: 2TB\nInterface: NVMe PCIe 4.0\nRead Speed: 7,000 MB/s\nWrite Speed: 5,100 MB/s'),
('Logitech G Pro X Superlight', 'Superlight', 8799.45, 15, 'peripheral1.jpg', 1, 1, 1, 6, 9, 'The Logitech G PRO X SUPERLIGHT is our lightest, fastest PRO mouse ever. Meticulously redesigned to be nearly 25% lighter than the standard PRO Wireless mouse.', 'Weight: <63g\nSensor: HERO 25K\nDPI: 100 - 25,600\nBattery Life: 70 hours'),
('Intel Core i5-13600K', 'i5-13600K', 17545.00, 18, 'cpu3.jpg', 0, 1, 1, 1, 1, 'The Intel Core i5-13600K offers an excellent balance of performance and value for gamers and creators.', 'Cores: 14 (6P + 8E)\nThreads: 20\nMax Turbo Frequency: 5.1 GHz\nCache: 24 MB Intel Smart Cache'),
('AMD Ryzen 7 7700X', 'R7-7700X', 19195.00, 22, 'cpu4.jpg', 0, 1, 1, 1, 2, 'The AMD Ryzen 7 7700X is a high-performance CPU that excels in gaming and multitasking.', 'Cores: 8\nThreads: 16\nMax Boost Clock: 5.4 GHz\nCache: 40 MB'),
('NVIDIA GeForce RTX 4080', 'RTX 4080', 65945.00, 9, 'gpu3.jpg', 0, 1, 1, 2, 3, 'The NVIDIA GeForce RTX 4080 delivers the ultra performance and features that enthusiast gamers and creators demand.', 'CUDA Cores: 9728\nBoost Clock: 2.51 GHz\nMemory: 16 GB GDDR6X\nMemory Interface: 256-bit'),
('AMD Radeon RX 7900 XT', 'RX7900XT', 49445.00, 11, 'gpu4.jpg', 0, 1, 1, 2, 2, 'Experience unprecedented performance, visuals, and efficiency at 4K and beyond with AMD Radeon RX 7900 XT graphics cards.', 'Stream Processors: 5376\nGame Clock: 2.0 GHz\nMemory: 20 GB GDDR6\nMemory Interface: 320-bit'),
('ASUS ROG Strix B650E-F', 'B650E-F', 16445.00, 14, 'mobo3.jpg', 0, 1, 1, 3, 6, 'The ASUS ROG Strix B650E-F Gaming WiFi is a well-rounded motherboard for AMD Ryzen 7000 series processors.', 'Chipset: AMD B650E\nMemory: 4x DDR5, 128GB Max\nExpansion Slots: 1x PCIe 5.0 x16\nStorage: 4x M.2 slots, 4x SATA 6Gb/s'),
('MSI MPG B550 Gaming Edge', 'B550 Edge', 10395.00, 16, 'mobo4.jpg', 0, 1, 1, 3, 7, 'The MSI MPG B550 Gaming Edge WiFi is a solid choice for a mainstream AMD build, offering a good balance of features and price.', 'Chipset: AMD B550\nMemory: 4x DDR4, 128GB Max\nExpansion Slots: 1x PCIe 4.0 x16\nStorage: 2x M.2 slots, 6x SATA 6Gb/s'),
('Corsair Dominator Platinum 32GB DDR5', 'CMT32GX5', 10999.45, 18, 'ram3.jpg', 0, 1, 1, 4, 5, 'Push the limits of performance with Corsair Dominator Platinum RGB DDR5 memory, optimized for Intel.', 'Capacity: 32GB (2x16GB)\nSpeed: 6200MHz\nCAS Latency: 36\nVoltage: 1.35V'),
('Kingston Renegade 2TB NVMe SSD', 'SFYRD2000', 10999.45, 25, 'storage2.jpg', 0, 1, 1, 5, 4, 'Kingston FURY Renegade PCIe 4.0 NVMe M.2 SSD provides cutting-edge performance in high capacities for gaming and hardware enthusiasts.', 'Capacity: 2TB\nInterface: NVMe PCIe 4.0\nRead Speed: 7,300 MB/s\nWrite Speed: 7,000 MB/s'),
('Logitech G915 TKL Keyboard', 'G915 TKL', 12649.45, 12, 'peripheral2.jpg', 0, 1, 1, 6, 9, 'The Logitech G915 TKL is a breakthrough in design and engineering, featuring LIGHTSPEED pro-grade wireless, advanced LIGHTSYNC RGB, and new high-performance low-profile mechanical switches.', 'Switch Type: GL Tactile\nConnectivity: LIGHTSPEED Wireless, Bluetooth\nBattery Life: 40 hours\nDimensions: 368 x 150 x 22 mm');

INSERT INTO expenses (title, amount, category, notes, created_at)
VALUES
('Website Hosting', 6600.00, 'Operations', 'Yearly server costs', NOW()),
('Advertising Campaign', 13750.00, 'Marketing', 'Facebook ads', NOW()),
('Office Supplies', 4125.00, 'Admin', 'Printer ink, paper', NOW());

INSERT INTO orders (user_id, order_code, payment_method, subtotal, tax, total, status)
VALUES
(1, 'ORDER-1001', 'Cash', 589.00, 70.68, 659.68, 'Completed'),
(2, 'ORDER-1002', 'Credit Card', 1599.00, 191.88, 1790.88, 'Shipped');

INSERT INTO order_items (name_snapshot, unit_price_snapshot, quantity, line_total, order_id, product_id)
VALUES
('Intel Core i9-13900K', 589.00, 1, 589.00, 1, 1),
('NVIDIA GeForce RTX 4090', 1599.00, 1, 1599.00, 2, 3);

INSERT INTO reviews (product_id, user_id, rating, comment)
VALUES
(1, 1, 5, 'This CPU is an absolute beast! Unbelievable performance.');
