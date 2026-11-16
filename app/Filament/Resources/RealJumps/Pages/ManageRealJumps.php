<?php

namespace App\Filament\Resources\RealJumps\Pages;

use App\Filament\Resources\RealJumps\RealJumpResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRealJumps extends ManageRecords
{
    protected static string $resource = RealJumpResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
