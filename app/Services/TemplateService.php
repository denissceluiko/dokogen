<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Iris\Dokogen\Template;

class TemplateService
{
    public static $defaultDisk = 'templateDisk';

    public static function load(string $path, $disk = null) : ?Template
    {
        if (!self::disk($disk)->exists($path)) return null;

        return Template::load(self::disk($disk)->path($path));
    }

    public static function disk($disk = null) : Filesystem
    {
        return Storage::disk($disk ?? self::$defaultDisk);
    }
}
