<?php
// Debug script to see what data is being sent
// Add this temporarily to BiayaBahanController store method

// At the beginning of store method, add:
\Log::info('=== BIAYA BAHAN STORE DEBUG ===');
\Log::info('Request All:', $request->all());
\Log::info('Bahan Baku Array:', $request->input('bahan_baku'));
\Log::info('Produk ID:', $request->input('produk_id'));

// Then continue with validation...
