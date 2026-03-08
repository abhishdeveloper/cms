ALTER TABLE orders
    MODIFY COLUMN order_status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled', 'draft', 'delivered') DEFAULT 'pending',
    ADD COLUMN delivered_at TIMESTAMP NULL,
    ADD COLUMN review_requested BOOLEAN DEFAULT FALSE;
