<?php

namespace App\Filament\Resources;

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
                Forms\Components\Select::make('device_id')
                    ->placeholder('Random')
                    ->relationship('device', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('contact_id')
                    ->relationship('contact', 'name')
                    ->getSearchResultsUsing(function (string $search): array {
                        return Contact::query()
                            ->where('name', 'like', "%$search%")
                            ->orWhere('number', 'like', "%$search%")
                            ->limit(10)->get()->mapWithKeys(fn (Contact $contact): array => [
                                $contact->getKey() => $contact->name . ' [' . $contact->number . ']',
                            ])->toArray();
                    })
                    ->getOptionLabelUsing(function ($value): ?string {
                        if (!$contact = Contact::find($value)) {
                            return null;
                        }

                        return $contact->name . ' [' . $contact->number . ']';
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Schedule')
                    ->placeholder('Now')
                    ->nullable()
                    ->native(false),
                Forms\Components\MarkdownEditor::make('content')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'blockquote',
                        'codeBlock',
                        'bulletList',
                        'orderedList',
                        'undo',
                        'redo',
                    ])
                    ->required(),
                
                \Awcodes\Curator\Components\Forms\CuratorPicker::make('media_id')
                    ->hiddenLabel()
                    ->outlined()
                    ->size('sm')
                    ->constrained()
                    ->preserveFilenames()
                    ->relationship('media', 'id'),
            ])
            ->columns(1);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contact')
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
                Tables\Actions\EditAction::make()
                    ->modalWidth('md')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['scheduled_at'] ??= now();
                        $data['status'] = 'scheduled';

                        return $data;
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
            'index' => Pages\ListMessages::route('/'),
            // 'create' => Pages\CreateMessage::route('/create'),
            // 'edit' => Pages\EditMessage::route('/{record}/edit'),
        ];
    }
}
