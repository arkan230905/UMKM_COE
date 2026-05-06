<?php

echo "=== DEBUG PRODUK LOADING ISSUE ===\n\n";

echo "1. Database Analysis:\n";
echo "✅ Data exists in bom_job_bbb:\n";
echo "   - id: 5\n";
echo "   - user_id: 1\n";
echo "   - produk_id: 2\n";
echo "   - bahan_baku_id: 8\n";
echo "   - jumlah: 50.0000\n";
echo "   - satuan: Gram\n";
echo "   - harga_satuan: 50.00\n";
echo "   - subtotal: 2500.00\n\n";

echo "2. Controller Query Analysis:\n";
echo "Current BomController@create query:\n";
echo "\$produkIds = \\App\\Models\\BomJobBBB::where('user_id', auth()->id())\n";
echo "    ->pluck('produk_id')\n";
echo "    ->unique();\n\n";

echo "\$produks = Produk::where('user_id', auth()->id())\n";
echo "    ->whereIn('id', \$produkIds)\n";
echo "    ->with(['bomJobCosting' => function(\$query) {\n";
echo "        \$query->where('user_id', auth()->id());\n";
echo "    }])\n";
echo "    ->get();\n\n";

echo "3. Step-by-step Debug:\n";

// Simulate the controller query
echo "Step 1: Get produk_id from bom_job_bbb for user_id = 1\n";
$produkIds = [2]; // From the database record
echo "   Result: [" . implode(', ', $produkIds) . "]\n\n";

echo "Step 2: Get produk with user_id = 1 and id in [2]\n";
echo "   Query: Produk::where('user_id', 1)->whereIn('id', [2])->get()\n\n";

echo "Step 3: Check if produk exists\n";
echo "   Need to verify: Produk with id=2 and user_id=1 exists\n\n";

echo "4. Potential Issues:\n";
echo "❌ Produk with id=2 might not exist\n";
echo "❌ Produk with id=2 might have different user_id\n";
echo "❌ Produk with id=2 might be deleted/inactive\n";
echo "❌ bomJobCosting relation might be missing\n\n";

echo "5. Debug SQL Queries:\n";
echo "Check these queries in database:\n\n";

echo "Query 1: Verify BBB data exists\n";
echo "SELECT COUNT(*) as count FROM bom_job_bbb WHERE user_id = 1;\n\n";

echo "Query 2: Verify produk exists\n";
echo "SELECT COUNT(*) as count FROM produks WHERE id = 2 AND user_id = 1;\n\n";

echo "Query 3: Check produk details\n";
echo "SELECT * FROM produks WHERE id = 2 AND user_id = 1;\n\n";

echo "Query 4: Check bom_job_costing\n";
echo "SELECT * FROM bom_job_costings WHERE produk_id = 2 AND user_id = 1;\n\n";

echo "6. Expected Results:\n";
echo "✅ If produk exists: Should appear in dropdown\n";
echo "✅ If bomJobCosting exists: Should show biaya bahan\n";
echo "✅ If both exist: Dropdown should be populated\n\n";

echo "=== DEBUG COMPLETE ===\n";
echo "Run the SQL queries above to identify the exact issue\n";
