<?php

echo "=== DEBUG BIAYA BAHAN ZERO ISSUE ===\n\n";

echo "1. Problem Analysis:\n";
echo "- Product 'Jasuke' appears in dropdown (good!)\n";
echo "- Biaya bahan shows Rp 0 (problem!)\n";
echo "- This means query works but calculation is wrong\n\n";

echo "2. Expected Data Flow:\n";
echo "BBB Data: bom_job_bbb with user_id=1, produk_id=X\n";
echo "Costing Data: bom_job_costings with produk_id=X, user_id=1\n";
echo "Calculation: total_bbb + total_bahan_pendukung = total_biaya_bahan\n";
echo "Display: data-biaya-bahan attribute should have calculated value\n\n";

echo "3. Potential Issues:\n";
echo "❌ bom_job_costings doesn't exist for the product\n";
echo "❌ bom_job_costings.total_bbb is NULL/0\n";
echo "❌ bom_job_costings.total_bahan_pendukung is NULL/0\n";
echo "❌ LEFT JOIN returns NULL for bom_job_costings columns\n";
echo "❌ DB::raw() calculation fails with NULL values\n";
echo "❌ data-biaya-bahan attribute not getting correct value\n\n";

echo "4. SQL Queries to Check:\n\n";

echo "Query 1: Find product ID for 'Jasuke'\n";
echo "SELECT id, nama_produk FROM produks WHERE user_id = 1 AND nama_produk = 'Jasuke';\n\n";

echo "Query 2: Check BBB data for that product\n";
echo "SELECT * FROM bom_job_bbb WHERE user_id = 1 AND produk_id = [PRODUCT_ID];\n\n";

echo "Query 3: Check bom_job_costings data\n";
echo "SELECT * FROM bom_job_costings WHERE produk_id = [PRODUCT_ID] AND user_id = 1;\n\n";

echo "Query 4: Test the exact controller query\n";
echo "SELECT produks.*, \n";
echo "       bom_job_costings.total_bbb, \n";
echo "       bom_job_costings.total_bahan_pendukung,\n";
echo "       (bom_job_costings.total_bbb + bom_job_costings.total_bahan_pendukung) as total_biaya_bahan\n";
echo "FROM produks \n";
echo "LEFT JOIN bom_job_costings ON produks.id = bom_job_costings.produk_id \n";
echo "                        AND bom_job_costings.user_id = 1\n";
echo "WHERE produks.user_id = 1 AND produks.id = [PRODUCT_ID];\n\n";

echo "5. Alternative Approach:\n";
echo "If bom_job_costings doesn't exist, calculate from bom_job_bbb directly:\n";
echo "\$totalBiayaBahan = DB::table('bom_job_bbb')\n";
echo "    ->where('user_id', auth()->id())\n";
echo "    ->where('produk_id', \$produk->id)\n";
echo "    ->sum('subtotal');\n\n";

echo "6. Debug Steps:\n";
echo "1. Check Laravel logs for debug info\n";
echo "2. Run SQL queries above to verify data\n";
echo "3. Check if bom_job_costings table has data\n";
echo "4. Verify product ID mapping\n";
echo "5. Test calculation logic\n\n";

echo "=== DEBUG COMPLETE ===\n";
echo "Run the SQL queries to identify why biaya bahan is 0\n";
echo "Most likely cause: bom_job_costings data missing or NULL\n";
