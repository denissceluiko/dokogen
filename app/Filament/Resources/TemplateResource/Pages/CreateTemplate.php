<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use App\Services\TemplateService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->user()->id;
        $data['hash'] = TemplateService::hash($data['path'], 'templateDisk');
        $data['bindings'] = TemplateService::bindings($data['path'], 'templateDisk', json: true);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        dd($record);

        $data['hash'] = TemplateService::hash($data['path'], 'templateDisk');
        $data['bindings'] = TemplateService::bindings($data['path'], 'templateDisk', json: true);

        $record->update($data);
        return $record;
    }
}
