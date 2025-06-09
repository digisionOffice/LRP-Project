<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🌱 Running SuratSeeder manually...\n\n";

try {
    $seeder = new \Database\Seeders\SuratSeeder();
    $seeder->setCommand(new class {
        public function info($message) {
            echo "ℹ️  {$message}\n";
        }
    });
    
    $seeder->run();
    
    echo "\n✅ SuratSeeder completed successfully!\n";
    
    // Verify the data
    $suratCount = \App\Models\Surat::count();
    $withFiles = \App\Models\Surat::whereNotNull('file_dokumen')->count();
    
    echo "📊 Results:\n";
    echo "  - Total documents: {$suratCount}\n";
    echo "  - Documents with files: {$withFiles}\n";
    echo "  - Documents without files: " . ($suratCount - $withFiles) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error running seeder: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Clean up this file
if (file_exists('run_surat_seeder.php')) {
    unlink('run_surat_seeder.php');
}

echo "\n🎉 Done!\n";
