<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Business;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()->hasRole('owner')) {
            abort_unless(
                Business::whereKey($data['business_id'] ?? null)
                    ->where('user_id', auth()->id())
                    ->exists(),
                403,
            );
        }

        return $data;
    }
}
