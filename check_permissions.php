<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== Financial Permissions Check ===" . PHP_EOL;

// Check if permissions exist
$invoicePerms = Permission::where('name', 'like', '%invoice%')->count();
$receiptPerms = Permission::where('name', 'like', '%receipt%')->count();
$taxInvoicePerms = Permission::where('name', 'like', '%tax_invoice%')->count();

echo "Invoice permissions: {$invoicePerms}" . PHP_EOL;
echo "Receipt permissions: {$receiptPerms}" . PHP_EOL;
echo "Tax Invoice permissions: {$taxInvoicePerms}" . PHP_EOL;

// Check Finance role permissions
$finance = Role::where('name', 'finance')->first();
if ($finance) {
    $financialPerms = $finance->permissions->filter(function($p) {
        return str_contains($p->name, 'invoice') || 
               str_contains($p->name, 'receipt') || 
               str_contains($p->name, 'tax_invoice');
    });
    
    echo PHP_EOL . "Finance role financial permissions ({$financialPerms->count()}):" . PHP_EOL;
    foreach($financialPerms->take(15) as $perm) {
        echo "- {$perm->name}" . PHP_EOL;
    }
}

// Check Sales role permissions
$sales = Role::where('name', 'sales')->first();
if ($sales) {
    $salesFinancialPerms = $sales->permissions->filter(function($p) {
        return str_contains($p->name, 'invoice') || str_contains($p->name, 'receipt');
    });
    
    echo PHP_EOL . "Sales role financial permissions ({$salesFinancialPerms->count()}):" . PHP_EOL;
    foreach($salesFinancialPerms->take(10) as $perm) {
        echo "- {$perm->name}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== Check Complete ===" . PHP_EOL;
