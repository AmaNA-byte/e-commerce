-- Task 13: run once on each database (local XAMPP and the school server).
-- orders.created_at: exact time the order was placed (the handout's step 2).
-- payment.transaction_ref: the Paystack reference. UNIQUE so one payment can never pay for two orders.
ALTER TABLE orders ADD COLUMN created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payment ADD COLUMN transaction_ref VARCHAR(100) NULL, ADD UNIQUE KEY transaction_ref (transaction_ref);
