<?php

namespace App\Models\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A reusable Trait to automatically log model updates to the activity_logs table.
 * To use, simply add `use LogsModelUpdates;` to any Eloquent model.
 */
trait LogsModelUpdates
{
    /**
     * Boot the trait.
     * This method is called when a model using this trait is booted,
     * allowing us to register our model event listeners automatically.
     */
    protected static function bootLogsModelUpdates(): void
    {
        /**
         * Listen to the 'updating' event. This event fires just before
         * a model's changes are saved to the database. This is the ideal
         * event because it gives us access to both the original and new data.
         *
         * @param Model $model The instance of the model being updated.
         */
        static::updating(function (Model $model) {
            try {
                // 1. Get the user who is performing the action.
                // It can be null if the update is done by the system (e.g., seeder, job).
                $user = Auth::user();

                // 2. Prepare the array to hold the changes.
                $changes = [
                    'before' => [],
                    'after' => [],
                ];

                // 3. Loop through only the attributes that have been changed ('dirty' attributes).
                foreach ($model->getDirty() as $field => $newValue) {
                    // Get the original value of the changed field from before the update.
                    $oldValue = $model->getOriginal($field);

                    // Add the old and new values to our changes array.
                    $changes['before'][$field] = $oldValue;
                    $changes['after'][$field] = $newValue;
                }
                
                // 4. Only create a log entry if there were actual changes to log.
                // This prevents creating empty logs if an update is triggered but no data changes.
                if (!empty($changes['before'])) {
                    // 5. Create the log record. This uses a polymorphic relationship
                    // which allows the ActivityLog to be associated with any model type.
                    ActivityLog::create([
                        'user_id'       => $user?->id,
                        'loggable_id'   => $model->getKey(),
                        'loggable_type' => get_class($model),
                        'changes'       => $changes, // The changes are stored as JSON.
                    ]);
                }

            } catch (Throwable $e) {
                // 6. If logging fails for any reason (e.g., database connection issue),
                // we don't want to crash the main application. We just log the logging
                // error to the default Laravel log file for later inspection.
                Log::error('Failed to log model update.', [
                    'error'    => $e->getMessage(),
                    'model'    => get_class($model),
                    'model_id' => $model->getKey(),
                ]);
            }
        });
    }
}
