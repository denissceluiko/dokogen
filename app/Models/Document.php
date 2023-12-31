<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'template_id', 'template_data', ];

    protected $casts = [
        'template_data' => 'array',
        'compiled_at' => 'datetime',
    ];

    public function template() : BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
