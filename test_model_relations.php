<?php

echo "=== TESTING MODEL RELATIONS ===\n\n";

echo "1. Check if Produk model exists and is correct\n";
echo "Need to verify:\n";
echo "- Produk model exists\n";
echo "- Produk table has 'user_id' column\n";
echo "- Produk table has correct structure\n\n";

echo "2. Check if BomJobBBB model exists and is correct\n";
echo "Need to verify:\n";
echo "- BomJobBBB model exists\n";
echo "- BomJobBBB table has 'user_id' and 'produk_id' columns\n";
echo "- Relation works correctly\n\n";

echo "3. Database Structure Check:\n";
echo "Produk table should have:\n";
echo "- id (primary key)\n";
echo "- user_id (foreign key to users)\n";
echo "- nama_produk\n";
echo "- other columns\n\n";

echo "BomJobBBB table should have:\n";
echo "- id (primary key)\n";
echo "- user_id (foreign key to users)\n";
echo "- produk_id (foreign key to produks)\n";
echo "- bahan_baku_id\n";
echo "- jumlah, satuan, harga_satuan, subtotal\n\n";

echo "4. SQL Queries to Run:\n\n";

echo "Query 1: Check Produk table structure\n";
echo "DESCRIBE produks;\n\n";

echo "Query 2: Check BomJobBBB table structure\n";
echo "DESCRIBE bom_job_bbb;\n\n";

echo "Query 3: Verify data exists\n";
echo "SELECT COUNT(*) as bbb_count FROM bom_job_bbb WHERE user_id = 1;\n";
echo "SELECT COUNT(*) as produk_count FROM produks WHERE user_id = 1;\n\n";

echo "Query 4: Check specific product\n";
echo "SELECT * FROM produks WHERE id = 2;\n";
echo "SELECT * FROM produks WHERE user_id = 1 AND id = 2;\n\n";

echo "Query 5: Check BBB data for product 2\n";
echo "SELECT * FROM bom_job_bbb WHERE produk_id = 2 AND user_id = 1;\n\n";

echo "5. Potential Issues:\n";
echo "❌ Produk model might not exist or be incorrect\n";
echo "❌ Produk table might not have user_id column\n";
echo "❌ Produk with id=2 might not exist\n";
echo "❌ Produk with id=2 might have different user_id\n";
echo "❌ BomJobBBB model might not exist\n";
echo "❌ Database connection issue\n\n";

echo "6. Alternative Approach:\n";
echo "If current approach fails, try direct DB query:\n";
echo "\$produks = DB::table('produks')\n";
echo "    ->where('user_id', auth()->id())\n";
echo "    ->whereIn('id', function(\$query) {\n";
echo "        \$query->select('produk_id')\n";
echo "            ->from('bom_job_bbb')\n";
echo "            ->where('user_id', auth()->id());\n";
echo "    })\n";
echo "    ->get();\n\n";

echo "=== TEST COMPLETE ===\n";
echo "Run the SQL queries above to identify the exact issue\n";
