<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'items',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }
}
