<?php
namespace App\Filament\Resources\FlightSessions;
use App\Filament\Resources\FlightSessions\Pages\ManageFlightSessions;
use App\Models\FlightSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
class FlightSessionResource extends Resource {
    protected static ?string $model = FlightSession::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema):Schema { return $schema->components([]); }
    public static function table(Table $table):Table {
        return $table->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('hill')->searchable(),
                TextColumn::make('gate')->searchable(),
                TextColumn::make('physics_version')->searchable(),
                TextColumn::make('rho')->searchable(),
                TextColumn::make('mu')->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])->defaultSort('created_at','desc')->paginated([10,25,50]);
    }
    public static function getPages():array { return ['index'=>ManageFlightSessions::route('/')]; }
}
