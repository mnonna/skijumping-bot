<?php

namespace App\Filament\Resources\Simulations\Pages;

use App\Filament\Resources\Simulations\SimulationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSimulations extends ManageRecords
{
    protected static string $resource = SimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
