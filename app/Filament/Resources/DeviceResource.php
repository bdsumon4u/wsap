<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use App\WSAP;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min_delay')
                    ->hint('seconds')
                    ->numeric()
                    ->required()
                    ->default(25)
                    ->minValue(25),
                Forms\Components\TextInput::make('max_delay')
                    ->hint('seconds')
                    ->numeric()
                    ->required()
                    ->default(50)
                    ->minValue(50),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('number'),
                Tables\Columns\TextColumn::make('min_delay'),
                Tables\Columns\TextColumn::make('max_delay'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md')
                    ->form(fn ($form) => static::form($form))
                    ->modalHeading('Edit Device')
                    ->slideOver(),
                Tables\Actions\Action::make('scan')
                    ->icon('heroicon-o-qr-code')
                    ->modal()
                    ->modalWidth('sm')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalHeading(fn ($record) => $record->name)
                    ->modalContent(fn ($record) => WSAP::qr($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            // 'create' => Pages\CreateDevice::route('/create'),
            // 'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
