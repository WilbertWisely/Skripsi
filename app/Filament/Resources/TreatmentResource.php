<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatmentResource\Pages;
use App\Models\Treatment;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Exports\TreatmentsExport;
use Maatwebsite\Excel\Facades\Excel;

class TreatmentResource extends Resource
{
    protected static ?string $model = Treatment::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
 

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Treatment Name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('treatment_price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('IDR'),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'Hair' => 'Hair',
                        'Nail' => 'Nail',
                        'Facial' => 'Facial',
                    ])
                    ->required()
                    ->default('Hair'),
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
                TextColumn::make('name')
                    ->label('Treatment Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hair' => 'success',
                        'Nail' => 'warning',
                        'Facial' => 'info',
                        default => 'secondary',
                    }),
                TextColumn::make('treatment_price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('name')
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
                    ->label('Export Treatments')
                    ->color('success')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => Excel::download(new TreatmentsExport, 'treatments_report.xlsx'))
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreatments::route('/'),
            'create' => Pages\CreateTreatment::route('/create'),
            'edit' => Pages\EditTreatment::route('/{record}/edit'),
        ];
    }

}