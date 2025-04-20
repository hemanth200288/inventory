-- Create the database
CREATE DATABASE invoice_management;
USE invoice_management;

-- Create clients table
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255) NOT NULL,
    is_client_inv_req_auto_incre TINYINT(1) DEFAULT 0,
    order_prefix VARCHAR(10) NOT NULL
);

-- Create invoices table
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    order_no VARCHAR(20) NOT NULL,
    order_date DATE NOT NULL,
    payment_method TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Create invoice_items table
CREATE TABLE invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    task_desc TEXT NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_subtask TINYINT(1) DEFAULT 0,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
