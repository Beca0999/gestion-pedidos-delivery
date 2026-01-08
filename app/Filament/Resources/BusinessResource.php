<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Models\Business;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessResource extends Resource {
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $label = 'Negocio';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Negocio')
                        ->required(),
                    Forms\Components\FileUpload::make('image')
                        ->label('Logo o Foto')
                        ->image()
                        ->directory('businesses'),
                ])
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label('Logo')->circular(),
            Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}
