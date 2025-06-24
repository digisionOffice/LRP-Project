# Pelanggan Resource - Google Maps Integration

## Overview

Dokumentasi ini menjelaskan integrasi Google Maps yang telah ditambahkan ke PelangganResource untuk mengelola alamat pelanggan dengan fitur peta interaktif.

## Fitur yang Ditambahkan

### ✅ **Repeater Alamat dengan Google Maps**

#### **Form Components yang Diupdate:**
1. **Section Alamat Pelanggan**: Wrapper section dengan icon map-pin
2. **Geocomplete Field**: Input alamat dengan auto-complete dan geocoding
3. **Interactive Map**: Peta Google Maps dengan kontrol lengkap
4. **Coordinate Fields**: Latitude dan longitude yang auto-populate
5. **Primary Address Toggle**: Toggle untuk alamat utama dengan notifikasi

#### **Map Configuration:**
```php
Map::make('location')
    ->label('Lokasi di Peta')
    ->mapControls([
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'searchBoxControl'  => false,
        'zoomControl'       => true,
    ])
    ->height('300px')
    ->defaultZoom(15)
    ->defaultLocation([-6.2088, 106.8456]) // Jakarta
    ->draggable()
    ->clickable()
```

#### **Geocomplete Configuration:**
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

### ✅ **Table Enhancements**

#### **New Columns Added:**
1. **Jumlah Alamat**: Badge showing count of addresses per customer
2. **Alamat Utama**: Display primary address with truncation and tooltip

#### **Column Features:**
- **Address Count**: Badge with map-pin icon showing total addresses
- **Primary Address**: Star icon with color coding (warning/gray)
- **Tooltip Support**: Full address display on hover
- **Responsive Design**: Toggleable columns for mobile view

### ✅ **Repeater Features**

#### **Enhanced Repeater Functionality:**
1. **Collapsible Items**: Each address can be collapsed/expanded
2. **Smart Labels**: Shows address preview with star for primary
3. **Confirmation Dialogs**: Delete confirmation with Indonesian text
4. **Visual Indicators**: Star emoji for primary addresses
5. **Address Truncation**: Long addresses truncated in item labels

#### **Item Label Logic:**
```php
->itemLabel(function (array $state): ?string {
    if (!empty($state['alamat'])) {
        $primary = $state['is_primary'] ?? false;
        $label = $state['alamat'];
        if (strlen($label) > 50) {
            $label = substr($label, 0, 50) . '...';
        }
        return ($primary ? '⭐ ' : '') . $label;
    }
    return 'Alamat Baru';
})
```

### ✅ **User Experience Improvements**

#### **Interactive Features:**
1. **Real-time Geocoding**: Address auto-completes coordinates
2. **Map Click-to-Set**: Click map to set precise location
3. **Reverse Geocoding**: Coordinates auto-complete address
4. **Primary Address Validation**: Automatic notification system
5. **Responsive Layout**: Mobile-friendly design

#### **Validation & Notifications:**
- **Primary Address**: Notification when setting primary address
- **Delete Confirmation**: Modal confirmation for address deletion
- **Required Fields**: Address field is required
- **Coordinate Sync**: Automatic sync between map and coordinate fields

## Technical Implementation

### **Dependencies Added:**
```php
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Filament\Notifications\Notification;
```

### **Form Structure:**
```
PelangganResource Form
├── Informasi Pelanggan Section
├── Informasi Kontak Section
└── Alamat Pelanggan Section
    └── Repeater
        ├── Grid (2 columns)
        │   ├── Geocomplete (alamat)
        │   └── Toggle (is_primary)
        ├── Map (location)
        └── Grid (2 columns)
            ├── TextInput (latitude)
            └── TextInput (longitude)
```

### **Database Integration:**
- **Existing Fields**: id_pelanggan, alamat, is_primary
- **New Fields**: latitude, longitude (from previous migration)
- **Relationships**: Proper foreign key to pelanggan table
- **Validation**: Primary address enforcement via model boot method

## Usage Instructions

### **Creating Customer with Addresses:**
1. Navigate to `/admin/pelanggans/create`
2. Fill customer information
3. In "Alamat Pelanggan" section:
   - Enter address in geocomplete field
   - Coordinates will auto-populate
   - Or click on map to set precise location
   - Toggle primary address if needed
   - Add multiple addresses using "Tambah Alamat"

### **Editing Customer Addresses:**
1. Navigate to customer edit page
2. Expand "Alamat Pelanggan" section
3. Click on address items to expand/edit
4. Map will show current location if coordinates exist
5. Modify address or coordinates as needed

### **Managing Multiple Addresses:**
1. Use "Tambah Alamat" to add new addresses
2. Each address shows preview in collapsed state
3. Primary addresses marked with ⭐ star
4. Delete addresses with confirmation dialog

## Configuration Requirements

### **Environment Variables:**
```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
FILAMENT_GOOGLE_MAPS_REGION_CODE=ID
FILAMENT_GOOGLE_MAPS_LANGUAGE_CODE=id
```

### **Google APIs Required:**
- Google Maps JavaScript API
- Places API (for geocoding)
- Geocoding API (for reverse geocoding)

## Benefits

### **For Users:**
1. **Visual Address Selection**: See exact location on map
2. **Auto-completion**: Faster address entry with geocoding
3. **Multiple Addresses**: Support for multiple customer addresses
4. **Primary Address Management**: Clear indication of main address
5. **Mobile Friendly**: Responsive design for all devices

### **For Administrators:**
1. **Better Data Quality**: Accurate coordinates for all addresses
2. **Improved UX**: Intuitive map-based interface
3. **Efficient Management**: Quick overview of customer addresses
4. **Integration Ready**: Coordinates available for delivery routing

## Troubleshooting

### **Common Issues:**
1. **Map Not Loading**: Check Google Maps API key and permissions
2. **Geocoding Fails**: Verify Places API is enabled
3. **Coordinates Not Saving**: Check latitude/longitude field configuration
4. **Primary Address Issues**: Verify model boot method implementation

### **Performance Tips:**
1. **API Caching**: Google Maps responses are cached for 30 days
2. **Lazy Loading**: Maps load only when section is expanded
3. **Efficient Queries**: Address count uses database aggregation
4. **Optimized Display**: Long addresses truncated in table view

## Future Enhancements

### **Potential Improvements:**
1. **Address Validation**: Real-time address verification
2. **Bulk Import**: CSV import with geocoding
3. **Route Planning**: Integration with delivery systems
4. **Address History**: Track address changes over time
5. **Proximity Search**: Find customers near specific locations

The Google Maps integration is now fully functional in the PelangganResource, providing a comprehensive address management system with visual map interface and enhanced user experience.
