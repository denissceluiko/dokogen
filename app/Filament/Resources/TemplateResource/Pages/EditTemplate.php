<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use App\Models\Template;
use App\Services\TemplateService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }


    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // New template file
        if ($record->path !== $data['path']) {
            $template = TemplateService::load($data['path']);

            $data['hash'] = $template->hash();
            $data[Template::$fieldStorage] = $template->getFields()->blank();
        }

        $record->update($data);
        return $record;
    }
}
