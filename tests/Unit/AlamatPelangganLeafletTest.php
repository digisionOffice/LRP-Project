<?php

namespace Tests\Unit;

use App\Models\AlamatPelanggan;
use PHPUnit\Framework\TestCase;

class AlamatPelangganLeafletTest extends TestCase
{
    /** @test */
    public function location_attribute_is_in_appends()
    {
        $alamat = new AlamatPelanggan();

        $this->assertContains('location', $alamat->getAppends());
    }

    /** @test */
    public function location_is_cast_as_array()
    {
        $alamat = new AlamatPelanggan();

        $casts = $alamat->getCasts();
        $this->assertArrayHasKey('location', $casts);
        $this->assertEquals('array', $casts['location']);
    }

    /** @test */
    public function location_getter_returns_array_when_coordinates_exist()
    {
        $alamat = new AlamatPelanggan();
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;

        $location = $alamat->getLocationAttribute();

        $this->assertIsArray($location);
        $this->assertCount(2, $location);
        $this->assertEquals(-6.2088, $location[0]);
        $this->assertEquals(106.8456, $location[1]);
    }

    /** @test */
    public function location_getter_returns_null_when_no_coordinates()
    {
        $alamat = new AlamatPelanggan();

        $location = $alamat->getLocationAttribute();

        $this->assertNull($location);
    }

    /** @test */
    public function location_setter_updates_latitude_longitude_and_location_column()
    {
        $alamat = new AlamatPelanggan();

        $alamat->setLocationAttribute([-6.2088, 106.8456]);

        $this->assertEquals(-6.2088, $alamat->latitude);
        $this->assertEquals(106.8456, $alamat->longitude);
        $this->assertEquals('[-6.2088,106.8456]', $alamat->getAttributes()['location']);
    }

    /** @test */
    public function location_setter_clears_all_location_data_when_null()
    {
        $alamat = new AlamatPelanggan();
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;
        $alamat->setLocationAttribute([-6.2088, 106.8456]); // Set location first

        $alamat->setLocationAttribute(null);

        $this->assertNull($alamat->latitude);
        $this->assertNull($alamat->longitude);
        $this->assertNull($alamat->getAttributes()['location']);
    }

    /** @test */
    public function has_coordinates_method_works_correctly()
    {
        $alamat = new AlamatPelanggan();

        // Test without coordinates
        $this->assertFalse($alamat->hasCoordinates());

        // Test with coordinates
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;
        $this->assertTrue($alamat->hasCoordinates());

        // Test with only latitude
        $alamat->longitude = null;
        $this->assertFalse($alamat->hasCoordinates());
    }

    /** @test */
    public function formatted_coordinates_attribute_works_correctly()
    {
        $alamat = new AlamatPelanggan();

        // Test without coordinates
        $this->assertNull($alamat->getFormattedCoordinatesAttribute());

        // Test with coordinates
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;
        $this->assertEquals('-6.20880000, 106.84560000', $alamat->getFormattedCoordinatesAttribute());
    }

    /** @test */
    public function location_getter_prioritizes_location_column_over_lat_lng()
    {
        $alamat = new AlamatPelanggan();

        // Set both location column and lat/lng with different values
        $alamat->setRawAttributes([
            'location' => '[-7.2575, 112.7521]', // Surabaya
            'latitude' => -6.2088,  // Jakarta
            'longitude' => 106.8456 // Jakarta
        ]);

        $location = $alamat->getLocationAttribute();

        // Should return Surabaya coordinates from location column
        $this->assertEquals([-7.2575, 112.7521], $location);
    }

    /** @test */
    public function location_getter_falls_back_to_lat_lng_when_location_column_empty()
    {
        $alamat = new AlamatPelanggan();

        // Set only lat/lng, no location column
        $alamat->latitude = -6.2088;
        $alamat->longitude = 106.8456;

        $location = $alamat->getLocationAttribute();

        // Should return coordinates from lat/lng columns
        $this->assertEquals([-6.2088, 106.8456], $location);
    }
}
