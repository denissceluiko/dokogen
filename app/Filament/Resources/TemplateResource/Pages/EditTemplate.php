<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
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
            $data['hash'] = TemplateService::hash($data['path'], 'templateDisk');
            $data['bindings'] = TemplateService::bindings($data['path'], 'templateDisk');
        }

        $record->update($data);
        return $record;
    }
}
