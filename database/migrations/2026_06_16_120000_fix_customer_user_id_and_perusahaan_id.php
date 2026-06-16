<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CRITICAL FIX: Existing customers with NULL user_id need to be fixed
     * This ensures they are properly scoped to their store owner
     * 
     * For each order, find the seller (product owner) and:
     * 1. Set customer's user_id to seller's user_id
     * 2. Set customer's perusahaan_id to seller's perusahaan_id
     */
    public function up(): void
    {
        // Get all customers with NULL user_id
        $customersWithNullUserId = DB::table('users')
            ->where('role', 'pelanggan')
            ->whereNull('user_id')
            ->get();

        foreach ($customersWithNullUserId as $customer) {
            // Try to find orders for this customer
            // Orders table structure might vary, so we check column existence first
            $sellerId = null;
            $perusahaanId = null;
            
            // Try method 1: Orders linked via pelanggan_id or user_id
            if (Schema::hasColumn('orders', 'user_id')) {
                // If orders.user_id is the customer
                $order = DB::table('orders')
                    ->where('user_id', $customer->id)
                    ->first();
                
                if ($order) {
                    // Get the seller info from order (if seller_id exists)
                    if (Schema::hasColumn('orders', 'seller_id')) {
                        $seller = DB::table('users')->where('id', $order->seller_id)->first();
                    } elseif (Schema::hasColumn('orders', 'created_by')) {
                        $seller = DB::table('users')->where('id', $order->created_by)->first();
                    } else {
                        // Default: assign to first user (owner)
                        $seller = DB::table('users')->where('role', 'owner')->first();
                    }
                    
                    if ($seller) {
                        $sellerId = $seller->id;
                        $perusahaanId = $seller->perusahaan_id ?? null;
                    }
                }
            }
            
            // Method 2: Check carts if no orders found
            if (!$sellerId && Schema::hasColumn('carts', 'user_id')) {
                $cart = DB::table('carts')
                    ->where('user_id', $customer->id)
                    ->first();

                if ($cart && Schema::hasColumn('carts', 'produk_id')) {
                    // Get product's owner
                    $product = DB::table('produks')
                        ->where('id', $cart->produk_id)
                        ->first();
                    
                    if ($product && Schema::hasColumn('produks', 'user_id')) {
                        $seller = DB::table('users')
                            ->where('id', $product->user_id)
                            ->first();
                        
                        if ($seller) {
                            $sellerId = $seller->id;
                            $perusahaanId = $seller->perusahaan_id ?? null;
                        }
                    }
                }
            }
            
            // If still no seller found, assign to first owner
            if (!$sellerId) {
                $owner = DB::table('users')->where('role', 'owner')->first();
                if ($owner) {
                    $sellerId = $owner->id;
                    $perusahaanId = $owner->perusahaan_id ?? null;
                }
            }
            
            // Update customer if we found a seller
            if ($sellerId) {
                DB::table('users')
                    ->where('id', $customer->id)
                    ->update([
                        'user_id' => $sellerId,
                        'perusahaan_id' => $perusahaanId,
                        'updated_at' => now(),
                    ]);
                
                echo "✓ Fixed customer {$customer->email}: assigned to seller {$sellerId}\n";
            } else {
                echo "⚠ Customer {$customer->email}: could not determine seller, skipping\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed
        // Do not revert user_id and perusahaan_id back to NULL
    }
};
