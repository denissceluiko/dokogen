<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Template;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $template = Template::find($data['template_id']);

        $template->fields()->fillValues($data);

        dd($data, $template->fields()->toArray());

        $document = Document::create($data);
        return $document;
    }
}
