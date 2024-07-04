<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Filament\Resources\ContactResource\RelationManagers;
use App\Models\Contact;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->placeholder('John Doe'),
                Forms\Components\TextInput::make('number')
                    ->label('Number')
                    ->required()
                    ->placeholder('8801xxxxxxxxx')
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (!preg_match('/^8801\d{9}$/', $value)) {
                                $fail('The number must be a valid Bangladeshi mobile number starting with 8801.');
                            }
                        }
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('exists')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->action(function (Contact $record) {
                        $uuid = value(Filament::auth()->user())
                            ->devices()->where('status', 'connected')
                            ->inRandomOrder()->value('uuid');

                        $exists = Http::post(config('services.wsap.point') . '/check/' . $uuid, [
                            'phone' => $record->number,
                        ])->json('success');

                        $record->update(compact('exists'));

                        Notification::make()
                            ->title('Contact Exists in WhatsApp?')
                            ->color($exists ? 'success' : 'danger')
                            ->body($exists ? 'The contact exists in WhatsApp.' : 'The contact does not exist in WhatsApp.')
                            ->icon($exists ? 'heroicon-o-check' : 'heroicon-o-x-mark')
                            ->iconPosition(IconPosition::Before)
                            ->duration(3000)
                            ->send();
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth('md')
                    ->after(function (Contact $record) {
                        $uuid = value(Filament::auth()->user())
                            ->devices()->where('status', 'connected')
                            ->inRandomOrder()->value('uuid');

                        $record->update([
                            'exists' => Http::post(config('services.wsap.point') . '/check/' . $uuid, [
                                'phone' => $record->number,
                            ])->json('success'),
                        ]);
                    })
                    ->slideOver(),
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
            'index' => Pages\ListContacts::route('/'),
            // 'create' => Pages\CreateContact::route('/create'),
            // 'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
