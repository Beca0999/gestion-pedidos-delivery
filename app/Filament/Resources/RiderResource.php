<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiderResource\Pages;
use App\Models\Rider;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class RiderResource extends Resource
{
    protected static ?string $model = Rider::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Repartidores';

    public static function shouldRegisterNavigation(): bool 
    { 
        return auth()->check() && auth()->id() === 1; 
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('phone')->tel()->required(),
            Forms\Components\Select::make('user_id')
                ->options(User::all()->pluck('name', 'id'))
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('phone'),
        ]);
    }

    public static function getPages(): array { return ['index' => Pages\ListRiders::route('/')]; }
}
