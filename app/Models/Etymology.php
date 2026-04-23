<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Etymology extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'word_id',
        'parent_word_id',
        'relation_type',
        'language_origin',
        'notes',
        'source',
    ];

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    public function parentWord(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'parent_word_id');
    }
}
