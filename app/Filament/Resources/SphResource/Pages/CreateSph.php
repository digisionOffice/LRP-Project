<?php

namespace App\Filament\Resources\SphResource\Pages;

use App\Filament\Resources\SphResource;
use App\Services\SphService; // <-- ADDED: Import the service
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; // <-- ADDED: Import the Model class

class CreateSph extends CreateRecord
{
    protected static string $resource = SphResource::class;

    /**
     * Override the default record creation logic to use our custom service.
     * This gives us full control over the creation process, including calculations
     * and creating related records.
     *
     * @param array $data The validated data from the form.
     * @return Model The newly created Sph model instance.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // 1. Resolve the SphService from Laravel's service container.
        $service = resolve(SphService::class);

        // 2. Call the createSph method on the service, passing the
        //    currently authenticated user and the form data.
        return $service->createSph(auth()->user(), $data);
    }
}
