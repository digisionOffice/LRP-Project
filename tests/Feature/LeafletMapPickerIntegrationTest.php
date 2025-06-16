<?php

namespace Tests\Feature;

use App\Models\AlamatPelanggan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeafletMapPickerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations to ensure database is set up
        $this->artisan('migrate');
    }

    /** @test */
    public function can_create_alamat_pelanggan_with_location_data()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamatData = [
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'location' => [-6.2088, 106.8456], // Jakarta coordinates
            'is_primary' => true,
        ];

        $alamat = AlamatPelanggan::create($alamatData);

        $this->assertDatabaseHas('alamat_pelanggan', [
            'id' => $alamat->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'location' => '[-6.2088,106.8456]',
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function location_attribute_works_with_plugin_format()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'location' => [-6.2088, 106.8456],
            'is_primary' => true,
        ]);

        // Refresh from database
        $alamat->refresh();

        // Test that location attribute returns the correct format
        $this->assertEquals([-6.2088, 106.8456], $alamat->location);
        $this->assertTrue($alamat->hasCoordinates());
        $this->assertEquals('-6.20880000, 106.84560000', $alamat->formatted_coordinates);
    }

    /** @test */
    public function can_update_location_through_location_attribute()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'is_primary' => true,
        ]);

        // Update location using the location attribute (as the plugin would)
        $alamat->location = [-7.2575, 112.7521]; // Surabaya coordinates
        $alamat->save();

        $alamat->refresh();

        $this->assertEquals([-7.2575, 112.7521], $alamat->location);
        $this->assertEquals(-7.2575, $alamat->latitude);
        $this->assertEquals(112.7521, $alamat->longitude);
        $this->assertEquals('[-7.2575,112.7521]', $alamat->getAttributes()['location']);
    }

    /** @test */
    public function backward_compatibility_with_existing_lat_lng_data()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        // Create record with only lat/lng (simulating existing data)
        $alamat = new AlamatPelanggan([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'is_primary' => true,
        ]);
        
        // Manually set lat/lng without location column
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;
        $alamat->save();

        $alamat->refresh();

        // Should still work with location attribute
        $this->assertEquals([-6.2088, 106.8456], $alamat->location);
        $this->assertTrue($alamat->hasCoordinates());
    }

    /** @test */
    public function primary_address_validation_still_works()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        // Create first address as primary
        $alamat1 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Alamat 1',
            'location' => [-6.2088, 106.8456],
            'is_primary' => true,
        ]);

        // Create second address as primary (should make first non-primary)
        $alamat2 = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Alamat 2',
            'location' => [-7.2575, 112.7521],
            'is_primary' => true,
        ]);

        $alamat1->refresh();
        $alamat2->refresh();

        $this->assertFalse($alamat1->is_primary);
        $this->assertTrue($alamat2->is_primary);
    }

    /** @test */
    public function can_clear_location_data()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'location' => [-6.2088, 106.8456],
            'is_primary' => true,
        ]);

        // Clear location
        $alamat->location = null;
        $alamat->save();

        $alamat->refresh();

        $this->assertNull($alamat->location);
        $this->assertNull($alamat->latitude);
        $this->assertNull($alamat->longitude);
        $this->assertNull($alamat->getAttributes()['location']);
        $this->assertFalse($alamat->hasCoordinates());
    }
}
