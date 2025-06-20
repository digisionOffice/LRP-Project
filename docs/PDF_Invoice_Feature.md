# PDF Invoice Printing Feature

## Overview

This document describes the PDF invoice printing feature implemented for the Invoice resource in the Filament admin panel. The feature allows users to generate and download professional PDF invoices directly from the invoice table and view pages.

## Features Implemented

### 1. PDF Print Actions
- **Table Action**: Added "Cetak PDF" button in the InvoiceResource table actions
- **View Page Action**: Added "Cetak PDF" button in the ViewInvoice page header actions
- **Authorization**: Both actions respect existing Filament Shield permissions (requires `view_invoice` permission)

### 2. Professional PDF Template
- **Company Branding**: PT. Lintas Riau Prima header with logo placeholder
- **Invoice Information**: Complete invoice details including number, dates, customer info
- **Itemized Breakdown**: Displays items from related delivery order and sales transaction
- **Tax Calculations**: Shows DPP, PPN 11%, and total amounts
- **Payment Information**: Displays payment status, amounts paid, and remaining balance
- **Indonesian Formatting**: Proper Rupiah formatting and Indonesian date formats

### 3. Number to Words Helper
- **Currency Conversion**: Converts numeric amounts to Indonesian words
- **Terbilang Feature**: Displays invoice total in words (e.g., "seratus sebelas juta rupiah")
- **Proper Indonesian Grammar**: Handles special cases like "seribu" vs "satu ribu"

## Files Created/Modified

### New Files
1. `resources/views/pdf/invoice.blade.php` - PDF template for invoices
2. `app/Helpers/NumberToWords.php` - Helper class for number to words conversion
3. `tests/Feature/InvoicePdfGenerationTest.php` - Comprehensive test suite
4. `docs/PDF_Invoice_Feature.md` - This documentation file

### Modified Files
1. `app/Filament/Resources/InvoiceResource.php` - Added PDF print action to table
2. `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php` - Added PDF print action to view page

## Technical Implementation

### PDF Generation
- **Library**: Uses `barryvdh/laravel-dompdf` (already installed)
- **Paper Size**: A4 portrait orientation
- **Options**: HTML5 parser enabled, Arial font default
- **Error Handling**: Comprehensive try-catch with user notifications and logging

### Data Loading
The PDF generation loads invoices with all necessary relationships:
```php
$invoice = Invoice::with([
    'deliveryOrder.transaksi.pelanggan.alamatUtama.subdistrict.district.regency',
    'transaksiPenjualan.penjualanDetails.item.satuanDasar',
    'createdBy'
])->find($record->id);
```

### Authorization
- Uses existing Filament Shield permissions
- Requires `view_invoice` permission to access PDF print actions
- Gracefully handles unauthorized access

### File Naming
Generated PDFs use dynamic naming:
```
Invoice_{invoice_number}_{timestamp}.pdf
```
Example: `Invoice_INV-001_20250617_143022.pdf`

## PDF Template Features

### Header Section
- Company logo placeholder
- Company name and tagline
- Contact information
- Invoice title and number

### Customer Information
- Customer name and address
- NPWP (tax identification number)
- Delivery order and transaction references

### Invoice Details
- Invoice and due dates
- Status with color-coded badges
- Payment terms and conditions

### Items Table
- Item descriptions and specifications
- Unit prices and volumes
- Tax calculations (PPN 11%)
- Line totals

### Summary Section
- Subtotal (DPP)
- Tax amount (PPN)
- Total invoice amount
- Payment information
- Amount in words (terbilang)

### Footer
- Payment instructions
- Company contact details
- Generation timestamp
- Signature sections

## Usage Instructions

### For Users
1. **From Invoice Table**:
   - Navigate to Admin → Manajemen Keuangan → Invoice
   - Click the green "Cetak PDF" button next to any invoice
   - PDF will be automatically downloaded

2. **From Invoice View Page**:
   - Open any invoice detail page
   - Click the "Cetak PDF" button in the header
   - PDF will be automatically downloaded

### For Developers
1. **Customizing the Template**:
   - Edit `resources/views/pdf/invoice.blade.php`
   - Modify CSS styles for layout changes
   - Update company information as needed

2. **Adding New Fields**:
   - Add fields to the PDF template
   - Ensure proper data loading in the action methods
   - Test with various invoice states

3. **Modifying Number to Words**:
   - Edit `app/Helpers/NumberToWords.php`
   - Add support for other languages if needed
   - Update conversion logic for different number formats

## Testing

### Automated Tests
The feature includes comprehensive tests covering:
- PDF generation functionality
- Template rendering
- Number to words conversion
- Authorization checks
- Error handling
- Edge cases with missing data

### Manual Testing
1. **Basic Functionality**:
   - Generate PDFs for different invoice types
   - Verify all data displays correctly
   - Check formatting and layout

2. **Authorization**:
   - Test with different user roles
   - Verify permission restrictions work

3. **Error Handling**:
   - Test with invalid invoice IDs
   - Test with missing related data
   - Verify error notifications appear

## Performance Considerations

### Optimization
- Eager loading of relationships prevents N+1 queries
- PDF generation is done in memory for better performance
- Error logging helps identify performance bottlenecks

### Scalability
- PDF generation is synchronous (suitable for current usage)
- For high-volume usage, consider implementing queue-based generation
- Monitor memory usage for large invoices with many items

## Security

### Authorization
- All PDF actions require proper authentication
- Filament Shield integration ensures role-based access
- Users can only print invoices they have permission to view

### Data Protection
- No sensitive data is logged in error messages
- PDF generation respects existing data access policies
- Generated PDFs are streamed directly (not stored on server)

## Maintenance

### Regular Tasks
1. **Monitor Error Logs**: Check for PDF generation failures
2. **Update Templates**: Keep company information current
3. **Test Permissions**: Verify authorization works correctly
4. **Performance Monitoring**: Watch for slow PDF generation

### Troubleshooting
1. **PDF Generation Fails**:
   - Check DomPDF configuration
   - Verify template syntax
   - Review error logs

2. **Missing Data in PDF**:
   - Check relationship loading
   - Verify model relationships
   - Test with different invoice states

3. **Permission Issues**:
   - Verify Filament Shield configuration
   - Check user roles and permissions
   - Test authorization logic

## Future Enhancements

### Potential Improvements
1. **Email Integration**: Send PDFs via email
2. **Bulk PDF Generation**: Generate multiple invoices at once
3. **Template Customization**: Allow users to customize templates
4. **Digital Signatures**: Add digital signature support
5. **Archive Management**: Store generated PDFs for audit trails

### Configuration Options
1. **Company Logo**: Upload and manage company logos
2. **Template Variants**: Multiple template designs
3. **Language Support**: Multi-language PDF generation
4. **Custom Fields**: User-defined fields in templates

## Conclusion

The PDF invoice printing feature provides a comprehensive solution for generating professional invoices in the Filament admin panel. It follows Laravel best practices, integrates seamlessly with existing authorization systems, and provides a solid foundation for future enhancements.

The implementation is production-ready with proper error handling, comprehensive testing, and detailed documentation to ensure maintainability and scalability.
