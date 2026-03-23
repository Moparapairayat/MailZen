<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranslationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'translation_locale_id',
        'group',
        'key',
        'text',
    ];

    public function locale(): BelongsTo
    {
        return $this->belongsTo(TranslationLocale::class, 'translation_locale_id');
    }
}
