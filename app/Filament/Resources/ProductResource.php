<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Mi Menú';
 
    public static function shouldRegisterNavigation(): bool { 
        return auth()->user()->id === 1 || auth()->user()->business !== null; 
    }

    // EL CANDADO: Solo ver productos de mi negocio
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if (auth()->check() && auth()->user()->id !== 1) {
            return $query->whereHas('business', function($q) {
                $q->where('user_id', auth()->user()->id);
            });
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->label('Nombre del Platillo'),
                Forms\Components\TextInput::make('price')->numeric()->required()->prefix('$'),
                Forms\Components\Select::make('business_id')
                    ->relationship('business', 'name', function (Builder $query) {
                        // Al crear un producto, solo deja elegir el negocio del dueño
                        if (auth()->user()->id !== 1) {
                            return $query->where('user_id', auth()->user()->id);
                        }
                    })
                    ->required()
                    ->label('Restaurante'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Producto')->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Precio')->money('usd'),
                Tables\Columns\TextColumn::make('business.name')->label('Negocio'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
