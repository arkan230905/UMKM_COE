-- Add stock layer for product #2
INSERT INTO stock_layers (
    item_type, 
    item_id, 
    qty, 
    remaining_qty, 
    unit_cost, 
    ref_type, 
    ref_id, 
    tanggal,
    created_at, 
    updated_at
) VALUES (
    'product', 
    2, 
    10, 
    10, 
    50000, 
    'initial_stock', 
    0, 
    '2026-04-08',
    NOW(), 
    NOW()
);