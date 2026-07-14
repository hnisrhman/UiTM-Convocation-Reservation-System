
USE robeReserve;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user'
);

-- Products table (Inventory)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100),
    product_type VARCHAR(50),
    description TEXT,
    price DECIMAL(8,2),
    image_path VARCHAR(255)
);

-- Reservations table
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    robe_size VARCHAR(20),
    robe_type VARCHAR(50),
    graduation_cap VARCHAR(50),
    hood_code VARCHAR(50),
    collection_date DATE,
    total_price DECIMAL(8,2),
    status VARCHAR(20) DEFAULT 'Pending',
    payment_status VARCHAR(20) DEFAULT 'Unpaid',
    payment_ref VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert Product Inventory
INSERT INTO products (product_name, product_type, description, price, image_path) VALUES
('Diploma Robe', 'robe', 'Diploma robe rental', 15.00, 'images/diploma_robe.jpg'),
('Degree Robe', 'robe', 'Degree robe rental', 25.00, 'images/degree_robe.jpg'),
('Master Robe', 'robe', 'Master robe rental', 35.00, 'images/master_robe.jpg'),
('PhD Robe', 'robe', 'PhD robe rental', 40.00, 'images/phd_robe.jpg'),
('Hood', 'hood', 'Program-specific hood', 10.00, 'images/hood.jpg'),
('Mortar Board', 'cap', 'Mortar board cap', 10.00, 'images/mortarboard.jpg'),
('Bonnet', 'cap', 'Bonnet cap', 15.00, 'images/bonnet.jpg'),
('Diploma Set', 'package', 'One set (robe, hood, mortar board)', 30.00, 'images/diploma_set.jpg'),
('Degree Set', 'package', 'One set (robe, hood, mortar board)', 40.00, 'images/degree_set.jpg'),
('Master Set', 'package', 'One set (robe, hood, bonnet)', 50.00, 'images/master_set.jpg'),
('PhD Set', 'package', 'One set (robe, hood, bonnet)', 65.00, 'images/phd_set.jpg');

-- Insert Admin Account (password is 'admin123')
INSERT INTO users (full_name, email, password, role) VALUES
('Admin UiTM', 'admin@uitm.my', '$2y$10$1E7pPjZkdhc5Em1.l4H/keVXQ2h8ZlYxjJwLz0pTcdDNYs3XItBxi', 'admin');
