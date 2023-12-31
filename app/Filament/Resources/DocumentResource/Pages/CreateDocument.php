<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Template;
use App\Services\DocumentService;
use App\Services\TemplateService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $template = Template::find($data['template_id']);

        $template->template()->fill($data);

        $path = DocumentService::save($template->template()->compile());

        $data = [
            'template_id' => $data['template_id'],
            'template_data' => $template->template()->getFields()->toArray(),
            'path' => $path,
            'name' => $template->template()->populate($template->naming),
            'compiled_at' => now(),
        ];

        $document = Document::create($data);

        return $document;
    }
}
