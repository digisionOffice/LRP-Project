<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AlamatPelanggan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AlamatPelangganResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function alamat_pelanggan_can_store_coordinates()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $this->assertDatabaseHas('alamat_pelanggan', [
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertEquals(106.8456, $alamat->longitude);
        $this->assertTrue($alamat->is_primary);
    }

    /** @test */
    public function alamat_pelanggan_belongs_to_pelanggan()
    {
        $pelanggan = Pelanggan::factory()->create();
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123',
            'is_primary' => true,
        ]);

        $this->assertEquals($pelanggan->id, $alamat->pelanggan->id);
        $this->assertEquals($pelanggan->nama, $alamat->pelanggan->nama);
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
    public function only_one_primary_address_per_customer()
    {
        $pelanggan = Pelanggan::factory()->create();

        // Create first primary address
        $alamat1 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'is_primary' => true,
        ]);

        // Create second primary address - should make first one non-primary
        $alamat2 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 2',
            'is_primary' => true,
        ]);

        // Refresh models from database
        $alamat1->refresh();
        $alamat2->refresh();

        $this->assertFalse($alamat1->is_primary);
        $this->assertTrue($alamat2->is_primary);
    }

    /** @test */
    public function alamat_has_coordinates_helper_method()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamatWithCoordinates = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $alamatWithoutCoordinates = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 2',
            'is_primary' => false,
        ]);

        $this->assertTrue($alamatWithCoordinates->hasCoordinates());
        $this->assertFalse($alamatWithoutCoordinates->hasCoordinates());
    }

    /** @test */
    public function alamat_formatted_coordinates_attribute()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $this->assertEquals('-6.2088, 106.8456', $alamat->formatted_coordinates);
    }

    /** @test */
    public function alamat_formatted_coordinates_returns_null_without_coordinates()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 1',
            'is_primary' => true,
        ]);

        $this->assertNull($alamat->formatted_coordinates);
    }

    /** @test */
    public function new_fields_are_fillable()
    {
        $alamat = new AlamatPelanggan();
        $fillable = $alamat->getFillable();

        $this->assertContains('latitude', $fillable);
        $this->assertContains('longitude', $fillable);
        $this->assertContains('id_pelanggan', $fillable);
        $this->assertContains('alamat', $fillable);
        $this->assertContains('is_primary', $fillable);
    }

    /** @test */
    public function coordinates_are_cast_to_decimal()
    {
        $alamat = new AlamatPelanggan();
        $casts = $alamat->getCasts();

        $this->assertEquals('decimal:8', $casts['latitude']);
        $this->assertEquals('decimal:8', $casts['longitude']);
        $this->assertEquals('boolean', $casts['is_primary']);
    }
}
