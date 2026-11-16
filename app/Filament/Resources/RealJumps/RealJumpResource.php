<?php
namespace App\Filament\Resources\RealJumps;
use App\Filament\Resources\RealJumps\Pages\ManageRealJumps;
use App\Models\RealJump;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
class RealJumpResource extends Resource {
    protected static ?string $model = RealJump::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function form(Schema $schema):Schema { return $schema->components([]); }
    public static function table(Table $table):Table {
        return $table->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('session_id')->searchable(),
                TextColumn::make('simulation_id')->searchable(),
                TextColumn::make('distance')->numeric(1)->sortable(),
                IconColumn::make('telemark')->boolean(),
                IconColumn::make('fall')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])->defaultSort('created_at','desc')->paginated([10,25,50]);
    }
    public static function getPages():array { return ['index'=>ManageRealJumps::route('/')]; }
}
