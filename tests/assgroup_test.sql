-- Drop existing tables if they exist
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS user;

-- Create user table with InnoDB engine
CREATE TABLE user (
    id int(11) NOT NULL AUTO_INCREMENT,
    firstname varchar(25) NOT NULL,
    lastname varchar(25) NOT NULL,
    email varchar(100) NOT NULL,
    phoneno varchar(20) NOT NULL,
    address varchar(120) NOT NULL,
    password varchar(100) NOT NULL,
    isAdmin TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Create products table with InnoDB engine
CREATE TABLE products (
    id int(11) NOT NULL AUTO_INCREMENT,
    productname varchar(100) NOT NULL,
    price decimal(10,2) NOT NULL,
    description text NOT NULL,
    availableunit int(11) NOT NULL,
    item varchar(100) NOT NULL,
    image text NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Create cart table with InnoDB engine
CREATE TABLE cart (
    id int(11) NOT NULL AUTO_INCREMENT,
    userid int(11) NOT NULL,
    productid int(11) NOT NULL,
    quantity int(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (userid) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (productid) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create orders table with InnoDB engine
CREATE TABLE orders (
    order_id int(11) NOT NULL AUTO_INCREMENT,
    userid int(11) NOT NULL,
    billingaddress varchar(255) NOT NULL,
    phoneno varchar(20) NOT NULL,
    orderdate date NOT NULL,
    deliverydate date DEFAULT NULL,
    delivery varchar(50) NOT NULL,
    payment_status varchar(50) DEFAULT 'Pending',
    total decimal(10,2) NOT NULL,
    delivery_fee decimal(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (order_id),
    FOREIGN KEY (userid) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create order_items table with InnoDB engine
CREATE TABLE order_items (
    item_id int(11) NOT NULL AUTO_INCREMENT,
    order_id int(11) NOT NULL,
    productid int(11) NOT NULL,
    quantity int(11) NOT NULL,
    PRIMARY KEY (item_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (productid) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert test data
INSERT INTO user (firstname, lastname, email, phoneno, address, password, isAdmin) VALUES
('Test', 'Admin', 'admin@gmail.com', '1234567890', 'Test Address', '202cb962ac59075b964b07152d234b70', 1),
('Test', 'User', 'user@test.com', '0987654321', 'User Address', '202cb962ac59075b964b07152d234b70', 0);

INSERT INTO products (productname, price, description, availableunit, item, image) VALUES
('Test Product 1', 99.99, 'Test Description 1', 100, 'clothes', 'test1.png'),
('Test Product 2', 149.99, 'Test Description 2', 100, 'shoes', 'test2.png');
