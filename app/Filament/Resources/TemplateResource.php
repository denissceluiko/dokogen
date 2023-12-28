<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Filament\Resources\TemplateResource\RelationManagers;
use App\Models\Template;
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
                    ->disk('templateDisk')
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
                                return Storage::disk('templateDisk')->download($record->path, $record->name_uploaded);
                            })
                    ),
                Infolists\Components\TextEntry::make('bindings.bindings')
                    ->label('Fields')
                    ->limitList(5)
                    ->expandableLimitedList()
                    ->listWithLineBreaks(),
                Infolists\Components\RepeatableEntry::make('bindings.rows')
                    ->label('Tables')
                    ->schema(function (Template $record): array {
                        $tables = [];
                        foreach ($record->bindings['rows'] as $table) {
                            $rows = [];
                            foreach ($table as $col) {
                                $label = explode('.', $col);
                                $rows[] = TextEntry::make(array_pop($label));
                            }
                            $tables[] = Fieldset::make($table[0])->schema($rows)->columns(count($table));
                        }

                        return $tables;
                    })->columnSpanFull(),
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'view' => Pages\ViewTemplate::route('/{record}'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
