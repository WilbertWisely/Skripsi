<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Exports\StocksExport;
use Maatwebsite\Excel\Facades\Excel;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('item_name')
                    ->label('Item Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Select::make('unit')
                    ->label('Unit')
                    ->options([
                        'ml' => 'Milliliter',
                        'unit' => 'Unit',
                    ])
                    ->required()
                    ->default('ml'),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->step(0.01),

                TextInput::make('buy_price')
                    ->label('Buy Price')
                    ->numeric()
                    ->prefix('IDR')
                    ->minValue(0)
                    ->required()
                    ->step(0.01)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('sell_price', round($state * 1.2, 2));
                        }
                    }),

                TextInput::make('sell_price')
                    ->label('Sell Price')
                    ->numeric()
                    ->prefix('IDR')
                    ->minValue(0)
                    ->required()
                    ->readonly()
                    ->step(0.01),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No.')
                    ->state(
                        static function ($record, $rowLoop): string {
                            return (string) $rowLoop->iteration;
                        }
                    ),
                TextColumn::make('item_name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ml' => 'primary',
                        'unit' => 'success',
                        default => 'secondary',
                    }),
                TextColumn::make('qty')
                    ->label('Quantity')
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => 
                        number_format($state, 2) . ' ' . $record->unit
                    ),
                TextColumn::make('buy_price')
                    ->label('Buy Price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->label('Sell Price')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('item_name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Stocks')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new StocksExport, 'stocks_report.xlsx'))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }


}