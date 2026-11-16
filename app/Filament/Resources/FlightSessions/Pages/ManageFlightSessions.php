<?php

namespace App\Filament\Resources\FlightSessions\Pages;

use App\Filament\Resources\FlightSessions\FlightSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFlightSessions extends ManageRecords
{
    protected static string $resource = FlightSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
