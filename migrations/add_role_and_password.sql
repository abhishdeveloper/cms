ALTER TABLE users
    ADD COLUMN role ENUM('admin', 'customer') DEFAULT 'customer',
    ADD COLUMN password_hash VARCHAR(255) DEFAULT NULL,
    ADD COLUMN email VARCHAR(255) UNIQUE DEFAULT NULL;

-- Create default admin user (password: admin123)
INSERT INTO users (name, email, password_hash, role, phone, is_verified)
VALUES ('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0000000000', 1);
