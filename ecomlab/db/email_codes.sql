-- Email verification + forgot password with 6-digit codes. Run ONCE on each database (local XAMPP and the school server).

-- New sign-ups must verify their email before logging in. Accounts that already exist are treated as verified.
ALTER TABLE customer ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0;
UPDATE customer SET is_verified = 1;

-- Replaced by email_codes (from the earlier link-based reset, if it was created).
DROP TABLE IF EXISTS password_resets;

-- One row per code sent. purpose: 'verify' (sign-up) or 'reset' (forgot password).
-- Only a hash of the code is stored; after 5 wrong tries the code stops working.
CREATE TABLE IF NOT EXISTS email_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  purpose VARCHAR(10) NOT NULL,
  code_hash CHAR(64) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  KEY customer_purpose (customer_id, purpose),
  CONSTRAINT email_codes_ibfk_1 FOREIGN KEY (customer_id) REFERENCES customer (customer_id) ON DELETE CASCADE
) ENGINE=InnoDB;
