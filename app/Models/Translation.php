<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'content',
        'locale',
    ];
    public function tags()
{
    return $this->belongsToMany(Tag::class, 'tag_translation');
}
}
