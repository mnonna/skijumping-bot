<?php

namespace App\Filament\Resources\Strategies\Pages;

use App\Filament\Resources\Strategies\StrategyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageStrategies extends ManageRecords
{
    protected static string $resource = StrategyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
