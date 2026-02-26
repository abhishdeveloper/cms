-- Alter Products Table
ALTER TABLE products ADD COLUMN is_quote_only BOOLEAN DEFAULT FALSE;

-- Create Quotations Table
CREATE TABLE IF NOT EXISTS quotations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_phone VARCHAR(20) NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    requirements TEXT,
    budget_range VARCHAR(50),
    status ENUM('pending', 'reviewed', 'rejected', 'converted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
