<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TransaksiPenjualan;
use App\Models\Pelanggan;
use App\Models\AlamatPelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransaksiPenjualanEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function transaksi_penjualan_can_store_nomor_sph_and_data_dp()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $transaksi = TransaksiPenjualan::create([
            'kode' => 'SO-001',
            'tipe' => 'dagang',
            'tanggal' => now(),
            'id_pelanggan' => $pelanggan->id,
            'nomor_sph' => 'SPH-001',
            'data_dp' => 1000000.50,
        ]);

        $this->assertDatabaseHas('transaksi_penjualan', [
            'kode' => 'SO-001',
            'nomor_sph' => 'SPH-001',
            'data_dp' => 1000000.50,
        ]);

        $this->assertEquals('SPH-001', $transaksi->nomor_sph);
        $this->assertEquals(1000000.50, $transaksi->data_dp);
    }

    /** @test */
    public function transaksi_penjualan_can_upload_dokumen_sph()
    {
        $pelanggan = Pelanggan::factory()->create();
        $transaksi = TransaksiPenjualan::factory()->create(['id_pelanggan' => $pelanggan->id]);

        $file = UploadedFile::fake()->create('sph-document.pdf', 1000, 'application/pdf');
        
        $transaksi->addMediaFromRequest('dokumen_sph')
            ->toMediaCollection('dokumen_sph');

        $this->assertCount(1, $transaksi->getMedia('dokumen_sph'));
        $this->assertEquals('dokumen_sph', $transaksi->getFirstMedia('dokumen_sph')->collection_name);
    }

    /** @test */
    public function transaksi_penjualan_can_upload_dokumen_dp()
    {
        $pelanggan = Pelanggan::factory()->create();
        $transaksi = TransaksiPenjualan::factory()->create(['id_pelanggan' => $pelanggan->id]);

        $file = UploadedFile::fake()->create('dp-document.pdf', 1000, 'application/pdf');
        
        $transaksi->addMediaFromRequest('dokumen_dp')
            ->toMediaCollection('dokumen_dp');

        $this->assertCount(1, $transaksi->getMedia('dokumen_dp'));
        $this->assertEquals('dokumen_dp', $transaksi->getFirstMedia('dokumen_dp')->collection_name);
    }

    /** @test */
    public function pelanggan_can_store_npwp()
    {
        $pelanggan = Pelanggan::create([
            'kode' => 'CUST-001',
            'nama' => 'Test Customer',
            'type' => 'Corporate',
            'npwp' => '01.234.567.8-901.000',
        ]);

        $this->assertDatabaseHas('pelanggan', [
            'kode' => 'CUST-001',
            'npwp' => '01.234.567.8-901.000',
        ]);

        $this->assertEquals('01.234.567.8-901.000', $pelanggan->npwp);
    }

    /** @test */
    public function pelanggan_can_have_multiple_alamat()
    {
        $pelanggan = Pelanggan::factory()->create();

        $alamat1 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'is_primary' => true,
        ]);

        $alamat2 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 2',
            'is_primary' => false,
        ]);

        $this->assertCount(2, $pelanggan->alamatPelanggan);
        $this->assertTrue($alamat1->is_primary);
        $this->assertFalse($alamat2->is_primary);
    }

    /** @test */
    public function alamat_pelanggan_belongs_to_pelanggan()
    {
        $pelanggan = Pelanggan::factory()->create();
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'is_primary' => true,
        ]);

        $this->assertEquals($pelanggan->id, $alamat->pelanggan->id);
        $this->assertEquals($pelanggan->nama, $alamat->pelanggan->nama);
    }

    /** @test */
    public function legacy_attachment_fields_are_not_fillable()
    {
        $transaksi = new TransaksiPenjualan();
        $fillable = $transaksi->getFillable();

        $this->assertNotContains('attachment_path', $fillable);
        $this->assertNotContains('attachment_original_name', $fillable);
        $this->assertNotContains('attachment_mime_type', $fillable);
        $this->assertNotContains('attachment_size', $fillable);
    }

    /** @test */
    public function new_fields_are_fillable()
    {
        $transaksi = new TransaksiPenjualan();
        $fillable = $transaksi->getFillable();

        $this->assertContains('nomor_sph', $fillable);
        $this->assertContains('data_dp', $fillable);
    }
}
