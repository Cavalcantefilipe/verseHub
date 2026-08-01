<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiblePassage extends Model
{
    protected $fillable = ['version', 'book_abbrev', 'book_name', 'chapter', 'verse_number', 'text'];
}
