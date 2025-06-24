<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Exception;

class PostingRuleEntry extends Model
{
    use HasFactory;

    protected $table = 'posting_rule_entries';

    protected $fillable = [
        'posting_rule_id',
        'account_id',
        'dc_type',
        'amount_type',
        'fixed_amount',
        'source_property',
        'calculation_expression',
        'description_template',
        'sort_order',
    ];

    protected $casts = [
        'fixed_amount' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function postingRule()
    {
        return $this->belongsTo(PostingRule::class);
    }

    public function account()
    {
        return $this->belongsTo(Akun::class, 'account_id');
    }

    // Helper methods
    public function calculateAmount($sourceModel)
    {
        switch ($this->amount_type) {
            case 'Fixed':
                return $this->fixed_amount;

            case 'SourceValue':
                $value = data_get($sourceModel, $this->source_property);

                // Special handling for ExpenseRequest approved_amount fallback
                if (($value === null || $value === 0) && $this->source_property === 'approved_amount' && $sourceModel instanceof \App\Models\ExpenseRequest) {
                    $value = $sourceModel->requested_amount;
                }

                // Special handling for Invoice calculated fields
                if ($sourceModel instanceof \App\Models\Invoice) {
                    if ($this->source_property === 'total_invoice') {
                        $value = $sourceModel->getTotalInvoiceAttribute();
                    } elseif ($this->source_property === 'subtotal') {
                        $value = $sourceModel->getSubtotalAttribute();
                    } elseif ($this->source_property === 'total_pajak') {
                        $value = $sourceModel->getTotalPajakAttribute();
                    }
                }

                return (float) ($value ?? 0);

            case 'Calculated':
                return $this->evaluateExpression($sourceModel);

            default:
                return 0;
        }
    }

    public function generateDescription($sourceModel)
    {
        $template = $this->description_template ?: 'Transaction - {source.id}';

        // Simple placeholder replacement
        $description = preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($sourceModel) {
            $path = $matches[1];
            return data_get($sourceModel, $path, $matches[0]);
        }, $template);

        return $description;
    }

    private function evaluateExpression($sourceModel)
    {
        try {
            $expression = $this->calculation_expression;

            // Simple expression evaluation for common patterns
            if (preg_match('/^(\w+)\.sum\((\w+)\s*\*\s*(\w+)\)$/', $expression, $matches)) {
                $relation = $matches[1];
                $field1 = $matches[2];
                $field2 = $matches[3];

                $items = data_get($sourceModel, $relation, collect());
                return $items->sum(function ($item) use ($field1, $field2) {
                    return data_get($item, $field1, 0) * data_get($item, $field2, 0);
                });
            }

            // Add more expression patterns as needed
            return 0;
        } catch (Exception $e) {
            Log::error('Error evaluating posting rule expression', [
                'expression' => $this->calculation_expression,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
