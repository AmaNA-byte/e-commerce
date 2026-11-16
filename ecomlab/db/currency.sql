-- USD / GHS: prices are stored in USD and Paystack charges in GHS.
-- Saves the exchange rate used for each payment (cedis per 1 US dollar) so receipts show the exact conversion.
-- Run ONCE on each database (local XAMPP and the school server).
ALTER TABLE payment ADD COLUMN usd_rate DECIMAL(12,6) NULL;
