<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AlamatPelanggan;
use App\Models\Pelanggan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GoogleMapsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function alamat_pelanggan_has_required_google_maps_methods()
    {
        $alamat = new AlamatPelanggan();

        // Test that required methods exist
        $this->assertTrue(method_exists($alamat, 'getLatLngAttributes'));
        $this->assertTrue(method_exists($alamat, 'getComputedLocation'));
        $this->assertTrue(method_exists($alamat, 'getLocationAttribute'));
        $this->assertTrue(method_exists($alamat, 'setLocationAttribute'));
    }

    /** @test */
    public function alamat_pelanggan_has_location_in_appends()
    {
        $alamat = new AlamatPelanggan();
        
        $this->assertContains('location', $alamat->getAppends());
    }

    /** @test */
    public function get_lat_lng_attributes_returns_correct_mapping()
    {
        $mapping = AlamatPelanggan::getLatLngAttributes();
        
        $this->assertEquals([
            'lat' => 'latitude',
            'lng' => 'longitude',
        ], $mapping);
    }

    /** @test */
    public function get_computed_location_returns_location()
    {
        $this->assertEquals('location', AlamatPelanggan::getComputedLocation());
    }

    /** @test */
    public function location_attribute_converts_lat_lng_to_google_format()
    {
        $alamat = new AlamatPelanggan([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $location = $alamat->location;

        $this->assertEquals([
            'lat' => -6.2088,
            'lng' => 106.8456,
        ], $location);
    }

    /** @test */
    public function location_attribute_handles_null_coordinates()
    {
        $alamat = new AlamatPelanggan([
            'latitude' => null,
            'longitude' => null,
        ]);

        $location = $alamat->location;

        $this->assertEquals([
            'lat' => 0.0,
            'lng' => 0.0,
        ], $location);
    }

    /** @test */
    public function set_location_attribute_updates_lat_lng_fields()
    {
        $alamat = new AlamatPelanggan();
        
        $alamat->location = [
            'lat' => -6.2088,
            'lng' => 106.8456,
        ];

        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertEquals(106.8456, $alamat->longitude);
    }

    /** @test */
    public function set_location_attribute_handles_null_values()
    {
        $alamat = new AlamatPelanggan();
        
        $alamat->location = null;

        $this->assertNull($alamat->latitude);
        $this->assertNull($alamat->longitude);
    }

    /** @test */
    public function set_location_attribute_handles_partial_data()
    {
        $alamat = new AlamatPelanggan();
        
        $alamat->location = [
            'lat' => -6.2088,
            // missing lng
        ];

        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertNull($alamat->longitude);
    }

    /** @test */
    public function alamat_pelanggan_can_be_created_with_location_data()
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

        // Test that location attribute works
        $this->assertEquals([
            'lat' => -6.2088,
            'lng' => 106.8456,
        ], $alamat->fresh()->location);
    }

    /** @test */
    public function google_maps_integration_works_with_model_serialization()
    {
        $pelanggan = Pelanggan::factory()->create();
        
        $alamat = AlamatPelanggan::create([
            'id_pelanggan' => $pelanggan->id,
            'alamat' => 'Jl. Test No. 123, Jakarta',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'is_primary' => true,
        ]);

        $serialized = $alamat->toArray();
        
        // Check that location is included in serialization due to appends
        $this->assertArrayHasKey('location', $serialized);
        $this->assertEquals([
            'lat' => -6.2088,
            'lng' => 106.8456,
        ], $serialized['location']);
    }
}
