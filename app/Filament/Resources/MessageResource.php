<?php

namespace App\Filament\Resources;

use App\Enums\MessageType;
use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Contact;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Select::make('device_id')
                        ->placeholder('Randomly')
                        ->relationship('device', 'name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('type')
                        ->options(MessageType::names())
                        ->native(false)
                        ->default(MessageType::PlainText->name),
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Schedule')
                        ->placeholder('Automatic')
                        ->nullable()
                        ->native(false),
                    Forms\Components\Select::make('contact_id')
                        ->relationship('contact', 'name')
                        ->searchable(),
                ])
                ->columns(2),
                static::getContentField($form->getState()),
            ]);
    }

    protected static function getContentField(array $state): Forms\Components\Component
    {
        return match($state['type'] ?? null) {
            // MessageType::Poll->name => static::getPollContentField(),
            // MessageType::Contact->name => static::getContactContentField(),
            // MessageType::List->name => static::getListContentField(),
            // MessageType::Buttons->name => static::getButtonsContentField(),
            // MessageType::Location->name => static::getLocationContentField(),
            // MessageType::MediaMessage->name => static::getMediaContentField(),
            MessageType::MediaFromURL->name => static::getMediaFromURLContentField(),
            default => static::getPlainTextContentField(),
        };
        return Forms\Components\Textarea::make('content')
            ->label('Content')
            ->rows(3)
            ->required();
    }

    // protected static function getMediaContentField(): Forms\Components\Component
    // {
    //     return Forms\Components\Media::make('content')
    //         ->label('Media')
    //         ->required();
    // }

    protected static function getMediaFromURLContentField(): Forms\Components\Component
    {
        return Forms\Components\TextInput::make('content')
            ->label('URL')
            ->required();
    }

    protected static function getPlainTextContentField(): Forms\Components\Component
    {
        return Forms\Components\Textarea::make('content')
            ->label('Content')
            ->rows(3)
            ->required();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contact')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListMessages::route('/'),
            'create' => Pages\CreateMessage::route('/create'),
            'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
