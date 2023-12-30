<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Filament\Resources\TemplateResource\RelationManagers;
use App\Models\Template;
use App\Services\TemplateService;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('naming')->required(),
                FileUpload::make('path')
                    ->storeFileNamesIn('name_uploaded')
                    ->disk(TemplateService::$defaultDisk)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('name_uploaded'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('naming'),
                Infolists\Components\TextEntry::make('name_uploaded')
                    ->label('Template file')
                    ->suffixAction(
                        Action::make('Download')
                            ->icon('heroicon-m-arrow-down-tray')
                            ->action(function(Template $record) {
                                return TemplateService::disk()->download($record->path, $record->name_uploaded);
                            })
                    ),
                Infolists\Components\TextEntry::make('fields.values')
                    ->label('Fields')
                    ->placeholder('Template has no fields.')
                    ->badge()
                    ->columns(2),
                Infolists\Components\RepeatableEntry::make('fields.tables')
                    ->columnSpan(2)
                    ->placeholder('Template has no tables.')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('columns')
                            ->badge(),
                    ])->columns(2),

                Infolists\Components\RepeatableEntry::make('fields.blocks')
                    ->columnSpan(2)
                    ->placeholder('Template has no blocks.')
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('fields')
                            ->badge(),
                    ])->columns(2),
            ])->state(array_merge($infolist->getState()->toArray(), [
                'fields' => [
                    'values' => $infolist->record->fields()->names()['values'],
                    'tables' => array_map(
                        fn ($table, $name) => ['columns' => $table, 'name' => $name],
                        $infolist->record->fields()->names()['tables'],
                        array_keys($infolist->record->fields()->names()['tables']),
                    ),
                    'blocks' => array_map(
                        fn ($block, $name) => ['fields' => $block, 'name' => $name],
                        $infolist->record->fields()->names()['blocks'],
                        array_keys($infolist->record->fields()->names()['blocks']),
                    ),
                ],
            ]))->columns(3);
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'view' => Pages\ViewTemplate::route('/{record}'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
