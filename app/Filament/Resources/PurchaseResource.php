<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PurchasesExport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('stock_id')
                    ->label('Product')
                    ->options(
                        Stock::query()
                            ->whereNotNull('item_name')
                            ->get()
                            ->mapWithKeys(fn ($stock) => [$stock->id => "{$stock->item_name} ({$stock->unit})"])
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),
                    
                TextInput::make('price_per_unit')
                    ->label('Price Per Unit')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01),

                TextInput::make('qty')
                    ->label('Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.01)
                    ->live(),

                TextInput::make('total_price')
                    ->label('Total Price')
                    ->numeric()
                    ->readOnly()
                    ->suffix('Auto-calculated')
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                        $set('total_price', $get('price_per_unit') * $get('qty'))
                    ),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
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
                TextColumn::make('stock.item_name')->label('Product Name')->searchable(),
                TextColumn::make('price_per_unit')->label('Price Per Unit'),
                TextColumn::make('qty')->label('Quantity'),
                TextColumn::make('total_price')
                    ->label('Total Price')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->sortable()
                    ->date('Y-m-d'),
            ])
            ->filters([
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        TextInput::make('start_date')
                            ->label('Start Date')
                            ->type('date')
                            ->default(Carbon::now()->startOfMonth()->toDateString()),

                        TextInput::make('end_date')
                            ->label('End Date')
                            ->type('date')
                            ->default(Carbon::now()->endOfMonth()->toDateString()),
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            $query->whereBetween('created_at', [
                                Carbon::parse($data['start_date'])->startOfDay(),
                                Carbon::parse($data['end_date'])->endOfDay(),
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Purchase $record) {
                        \DB::transaction(function () use ($record) {
                            $stock = Stock::find($record->stock_id);
                            
                            if ($stock) {
                                // Subtract the quantity
                                $stock->qty -= $record->qty;
                                
                                // Recalculate average purchase price
                                $remainingPurchases = Purchase::where('stock_id', $stock->id)
                                    ->where('id', '!=', $record->id)
                                    ->get();
                                
                                if ($remainingPurchases->count() > 0) {
                                    $totalValue = $remainingPurchases->sum(function ($p) {
                                        return $p->price_per_unit * $p->qty;
                                    });
                                    $totalQty = $remainingPurchases->sum('qty');
                                    
                                    $newAveragePrice = $totalValue / $totalQty;
                                    $stock->buy_price = $newAveragePrice;
                                    $stock->sell_price = $newAveragePrice * 1.2; // 20% markup
                                } else {
                                    // If no purchases remain, reset prices to 0
                                    $stock->buy_price = 0;
                                    $stock->sell_price = 0;
                                }
                                
                                $stock->save();
                            }
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            \DB::transaction(function () use ($records) {
                                $records->each(function ($record) {
                                    $stock = Stock::find($record->stock_id);
                                    
                                    if ($stock) {
                                        // Subtract the quantity
                                        $stock->qty -= $record->qty;
                                        
                                        // Recalculate average purchase price
                                        $remainingPurchases = Purchase::where('stock_id', $stock->id)
                                            ->whereNotIn('id', $records->pluck('id'))
                                            ->get();
                                        
                                        if ($remainingPurchases->count() > 0) {
                                            $totalValue = $remainingPurchases->sum(function ($p) {
                                                return $p->price_per_unit * $p->qty;
                                            });
                                            $totalQty = $remainingPurchases->sum('qty');
                                            
                                            $newAveragePrice = $totalValue / $totalQty;
                                            $stock->buy_price = $newAveragePrice;
                                            $stock->sell_price = $newAveragePrice * 1.2; // 20% markup
                                        } else {
                                            // If no purchases remain, reset prices to 0
                                            $stock->buy_price = 0;
                                            $stock->sell_price = 0;
                                        }
                                        
                                        $stock->save();
                                    }
                                });
                            });
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Export Purchases')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new PurchasesExport, 'purchases_report.xlsx'))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}