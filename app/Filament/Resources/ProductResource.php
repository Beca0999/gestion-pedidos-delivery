<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource {
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $label = 'Producto';

    public static function form(Form $form): Form {
        return $form->schema([
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('business_id')
                        ->relationship('business', 'name')
                        ->label('Negocio')
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Producto')
                        ->required(),
                    Forms\Components\TextInput::make('price')
                        ->label('Precio')
                        ->numeric()
                        ->prefix('$')
                        ->required(),
                    Forms\Components\FileUpload::make('image')
                        ->label('Foto del Producto')
                        ->image()
                        ->directory('products'),
                ])
        ]);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\ImageColumn::make('image')->label('Foto'),
            Tables\Columns\TextColumn::make('business.name')->label('Negocio'),
            Tables\Columns\TextColumn::make('name')->label('Producto'),
            Tables\Columns\TextColumn::make('price')->label('Precio')->money('usd'),
        ]);
    }

    public static function getPages(): array {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
