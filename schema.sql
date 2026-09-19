CREATE DATABASE IF NOT EXISTS inventaris_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE inventaris_db;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    category_id INT UNSIGNED NOT NULL,
    supplier_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (supplier_id)
        REFERENCES suppliers(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
('Laptop'),
('Smartphone'),
('Aksesoris'),
('Monitor'),
('Komponen PC');

INSERT INTO suppliers (name, phone) VALUES
('PT Teknologi Nusantara', '081234567801'),
('CV Digital Jaya', '081234567802'),
('PT Komputer Indonesia', '081234567803'),
('CV Sumber Elektronik', '081234567804'),
('PT Mitra Teknologi', '081234567805');

INSERT INTO products (name, price, stock, category_id, supplier_id) VALUES
('ASUS Vivobook 15', 8500000.00, 10, 1, 1),
('Samsung Galaxy A55', 5999000.00, 15, 2, 2),
('Logitech Wireless Mouse', 350000.00, 25, 3, 3),
('LG UltraGear 24GN60R', 3200000.00, 8, 4, 4),
('Kingston NV2 1TB SSD', 950000.00, 20, 5, 5);

CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_supplier ON products(supplier_id);