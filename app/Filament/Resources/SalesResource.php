<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Models\Sales;
use App\Models\User;
use App\Models\Stock;
use App\Models\Treatment;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Sales Information')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                DatePicker::make('transaction_date')
                                    ->label('Transaction Date')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),

                                Select::make('user_id')
                                    ->label('Sales Person')
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->default(fn () => auth()->user()->id)
                                    ->columnSpan(1),

                                Select::make('payment_method_name')
                                    ->label('Payment Method')
                                    ->options([
                                        'Cash' => 'Cash',
                                        'Credit Card' => 'Credit Card',
                                        'Debit Card' => 'Debit Card',
                                        'Online Transfer' => 'Online Transfer',
                                    ])
                                    ->required()
                                    ->default('Cash')
                                    ->columnSpan(1),
                            ]),
                    ])->columns(3),

                Section::make('Sales Items')
                    ->schema([
                        Forms\Components\Repeater::make('salesItems')
                            ->label('Products')
                            ->schema([
                                Select::make('stock_id')
                                    ->label('Product')
                                    ->options(Stock::where('qty', '>', 0)->pluck('item_name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $stock = Stock::find($state);
                                        if ($stock) {
                                            $set('price', $stock->sell_price);
                                            $set('max_qty', $stock->qty);
                                        }
                                    })
                                    ->columnSpan(3),

                                TextInput::make('qty')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->reactive()
                                    ->maxValue(fn ($get) => $get('max_qty') ?? 1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $price = $get('price') ?? 0;
                                        $totalItemPrice = (float)$state * (float)$price;  // Ensuring proper casting
                                        $set('total_item_price', $totalItemPrice);

                                        static::updateTotalPrice($get, $set);  // Update total price
                                    })
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->disabled()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $qty = $get('qty') ?? 0;
                                        $set('total_item_price', (float)$qty * (float)$state);  // Update total item price when price changes
                                        static::updateTotalPrice($get, $set);  // Update total price
                                    })
                                    ->columnSpan(1),

                                TextInput::make('total_item_price')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->disabled()
                                    ->reactive()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Product')
                            ->deleteAction(
                                fn ($action) => $action->label('Remove Product')
                            )
                            ->live(),
                    ]),

                Section::make('Treatment Services')
                    ->schema([
                        Forms\Components\Repeater::make('salesTreatments')
                            ->label('Treatments')
                            ->schema([
                                Select::make('treatment_id')
                                    ->label('Treatment')
                                    ->options(Treatment::all()->pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $treatment = Treatment::find($state);
                                        if ($treatment) {
                                            $set('unit_price', $treatment->treatment_price);
                                        }
                                    })
                                    ->columnSpan(3),

                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1)
                                    ->reactive()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $price = $get('unit_price') ?? 0;
                                        $totalTreatmentPrice = (float)$state * (float)$price;  // Ensuring proper casting
                                        $set('total_treatment_price', $totalTreatmentPrice);

                                        static::updateTotalPrice($get, $set);  // Update total price
                                    })
                                    ->columnSpan(1),

                                TextInput::make('unit_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->disabled()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        $qty = $get('quantity') ?? 0;
                                        $set('total_treatment_price', (float)$qty * (float)$state);  // Update total treatment price when price changes
                                        static::updateTotalPrice($get, $set);  // Update total price
                                    })
                                    ->columnSpan(1),

                                TextInput::make('total_treatment_price')
                                    ->label('Total Price')
                                    ->numeric()
                                    ->disabled()
                                    ->reactive()
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Add Treatment')
                            ->deleteAction(
                                fn ($action) => $action->label('Remove Treatment')
                            )
                            ->live(),
                    ]),

                Section::make('Total Sales')
                    ->schema([
                        TextInput::make('total_price')
                            ->label('Total Sales Amount')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->default(0),
                    ]),

                Section::make('Discount')
                    ->schema([
                        TextInput::make('discount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                static::updateTotalPrice($get, $set);  // Recalculate total price when discount changes
                            })
                            ->columnSpan(1),
                    ]),
            ]);
    }

    // Helper method to update the total price
    private static function updateTotalPrice($get, $set): void
    {
        // Ensure the values are cast to floats or integers to avoid string operations
        $totalItemsPrice = collect($get('salesItems') ?? [])->sum(function ($item) {
            return (float) ($item['qty'] ?? 0) * (float) ($item['price'] ?? 0);  // Ensure price and qty exist and are numeric
        });

        $totalTreatmentsPrice = collect($get('salesTreatments') ?? [])->sum(function ($treatment) {
            return (float) ($treatment['quantity'] ?? 0) * (float) ($treatment['unit_price'] ?? 0);  // Ensure unit_price and quantity exist and are numeric
        });

        // Ensure discount is a number
        $discount = (float) ($get('discount') ?? 0);  // Get discount amount (not percentage)
        
        // Total price before discount
        $totalPrice = $totalItemsPrice + $totalTreatmentsPrice;
        
        // Total price after discount
        $totalPriceAfterDiscount = $totalPrice - $discount;  // Deduct the discount amount

        // Set the total price and total price after discount
        $set('total_price', $totalPrice);  
        $set('total_price_after_discount', $totalPriceAfterDiscount);
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
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('user.name')->label('Sales Person'),
                TextColumn::make('payment_method_name')->label('Payment Method'),
                TextColumn::make('total_price')
                    ->label('Total Amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('total_price_after_discount')
                    ->label('Total After Discount')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')->label('From'),
                        DatePicker::make('end_date')->label('To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_date'],
                                fn ($query) => $query->whereDate('transaction_date', '>=', $data['start_date'])
                            )
                            ->when(
                                $data['end_date'],
                                fn ($query) => $query->whereDate('transaction_date', '<=', $data['end_date'])
                            );
                    })
            ])
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
                    ->label('Export Sales')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new SalesExport, 'sales_report.xlsx'))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
