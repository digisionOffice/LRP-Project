# Alamat Pelanggan Resource - Comprehensive Guide

## Overview

This guide documents the comprehensive Filament Resource implementation for the AlamatPelanggan model, featuring interactive map integration, GPS coordinates, and advanced customer address management.

## Features Implemented

### ✅ **Database Enhancements**
- **Latitude/Longitude Columns**: Added decimal fields with precision for GPS coordinates
- **Performance Indexes**: Added composite index on latitude/longitude for efficient location queries
- **Foreign Key Constraints**: Proper relationship with pelanggan table with cascade delete

### ✅ **Filament Resource Features**
- **Full CRUD Operations**: Create, Read, Update, Delete with comprehensive forms
- **Interactive Map Integration**: Google Maps with Leaflet-style controls
- **Address Geocoding**: Automatic coordinate detection from address input
- **Primary Address Validation**: Ensures only one primary address per customer
- **Advanced Filtering**: Customer selection, primary status, and coordinate availability filters

### ✅ **Map Integration Features**
- **Interactive Map Component**: Click-to-set coordinates with visual feedback
- **Address Search/Geocoding**: Auto-complete address search with coordinate population
- **Reverse Geocoding**: Address auto-completion from map coordinates
- **Map Controls**: Full control suite including zoom, street view, map type selection
- **Default Location**: Jakarta, Indonesia as default center point
- **Responsive Design**: Mobile-friendly map interface

### ✅ **Form Components**
- **Customer Selection**: Searchable dropdown with inline customer creation
- **Address Input**: Geocomplete field with Indonesia-specific geocoding
- **Primary Address Toggle**: With automatic validation and user notifications
- **Coordinate Display**: Auto-populated latitude/longitude fields
- **Map Visualization**: 400px height interactive map with full controls

### ✅ **Table Features**
- **Customer Information**: Name, code with search and sort capabilities
- **Address Display**: Truncated with tooltip for full address view
- **Primary Status**: Visual indicator with star icon for primary addresses
- **Coordinate Display**: Formatted coordinates with copy functionality
- **Map Links**: Direct links to Google Maps for addresses with coordinates
- **Advanced Actions**: Edit, delete with confirmation dialogs

### ✅ **Validation & Business Logic**
- **Primary Address Enforcement**: Automatic deactivation of other primary addresses
- **Coordinate Validation**: Proper decimal precision for GPS accuracy
- **Customer Relationship**: Required customer selection with validation
- **Address Requirements**: Mandatory address field with geocoding integration

## Database Schema

### alamat_pelanggan Table Structure
```sql
CREATE TABLE alamat_pelanggan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan BIGINT UNSIGNED NOT NULL,
    alamat TEXT NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id) ON DELETE CASCADE,
    INDEX idx_pelanggan (id_pelanggan),
    INDEX idx_primary (is_primary),
    INDEX idx_coordinates (latitude, longitude)
);
```

## Model Features

### AlamatPelanggan Model
- **Fillable Fields**: id_pelanggan, alamat, latitude, longitude, is_primary
- **Casts**: Proper decimal casting for coordinates, boolean for is_primary
- **Relationships**: belongsTo Pelanggan with proper foreign key
- **Validation**: Boot method ensures only one primary address per customer
- **Helper Methods**: 
  - `getFormattedCoordinatesAttribute()`: Returns formatted lat,lng string
  - `hasCoordinates()`: Checks if address has valid coordinates

## Google Maps Configuration

### Required Environment Variables
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
FILAMENT_GOOGLE_MAPS_REGION_CODE=ID
FILAMENT_GOOGLE_MAPS_LANGUAGE_CODE=id
```

### API Requirements
- **Google Maps JavaScript API**: For interactive map display
- **Places API**: For address autocomplete and geocoding
- **Geocoding API**: For coordinate-to-address conversion

## Usage Instructions

### 1. **Creating New Address**
1. Navigate to `/admin/alamat-pelanggans/create`
2. Select customer from dropdown (or create new inline)
3. Enter address in the geocomplete field
4. Coordinates will auto-populate from address
5. Alternatively, click on map to set precise location
6. Toggle primary address if needed
7. Save the record

### 2. **Editing Existing Address**
1. Navigate to address list and click Edit
2. Modify address or coordinates as needed
3. Map will update to show current location
4. Primary status can be changed with automatic validation

### 3. **Viewing Address List**
1. Navigate to `/admin/alamat-pelanggans`
2. Use filters to find specific addresses
3. Click map icon to view location in Google Maps
4. Use search to find addresses by customer or address text

## Technical Implementation

### Map Component Configuration
```php
Map::make('location')
    ->label('Lokasi di Peta')
    ->mapControls([
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'zoomControl'       => true,
    ])
    ->height('400px')
    ->defaultZoom(15)
    ->defaultLocation([-6.2088, 106.8456]) // Jakarta
    ->draggable()
    ->clickable()
```

### Geocomplete Configuration
```php
Geocomplete::make('alamat')
    ->label('Alamat Lengkap')
    ->geocodeOnLoad()
    ->countries(['ID'])
    ->updateLatLng()
    ->reverseGeocode([
        'street_number' => '%n',
        'route' => '%S',
        'locality' => '%L',
        'administrative_area_level_2' => '%A2',
        'administrative_area_level_1' => '%A1',
        'country' => '%C',
        'postal_code' => '%z',
    ])
```

## Performance Considerations

### Database Optimization
- **Composite Index**: On latitude/longitude for spatial queries
- **Foreign Key Index**: On id_pelanggan for relationship queries
- **Primary Status Index**: For filtering primary addresses

### Caching
- **Google Maps API**: 30-day cache duration for API responses
- **Geocoding Results**: Cached to reduce API calls and costs

## Security Features

### Access Control
- **Filament Shield Integration**: Role-based access control
- **CSRF Protection**: Built-in Laravel CSRF protection
- **Input Validation**: Server-side validation for all inputs

### Data Protection
- **Foreign Key Constraints**: Prevents orphaned records
- **Soft Deletes**: Can be enabled if needed for audit trails
- **Input Sanitization**: Automatic sanitization of address inputs

## Troubleshooting

### Common Issues

1. **Map Not Loading**
   - Check Google Maps API key configuration
   - Verify API key has required permissions
   - Check browser console for JavaScript errors

2. **Geocoding Not Working**
   - Ensure Places API is enabled
   - Check API quota limits
   - Verify region/language settings

3. **Primary Address Validation**
   - Check model boot method implementation
   - Verify database constraints
   - Test with multiple addresses per customer

### API Key Setup
1. Go to Google Cloud Console
2. Enable Maps JavaScript API and Places API
3. Create API key with appropriate restrictions
4. Add key to .env file

## Future Enhancements

### Potential Improvements
- **Radius Search**: Find addresses within specific distance
- **Bulk Import**: CSV import with geocoding
- **Address Validation**: Real-time address verification
- **Route Planning**: Integration with delivery routes
- **Mobile App**: API endpoints for mobile applications

## Testing

### Manual Testing Checklist
- [ ] Create new address with map selection
- [ ] Edit existing address coordinates
- [ ] Test primary address validation
- [ ] Verify geocoding functionality
- [ ] Test address search and filtering
- [ ] Check map links to Google Maps
- [ ] Validate coordinate precision
- [ ] Test customer relationship integrity

The AlamatPelanggan Resource is now fully implemented with comprehensive map integration, validation, and user-friendly interface following Indonesian localization standards.
