<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class DocumentService
{
    public static $defaultDisk = 'documentDisk';

    public static function save(TemplateProcessor $processor, $disk = null) : string
    {
        return self::disk($disk)->putFile('', new File($processor->save()));
    }

    public static function disk($disk = null) : Filesystem
    {
        return Storage::disk($disk ?? self::$defaultDisk);
    }
}
