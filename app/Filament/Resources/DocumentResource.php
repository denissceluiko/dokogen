<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                        foreach ($template->bindings['bindings'] as $value) {
                            $fields[] = TextInput::make($value);
                        }
                        return $fields;
                    }),
                    Wizard\Step::make('Fill tables')
                    ->schema(function (Get $get) {
                        $template = Template::find($get('template_id'));
                        if (!$template) return [];

                        $tables = [];
                        foreach ($template->bindings['rows'] as $tableName => $columns) {
                            $table = [];
                            foreach ($columns as $column) {
                                $table[] = TextInput::make($column);
                            }

                            $tables[] = Repeater::make($tableName)->schema($table)->columns(count($table));
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
                //
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
