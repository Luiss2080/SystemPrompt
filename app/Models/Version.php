<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Version extends Model
{
    protected $table = 'versiones';

    protected $fillable = [
        'prompt_id',
        'numero',
        'contenido',
        'contenido_anterior',
        'motivo_cambio',
        'fecha_version'
    ];

    protected $casts = [
        'fecha_version' => 'datetime'
    ];

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }
}
