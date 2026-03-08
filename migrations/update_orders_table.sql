ALTER TABLE orders
    MODIFY COLUMN order_status ENUM('pending', 'processing', 'completed', 'cancelled', 'draft') DEFAULT 'pending',
    ADD COLUMN recovery_message_sent BOOLEAN DEFAULT FALSE,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
