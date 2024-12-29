-- Drop existing tables if they exist
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS user;

-- Create user table
CREATE TABLE user (
    id int(11) NOT NULL AUTO_INCREMENT,
    firstname varchar(25) NOT NULL,
    lastname varchar(25) NOT NULL,
    email varchar(100) NOT NULL,
    phoneno varchar(20) NOT NULL,
    address varchar(120) NOT NULL,
    password varchar(100) NOT NULL,
    isAdmin int(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

-- Create products table
CREATE TABLE products (
    id int(11) NOT NULL AUTO_INCREMENT,
    productname varchar(100) NOT NULL,
    price decimal(10,2) NOT NULL,
    description text,
    PRIMARY KEY (id)
);

-- Create cart table
CREATE TABLE cart (
    id int(11) NOT NULL AUTO_INCREMENT,
    userid int(11) NOT NULL,
    productid int(11) NOT NULL,
    quantity int(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    FOREIGN KEY (userid) REFERENCES user(id),
    FOREIGN KEY (productid) REFERENCES products(id)
);

-- Create orders table
CREATE TABLE orders (
    order_id int(11) NOT NULL AUTO_INCREMENT,
    userid int(11) NOT NULL,
    billingaddress varchar(255) NOT NULL,
    phoneno varchar(20) NOT NULL,
    orderdate datetime NOT NULL,
    deliverydate datetime DEFAULT NULL,
    delivery varchar(50) NOT NULL,
    payment_status varchar(50) DEFAULT 'Pending',
    total decimal(10,2) NOT NULL,
    delivery_fee decimal(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (order_id),
    FOREIGN KEY (userid) REFERENCES user(id)
);

-- Create order_items table
CREATE TABLE order_items (
    id int(11) NOT NULL AUTO_INCREMENT,
    order_id int(11) NOT NULL,
    productid int(11) NOT NULL,
    quantity int(11) NOT NULL,
    price decimal(10,2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (productid) REFERENCES products(id)
);