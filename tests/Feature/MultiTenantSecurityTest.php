<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Aset;
use App\Models\JenisAset;
use App\Models\KategoriAset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MultiTenantSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: User A cannot see User B's data
     */
    public function test_user_cannot_see_other_user_data()
    {
        // Create two users
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create jenis aset global (can be accessed by all users)
        $jenisAset = JenisAset::create([
            'nama' => 'Aset Tetap',
            'user_id' => null, // Global data
        ]);

        // Create kategori aset global
        $kategori = KategoriAset::create([
            'jenis_aset_id' => $jenisAset->id,
            'nama' => 'Mesin',
            'user_id' => null,
        ]);

        // User A creates an asset
        $this->actingAs($userA);
        $asetA = Aset::create([
            'kode_aset' => 'AST-001',
            'nama_aset' => 'Mesin User A',
            'kategori_aset_id' => $kategori->id,
            'harga_perolehan' => 10000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat' => 5,
            'metode_penyusutan' => 'garis_lurus',
            'tanggal_beli' => now(),
            'status' => 'aktif',
        ]);

        // User B creates an asset
        $this->actingAs($userB);
        $asetB = Aset::create([
            'kode_aset' => 'AST-002',
            'nama_aset' => 'Mesin User B',
            'kategori_aset_id' => $kategori->id,
            'harga_perolehan' => 20000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat' => 5,
            'metode_penyusutan' => 'garis_lurus',
            'tanggal_beli' => now(),
            'status' => 'aktif',
        ]);

        // User A login and check data
        $this->actingAs($userA);
        $userAData = Aset::all();
        
        $this->assertCount(1, $userAData);
        $this->assertEquals('Mesin User A', $userAData->first()->nama_aset);
        $this->assertNotContains('Mesin User B', $userAData->pluck('nama_aset'));

        // User B login and check data
        $this->actingAs($userB);
        $userBData = Aset::all();
        
        $this->assertCount(1, $userBData);
        $this->assertEquals('Mesin User B', $userBData->first()->nama_aset);
        $this->assertNotContains('Mesin User A', $userBData->pluck('nama_aset'));
    }

    /**
     * Test 2: All users can access global data
     */
    public function test_all_users_can_access_global_data()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create global jenis aset
        $jenisAsetGlobal = JenisAset::create([
            'nama' => 'Aset Tetap',
            'user_id' => null, // Global
        ]);

        // User A creates custom jenis aset
        $this->actingAs($userA);
        $jenisAsetUserA = JenisAset::create([
            'nama' => 'Aset Custom A',
            'user_id' => $userA->id,
        ]);

        // Check User A can see global + own data
        $this->actingAs($userA);
        $dataA = JenisAset::all();
        $this->assertCount(2, $dataA); // Global + own
        $this->assertContains('Aset Tetap', $dataA->pluck('nama'));
        $this->assertContains('Aset Custom A', $dataA->pluck('nama'));

        // Check User B can see only global data
        $this->actingAs($userB);
        $dataB = JenisAset::all();
        $this->assertCount(1, $dataB); // Only global
        $this->assertContains('Aset Tetap', $dataB->pluck('nama'));
        $this->assertNotContains('Aset Custom A', $dataB->pluck('nama'));
    }

    /**
     * Test 3: User cannot access other user's data via direct URL
     */
    public function test_user_cannot_access_other_user_asset_via_url()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create global data
        $jenisAset = JenisAset::create(['nama' => 'Aset Tetap', 'user_id' => null]);
        $kategori = KategoriAset::create([
            'jenis_aset_id' => $jenisAset->id,
            'nama' => 'Mesin',
            'user_id' => null,
        ]);

        // User A creates an asset
        $this->actingAs($userA);
        $asetA = Aset::create([
            'kode_aset' => 'AST-001',
            'nama_aset' => 'Mesin User A',
            'kategori_aset_id' => $kategori->id,
            'harga_perolehan' => 10000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat' => 5,
            'metode_penyusutan' => 'garis_lurus',
            'tanggal_beli' => now(),
            'status' => 'aktif',
        ]);

        // User B tries to access User A's asset via direct URL
        $this->actingAs($userB);
        
        // Should redirect with error (not show the asset)
        $response = $this->get(route('master-data.aset.show', $asetA->id));
        $response->assertRedirect(route('master-data.aset.index'));
        $response->assertSessionHas('error');
    }

    /**
     * Test 4: Eager loading relationships with global data
     */
    public function test_relationships_load_global_data_correctly()
    {
        $userA = User::factory()->create();

        // Create global jenis aset
        $jenisAset = JenisAset::create([
            'nama' => 'Aset Tetap',
            'user_id' => null, // Global
        ]);

        // Create global kategori aset
        $kategori = KategoriAset::create([
            'jenis_aset_id' => $jenisAset->id,
            'nama' => 'Mesin',
            'user_id' => null, // Global
        ]);

        // User A creates an asset
        $this->actingAs($userA);
        $aset = Aset::create([
            'kode_aset' => 'AST-001',
            'nama_aset' => 'Mesin User A',
            'kategori_aset_id' => $kategori->id,
            'harga_perolehan' => 10000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat' => 5,
            'metode_penyusutan' => 'garis_lurus',
            'tanggal_beli' => now(),
            'status' => 'aktif',
        ]);

        // Load with relationships
        $asetWithRelations = Aset::with(['kategori.jenisAset'])->find($aset->id);

        // Assert relationships loaded correctly
        $this->assertNotNull($asetWithRelations->kategori);
        $this->assertEquals('Mesin', $asetWithRelations->kategori->nama);
        
        $this->assertNotNull($asetWithRelations->kategori->jenisAset);
        $this->assertEquals('Aset Tetap', $asetWithRelations->kategori->jenisAset->nama);
    }

    /**
     * Test 5: Auto-fill user_id on create
     */
    public function test_user_id_auto_filled_on_create()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create global data first
        $jenisAset = JenisAset::create(['nama' => 'Aset Tetap', 'user_id' => null]);
        $kategori = KategoriAset::create([
            'jenis_aset_id' => $jenisAset->id,
            'nama' => 'Mesin',
            'user_id' => null,
        ]);

        // Create aset without explicitly setting user_id
        $aset = Aset::create([
            'kode_aset' => 'AST-001',
            'nama_aset' => 'Mesin Test',
            'kategori_aset_id' => $kategori->id,
            'harga_perolehan' => 10000000,
            'biaya_perolehan' => 0,
            'nilai_residu' => 0,
            'umur_manfaat' => 5,
            'metode_penyusutan' => 'garis_lurus',
            'tanggal_beli' => now(),
            'status' => 'aktif',
            // user_id NOT set explicitly
        ]);

        // user_id should be auto-filled
        $this->assertEquals($user->id, $aset->user_id);
    }
}
