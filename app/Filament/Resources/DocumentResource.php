<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\Template;
use App\Services\DocumentService;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Select template')
                    ->schema([
                        Select::make('template_id')
                            ->label('Template')
                            ->searchable()
                            ->options(auth()->user()->templates()->pluck('name', 'id'))
                            ->live()
                    ]),
                    Wizard\Step::make('Fill values')
                    ->schema(function (Get $get) {
                        $template = Template::find($get('template_id'));
                        if (!$template) return [];

                        $fields = [];
                        foreach ($template->fields()->blank()['values'] as $key => $value) {
                            $fields[] = TextInput::make($key);
                        }
                        return $fields;
                    }),
                    Wizard\Step::make('Fill tables')
                    ->schema(function (Get $get) {
                        $template = Template::find($get('template_id'));
                        if (!$template) return [];

                        $tables = [];
                        foreach ($template->fields()->blank()['tables'] as $tableName => $columns) {
                            $table = [];
                            foreach ($columns as $column => $value) {
                                $table[] = TextInput::make($column);
                            }

                            $tables[] = Repeater::make($tableName)
                                ->schema($table)
                                ->cloneable()
                                ->defaultItems(3)
                                ->columns(count($table));
                        }
                        return $tables;
                    }),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('template.name'),
                Tables\Columns\TextColumn::make('created_at'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Download')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(function(Document $record) {
                        return DocumentService::disk()->download($record->path, $record->name);
                    }),
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
                TextEntry::make('name'),
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
