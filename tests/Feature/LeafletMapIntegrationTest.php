<?php

namespace Tests\Feature;

use App\Models\AlamatPelanggan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeafletMapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function alamat_pelanggan_can_store_location_as_array()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'location' => [-6.2088, 106.8456], // Jakarta coordinates
            'is_primary' => true,
        ]);

        $this->assertNotNull($alamat->latitude);
        $this->assertNotNull($alamat->longitude);
        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertEquals(106.8456, $alamat->longitude);
    }

    /** @test */
    public function alamat_pelanggan_location_attribute_returns_array()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $location = $alamat->location;
        
        $this->assertIsArray($location);
        $this->assertCount(2, $location);
        $this->assertEquals(-6.2088, $location[0]);
        $this->assertEquals(106.8456, $location[1]);
    }

    /** @test */
    public function alamat_pelanggan_location_attribute_returns_null_when_no_coordinates()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'is_primary' => true,
        ]);

        $this->assertNull($alamat->location);
    }

    /** @test */
    public function alamat_pelanggan_can_set_location_from_array()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = new AlamatPelanggan([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'is_primary' => true,
        ]);

        $alamat->location = [-6.2088, 106.8456];
        $alamat->save();

        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertEquals(106.8456, $alamat->longitude);
    }

    /** @test */
    public function alamat_pelanggan_clears_coordinates_when_location_set_to_null()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $alamat->location = null;
        $alamat->save();

        $this->assertNull($alamat->latitude);
        $this->assertNull($alamat->longitude);
    }

    /** @test */
    public function alamat_pelanggan_has_coordinates_method_works_correctly()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        // Test with coordinates
        $alamatWithCoords = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $this->assertTrue($alamatWithCoords->hasCoordinates());

        // Test without coordinates
        $alamatWithoutCoords = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 456, Jakarta',
            'is_primary' => false,
        ]);

        $this->assertFalse($alamatWithoutCoords->hasCoordinates());
    }

    /** @test */
    public function alamat_pelanggan_formatted_coordinates_works_correctly()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $this->assertEquals('-6.2088, 106.8456', $alamat->formatted_coordinates);

        // Test without coordinates
        $alamatWithoutCoords = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 456, Jakarta',
            'is_primary' => false,
        ]);

        $this->assertNull($alamatWithoutCoords->formatted_coordinates);
    }
}
