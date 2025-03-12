<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Exports\UsersExport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserResource extends Resource
{
   protected static ?string $model = User::class;
   protected static ?string $navigationIcon = 'heroicon-o-user-circle';
   public static function form(Forms\Form $form): Forms\Form
   {
       return $form
           ->schema([
               TextInput::make('name')
                   ->label('Name')
                   ->required()
                   ->maxLength(255),

               TextInput::make('email')
                   ->label('Email')
                   ->email()
                   ->required()
                   ->unique(ignoreRecord: true)
                   ->maxLength(255),

               TextInput::make('password')
                   ->label('Password')
                   ->password()
                   ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                   ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                   ->dehydrated(fn ($state) => filled($state))
                   ->maxLength(255),

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
                   ->label('Name')
                   ->searchable()
                   ->sortable(),
               TextColumn::make('email')
                   ->label('Email')
                   ->searchable()
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
                ->label('Export Users')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => Excel::download(new UsersExport, 'users_report.xlsx'))
        ]);
   }

   public static function getPages(): array
   {
       return [
           'index' => Pages\ListUsers::route('/'),
           'create' => Pages\CreateUser::route('/create'),
           'edit' => Pages\EditUser::route('/{record}/edit'),
       ];
   }

}