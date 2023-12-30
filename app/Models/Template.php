<?php

namespace App\Models;

use App\Services\TemplateService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Iris\Dokogen\Traits\HasTemplate;

class Template extends Model
{
    use HasFactory, HasTemplate;

    public static string $fieldStorage = 'template_fields';

    protected $fillable = [
        'owner_id', 'name', 'name_uploaded', 'path', 'hash', 'naming', 'template_fields',
    ];

    protected $casts = [
        'template_fields' => 'array',
    ];

    public function owner() : BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
