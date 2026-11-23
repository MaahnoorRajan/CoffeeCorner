-- Update only unchanged demo prices. Historical orders keep their snapshots.
START TRANSACTION;
UPDATE menu_items SET base_price_kgs = 190 WHERE name = 'Cappuccino' AND base_price_kgs = 140;
UPDATE menu_items SET base_price_kgs = 230 WHERE name = 'Latte' AND base_price_kgs = 150;
COMMIT;
