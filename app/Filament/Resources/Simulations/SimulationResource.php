<?php
namespace App\Filament\Resources\Simulations;
use App\Filament\Resources\Simulations\Pages\ManageSimulations;
use App\Models\Simulation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
class SimulationResource extends Resource {
    protected static ?string $model = Simulation::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema):Schema { return $schema->components([]); }
    public static function table(Table $table):Table {
        return $table->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('session_id')->searchable(),
                TextColumn::make('strategy_id')->searchable(),
                TextColumn::make('distance')->numeric(1)->sortable(),
                IconColumn::make('telemark')->boolean(),
                IconColumn::make('fall')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])->defaultSort('created_at','desc')->paginated([10,25,50]);
    }
    public static function getPages():array { return ['index'=>ManageSimulations::route('/')]; }
}
