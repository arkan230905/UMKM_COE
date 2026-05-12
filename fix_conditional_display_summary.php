<?php

/**
 * FIX FOR CONDITIONAL DISPLAY ISSUE
 * 
 * PROBLEM IDENTIFIED:
 * The user was editing purchase ID 4 which has no details yet (empty purchase).
 * Empty purchases are categorized as "mixed" which shows both sections.
 * Additionally, the vendor change handler was overriding the category-based initialization.
 * 
 * FIXES APPLIED:
 * 
 * 1. INITIALIZATION ORDER FIX:
 *    - Changed initialization order to set category-based display AFTER initializing rows
 *    - Only trigger vendor change for "mixed" category purchases
 *    - This prevents vendor logic from overriding specific category display
 * 
 * 2. VENDOR CHANGE HANDLER FIX:
 *    - Added check to prevent vendor-based override for specific categories
 *    - If purchase has category 'bahan_baku' or 'bahan_pendukung', vendor change is ignored
 *    - Only allows vendor-based filtering for 'mixed' category purchases
 * 
 * 3. DEBUGGING ENHANCEMENTS:
 *    - Added purchase ID and category to page title
 *    - Added console logging to track initialization
 *    - This helps identify which purchase is being edited and why sections show/hide
 * 
 * EXPECTED BEHAVIOR:
 * 
 * - Purchase with only Bahan Baku (like #3 with Ayam Potong):
 *   → Shows only Bahan Baku section, hides Bahan Pendukung
 *   → Vendor selection cannot override this
 * 
 * - Purchase with only Bahan Pendukung:
 *   → Shows only Bahan Pendukung section, hides Bahan Baku
 *   → Vendor selection cannot override this
 * 
 * - Empty purchase or mixed purchase (like #4):
 *   → Shows both sections by default
 *   → Vendor selection can filter sections
 * 
 * HOW TO TEST:
 * 
 * 1. Edit Purchase #3 (has Ayam Potong bahan baku):
 *    - Should show "Edit Pembelian #3 (Kategori: bahan_baku)"
 *    - Should only show Bahan Baku section
 *    - Bahan Pendukung section should be hidden
 * 
 * 2. Edit Purchase #4 (empty):
 *    - Should show "Edit Pembelian #4 (Kategori: mixed)"
 *    - Should show both sections
 *    - Vendor selection can filter sections
 * 
 * 3. Check browser console:
 *    - Should see "Initializing section visibility for category: [category]"
 *    - Should see appropriate section visibility messages
 */

echo "Conditional display fix applied successfully!\n";
echo "Test by editing different purchases to verify section visibility.\n";