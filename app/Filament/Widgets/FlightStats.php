<?php
namespace App\Filament\Widgets;
use App\Models\Simulation;
use App\Models\RealJump;
use App\Models\FlightSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
class FlightStats extends StatsOverviewWidget {
    protected function getStats(): array {
        return [
            Stat::make('Sessions', FlightSession::count()),
            Stat::make('Simulations', Simulation::count()),
            Stat::make('Real jumps', RealJump::count()),
            Stat::make('Best real flight', (RealJump::where('telemark',true)->where('fall',false)->max('distance') ?? 0).' m'),
        ];
    }
}
