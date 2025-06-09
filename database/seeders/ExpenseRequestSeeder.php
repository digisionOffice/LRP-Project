<?php

namespace Database\Seeders;

use App\Models\ExpenseRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ExpenseRequestSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create expense-requests directory if it doesn't exist
        if (!Storage::disk('public')->exists('expense-requests')) {
            Storage::disk('public')->makeDirectory('expense-requests');
        }

        $categories = [
            'tank_truck_maintenance',
            'license_fee', 
            'business_travel',
            'utilities',
            'other'
        ];

        $statuses = [
            'draft' => 20,
            'submitted' => 30,
            'under_review' => 15,
            'approved' => 20,
            'rejected' => 10,
            'paid' => 5
        ];

        $priorities = ['low', 'medium', 'high', 'urgent'];

        $maintenanceItems = [
            'Engine Oil Change',
            'Brake System Maintenance',
            'Tire Replacement',
            'Transmission Service',
            'Hydraulic System Repair',
            'Tank Cleaning Service',
            'Safety Equipment Inspection',
            'Electrical System Repair'
        ];

        $licenseItems = [
            'Microsoft Office 365 License',
            'Antivirus Software License',
            'Accounting Software License',
            'Fleet Management Software',
            'GPS Tracking System License',
            'Business Operating License Renewal',
            'Environmental Permit Renewal',
            'Transport License Renewal'
        ];

        $travelItems = [
            'Client Meeting in Jakarta',
            'Training Seminar in Surabaya',
            'Business Conference in Bandung',
            'Supplier Visit in Medan',
            'Equipment Installation in Palembang',
            'Customer Service Visit',
            'Market Research Trip',
            'Partnership Meeting'
        ];

        $utilityItems = [
            'Office Electricity Bill',
            'Water Supply Bill',
            'Internet & Telephone Bill',
            'Warehouse Utilities',
            'Security System Maintenance',
            'Cleaning Service',
            'Waste Management Service',
            'HVAC System Maintenance'
        ];

        $otherItems = [
            'Office Supplies Purchase',
            'Marketing Materials',
            'Employee Training Costs',
            'Insurance Premium',
            'Legal Consultation Fees',
            'Audit Services',
            'Equipment Rental',
            'Facility Maintenance'
        ];

        $totalRequests = 50;
        $createdCount = 0;

        foreach ($statuses as $status => $percentage) {
            $count = round(($percentage / 100) * $totalRequests);
            
            for ($i = 0; $i < $count; $i++) {
                $category = $categories[array_rand($categories)];
                $requestedBy = $users->random();
                $approvedBy = $status === 'draft' ? null : $users->random();
                
                // Generate title and description based on category
                [$title, $description] = $this->generateTitleAndDescription($category, $maintenanceItems, $licenseItems, $travelItems, $utilityItems, $otherItems);
                
                $requestedAmount = $this->generateAmount($category);
                $approvedAmount = in_array($status, ['approved', 'paid']) ? $requestedAmount * (0.8 + (rand(0, 40) / 100)) : null;
                
                $requestDate = now()->subDays(rand(1, 90));
                $neededByDate = $requestDate->copy()->addDays(rand(7, 30));
                
                // Generate supporting documents
                $supportingDocs = $this->generateSupportingDocuments($category);
                
                $expenseRequest = ExpenseRequest::create([
                    'request_number' => ExpenseRequest::generateRequestNumber($category),
                    'category' => $category,
                    'title' => $title,
                    'description' => $description,
                    'requested_amount' => $requestedAmount,
                    'approved_amount' => $approvedAmount,
                    'status' => $status,
                    'priority' => $priorities[array_rand($priorities)],
                    'requested_date' => $requestDate,
                    'needed_by_date' => $neededByDate,
                    'justification' => $this->generateJustification($category),
                    'supporting_documents' => $supportingDocs,
                    'requested_by' => $requestedBy->id,
                    'approved_by' => $approvedBy?->id,
                    'submitted_at' => $status !== 'draft' ? $requestDate->copy()->addHours(rand(1, 24)) : null,
                    'reviewed_at' => in_array($status, ['approved', 'rejected', 'paid']) ? $requestDate->copy()->addDays(rand(1, 5)) : null,
                    'approved_at' => in_array($status, ['approved', 'paid']) ? $requestDate->copy()->addDays(rand(1, 7)) : null,
                    'paid_at' => $status === 'paid' ? $requestDate->copy()->addDays(rand(7, 14)) : null,
                    'approval_notes' => in_array($status, ['approved', 'paid']) ? 'Approved as per company policy and budget allocation.' : null,
                    'rejection_reason' => $status === 'rejected' ? 'Budget constraints for this period. Please resubmit next quarter.' : null,
                    'cost_center' => $this->generateCostCenter($category),
                    'budget_code' => $this->generateBudgetCode($category),
                ]);

                $createdCount++;
            }
        }

        $this->command->info("ExpenseRequest seeder completed! Created {$createdCount} expense requests.");
    }

    private function generateTitleAndDescription($category, $maintenanceItems, $licenseItems, $travelItems, $utilityItems, $otherItems): array
    {
        return match ($category) {
            'tank_truck_maintenance' => [
                $maintenanceItems[array_rand($maintenanceItems)],
                'Regular maintenance required for tank truck fleet to ensure operational safety and compliance with transportation regulations.'
            ],
            'license_fee' => [
                $licenseItems[array_rand($licenseItems)],
                'Annual license renewal required for continued business operations and software usage.'
            ],
            'business_travel' => [
                $travelItems[array_rand($travelItems)],
                'Business travel expenses including transportation, accommodation, and meals for official company business.'
            ],
            'utilities' => [
                $utilityItems[array_rand($utilityItems)],
                'Monthly utility expenses for office and warehouse facilities operations.'
            ],
            'other' => [
                $otherItems[array_rand($otherItems)],
                'Miscellaneous business expense required for operational efficiency and business growth.'
            ],
        };
    }

    private function generateAmount($category): float
    {
        return match ($category) {
            'tank_truck_maintenance' => rand(5000000, 50000000), // 5M - 50M
            'license_fee' => rand(1000000, 15000000), // 1M - 15M
            'business_travel' => rand(2000000, 10000000), // 2M - 10M
            'utilities' => rand(3000000, 20000000), // 3M - 20M
            'other' => rand(1000000, 25000000), // 1M - 25M
        };
    }

    private function generateJustification($category): string
    {
        return match ($category) {
            'tank_truck_maintenance' => 'Essential for maintaining fleet safety standards and preventing costly breakdowns that could disrupt delivery schedules.',
            'license_fee' => 'Required for legal compliance and continued access to essential business software and systems.',
            'business_travel' => 'Necessary for maintaining client relationships, exploring new business opportunities, and staff development.',
            'utilities' => 'Essential operational expenses for maintaining office and warehouse facilities.',
            'other' => 'Required for supporting business operations and maintaining competitive advantage in the market.',
        };
    }

    private function generateCostCenter($category): string
    {
        return match ($category) {
            'tank_truck_maintenance' => 'Operations',
            'license_fee' => 'IT & Administration',
            'business_travel' => 'Sales & Marketing',
            'utilities' => 'Facilities',
            'other' => 'General & Administrative',
        };
    }

    private function generateBudgetCode($category): string
    {
        $year = now()->year;
        return match ($category) {
            'tank_truck_maintenance' => "MAINT-{$year}",
            'license_fee' => "LIC-{$year}",
            'business_travel' => "TRAVEL-{$year}",
            'utilities' => "UTIL-{$year}",
            'other' => "MISC-{$year}",
        };
    }

    private function generateSupportingDocuments($category): array
    {
        $docs = [];
        $docCount = rand(1, 3);
        
        for ($i = 0; $i < $docCount; $i++) {
            $filename = match ($category) {
                'tank_truck_maintenance' => "maintenance-quote-{$i}.pdf",
                'license_fee' => "license-invoice-{$i}.pdf",
                'business_travel' => "travel-estimate-{$i}.pdf",
                'utilities' => "utility-bill-{$i}.pdf",
                'other' => "supporting-doc-{$i}.pdf",
            };
            
            // Create dummy file
            $content = "This is a dummy supporting document for expense request.\nCategory: {$category}\nDocument: {$filename}";
            Storage::disk('public')->put("expense-requests/{$filename}", $content);
            
            $docs[] = "expense-requests/{$filename}";
        }
        
        return $docs;
    }
}
