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


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
